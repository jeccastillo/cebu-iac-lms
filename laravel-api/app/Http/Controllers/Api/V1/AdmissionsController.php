<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class AdmissionsController extends Controller
{
    /**
     * POST /api/v1/admissions/student-info
     * - Inserts core fields into tb_mas_users with status "applicant"
     * - Persists all extra fields into tb_mas_applicant_data (JSON)
     * - Sends a confirmation email to the applicant
     * - Returns success response with slug (if present) and user_id
     */
    public function store(Request $request)
    {
        // Basic validation (extend as needed)
        $validated = $request->validate([
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'required|string|max:255',
            'email'       => 'required|email',
            'mobile_number' => 'nullable|string|max:50',
            'gender'      => 'nullable|string|max:50',
            'date_of_birth' => 'required|date',
            'citizenship_country' => 'required|string|max:100',
            'intTuitionYear' => 'int',
            // Require at least one awareness item
            'awareness' => 'required|array|min:1',
            'awareness.*.name' => 'required|string|max:255',
            'awareness.*.sub_name' => 'nullable|string|max:255',
            'awareness.*.referral' => 'sometimes|boolean',
            'awareness.*.name_of_referee' => 'nullable|string|max:255',
        ]);

        $now = Carbon::now();

        // Detect available columns on tb_mas_users to map safely
        $userColumns = Schema::getColumnListing('tb_mas_users');

        // Build core user data with column checks
        $userData = [];

        // Names
        if (in_array('strFirstname', $userColumns)) {
            $userData['strFirstname'] = $request->input('first_name');
        }
        if (in_array('strMiddlename', $userColumns) && $request->filled('middle_name')) {
            $userData['strMiddlename'] = $request->input('middle_name');
        }
        if (in_array('strLastname', $userColumns)) {
            $userData['strLastname'] = $request->input('last_name');
        }

        // Email
        if (in_array('strEmail', $userColumns)) {
            $userData['strEmail'] = $request->input('email');
        }

        // Campus
        if (in_array('campus_id', $userColumns)) {
            $userData['campus_id'] = $request->input('campus_id');
        }

        // Mobile
        if (in_array('strMobileNumber', $userColumns) && $request->filled('mobile_number')) {
            $userData['strMobileNumber'] = $request->input('mobile_number');
        }

        // Gender
        if (in_array('enumGender', $userColumns) && $request->filled('gender')) {
            $userData['enumGender'] = $request->input('gender');
        }        

        // Birthdate
        if (in_array('dteBirthDate', $userColumns) && $request->filled('date_of_birth')) {
            $userData['dteBirthDate'] = Carbon::parse($request->input('date_of_birth'))->format('Y-m-d');
        }

        // Tuition
        if (in_array('intTuitionYear', $userColumns) && $request->filled('intTuitionYear')) {
            $userData['intTuitionYear'] = $request->input('intTuitionYear');
        }

        // Citizenship / Nationality
        if ($request->filled('citizenship_country')) {
            $valCit = $request->input('citizenship_country');
            $citCols = ['citizenship', 'nationality', 'country_of_citizenship', 'strCitizenship', 'strNationality'];
            foreach ($citCols as $col) {
                if (in_array($col, $userColumns)) {
                    $userData[$col] = $valCit;
                    break;
                }
            }
        }        
        

        // Address (combine pieces if provided)
        $fullAddress = $request->input('address');
        $city = $request->input('city');
        $province = $request->input('state') ?: $request->input('province');
        $country = $request->input('country');
        $addressParts = array_filter([$fullAddress, $city, $province, $country], function ($v) {
            return !empty($v);
        });
        $addressString = implode(', ', $addressParts);
        if (in_array('strAddress', $userColumns) && !empty($addressString)) {
            $userData['strAddress'] = $addressString;
        }

        // Program mapping (if provided)
        // Frontend sends: type_id (program id), type (group), program (title)
        $typeId = $request->input('type_id');
        if (in_array('intProgramID', $userColumns) && is_numeric($typeId)) {
            $userData['intProgramID'] = (int)$typeId;
            $program = Program::find($typeId);
            $userData['intCurriculumID'] = $program->default_curriculum;
        }

        // Student type (e.g., 'College - Freshmen Other', 'SHS - New', etc.)
        if (in_array('student_type', $userColumns) && $request->filled('student_type')) {
            $userData['student_type'] = $request->input('student_type');
        }

        // Created date
        if (in_array('dteCreated', $userColumns)) {
            $userData['dteCreated'] = $now->format('Y-m-d');
        }

        // Username/Password placeholders if columns exist and required by schema
        if (in_array('strUsername', $userColumns) && empty($userData['strUsername'] ?? null)) {
            // Use email without domain or a generated username
            $userData['strUsername'] = $this->makeUsername($request->input('email'), $request->input('first_name'), $request->input('last_name'));
        }
        if (in_array('strPass', $userColumns) && empty($userData['strPass'] ?? null)) {
            // Store a placeholder hashed password to satisfy NOT NULL if any (not used for auth here)
            $userData['strPass'] = bcrypt(Str::random(12));
        }

        // Status fields
        // Prefer modern 'student_status' if present, else fall back to legacy 'enumEnrolledStatus'
        if (in_array('student_status', $userColumns)) {
            $userData['student_status'] = 'applicant';
        } elseif (in_array('enumEnrolledStatus', $userColumns)) {
            $userData['enumEnrolledStatus'] = 'applicant';
        }

        // Generate a unique slug if column exists (logs showed slug can be required)
        $slug = null;
        if (in_array('slug', $userColumns)) {
            $slug = $this->makeUniqueSlug($request->input('first_name'), $request->input('last_name'));
            $userData['slug'] = $slug;
        }

        // Optional campus
        if (in_array('campus', $userColumns) && $request->filled('campus')) {
            $userData['campus'] = $request->input('campus');
        }

        try {
            $userId = null;
            DB::beginTransaction();

            // Disallow duplicate based on email if column exists
            if (in_array('strEmail', $userColumns) && !empty($userData['strEmail'])) {
                $exists = DB::table('tb_mas_users')->where('strEmail', $userData['strEmail'])->exists();
                if ($exists) {
                    // Allow multiple applications by same email? For now, return a clear message.
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'An account with this email already exists.'
                    ], 422);
                }
            }

            // Insert core user
            $userId = DB::table('tb_mas_users')->insertGetId($userData);

            // Persist raw payload into tb_mas_applicant_data
            $payload = $request->all();
            // Add server-hints
            $payload['_server'] = [
                'ts' => $now->toDateTimeString(),
                'ip' => $request->ip(),
                'ua' => substr($request->userAgent() ?? '', 0, 255)
            ];

            // Normalize applicant_type (if provided)
            $normalizedApplicantType = $request->input('applicant_type');
            $normalizedApplicantType = is_numeric($normalizedApplicantType) ? (int) $normalizedApplicantType : null;

            // Resolve syid from request (accept syid|term|current_sem)
            $syidFromReq = null;
            $candidates = [
                $request->input('syid'),
                $request->input('term'),
                $request->input('current_sem'),
            ];
            foreach ($candidates as $cand) {
                if ($cand !== null && $cand !== '' && is_numeric($cand)) {
                    $syidFromReq = (int) $cand;
                    break;
                }
            }

            // Generate a unique access hash if column exists (for public initial requirements link)
            $hashVal = null;
            if (Schema::hasColumn('tb_mas_applicant_data', 'hash')) {
                $tries = 0;
                do {
                    $candidate = Str::random(40);
                    $exists = DB::table('tb_mas_applicant_data')->where('hash', $candidate)->exists();
                    $tries++;
                } while ($exists && $tries < 5);
                $hashVal = $candidate;
            }

            // Build insert payload with optional normalized applicant_type and syid
            $insertData = [
                'user_id' => $userId,
                'data'    => json_encode($payload),
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if (Schema::hasColumn('tb_mas_applicant_data', 'hash')) {
                $insertData['hash'] = $hashVal;
            }
            if (Schema::hasColumn('tb_mas_applicant_data', 'applicant_type')) {
                $insertData['applicant_type'] = $normalizedApplicantType;
            }
            if (Schema::hasColumn('tb_mas_applicant_data', 'syid') && $request->filled('term')) {
                $insertData['syid'] = $request->input('term');
            }            

            $applicantDataId = DB::table('tb_mas_applicant_data')->insertGetId($insertData);

            // Persist application awareness (multi-select "How did you find out about iACADEMY?")
            try {
                $awareness = $request->input('awareness', []);
                if (is_array($awareness) && !empty($awareness)) {
                    app(\App\Services\ApplicationAwarenessService::class)->createMany((int) $applicantDataId, $awareness);
                }
            } catch (Throwable $e) {
                // Bubble up to rollback the transaction and return error
                throw $e;
            }

            DB::commit();

            // Journey log: Student Applied
            try {
                app(\App\Services\ApplicantJourneyService::class)->log((int) $applicantDataId, 'Student Applied');
            } catch (Throwable $e) {
                // ignore logging failure
            }

            // System alert: notify Admissions about new application
            try {
                $last = strtoupper(trim((string) $request->input('last_name', '')));
                $first = trim((string) $request->input('first_name', ''));
                $emailAddr = trim((string) $request->input('email', ''));
                $subject = 'New Application Submitted';
                $namePart = trim(($last !== '' ? $last : '') . ($first !== '' ? ($last !== '' ? ', ' : '') . $first : ''));
                $msg = 'New applicant signup' . ($namePart !== '' ? (': ' . $namePart) : '') . ($emailAddr !== '' ? ' (' . $emailAddr . ')' : '');

                $campusId = $request->input('campus_id');

                $payload = [
                    'title'            => $subject,
                    'message'          => $msg,
                    'link'             => '#/admissions/applicants/' . $userId,
                    'type'             => 'info',
                    'target_all'       => false,
                    'role_codes'       => ['admissions'],
                    'intActive'        => 1,
                    'system_generated' => 1,
                    'starts_at'        => now(),
                ];
                if ($campusId !== null && $campusId !== '' && is_numeric($campusId)) {
                    $payload['campus_ids'] = [ (int) $campusId ];
                }

                $alert = \App\Models\SystemAlert::create($payload);
                app(\App\Services\SystemAlertService::class)->broadcast('create', $alert);
            } catch (Throwable $e) {
                Log::warning('System alert creation failed for new application: ' . $e->getMessage());
            }

            // Send confirmation email; do not fail application if email sending fails
            $to = $request->input('email');
            $emailSent = false;
            $emailError = null;
            if ($to) {
                try {
                    $this->sendApplicantMail($to, $request->input('first_name'), $request->input('last_name'), $applicantDataId);
                    $emailSent = true;
                } catch (Throwable $mailEx) {
                    $emailSent = false;
                    $emailError = $mailEx->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'message' => $emailSent
                    ? 'Application submitted successfully.'
                    : 'Application submitted successfully, but the confirmation email could not be sent.',
                'data' => [
                    'user_id' => $userId,
                    'slug'    => $slug,
                    // Provide access hash so the frontend can construct the public upload link
                    'hash'    => $hashVal,
                    'email_sent' => $emailSent,
                    'email_error' => $emailError,
                ]
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Unable to submit application.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    protected function makeUsername(?string $email, ?string $first, ?string $last): string
    {
        if ($email && strpos($email, '@') !== false) {
            return substr($email, 0, strpos($email, '@'));
        }
        $base = trim(($first ? Str::slug($first) : '') . '.' . ($last ? Str::slug($last) : ''), '.');
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
        while (DB::table('tb_mas_users')->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i;
            $i++;
            if ($i > 1000) {
                $slug = $base . '-' . Str::lower(Str::random(6));
                break;
            }
        }
        return $slug;
    }

    protected function sendApplicantMail(string $to, ?string $first, ?string $last, int $applicantDataId): void
    {
        $name = trim(($first ?? '') . ' ' . ($last ?? ''));
        
        // Generate application number based on applicant data ID
        $applicationNumber = $this->generateApplicationNumber($applicantDataId);
        $confirmationCode = $this->generateConfirmationCode();
        
        // Generate username from email or name
        $username = $this->makeUsername($to, $first, $last);
        
        try {
            // Use PHPMailerService for better email handling
            $phpMailerService = app(\App\Services\PHPMailerService::class);
            $phpMailerService->sendApplicationConfirmation(
                $to,
                $name,
                $applicationNumber,
                $username,
                $confirmationCode
            );
        } catch (\Exception $e) {
            // Fallback to basic Laravel mail if PHPMailerService fails
            $subject = 'iACADEMY Application Received';
            $body = "Hello {$name},\n\n"
                . "Your application has been received successfully.\n\n"
                . "Application Number: {$applicationNumber}\n"
                . "Username: {$username}\n"
                . "Confirmation Code: {$confirmationCode}\n\n"
                . "Our Admissions Team will review your submission and contact you for the next steps.\n\n"
                . "Regards,\n"
                . "iACADEMY Admissions";

            Mail::raw($body, function ($message) use ($to, $subject) {
                $message->to($to);
                $message->from('josephedmundcastillo@gmail.com', 'iACADEMY Admissions');
                $message->subject($subject);
            });
        }
    }

    /**
     * Get active school year and semester from tb_mas_sy
     */
    protected function getActiveSchoolYearInfo(): array
    {
        $activeSy = DB::table('tb_mas_sy')
            ->where('enumStatus', 'active')
            ->first();

        if ($activeSy) {
            // Extract year and semester from active school year
            $year = $activeSy->strYear ?? date('Y');
            $sem = $activeSy->strSem ?? $activeSy->semester ?? '1';
            
            return [
                'year' => $year,
                'semester' => $sem
            ];
        }

        // Fallback to current year and semester 1 if no active SY found
        return [
            'year' => date('Y'),
            'semester' => '1'
        ];
    }

    /**
     * Generate application number based on tb_mas_applicant_data ID
     * Format: A000001, A000002, etc.
     */
    protected function generateApplicationNumber($applicantDataId): string
    {
        return 'A' . str_pad($applicantDataId, 6, '0', STR_PAD_LEFT);
    }

    protected function generateConfirmationCode(): string
    {
        $syInfo = $this->getActiveSchoolYearInfo();
        $year = $syInfo['year'];
        $sem = $syInfo['semester'];
        
        do {
            $randomNumber = rand(1000, 9999);
            $code = $year . '0' . $sem . $randomNumber;
            $confirmationCode = substr(md5($code), 0, 20);
            
            // Check if confirmation code already exists
            $exists = DB::table('tb_mas_applicant_data')->where('data', 'LIKE', '%"confirmation_code":"' . $confirmationCode . '"%')->exists();
            
        } while ($exists);
        
        return $confirmationCode;
    }
}