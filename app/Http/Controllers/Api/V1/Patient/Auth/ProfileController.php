<?php

namespace App\Http\Controllers\Api\V1\Patient\Auth;

use App\Actions\Chat\CreateConversationForUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CompleteProfileRequest;
use App\Http\Requests\Auth\UpdatePatientProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\Area;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * الملف الشخصي الحالي للمريض
     */
    public function me(Request $request)
    {
        /** @var Patient $user */
        $user = $request->user();

        if (!$user->is_active) {
            $user->tokens()->delete();
            return response()->json([
                'status'  => false,
                'message' => 'تم إيقاف حسابك من قبل الإدارة، وتم إنهاء جلستك.',
            ], 403);
        }

        return response()->json([
            'status' => true,
            'user'   => new UserResource($user->load(['district.branch'])),
        ]);
    }

    /**
     * استكمال الملف الشخصي لأول مرة
     */
    public function completeProfile(CompleteProfileRequest $request, CreateConversationForUserAction $createConversation)
    {
        /** @var Patient $user */
        $user = $request->user();

        $user->update([
            'name'                 => $request->input('name'),
            'birth_date'           => $request->input('birth_date'),
            'gender'               => $request->input('gender'),
            'is_profile_completed' => true,
        ]);

        // إنشاء محادثة الدعم فوراً
        $createConversation->execute($user);

        return response()->json([
            'status'    => true,
            'message'   => 'تم إكمال الملف الشخصي بنجاح، مرحباً بك في التطبيق',
            'next_step' => 'home',
            'user'      => new UserResource($user->fresh(['district.branch'])),
        ]);
    }

    /**
     * تحديث بيانات الملف الشخصي (باستخدام Form Request مخصص)
     */
    public function updateProfile(UpdatePatientProfileRequest $request)
    {
        /** @var Patient $user */
        $user = $request->user();

        $updateData = [];
        if ($request->has('name'))        $updateData['name'] = $request->input('name');
        if ($request->has('birth_date'))  $updateData['birth_date'] = $request->input('birth_date');
        if ($request->has('gender'))      $updateData['gender'] = $request->input('gender');

        if (!empty($updateData)) {
            $user->update($updateData);
        }

        return response()->json([
            'status'  => true,
            'message' => 'تم تحديث بيانات الحساب بنجاح ✓',
            'user'    => new UserResource($user->fresh(['district.branch'])),
        ]);
    }

    /**
     * طلب حذف الحساب وتعطيله فوراً
     */
    public function deleteAccount(Request $request)
    {
        /** @var Patient $user */
        $user = $request->user();

        $user->update(['is_active' => false]);
        $user->tokens()->delete();

        Log::info("طلب حذف حساب — تم تعطيل الحساب وإنهاء الجلسات. المريض ID: {$user->id} رقم الهاتف: {$user->phone}");

        return response()->json([
            'status'  => true,
            'message' => 'تم استلام طلب حذف حسابك وتعطيله فوراً، وسيتم حذف بياناتك نهائياً من قبل الإدارة.',
        ]);
    }
}
