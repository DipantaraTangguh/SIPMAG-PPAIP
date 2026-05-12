<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/login
     * Mahasiswa login with NIM (used as email lookup) or email + password.
     * Returns Sanctum token + user data + role-specific profile.
     */
    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',  // accepts NIM or email
            'password' => 'required|string',
        ]);

        // Try to find user by email first, then by NIM via student profile
        $user = User::where('email', $request->login)->first();

        if (!$user) {
            // Try NIM lookup
            $student = \App\Models\Student::where('nim', $request->login)->first();
            if ($student) {
                $user = $student->user;
            }
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Kredensial yang diberikan tidak valid.'],
            ]);
        }

        // Revoke previous tokens (single-session)
        $user->tokens()->delete();

        // Create new token with role-based ability
        $token = $user->createToken('auth-token', [$user->role])->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->buildUserPayload($user),
        ]);
    }

    /**
     * POST /api/logout
     * Revoke current token.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Berhasil logout.']);
    }

    /**
     * GET /api/me
     * Return authenticated user data + role-specific profile.
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => $this->buildUserPayload($request->user()),
        ]);
    }

    /**
     * Build the user response payload with role-specific data.
     */
    private function buildUserPayload(User $user): array
    {
        $payload = [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role,
        ];

        if ($user->isMahasiswa()) {
            $student = $user->student;
            if ($student) {
                $payload['student'] = [
                    'id'                     => $student->id,
                    'nim'                    => $student->nim,
                    'name'                   => $student->name,
                    'study_program'          => $student->study_program,
                    'email'                  => $student->email,
                    'access_status'          => $student->access_status,
                    'is_independent'         => $student->is_independent,
                    'approved_logbook_count' => $student->approved_logbook_count,
                    'dpm'                    => $student->dpm ? [
                        'name'    => $student->dpm->lecturer_name,
                        'nidn'    => $student->dpm->nidn,
                        'contact' => $student->dpm->contact,
                    ] : null,
                ];
            }
        }

        if ($user->isKaprodi() || $user->isDpm()) {
            $lecturer = $user->lecturer;
            if ($lecturer) {
                $payload['lecturer'] = [
                    'id'            => $lecturer->id,
                    'nidn'          => $lecturer->nidn,
                    'lecturer_name' => $lecturer->lecturer_name,
                    'study_program' => $lecturer->study_program,
                ];
            }
        }

        return $payload;
    }
}
