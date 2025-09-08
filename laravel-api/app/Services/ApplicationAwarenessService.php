<?php

namespace App\Services;

use App\Models\ApplicationAwareness;
use Illuminate\Support\Arr;
use InvalidArgumentException;

class ApplicationAwarenessService
{
    /**
     * Create multiple awareness rows for a given applicant_data_id.
     *
     * @param int $applicantDataId
     * @param array $items Array of awareness items. Each item keys:
     *  - name (string, required)
     *  - sub_name (string|null)
     *  - referral (bool|int|string)
     *  - name_of_referee (string|null)
     *
     * @return array Inserted row IDs
     * @throws \Throwable on validation or DB failure (to allow transaction rollback)
     */
    public function createMany(int $applicantDataId, array $items): array
    {
        $insertedIds = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                throw new InvalidArgumentException('Awareness item must be an object/associative array.');
            }

            $name = trim((string) ($item['name'] ?? ''));
            if ($name === '') {
                throw new InvalidArgumentException('Awareness item name is required.');
            }

            // Allowed list (case-insensitive compare)
            $allowed = [
                'Google', 'Facebook', 'Instagram', 'Tiktok', 'News',
                'School Fair/Orientation', 'Billboard', 'Event', 'Referral', 'Others',
            ];
            $valid = false;
            foreach ($allowed as $opt) {
                if (strcasecmp($name, $opt) === 0) {
                    $name = $opt; // normalize casing to canonical label
                    $valid = true;
                    break;
                }
            }
            if (!$valid) {
                // Allow unknown names but truncate to 100 chars to fit schema; or choose to reject.
                // Here we accept to avoid blocking submissions from updated front-ends.
                $name = mb_substr($name, 0, 100);
            }

            $subName = Arr::get($item, 'sub_name');
            $subName = ($subName === null || $subName === '') ? null : mb_substr((string) $subName, 0, 255);

            $refFlagRaw = Arr::get($item, 'referral', false);
            $refFlag = $this->toBool($refFlagRaw);

            // If the name is "Referral", force referral to true
            if (strcasecmp($name, 'Referral') === 0) {
                $refFlag = true;
            }

            $referee = Arr::get($item, 'name_of_referee');
            $referee = ($referee === null || $referee === '') ? null : mb_substr((string) $referee, 0, 255);

            $model = ApplicationAwareness::create([
                'applicant_data_id' => $applicantDataId,
                'name' => $name,
                'sub_name' => $subName,
                'referral' => $refFlag ? 1 : 0,
                'name_of_referee' => $referee,
            ]);

            $insertedIds[] = $model->id;
        }

        return $insertedIds;
    }

    private function toBool($val): bool
    {
        if (is_bool($val)) return $val;
        if (is_int($val)) return $val !== 0;
        if (is_string($val)) {
            $v = strtolower(trim($val));
            return in_array($v, ['1', 'true', 'yes', 'y'], true);
        }
        return false;
    }
}
