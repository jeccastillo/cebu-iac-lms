<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FacultySelfController extends Controller
{
    /**
     * Resolve acting faculty from X-Faculty-ID header.
     */
    protected function actingFaculty(Request $request): ?Faculty
    {
        $fid = (int) ($request->header('X-Faculty-ID') ?? 0);
        if ($fid <= 0) {
            return null;
        }
        return Faculty::find($fid);
    }

    /**
     * GET /api/v1/faculty/me
     * Returns the acting faculty row (based on X-Faculty-ID header).
     */
    public function me(Request $request): JsonResponse
    {
        $faculty = $this->actingFaculty($request);
        if (!$faculty) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: missing or invalid X-Faculty-ID header.',
                'error'   => 'unauthorized'
            ], 401);
        }

        // Return raw row (or tailor fields as needed)
        return response()->json([
            'success' => true,
            'data' => $faculty
        ]);
    }

    /**
     * PUT /api/v1/faculty/me
     * Update selected tb_mas_faculty fields for acting faculty.
     */
    public function update(Request $request): JsonResponse
    {
        $faculty = $this->actingFaculty($request);
        if (!$faculty) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: missing or invalid X-Faculty-ID header.',
                'error'   => 'unauthorized'
            ], 401);
        }

        // Validate only fields that can be edited by the faculty owner.
        $validated = $request->validate([
            'strFirstname'    => ['nullable', 'string', 'max:255'],
            'strMiddlename'   => ['nullable', 'string', 'max:255'],
            'strLastname'     => ['nullable', 'string', 'max:255'],
            'strEmail'        => ['nullable', 'string', 'max:255'],
            'strMobileNumber' => ['nullable', 'string', 'max:255'],
            // Optionally allow username edit; comment out to restrict
            // 'strUsername'     => ['nullable', 'string', 'max:255'],
        ]);

        // Normalize optional legacy NOT NULL columns
        if (!array_key_exists('strMiddlename', $validated) || $validated['strMiddlename'] === null) {
            $validated['strMiddlename'] = '';
        }

        // Build update payload with only provided keys
        $allowed = [
            'strFirstname',
            'strMiddlename',
            'strLastname',
            'strEmail',
            'strMobileNumber',
            // 'strUsername',
        ];
        $data = [];
        foreach ($allowed as $key) {
            if (array_key_exists($key, $validated)) {
                $data[$key] = $validated[$key];
            }
        }

        if (!empty($data)) {
            $faculty->update($data);
        }

        return response()->json([
            'success' => true,
            'data' => $faculty->fresh()
        ]);
    }

    /**
     * POST /api/v1/faculty/me/password
     * Update acting faculty password.
     * Expects:
     *  - current_password
     *  - new_password
     *  - new_password_confirmation (for confirmed rule)
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $faculty = $this->actingFaculty($request);
        if (!$faculty) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: missing or invalid X-Faculty-ID header.',
                'error'   => 'unauthorized'
            ], 401);
        }

        $validated = $request->validate([
            'current_password'          => ['required', 'string', 'min:6'],
            'new_password'              => ['required', 'string', 'min:8', 'confirmed'],
            // 'new_password_confirmation' is implied by 'confirmed'
        ]);

        $current = (string) $validated['current_password'];
        $new     = (string) $validated['new_password'];

        // Verify current password against stored hash (tb_mas_faculty.strPass)
        $storedHash = (string) ($faculty->strPass ?? '');
        if ($storedHash === '' || !password_verify($current, $storedHash)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
                'error'   => 'invalid_current_password'
            ], 422);
        }

        // Hash and update to new password
        $faculty->strPass = password_hash($new, PASSWORD_BCRYPT);
        $faculty->save();

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully.'
        ]);
    }
}
