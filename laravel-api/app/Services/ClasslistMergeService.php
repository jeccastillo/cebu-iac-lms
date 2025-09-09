<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClasslistMergeService
{
    /**
     * Merge multiple source classlists into a target classlist.
     *
     * Rules:
     * - All involved classlists must be in the same term (strAcademicYear).
     * - All involved classlists must have intFinalized = 0.
     * - If a student already exists in target (same intStudentID + intsyID), skip.
     * - After processing a source, set isDissolved = 1 on the source classlist.
     *
     * @param int $targetId
     * @param array $sourceIds
     * @param int|null $actorId
     * @param Request $request
     * @return array Summary result
     */
    public function merge(int $targetId, array $sourceIds, ?int $actorId, Request $request): array
    {
        $summary = [
            'moved' => 0,
            'skipped' => 0,
            'dissolved_sources' => 0,
            'details' => [],
            'errors' => [],
        ];

        $target = DB::table('tb_mas_classlist')->where('intID', $targetId)->first();
        if (!$target) {
            throw new \InvalidArgumentException('Target classlist not found');
        }
        $term = (int) ($target->strAcademicYear ?? 0);
        $targetFinalized = (int) ($target->intFinalized ?? 0);
        if ($targetFinalized !== 0) {
            throw new \InvalidArgumentException('Target classlist is finalized');
        }

        // De-duplicate and filter invalid source IDs; exclude target itself
        $sourceIds = array_values(array_unique(array_map('intval', $sourceIds)));
        $sourceIds = array_filter($sourceIds, function ($id) use ($targetId) {
            return $id > 0 && $id !== $targetId;
        });
        if (empty($sourceIds)) {
            throw new \InvalidArgumentException('No valid source classlists provided');
        }

        foreach ($sourceIds as $sid) {
            $detail = [
                'source_id' => $sid,
                'moved' => 0,
                'skipped' => 0,
                'errors' => [],
            ];

            $src = DB::table('tb_mas_classlist')->where('intID', $sid)->first();
            if (!$src) {
                $detail['errors'][] = ['code' => 'SOURCE_NOT_FOUND', 'message' => 'Source classlist not found'];
                $summary['details'][] = $detail;
                continue;
            }

            // Validate term
            $srcTerm = (int) ($src->strAcademicYear ?? 0);
            if ($srcTerm !== $term) {
                $detail['errors'][] = ['code' => 'TERM_MISMATCH', 'message' => 'Source classlist term does not match target'];
                $summary['details'][] = $detail;
                continue;
            }

            // Validate finalized state
            $srcFinalized = (int) ($src->intFinalized ?? 0);
            if ($srcFinalized !== 0) {
                $detail['errors'][] = ['code' => 'FINALIZED', 'message' => 'Source classlist is finalized'];
                $summary['details'][] = $detail;
                continue;
            }

            DB::beginTransaction();
            try {
                // Fetch all source enrollments scoped to the same term
                $rows = DB::table('tb_mas_classlist_student')
                    ->where('intClassListID', $sid)
                    ->where('intsyID', $term)
                    ->get();

                foreach ($rows as $r) {
                    $studentId = (int) $r->intStudentID;

                    // Skip if duplicate exists in target for same term
                    $exists = DB::table('tb_mas_classlist_student')
                        ->where('intClassListID', $targetId)
                        ->where('intStudentID', $studentId)
                        ->where('intsyID', $term)
                        ->exists();

                    if ($exists) {
                        $detail['skipped']++;
                        $summary['skipped']++;
                        continue;
                    }

                    // Update the classlist id to target
                    $old = (array) $r;
                    DB::table('tb_mas_classlist_student')
                        ->where('intCSID', $r->intCSID)
                        ->update(['intClassListID' => $targetId]);

                    $detail['moved']++;
                    $summary['moved']++;

                    // Best-effort logging
                    try {
                        \App\Services\SystemLogService::log(
                            'update',
                            'ClasslistStudent',
                            (int) $r->intCSID,
                            $old,
                            array_merge($old, ['intClassListID' => $targetId]),
                            $request
                        );
                    } catch (\Throwable $e) {
                        // swallow logging errors
                    }
                }

                // Dissolve source (idempotent)
                $oldCl = (array) $src;
                DB::table('tb_mas_classlist')->where('intID', $sid)->update(['isDissolved' => 1]);
                $summary['dissolved_sources']++;

                // Log dissolve
                try {
                    $newCl = DB::table('tb_mas_classlist')->where('intID', $sid)->first();
                    \App\Services\SystemLogService::log(
                        'update',
                        'Classlist',
                        (int) $sid,
                        $oldCl,
                        (array) $newCl,
                        $request
                    );
                } catch (\Throwable $e) {
                    // swallow logging errors
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                $detail['errors'][] = ['code' => 'EXCEPTION', 'message' => $e->getMessage()];
            }

            $summary['details'][] = $detail;
        }

        return $summary;
    }
}
