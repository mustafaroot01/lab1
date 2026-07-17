<?php

namespace Database\Seeders;

use App\Enums\Chat\ConversationStatus;
use App\Models\Chat\Conversation;
use App\Models\Chat\Message;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChatDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        $patients = [
            ['name' => 'أحمد محمد',  'phone' => '07701234567'],
            ['name' => 'زينب علي',   'phone' => '07701234568'],
            ['name' => 'حسين كاظم', 'phone' => '07701234569'],
        ];

        foreach ($patients as $p) {
            $user = User::firstOrCreate(
                ['phone' => $p['phone']],
                [
                    'name'                 => $p['name'],
                    'password'             => bcrypt('123456'),
                    'is_profile_completed' => true,
                    'is_active'            => true,
                    'agreed_to_terms'      => true,
                    'role'                 => 'patient',
                ]
            );

            $patient = \App\Models\Patient::firstOrCreate(
                ['phone' => $p['phone']],
                [
                    'name'                 => $p['name'],
                    'is_profile_completed' => true,
                    'is_active'            => true,
                ]
            );

            $conv = Conversation::firstOrCreate(
                ['user_id' => $user->id, 'patient_id' => $patient->id],
                ['status' => ConversationStatus::Open->value]
            );

            $m1 = Message::create([
                'conversation_id' => $conv->id,
                'sender_id'       => $user->id,
                'body'            => 'مرحباً، عندي استفسار عن نتيجة التحليل',
            ]);

            $m2 = Message::create([
                'conversation_id' => $conv->id,
                'sender_id'       => $admin->id,
                'body'            => 'أهلاً بك، تفضل ما هو استفسارك؟',
            ]);

            $m3 = Message::create([
                'conversation_id' => $conv->id,
                'sender_id'       => $user->id,
                'body'            => 'متى تظهر نتيجة تحليل السكر؟',
            ]);

            $conv->update([
                'last_message_at'            => now(),
                'last_sender_id'             => $user->id,
                'last_message_preview'       => 'متى تظهر نتيجة تحليل السكر؟',
                'admin_last_read_message_id' => null,
                'patient_last_read_message_id' => $m3->id,
            ]);

            $this->command->info("Created: {$user->name} (conv #{$conv->id})");
        }

        // محادثة رابعة مغلقة
        $closedUser = User::firstOrCreate(
            ['phone' => '07701234570'],
            [
                'name'                 => 'فاطمة حسن',
                'password'             => bcrypt('123456'),
                'is_profile_completed' => true,
                'is_active'            => true,
                'agreed_to_terms'      => true,
                'role'                 => 'patient',
            ]
        );

        $closedPatient = \App\Models\Patient::firstOrCreate(
            ['phone' => '07701234570'],
            [
                'name'                 => 'فاطمة حسن',
                'is_profile_completed' => true,
                'is_active'            => true,
            ]
        );

        $closedConv = Conversation::firstOrCreate(
            ['user_id' => $closedUser->id, 'patient_id' => $closedPatient->id],
            ['status' => ConversationStatus::Closed->value, 'closed_at' => now(), 'closed_by' => $admin->id]
        );

        Message::create(['conversation_id' => $closedConv->id, 'sender_id' => $closedUser->id, 'body' => 'شكراً لكم تم الحل']);
        Message::create(['conversation_id' => $closedConv->id, 'sender_id' => $admin->id, 'body' => 'بالتوفيق، نحن في الخدمة دائماً']);

        $closedConv->update([
            'last_message_at'            => now(),
            'last_sender_id'             => $admin->id,
            'last_message_preview'       => 'بالتوفيق، نحن في الخدمة دائماً',
            'admin_last_read_message_id' => Message::max('id'),
            'patient_last_read_message_id' => Message::max('id'),
        ]);

        $this->command->info("Created closed: {$closedUser->name} (conv #{$closedConv->id})");
        $this->command->info('Total conversations: ' . Conversation::count());
    }
}
