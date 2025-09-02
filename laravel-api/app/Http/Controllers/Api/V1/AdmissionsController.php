<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
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
            'date_of_birth' => 'nullable|date',
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

            DB::table('tb_mas_applicant_data')->insert([
                'user_id' => $userId,
                'data'    => json_encode($payload),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::commit();

            // Send confirmation email
            $to = $request->input('email');
            if ($to) {
                $this->sendApplicantMail($to, $request->input('first_name'), $request->input('last_name'));
            }

            return response()->json([
                'success' => true,
                'message' => 'Application submitted successfully.',
                'data' => [
                    'user_id' => $userId,
                    'slug'    => $slug,
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

    protected function sendApplicantMail(string $to, ?string $first, ?string $last): void
    {
        $subject = 'iACADEMY Application Received';
        $name = trim(($first ?? '') . ' ' . ($last ?? ''));
        $body = "Hello {$name},\n\n"
            . "Your application has been received successfully. Our Admissions Team will review your submission and contact you for the next steps.\n\n"
            . "Regards,\n"
            . "iACADEMY Admissions";

        // Use a test sender as requested
        Mail::raw($body, function ($message) use ($to, $subject) {
            $message->to($to);
            // Test sender address as requested
            $message->from('josephedmundcastillo@gmail.com', 'iACADEMY Admissions');
            $message->subject($subject);
        });
    }
}