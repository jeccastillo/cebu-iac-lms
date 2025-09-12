<?php

namespace App\Services;

use App\Models\ClinicAttachment;
use App\Models\ClinicVisit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ClinicVisitService
{
    /**
     * Create a clinic visit.
     *
     * @param array $payload
     * @return ClinicVisit
     */
    public function create(array $payload): ClinicVisit
    {
        $normalized = $this->normalizeVisitPayload($payload);

        $visit = new ClinicVisit();
        $visit->fill($normalized);
        if (empty($visit->visit_date)) {
            $visit->visit_date = now();
        }
        $visit->save();

        return $visit;
    }

    /**
     * Update a clinic visit by id.
     *
     * @param int $id
     * @param array $payload
     * @return ClinicVisit
     */
    public function update(int $id, array $payload): ClinicVisit
    {
        /** @var ClinicVisit $visit */
        $visit = ClinicVisit::findOrFail($id);

        $normalized = $this->normalizeVisitPayload($payload, false);
        $visit->fill($normalized);
        $visit->save();

        return $visit;
    }

    /**
     * List visits by record with optional date range + pagination.
     *
     * @param int $recordId
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function listByRecord(int $recordId, array $filters = [], int $page = 1, int $perPage = 20): LengthAwarePaginator
    {
        $page = max(1, $page);
        $perPage = min(max(1, $perPage), 100);

        $q = ClinicVisit::query()->where('record_id', $recordId);

        if (!empty($filters['date_from'])) {
            $q->where('visit_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $q->where('visit_date', '<=', $filters['date_to']);
        }

        $q->orderBy('visit_date', 'desc')->orderBy('id', 'desc');

        return $q->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Add an attachment (metadata only; file handling done at controller level).
     *
     * @param array $fileMeta
     * @return ClinicAttachment
     */
    public function addAttachment(array $fileMeta): ClinicAttachment
    {
        $attachment = new ClinicAttachment();
        $attachment->fill([
            'record_id'     => $fileMeta['record_id'] ?? null,
            'visit_id'      => $fileMeta['visit_id'] ?? null,
            'original_name' => $fileMeta['original_name'] ?? '',
            'path'          => $fileMeta['path'] ?? '',
            'mime'          => $fileMeta['mime'] ?? '',
            'size_bytes'    => $fileMeta['size_bytes'] ?? 0,
            'uploaded_by'   => $fileMeta['uploaded_by'] ?? null,
        ]);
        $attachment->save();

        // If tied to a visit, sync attachments_count
        if (!empty($attachment->visit_id)) {
            $count = ClinicAttachment::where('visit_id', $attachment->visit_id)->count();
            ClinicVisit::where('id', $attachment->visit_id)->update(['attachments_count' => $count]);
        }

        return $attachment;
    }

    /**
     * List attachments by record or visit.
     *
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function listAttachments(array $filters, int $page = 1, int $perPage = 20): LengthAwarePaginator
    {
        $page = max(1, $page);
        $perPage = min(max(1, $perPage), 100);

        $q = ClinicAttachment::query();

        if (!empty($filters['record_id'])) {
            $q->where('record_id', (int) $filters['record_id']);
        }
        if (!empty($filters['visit_id'])) {
            $q->where('visit_id', (int) $filters['visit_id']);
        }

        $q->orderBy('id', 'desc');

        return $q->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Normalize JSON fields and conditionally allow nulls for update.
     *
     * @param array $payload
     * @param bool $requireRecordId
     * @return array
     */
    protected function normalizeVisitPayload(array $payload, bool $requireRecordId = true): array
    {
        if ($requireRecordId && empty($payload['record_id'])) {
            throw new \InvalidArgumentException('record_id is required');
        }

        $normalized = [];

        // Required link
        if (isset($payload['record_id'])) {
            $normalized['record_id'] = (int) $payload['record_id'];
        }

        // Optional scalars
        foreach ([
            'reason', 'assessment', 'treatment', 'follow_up'
        ] as $key) {
            if (array_key_exists($key, $payload)) {
                $normalized[$key] = $payload[$key];
            }
        }

        if (array_key_exists('campus_id', $payload)) {
            $normalized['campus_id'] = $payload['campus_id'] !== null ? (int) $payload['campus_id'] : null;
        }
        if (array_key_exists('created_by', $payload)) {
            $normalized['created_by'] = (int) $payload['created_by'];
        }
        if (array_key_exists('updated_by', $payload)) {
            $normalized['updated_by'] = $payload['updated_by'] !== null ? (int) $payload['updated_by'] : null;
        }
        if (array_key_exists('attachments_count', $payload)) {
            $normalized['attachments_count'] = (int) $payload['attachments_count'];
        }

        if (!empty($payload['visit_date'])) {
            $normalized['visit_date'] = $payload['visit_date'];
        }

        // JSON arrays
        foreach (['triage', 'diagnosis_codes', 'medications_dispensed'] as $jsonField) {
            if (array_key_exists($jsonField, $payload)) {
                $value = $payload[$jsonField];
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $value = $decoded;
                    }
                }
                if ($value !== null && !is_array($value)) {
                    $value = null;
                }
                $normalized[$jsonField] = $value;
            }
        }

        return $normalized;
    }
}
