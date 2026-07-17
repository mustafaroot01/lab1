<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AdminLoginRequest;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    /**
     * تسجيل دخول الأدمن — يرجّع accessToken + userData + userAbilityRules
     * بنفس صيغة الـ demo API التي ينتظرها الـ frontend.
     */
    public function login(AdminLoginRequest $request)
    {

        $user = Admin::where(function ($q) use ($request) {
                $q->where('email', $request->email)
                  ->orWhere('phone', $request->email);
            })
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'errors' => ['email' => ['بيانات الدخول غير صحيحة']],
            ], 400);
        }

        if (!$user->is_active) {
            return response()->json([
                'errors' => ['email' => ['الحساب معطّل']],
            ], 400);
        }

        $token = $user->createToken('admin-panel')->plainTextToken;

        return response()->json([
            'accessToken'       => $token,
            'userData'          => [
                'id'       => $user->id,
                'fullName' => $user->name,
                'username' => $user->name,
                'email'    => $user->email,
                'role'     => $user->role,
                'avatar'   => null,
            ],
            'userAbilityRules' => [
                ['action' => 'manage', 'subject' => 'all'],
            ],
        ], 201);
    }

    /**
     * بيانات الأدمن الحالي
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'userData' => [
                'id'       => $user->id,
                'fullName' => $user->name,
                'username' => $user->name,
                'email'    => $user->email,
                'role'     => $user->role,
                'avatar'   => null,
            ],
            'userAbilityRules' => [
                ['action' => 'manage', 'subject' => 'all'],
            ],
        ]);
    }

    /**
     * تسجيل الخروج
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => true,
            'message' => 'تم تسجيل الخروج بنجاح',
        ]);
    }
}
