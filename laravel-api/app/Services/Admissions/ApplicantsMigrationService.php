<?php

namespace App\Services\Admissions;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ApplicantsMigrationService
{
    protected string $sourceConnection;
    protected string $targetConnection;

    // Cached column listings
    protected ?array $sourceColumns = null;
    protected ?array $userColumns = null;
    protected ?array $applicantDataColumns = null;

    public function __construct(?string $sourceConnectionName = null, ?string $targetConnectionName = null)
    {
        $this->sourceConnection = $sourceConnectionName ?: config('database.default');
        $this->targetConnection = $targetConnectionName ?: config('database.default');
    }

    public function preflight(): array
    {
        $sourceTable = 'admission_student_information'; // singular per plan
        $usersTable = 'tb_mas_users';
        $appDataTable = 'tb_mas_applicant_data';

        $out = [
            'connections' => [
                'source' => $this->sourceConnection,
                'target' => $this->targetConnection,
            ],
            'source' => [
                'table' => $sourceTable,
                'exists' => Schema::connection($this->sourceConnection)->hasTable($sourceTable),
                'columns' => [],
                'required_columns' => ['id', 'email','status'],
                'missing' => [],
            ],
            'users' => [
                'table' => $usersTable,
                'exists' => Schema::connection($this->targetConnection)->hasTable($usersTable),
                'columns' => [],
                'required_columns' => ['intID', 'strEmail'],
                'missing' => [],
            ],
            'applicant_data' => [
                'table' => $appDataTable,
                'exists' => Schema::connection($this->targetConnection)->hasTable($appDataTable),
                'columns' => [],
                'required_columns' => ['user_id', 'data', 'status'],
                'missing' => [],
            ],
            'ok' => false,
        ];

        // Populate columns and missing lists
        if ($out['source']['exists']) {
            $out['source']['columns'] = $this->getSourceColumns();
            $out['source']['missing'] = array_values(array_diff($out['source']['required_columns'], $out['source']['columns']));
        }

        if ($out['users']['exists']) {
            $out['users']['columns'] = $this->getUserColumns();
            $out['users']['missing'] = array_values(array_diff($out['users']['required_columns'], $out['users']['columns']));
        }

        if ($out['applicant_data']['exists']) {
            $out['applicant_data']['columns'] = $this->getApplicantDataColumns();
            $out['applicant_data']['missing'] = array_values(array_diff($out['applicant_data']['required_columns'], $out['applicant_data']['columns']));
        }

        $out['ok'] =
            $out['source']['exists'] && empty($out['source']['missing']) &&
            $out['users']['exists'] && empty($out['users']['missing']) &&
            $out['applicant_data']['exists'] && empty($out['applicant_data']['missing']);

        return $out;
    }

    public function fetchSourceRows(?string $since = null, ?string $email = null, ?int $limit = null): Collection
    {
        $table = 'admission_student_information';
        $q = DB::connection($this->sourceConnection)->table($table);

        // Optional filters
        if ($email !== null && $email !== '') {
            $q->where('email', '=', trim($email));
        }

        $hasCreatedAt = $this->sourceHas('created_at');
        if ($since !== null && $hasCreatedAt) {
            // Accept YYYY-MM-DD or full datetime
            $dt = $this->parseDate($since);
            if ($dt) {
                $q->where('created_at', '>=', $dt->toDateTimeString());
            }
        }

        // Ordering
        if ($hasCreatedAt) {
            $q->orderByDesc('created_at')->orderByDesc('id');
        } else {
            if ($this->sourceHas('id')) {
                $q->orderByDesc('id');
            }
        }

        if ($limit !== null && $limit > 0) {
            $q->limit($limit);
        }

        return $q->get();
    }

    public function dedupeLatestPerEmail(Collection $rows): Collection
    {
        if ($rows->isEmpty()) {
            return collect();
        }

        $hasCreatedAt = $this->sourceHas('created_at');
        $sorted = $rows->sort(function ($a, $b) use ($hasCreatedAt) {
            $aTs = $hasCreatedAt ? $this->parseDate(Arr::get((array)$a, 'created_at')) : null;
            $bTs = $hasCreatedAt ? $this->parseDate(Arr::get((array)$b, 'created_at')) : null;

            // Sort by created_at desc then id desc
            if ($aTs && $bTs) {
                if ($aTs->equalTo($bTs)) {
                    return ((int)($b->id ?? 0)) <=> ((int)($a->id ?? 0));
                }
                return $bTs <=> $aTs;
            }

            // Fallback to id desc
            return ((int)($b->id ?? 0)) <=> ((int)($a->id ?? 0));
        });

        $byEmail = [];
        foreach ($sorted as $row) {
            $email = $this->sanitizeString($this->get($row, ['email', 'strEmail']));
            if ($email === null || $email === '') {
                // no email -> cannot match, skip
                continue;
            }
            $norm = mb_strtolower(trim($email));
            if (!array_key_exists($norm, $byEmail)) {
                $byEmail[$norm] = $row;
            }
        }

        return collect(array_values($byEmail));
    }

    public function resolveUserIdByEmail(string $email): ?int
    {
        $email = trim($email);
        if ($email === '') {
            return null;
        }

        $id = DB::connection($this->targetConnection)
            ->table('tb_mas_users')
            ->where('strEmail', '=', $email)
            ->value('intID');

        return $id ? (int)$id : null;
    }

    public function hasAnyApplicantData(int $userId): bool
    {
        $exists = DB::connection($this->targetConnection)
            ->table('tb_mas_applicant_data')
            ->where('user_id', '=', $userId)
            ->limit(1)
            ->exists();

        return (bool)$exists;
    }

    public function normalizeRow(object $row): array
    {
        $nowIso = Carbon::now()->toIso8601String();

        // Identity
        $first = $this->get($row, ['first_name', 'firstname', 'strFirstname', 'first', 'firstName']);
        $middle = $this->get($row, ['middle_name', 'middlename', 'strMiddlename', 'middle', 'middleName']);
        $last = $this->get($row, ['last_name', 'lastname', 'strLastname', 'last', 'lastName']);
        $email = $this->get($row, ['email', 'strEmail']);
        $mobile = $this->get($row, ['mobile_number', 'mobile', 'strMobileNumber', 'contact_number', 'contact']);
        $tel = $this->get($row, ['tel_number', 'phone', 'strPhoneNumber', 'telephone', 'landline']);
        $gender = $this->get($row, ['gender', 'sex']);
        $dob = $this->formatDateYmd($this->get($row, ['date_of_birth', 'dob', 'birthdate', 'birth_date']));

        // Address
        $address = $this->get($row, ['address', 'home_address', 'street_address']);
        $barangay = $this->get($row, ['barangay', 'brgy']);
        $city = $this->get($row, ['city', 'city_municipality']);
        $province = $this->get($row, ['province', 'state']);
        $country = $this->get($row, ['country']);

        // Application
        $syid = $this->toInt($this->get($row, ['syid', 'school_year_id', 'sy_id']));
        $campus = $this->get($row, ['campus']);
        $studentType = $this->get($row, ['student_type', 'stud_type', 'type']);
        $status = $this->get($row, ['status']);
        $typeId1 = $this->toInt($this->get($row, ['type_id', 'student_type_id']));
        $typeId2 = $this->toInt($this->get($row, ['type_id2']));
        $typeId3 = $this->toInt($this->get($row, ['type_id3']));
        $program = $this->get($row, ['program', 'course', 'desired_program']);
        $program2 = $this->get($row, ['program2', 'course2']);
        $program3 = $this->get($row, ['program3', 'course3']);
        $school = $this->get($row, ['school', 'hs_name', 'last_school']);
        $schoolId = $this->toInt($this->get($row, ['school_id', 'hs_id']));
        $slug = $this->get($row, ['slug']) ?: (string) Str::uuid();
        $referrer = $this->get($row, ['referrer', 'referral']);
        $source = $this->get($row, ['source']);

        // Parents / guardian
        $fatherName = $this->get($row, ['father_name']);
        $fatherContact = $this->get($row, ['father_contact']);
        $fatherEmail = $this->get($row, ['father_email']);
        $fatherOcc = $this->get($row, ['father_occupation']);

        $motherName = $this->get($row, ['mother_name']);
        $motherContact = $this->get($row, ['mother_contact']);
        $motherEmail = $this->get($row, ['mother_email']);
        $motherOcc = $this->get($row, ['mother_occupation']);

        $guardianName = $this->get($row, ['guardian_name']);
        $guardianContact = $this->get($row, ['guardian_contact']);
        $guardianEmail = $this->get($row, ['guardian_email']);
        $guardianOcc = $this->get($row, ['guardian_occupation']);

        // Health
        $goodMoral = $this->boolish($this->get($row, ['good_moral']));
        $crime = $this->boolish($this->get($row, ['crime']));
        $hospitalized = $this->boolish($this->get($row, ['hospitalized']));
        $hospitalizedReason = $this->get($row, ['hospitalized_reason']);
        $healthConcern = $this->get($row, ['health_concern']);
        $otherHealthConcern = $this->get($row, ['other_health_concern']);

        // Schedules and dates
        $bestTime = $this->get($row, ['best_time']);
        $scheduleDate = $this->get($row, ['schedule_date']);
        $scheduleTimeFrom = $this->get($row, ['schedule_time_from']);
        $scheduleTimeTo = $this->get($row, ['schedule_time_to']);
        $dateInterviewed = $this->formatDateYmd($this->get($row, ['date_interviewed']));
        $dateReserved = $this->formatDateYmd($this->get($row, ['date_reserved']));
        $dateEnrolled = $this->formatDateYmd($this->get($row, ['date_enrolled']));
        $dateWithdrawn = $this->formatDateYmd($this->get($row, ['date_withdrawn']));

        $json = [
            '_meta' => [
                'source_table' => 'admission_student_information',
                'source_pk' => $this->toInt($this->get($row, ['id'])),
                'imported_at' => $nowIso,
                'import_version' => 'asi_to_applicant_data_v1',
            ],
            'identity' => [
                'first_name' => $this->sanitizeString($first),
                'middle_name' => $this->sanitizeString($middle),
                'last_name' => $this->sanitizeString($last),
                'email' => $this->sanitizeString($email),
                'mobile_number' => $this->sanitizeString($mobile),
                'tel_number' => $this->sanitizeString($tel),
                'gender' => $this->sanitizeString($gender),
                'date_of_birth' => $dob,
            ],
            'address' => [
                'address' => $this->sanitizeString($address),
                'barangay' => $this->sanitizeString($barangay),
                'city' => $this->sanitizeString($city),
                'province' => $this->sanitizeString($province),
                'country' => $this->sanitizeString($country),
            ],
            'application' => [
                'syid' => $syid,
                'campus' => $this->sanitizeString($campus),
                'student_type' => $this->sanitizeString($studentType),
                'status' => $this->sanitizeString($status), // preserve original source status
                'type_id' => $typeId1,
                'type_id2' => $typeId2,
                'type_id3' => $typeId3,
                'program' => $this->sanitizeString($program),
                'program2' => $this->sanitizeString($program2),
                'program3' => $this->sanitizeString($program3),
                'school' => $this->sanitizeString($school),
                'school_id' => $schoolId,
                'slug' => $slug,
                'referrer' => $this->sanitizeString($referrer),
                'source' => $this->sanitizeString($source),
            ],
            'parents_guardian' => [
                'father_name' => $this->sanitizeString($fatherName),
                'father_contact' => $this->sanitizeString($fatherContact),
                'father_email' => $this->sanitizeString($fatherEmail),
                'father_occupation' => $this->sanitizeString($fatherOcc),

                'mother_name' => $this->sanitizeString($motherName),
                'mother_contact' => $this->sanitizeString($motherContact),
                'mother_email' => $this->sanitizeString($motherEmail),
                'mother_occupation' => $this->sanitizeString($motherOcc),

                'guardian_name' => $this->sanitizeString($guardianName),
                'guardian_contact' => $this->sanitizeString($guardianContact),
                'guardian_email' => $this->sanitizeString($guardianEmail),
                'guardian_occupation' => $this->sanitizeString($guardianOcc),
            ],
            'health' => [
                'good_moral' => $goodMoral,
                'crime' => $crime,
                'hospitalized' => $hospitalized,
                'hospitalized_reason' => $this->sanitizeString($hospitalizedReason),
                'health_concern' => $this->sanitizeString($healthConcern),
                'other_health_concern' => $this->sanitizeString($otherHealthConcern),
            ],
            'schedules' => [
                'best_time' => $this->sanitizeString($bestTime),
                'schedule_date' => $this->sanitizeString($scheduleDate),
                'schedule_time_from' => $this->sanitizeString($scheduleTimeFrom),
                'schedule_time_to' => $this->sanitizeString($scheduleTimeTo),
                'date_interviewed' => $dateInterviewed,
                'date_reserved' => $dateReserved,
                'date_enrolled' => $dateEnrolled,
                'date_withdrawn' => $dateWithdrawn,
            ],
        ];

        return $json;
    }

    public function insertSnapshot(int $userId, array $json, $row,): void
    {
        $payload = [
            'user_id' => $userId,
            'data' => json_encode($json, JSON_UNESCAPED_UNICODE),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        // Optional waiver fields
        if ($this->applicantDataHas('waive_application_fee')) {
            $waiveApp = null;
            if (is_object($row)) {
                if (property_exists($row, 'waive_app_fee')) {
                    $waiveApp = $this->boolish($row->waive_app_fee);
                } elseif (property_exists($row, 'waive_application_fee')) {
                    $waiveApp = $this->boolish($row->waive_application_fee);
                }
            }
            if ($waiveApp !== null) {
                $payload['waive_application_fee'] = $waiveApp;
            }
        }

        if ($this->applicantDataHas('waive_reason')) {
            $waiveReason = is_object($row) && property_exists($row, 'waive_reason') ? $this->sanitizeString($row->waive_reason) : null;
            if ($waiveReason !== null) {
                $payload['waive_reason'] = $waiveReason;
            }
        }

        if ($row->status !== null && $this->applicantDataHas('status')) {
            $payload['status'] = $row->status;
        }

        if ($row->syid !== null && $this->applicantDataHas('syid')) {
            $payload['syid'] = $row->syid;
        }

        DB::connection($this->targetConnection)
            ->table('tb_mas_applicant_data')
            ->insert($payload);
    }

    // ============== Helpers ==============

    protected function getSourceColumns(): array
    {
        if ($this->sourceColumns === null) {
            if (Schema::connection($this->sourceConnection)->hasTable('admission_student_information')) {
                $this->sourceColumns = Schema::connection($this->sourceConnection)->getColumnListing('admission_student_information');
            } else {
                $this->sourceColumns = [];
            }
        }
        return $this->sourceColumns;
    }

    protected function getUserColumns(): array
    {
        if ($this->userColumns === null) {
            if (Schema::connection($this->targetConnection)->hasTable('tb_mas_users')) {
                $this->userColumns = Schema::connection($this->targetConnection)->getColumnListing('tb_mas_users');
            } else {
                $this->userColumns = [];
            }
        }
        return $this->userColumns;
    }

    protected function getApplicantDataColumns(): array
    {
        if ($this->applicantDataColumns === null) {
            if (Schema::connection($this->targetConnection)->hasTable('tb_mas_applicant_data')) {
                $this->applicantDataColumns = Schema::connection($this->targetConnection)->getColumnListing('tb_mas_applicant_data');
            } else {
                $this->applicantDataColumns = [];
            }
        }
        return $this->applicantDataColumns;
    }

    protected function sourceHas(string $column): bool
    {
        return in_array($column, $this->getSourceColumns(), true);
    }

    protected function applicantDataHas(string $column): bool
    {
        return in_array($column, $this->getApplicantDataColumns(), true);
    }

    protected function parseDate($value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }
        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function formatDateYmd($value): ?string
    {
        $dt = $this->parseDate($value);
        return $dt ? $dt->toDateString() : null;
    }

    protected function get(object $row, array $candidates)
    {
        foreach ($candidates as $key) {
            if (is_object($row) && property_exists($row, $key)) {
                return $row->{$key};
            }
        }
        return null;
    }

    protected function sanitizeString($value): ?string
    {
        if ($value === null) {
            return null;
        }
        if (is_numeric($value)) {
            $value = (string)$value;
        }
        if (!is_string($value)) {
            return null;
        }
        $v = trim($value);
        return $v === '' ? null : $v;
    }

    protected function toInt($value): ?int
    {
        if ($value === null) return null;
        if ($value === '') return null;
        if (!is_numeric($value)) return null;
        return (int)$value;
    }

    protected function boolish($value)
    {
        if ($value === null) return null;
        if (is_bool($value)) return $value;
        if (is_numeric($value)) return ((int)$value) !== 0;
        if (is_string($value)) {
            $v = strtolower(trim($value));
            if ($v === 'yes' || $v === 'true' || $v === 'y' || $v === '1') return true;
            if ($v === 'no' || $v === 'false' || $v === 'n' || $v === '0') return false;
        }
        return null;
    }

    // Create user in tb_mas_users if missing using available columns
    public function createOrGetUserIdFromRow(object $row): ?int
    {
        $email = $this->sanitizeString($this->get($row, ['email', 'strEmail']));
        if ($email === null || $email === '') {
            return null;
        }

        // Return existing if found
        $existing = DB::connection($this->targetConnection)
            ->table('tb_mas_users')
            ->where('strEmail', '=', $email)
            ->value('intID');
        if ($existing) {
            return (int) $existing;
        }

        $cols = $this->getUserColumns();
        $now = Carbon::now();

        $first = $this->sanitizeString($this->get($row, ['first_name', 'firstname', 'strFirstname', 'first', 'firstName']));
        $middle = $this->sanitizeString($this->get($row, ['middle_name', 'middlename', 'strMiddlename', 'middle', 'middleName']));
        $last = $this->sanitizeString($this->get($row, ['last_name', 'lastname', 'strLastname', 'last', 'lastName']));
        $gender = $this->sanitizeString($this->get($row, ['gender', 'sex']));
        $dob = $this->formatDateYmd($this->get($row, ['date_of_birth', 'dob', 'birthdate', 'birth_date']));
        $mobile = $this->sanitizeString($this->get($row, ['mobile_number', 'mobile', 'strMobileNumber', 'contact_number', 'contact']));
        if($this->get($row, ['campus']) == "Cebu")
            $campus_id = 2;
        else
            $campus_id = 1;
        $campus = $this->sanitizeString($this->get($row, ['campus']));
        $campusId = $this->toInt($campus_id);
        $studentType = $this->sanitizeString($this->get($row, ['student_type', 'stud_type', 'type']));
        $typeId = $this->toInt($this->get($row, ['type_id', 'student_type_id']));

        // Address compose
        $address = $this->sanitizeString($this->get($row, ['address', 'home_address', 'street_address']));
        $city = $this->sanitizeString($this->get($row, ['city', 'city_municipality']));
        $province = $this->sanitizeString($this->get($row, ['province', 'state']));
        $country = $this->sanitizeString($this->get($row, ['country']));
        $high_school = $this->sanitizeString($this->get($row, ['high_school']));;
        $high_school_address = $this->sanitizeString($this->get($row, ['high_school_address']));;
        $high_school_attended = $this->sanitizeString($this->get($row, ['high_school_attended']));;
        $senior_high = $this->sanitizeString($this->get($row, ['senior_high']));;
        $senior_high_address = $this->sanitizeString($this->get($row, ['senior_high_address']));;
        $senior_high_attended = $this->sanitizeString($this->get($row, ['senior_high_attended']));;
        $strand = $this->sanitizeString($this->get($row, ['strand']));;
        $addressParts = array_filter([$address, $city, $province, $country], function ($v) {
            return !empty($v);
        });
        $addressString = implode(', ', $addressParts);

        $data = [];        

        if (in_array('strEmail', $cols)) $data['strEmail'] = $email;
        if (in_array('strFirstname', $cols) && $first !== null) $data['strFirstname'] = $first;
        if (in_array('strMiddlename', $cols) && $middle !== null) $data['strMiddlename'] = $middle;
        if (in_array('strLastname', $cols) && $last !== null) $data['strLastname'] = $last;

        if (in_array('high_school', $cols) && $high_school !== null) $data['high_school'] = $high_school; else $data['high_school'] = "";
        if (in_array('high_school_address', $cols) && $high_school_address !== null) $data['high_school_address'] = $high_school_address; else $data['high_school_address'] = "";
        if (in_array('high_school_attended', $cols) && $high_school_attended !== null) $data['strLastname'] = $high_school_attended; else $data['high_school_attended'] = "";
        if (in_array('senior_high', $cols) && $senior_high !== null) $data['senior_high'] = $senior_high; else $data['senior_high'] = "";
        if (in_array('senior_high_address', $cols) && $senior_high_address !== null) $data['senior_high_address'] = $senior_high_address; else $data['senior_high_address'] = "";
        if (in_array('senior_high_attended', $cols) && $senior_high_attended !== null) $data['senior_high_attended'] = $senior_high_attended; else $data['senior_high_attended'] = "";
        if (in_array('strand', $cols) && $strand !== null) $data['strand'] = $strand; else $data['strand'] = "";

        if (in_array('enumGender', $cols) && $gender !== null) $data['enumGender'] = $gender;
        if (in_array('dteBirthDate', $cols) && $dob !== null) $data['dteBirthDate'] = $dob;
        if (in_array('strMobileNumber', $cols) && $mobile !== null) $data['strMobileNumber'] = $mobile;

        if (in_array('campus', $cols) && $campus !== null) $data['campus'] = $campus;
        if (in_array('campus_id', $cols) && $campusId !== null) $data['campus_id'] = $campusId;

        if (in_array('strAddress', $cols) && $addressString !== '') $data['strAddress'] = $addressString; else $data['strAddress'] = "";

        if (in_array('student_type', $cols) && $studentType !== null) $data['student_type'] = $studentType;
        if (in_array('intProgramID', $cols) && $typeId !== null) $data['intProgramID'] = $typeId; else $data['intProgramID'] = 19;

        if (in_array('dteCreated', $cols)) $data['dteCreated'] = $now->format('Y-m-d');

        // Username/Password placeholders if columns exist
        if (in_array('strUsername', $cols)) {
            $data['strUsername'] = $this->makeUsername($email, $first, $last);
        }
        if (in_array('strPass', $cols)) {
            $data['strPass'] = bcrypt(Str::random(12));
        }

        $data['intTuitionYear'] = 1;

        // // Status columns
        // if (in_array('student_status', $cols)) {
        //     $data['student_status'] = 'applicant';
        // } elseif (in_array('enumEnrolledStatus', $cols)) {
        //     $data['enumEnrolledStatus'] = 'applicant';
        // }
        
        // Slug
        if (in_array('slug', $cols)) {
            $data['slug'] = $this->makeUniqueSlug($first, $last);
        }        
        try {
            $id = DB::connection($this->targetConnection)
                ->table('tb_mas_users')
                ->insertGetId($data);            
            return (int) $id;
        } catch (\Throwable $e) {          
            echo "Caught exception: " . $e->getMessage();   
            return null;                       
        }
    }

    protected function makeUsername(?string $email, ?string $first, ?string $last): string
    {
        if ($email && strpos($email, '@') !== false) {
            return substr($email, 0, strpos($email, '@'));
        }
        $base = trim((Str::slug($first ?? '') ?: '') . '.' . (Str::slug($last ?? '') ?: ''), '.');
        if ($base === '') {
            $base = 'applicant';
        }
        return $base . '.' . Str::lower(Str::random(6));
    }

    protected function makeUniqueSlug(?string $first, ?string $last): string
    {
        $base = Str::slug(trim(($first ?? '') . ' ' . ($last ?? '')));
        if ($base === '') {
            $base = 'applicant';
        }
        $slug = $base;
        $i = 1;
        while (
            DB::connection($this->targetConnection)
                ->table('tb_mas_users')
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '-' . $i;
            $i++;
            if ($i > 1000) {
                $slug = $base . '-' . Str::lower(Str::random(6));
                break;
            }
        }
        return $slug;
    }
}
