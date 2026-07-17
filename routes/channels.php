<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['prefix' => 'api', 'middleware' => ['auth:sanctum']]);

// قناة المريض / المستخدم المعزولة لاستقبال الرسائل وتنبيهات انضمام المشرف (مع سماح للمشرف بالدخول لمتابعة البث مباشرة)
Broadcast::channel('private-conversation.{userId}', function ($user, $userId) {
    if ((method_exists($user, 'isAdmin') && $user->isAdmin()) || $user instanceof \App\Models\Admin) {
        return true;
    }
    return (int) $user->id === (int) $userId;
});

// قناة الإدارة الموحدة لاستقبال كل التنبيهات والرسائل الجديدة في لوحة التحكم
Broadcast::channel('private-admin-chat', function ($user) {
    return (method_exists($user, 'isAdmin') && $user->isAdmin()) || $user instanceof \App\Models\Admin || $user->role === 'admin' || $user->role === 'super_admin';
});
