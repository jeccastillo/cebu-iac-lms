<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ApplicantJourneyService
{
    /**
     * Insert an applicant journey log row.
     *
     * @param int $applicantDataId tb_mas_applicant_data.id
     * @param string $remarks Human-readable message (e.g., "Student Applied")
     * @param mixed $logDate Optional datetime (Carbon|string|\DateTimeInterface); defaults to now()
     */
    public function log(int $applicantDataId, string $remarks, $logDate = null): void
    {
        try {
            if ($applicantDataId <= 0) {
                return;
            }

            $dt = $this->normalizeDateTime($logDate);

            DB::table('tb_mas_applicant_journey')->insert([
                'applicant_data_id' => $applicantDataId,
                'remarks'           => $remarks,
                'log_date'          => $dt->toDateTimeString(),
            ]);
        } catch (\Throwable $e) {
            // Non-blocking: swallow logging failures
        }
    }

    /**
     * Normalize various datetime inputs into a Carbon instance.
     *
     * @param mixed $value
     */
    protected function normalizeDateTime($value): Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance(\DateTime::createFromInterface($value));
        }
        if ($value === null || $value === '') {
            return Carbon::now();
        }
        $ts = strtotime((string) $value);
        if ($ts === false) {
            return Carbon::now();
        }
        return Carbon::createFromTimestamp($ts);
    }
}
