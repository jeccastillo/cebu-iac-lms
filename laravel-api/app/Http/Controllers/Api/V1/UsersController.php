<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{
    /**
     * POST /api/v1/users/auth
     * Body: { loginType: "faculty"|"student", strUser: string, strPass: string }
     * CI parity for Users::auth (basic JSON response)
     */
    public function auth(Request $request)
    {
        $loginType = $request->input('loginType', 'faculty');
        // Accept strUsername (preferred) with fallback to legacy strUser
        $username  = $request->input('strUsername', $request->input('strUser'));
        $password  = $request->input('strPass');

        if (!$username || $password === null) {
            return response()->json([
                'success' => false,
                'message' => 'Wrong username or password'
            ], 422);
        }

        $table = $loginType === 'student' ? 'tb_mas_users' : 'tb_mas_faculty';

        $user = DB::table($table)->where('strUsername', $username)->first();

        if (!$user || !isset($user->strPass)) {
            return response()->json([
                'success' => false,
                'message' => 'Wrong username or password'
            ]);
        }

        // Support both modern password_hash and legacy CI pw_hash storage
        $ok = false;
        if (isset($user->strPass)) {
            if (password_verify($password, $user->strPass)) {
                $ok = true;
            } else {
                $decoded = $this->legacyUnhash($user->strPass);
                if ($decoded !== null && hash_equals($decoded, $password)) {
                    $ok = true;
                }
            }
        }

        if ($ok) {
            // Include roles for frontend RBAC
            $roles = [];
            if ($loginType === 'student') {
                $roles = ['student_view'];
            } else {
                try {
                    $codes = DB::table('tb_mas_roles as r')
                        ->join('tb_mas_faculty_roles as fr', 'fr.intRoleID', '=', 'r.intRoleID')
                        ->where('fr.intFacultyID', $user->intID)
                        ->where('r.intActive', 1)
                        ->orderBy('r.strCode')
                        ->pluck('r.strCode')
                        ->toArray();
                    $roles = array_values(array_map(function ($c) { return strtolower((string)$c); }, $codes));
                } catch (\Throwable $e) {
                    $roles = [];
                }
            }

            $campusId = null;
            $facultyId = null;
            if ($loginType !== 'student') {
                $campusId = $user->campus_id ?? null;
                $facultyId = $user->intID ?? null;
            }

            return response()->json([
                'success' => true,
                'message' => 'Success',
                'roles'   => $roles,
                'campus_id' => $campusId,
                'faculty_id' => $facultyId,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Wrong username or password'
        ]);
    }

    /**
     * POST /api/v1/users/auth-student
     * Body: { strUser: string, strPass: string }
     * CI parity for Users::auth_student (returns {success, message})
     */
    public function authStudent(Request $request)
    {
        // Accept strUsername (preferred) with fallback to legacy strUser
        $username  = $request->input('strUsername', $request->input('strUser'));
        $password  = $request->input('strPass');

        if (!$username || !$password) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Login'
            ], 422);
        }

        $user = DB::table('tb_mas_users')->where('strUsername', $username)->first();

        if (!$user || !isset($user->strPass)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Login'
            ]);
        }

        // Support both modern password_hash and legacy CI pw_hash storage
        $ok = false;
        if (isset($user->strPass)) {
            if (password_verify($password, $user->strPass)) {
                $ok = true;
            } else {
                $decoded = $this->legacyUnhash($user->strPass);
                if ($decoded !== null && hash_equals($decoded, $password)) {
                    $ok = true;
                }
            }
        }

        if ($ok) {
            return response()->json([
                'success' => true,
                'message' => 'Success',
                'roles'   => ['student_view']
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid Login'
        ]);
    }

    /**
     * Legacy CI pw_hash unhash equivalent used to support old stored passwords.
     * Mirrors application/libraries/Salting::unhash_string behavior.
     */
    private function legacyUnhash(?string $hashed): ?string
    {
        if (!$hashed || strlen($hashed) < 10) {
            return null;
        }
        // Remove first 5 and last 5 chars (prefix/suffix)
        $core = substr($hashed, 5, -5);
        $len = strlen($core);
        $get = '';
        $inc = 0;
        for ($i = 0; $i < $len; $i++) {
            if ($inc === 0) {
                $inc = 1;
                $get .= $core[$i];
            } else {
                $inc = 0;
            }
        }
        return strrev($get);
    }

    /**
     * POST /api/v1/users/register
     * Body: { strEmail, strUsername, strPass, strFirstname?, strLastname? }
     * CI parity with simplified email behavior (no mail send, but same outcome codes)
     * Returns:
     *   success = 1 -> registered
     *   success = 0 -> email in use
     *   success = 2 -> username in use
     *   else "Something went wrong"
     */
    public function register(Request $request)
    {
        $email    = $request->input('strEmail');
        $username = $request->input('strUsername');
        $password = $request->input('strPass');
        $first    = $request->input('strFirstname', '');
        $last     = $request->input('strLastname', '');

        if (!$email || !$username || !$password) {
            return response()->json([
                'success' => 0,
                'message' => 'Something went wrong'
            ], 422);
        }

        $existsEmail = DB::table('tb_mas_users')->where('strEmail', $email)->exists();
        if ($existsEmail) {
            return response()->json([
                'success' => 0,
                'message' => 'The email address you entered may already be in use'
            ]);
        }

        $existsUsername = DB::table('tb_mas_users')->where('strUsername', $username)->exists();
        if ($existsUsername) {
            return response()->json([
                'success' => 2,
                'message' => 'The username you entered may already be in use'
            ]);
        }

        $data = [
            'strEmail'     => $email,
            'strUsername'  => $username,
            'strPass'      => password_hash($password, PASSWORD_DEFAULT),
            'strFirstname' => $first,
            'strLastname'  => $last,
            'dteCreated'   => date('Y-m-d'),
            'strConfirmed' => substr(hash('sha1', bin2hex(random_bytes(8))), 0, 40),
        ];

        try {
            DB::table('tb_mas_users')->insert($data);
            return response()->json([
                'success' => 1,
                'message' => 'Your username has been registered check your email for confirmation'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => 0,
                'message' => 'Something went wrong'
            ], 500);
        }
    }

    /**
     * POST /api/v1/users/forgot
     * Body: { email } or { strEmail }
     * CI parity: echoes "1" on success, "0" otherwise
     */
    public function forgot(Request $request)
    {
        $email = $request->input('email', $request->input('strEmail'));

        if (!$email) {
            return response('0');
        }

        $user = DB::table('tb_mas_users')->where('strEmail', $email)->first();
        if (!$user) {
            return response('0');
        }

        $hash = sha1(bin2hex(random_bytes(16)));
        DB::table('tb_mas_users')->where('strEmail', $email)->update(['strReset' => $hash]);

        // In CI this would send an email; we just mark success like CI controller
        return response('1');
    }

    /**
     * POST /api/v1/users/password-reset
     * Body: { hash, password }
     * Updates strPass and clears strReset similar to CI Users::password_reset (form-based)
     */
    public function passwordReset(Request $request)
    {
        $hash     = $request->input('hash');
        $password = $request->input('password', $request->input('strPass'));

        if (!$hash || !$password) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request'
            ], 422);
        }

        $user = DB::table('tb_mas_users')->where('strReset', $hash)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid hash'
            ], 404);
        }

        DB::table('tb_mas_users')
            ->where('intID', $user->intID)
            ->update([
                'strPass'  => password_hash($password, PASSWORD_DEFAULT),
                'strReset' => ''
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Your password has been reset!.'
        ]);
    }

    /**
     * POST /api/v1/users/logout
     * Placeholder for stateless API; will revoke token when Sanctum is fully wired.
     */
    public function logout(Request $request)
    {
        return response()->json([
            'success' => true
        ]);
    }

    /**
     * GET /api/v1/users/debug-auth
     * Query: loginType=faculty|student, strUsername|strUser|username, strPass? (to test legacy match)
     * Returns minimal diagnostics to verify existence and password format without exposing secrets.
     */
    public function debugAuth(Request $request)
    {
        $loginType = $request->input('loginType', 'faculty');
        $username  = $request->input('strUsername', $request->input('strUser', $request->input('username')));
        $testPass  = $request->input('strPass');

        $table = $loginType === 'student' ? 'tb_mas_users' : 'tb_mas_faculty';

        $user = null;
        if ($username) {
            $user = DB::table($table)->where('strUsername', $username)->first();
        }

        $resp = [
            'loginType' => $loginType,
            'username'  => $username,
            'exists'    => (bool) $user,
        ];

        if ($user) {
            $hasStrPass = isset($user->strPass) && is_string($user->strPass) && $user->strPass !== '';
            $resp['has_strPass'] = $hasStrPass;
            if ($hasStrPass) {
                $resp['pass_len']   = strlen($user->strPass);
                $resp['pass_prefix'] = substr($user->strPass, 0, 10);

                // Attempt legacy decode preview (does not expose full value)
                $decoded = $this->legacyUnhash($user->strPass);
                $resp['legacy_decodable'] = $decoded !== null;
                if ($decoded !== null) {
                    $resp['legacy_prefix'] = substr($decoded, 0, 4);
                    if ($testPass !== null) {
                        $resp['legacy_matches_input'] = hash_equals($decoded, $testPass);
                    }
                } else {
                    $resp['legacy_prefix'] = null;
                    if ($testPass !== null) {
                        $resp['legacy_matches_input'] = false;
                    }
                }

                // If a test password provided, also try password_verify against modern hashes
                if ($testPass !== null) {
                    $resp['password_verify'] = password_verify($testPass, $user->strPass);
                }
            }
        }

        return response()->json([
            'success' => true,
            'data'    => $resp
        ]);
    }
}
