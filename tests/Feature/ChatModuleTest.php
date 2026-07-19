<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Chat\Conversation;
use App\Models\Chat\Message;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ChatModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $patient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'phone' => '07700000001', 'is_active' => true]);
        $this->patient = User::factory()->create(['role' => 'user', 'phone' => '07700000002', 'is_active' => true]);

        // نسخ مطابقة (نفس الـ id) في الجداول المعزولة لتُحل علاقات Admin/Patient،
        // كما هو الحال في الإنتاج بعد ترحيل المستخدمين للجداول المعزولة
        $admin = new Admin(['name' => $this->admin->name, 'email' => $this->admin->email, 'phone' => $this->admin->phone, 'password' => 'secret', 'role' => 'admin', 'is_active' => true]);
        $admin->id = $this->admin->id;
        $admin->save();

        $patient = new Patient(['name' => $this->patient->name, 'phone' => $this->patient->phone, 'is_active' => true, 'is_profile_completed' => true]);
        $patient->id = $this->patient->id;
        $patient->save();
    }

    private function createConversation(array $attributes = []): Conversation
    {
        return Conversation::create(array_merge(['user_id' => $this->patient->id, 'patient_id' => $this->patient->id], $attributes));
    }

    // ─── Admin: قائمة المحادثات ───────────────────────────────────────────

    public function test_admin_can_list_conversations(): void
    {
        $this->createConversation();

        $this->actingAs($this->admin)
            ->getJson('/api/admin/chat')
            ->assertOk()
            ->assertJsonStructure([
                'status',
                'conversations',
                'meta' => ['next_cursor', 'has_more', 'per_page'],
            ]);
    }

    public function test_non_admin_cannot_access_admin_chat(): void
    {
        $this->actingAs($this->patient)
            ->getJson('/api/admin/chat')
            ->assertForbidden();
    }

    public function test_guest_cannot_access_admin_chat(): void
    {
        $this->getJson('/api/admin/chat')->assertUnauthorized();
    }

    public function test_unread_count_is_correct_when_admin_never_read(): void
    {
        $conversation = $this->createConversation();

        // 3 رسائل من المريض — admin_last_read_message_id = NULL
        Message::create(['conversation_id' => $conversation->id, 'sender_id' => $this->patient->id, 'sender_type' => 'patient', 'body' => 'رسالة 1']);
        Message::create(['conversation_id' => $conversation->id, 'sender_id' => $this->patient->id, 'sender_type' => 'patient', 'body' => 'رسالة 2']);
        Message::create(['conversation_id' => $conversation->id, 'sender_id' => $this->patient->id, 'sender_type' => 'patient', 'body' => 'رسالة 3']);

        $response = $this->actingAs($this->admin)->getJson('/api/admin/chat');

        $response->assertOk();
        $this->assertSame(3, $response->json('conversations.0.unread_count'));
    }

    // ─── Admin: عرض المحادثة والرسائل ────────────────────────────────────

    public function test_admin_can_view_conversation_with_messages(): void
    {
        $conversation = $this->createConversation();
        Message::create(['conversation_id' => $conversation->id, 'sender_id' => $this->patient->id, 'sender_type' => 'patient', 'body' => 'مرحبا']);

        $this->actingAs($this->admin)
            ->getJson("/api/admin/chat/{$conversation->id}")
            ->assertOk()
            ->assertJsonPath('conversation.id', $conversation->id)
            ->assertJsonPath('messages.0.body', 'مرحبا');
    }

    public function test_viewing_conversation_marks_it_read_for_admin(): void
    {
        $conversation = $this->createConversation();
        $message = Message::create(['conversation_id' => $conversation->id, 'sender_id' => $this->patient->id, 'sender_type' => 'patient', 'body' => 'مرحبا']);

        $this->actingAs($this->admin)->getJson("/api/admin/chat/{$conversation->id}");

        $this->assertSame($message->id, $conversation->fresh()->admin_last_read_message_id);
    }

    public function test_messages_cursor_pagination(): void
    {
        $conversation = $this->createConversation();
        for ($i = 1; $i <= 40; $i++) {
            Message::create(['conversation_id' => $conversation->id, 'sender_id' => $this->patient->id, 'sender_type' => 'patient', 'body' => "رسالة $i"]);
        }

        $response = $this->actingAs($this->admin)
            ->getJson("/api/admin/chat/{$conversation->id}?per_page=30");

        $response->assertOk();
        $this->assertCount(30, $response->json('messages'));
        $this->assertTrue($response->json('meta.has_more'));

        // تحميل الصفحة الأقدم
        $cursor = $response->json('meta.next_cursor');
        $older = $this->actingAs($this->admin)
            ->getJson("/api/admin/chat/{$conversation->id}/messages?cursor=$cursor");

        $older->assertOk();
        $this->assertCount(10, $older->json('messages'));
        $this->assertFalse($older->json('meta.has_more'));
        // أقدم رسالة أولاً
        $this->assertSame('رسالة 1', $older->json('messages.0.body'));
    }

    // ─── Admin: الإرسال والاستلام (claim) ───────────────────────────────

    public function test_admin_send_message_auto_claims_conversation(): void
    {
        $conversation = $this->createConversation();

        $this->actingAs($this->admin)
            ->postJson("/api/admin/chat/{$conversation->id}/send", ['body' => 'أهلاً'])
            ->assertCreated();

        $conversation->refresh();
        $this->assertSame($this->admin->id, $conversation->assigned_to_user_id);
        // رسالة النظام + رسالة الأدمن
        $this->assertSame(2, $conversation->messages()->count());
        $this->assertTrue($conversation->messages()->where('is_system', true)->exists());
    }

    public function test_admin_cannot_send_to_closed_conversation(): void
    {
        $conversation = $this->createConversation(['status' => 'closed']);

        $this->actingAs($this->admin)
            ->postJson("/api/admin/chat/{$conversation->id}/send", ['body' => 'أهلاً'])
            ->assertUnprocessable();
    }

    public function test_send_requires_body_or_attachment(): void
    {
        $conversation = $this->createConversation();

        $this->actingAs($this->admin)
            ->postJson("/api/admin/chat/{$conversation->id}/send", [])
            ->assertUnprocessable();
    }

    public function test_send_with_attachment(): void
    {
        Storage::fake('local');
        $conversation = $this->createConversation();

        $response = $this->actingAs($this->admin)
            ->postJson("/api/admin/chat/{$conversation->id}/send", [
                'attachment' => UploadedFile::fake()->image('photo.jpg'),
            ]);

        $response->assertCreated();
        $this->assertNotNull($response->json('data.attachment.url'));
        $this->assertSame('image', $response->json('data.attachment.type'));
    }

    public function test_rejects_disallowed_attachment_types(): void
    {
        $conversation = $this->createConversation();

        $this->actingAs($this->admin)
            ->postJson("/api/admin/chat/{$conversation->id}/send", [
                'attachment' => UploadedFile::fake()->create('script.exe', 100),
            ])
            ->assertUnprocessable();
    }

    public function test_claim_endpoint_assigns_conversation(): void
    {
        $conversation = $this->createConversation();

        $this->actingAs($this->admin)
            ->postJson("/api/admin/chat/{$conversation->id}/claim")
            ->assertOk()
            ->assertJsonPath('data.assigned_to.id', $this->admin->id);
    }

    public function test_claim_twice_creates_only_one_system_message(): void
    {
        $conversation = $this->createConversation();

        $this->actingAs($this->admin)->postJson("/api/admin/chat/{$conversation->id}/claim");
        $this->actingAs($this->admin)->postJson("/api/admin/chat/{$conversation->id}/claim");

        $this->assertSame(1, $conversation->messages()->where('is_system', true)->count());
    }

    // ─── Admin: إغلاق وإعادة فتح ─────────────────────────────────────────

    public function test_admin_can_close_and_reopen_conversation(): void
    {
        $conversation = $this->createConversation();

        $this->actingAs($this->admin)
            ->postJson("/api/admin/chat/{$conversation->id}/close")
            ->assertOk()
            ->assertJsonPath('data.status', 'closed');

        $this->actingAs($this->admin)
            ->postJson("/api/admin/chat/{$conversation->id}/reopen")
            ->assertOk()
            ->assertJsonPath('data.status', 'open');
    }

    // ─── Mobile: المريض ──────────────────────────────────────────────────

    public function test_patient_show_creates_conversation_if_missing(): void
    {
        $this->assertDatabaseCount('conversations', 0);

        $this->actingAs($this->patient)
            ->getJson('/api/mobile/chat')
            ->assertOk();

        $this->assertDatabaseHas('conversations', ['user_id' => $this->patient->id]);
    }

    public function test_patient_can_send_message(): void
    {
        $this->actingAs($this->patient)
            ->postJson('/api/mobile/chat/send', ['body' => 'أحتاج مساعدة'])
            ->assertCreated()
            ->assertJsonPath('data.body', 'أحتاج مساعدة')
            ->assertJsonPath('data.is_admin', false);
    }

    public function test_patient_send_to_closed_conversation_opens_new_ticket(): void
    {
        // المحادثة المغلقة لا تُستأنف؛ إرسال المريض يفتح تذكرة جديدة تلقائياً
        $this->createConversation(['status' => 'closed']);

        $this->actingAs($this->patient)
            ->postJson('/api/mobile/chat/send', ['body' => 'مرحبا'])
            ->assertCreated();

        $this->assertDatabaseHas('conversations', ['patient_id' => $this->patient->id, 'status' => 'open']);
        $this->assertSame(2, Conversation::where('patient_id', $this->patient->id)->count());
    }

    public function test_patient_mobile_cursor_pagination(): void
    {
        $conversation = $this->createConversation();
        for ($i = 1; $i <= 35; $i++) {
            Message::create(['conversation_id' => $conversation->id, 'sender_id' => $this->admin->id, 'sender_type' => 'admin', 'body' => "رد $i"]);
        }

        $response = $this->actingAs($this->patient)->getJson('/api/mobile/chat?per_page=30');

        $response->assertOk();
        $this->assertCount(30, $response->json('messages'));
        $this->assertTrue($response->json('meta.has_more'));

        $cursor = $response->json('meta.next_cursor');
        $older = $this->actingAs($this->patient)->getJson("/api/mobile/chat/messages?cursor=$cursor");

        $older->assertOk();
        $this->assertCount(5, $older->json('messages'));
    }

    public function test_patient_show_marks_messages_read(): void
    {
        $conversation = $this->createConversation();
        $message = Message::create(['conversation_id' => $conversation->id, 'sender_id' => $this->admin->id, 'sender_type' => 'admin', 'body' => 'رد']);

        $this->actingAs($this->patient)->getJson('/api/mobile/chat');

        $this->assertSame($message->id, $conversation->fresh()->patient_last_read_message_id);
    }

    // ─── sender_type: منع تصادم هوية المُرسِل ───────────────────────────

    public function test_sender_type_recorded_for_admin_and_patient(): void
    {
        $conversation = $this->createConversation();

        $this->actingAs($this->patient)
            ->postJson('/api/mobile/chat/send', ['body' => 'من المريض'])
            ->assertCreated();

        $this->actingAs($this->admin)
            ->postJson("/api/admin/chat/{$conversation->id}/send", ['body' => 'من الأدمن'])
            ->assertCreated();

        $this->assertSame('patient', Message::where('body', 'من المريض')->value('sender_type'));
        $this->assertSame('admin', Message::where('body', 'من الأدمن')->value('sender_type'));
    }

    public function test_patient_message_not_misattributed_when_admin_shares_same_id(): void
    {
        // أدمن يحمل نفس id المريض — الخطأ القديم كان ينسب رسالة المريض للأدمن
        $clashAdmin = new Admin([
            'name' => 'clash', 'email' => 'clash@test.dev', 'phone' => '07709999999',
            'password' => 'secret', 'role' => 'admin', 'is_active' => true,
        ]);
        $clashAdmin->id = $this->patient->id;
        $clashAdmin->save();

        $this->actingAs($this->patient)
            ->postJson('/api/mobile/chat/send', ['body' => 'رسالة مريض'])
            ->assertCreated()
            ->assertJsonPath('data.is_admin', false);
    }

    public function test_new_patient_without_users_row_can_send_message(): void
    {
        // مريض جديد لا يملك صفاً مطابقاً في جدول users (id يتجاوز أعلى id هناك)
        // قبل إزالة قيد FK كانت هذه الرسالة تفشل — الآن تنجح بفضل sender_type
        $patient = new Patient(['name' => 'مريض جديد', 'phone' => '07701234567', 'is_active' => true, 'is_profile_completed' => true]);
        $patient->id = ((int) User::max('id')) + 500;
        $patient->save();

        $this->actingAs($patient)
            ->postJson('/api/mobile/chat/send', ['body' => 'أول رسالة'])
            ->assertCreated();

        $this->assertDatabaseHas('messages', ['sender_id' => $patient->id, 'sender_type' => 'patient']);
    }

    // ─── المرفقات: قرص خاص + رابط موقّع ─────────────────────────────────

    public function test_attachment_stored_on_private_disk(): void
    {
        Storage::fake('local');
        $conversation = $this->createConversation();

        $this->actingAs($this->admin)
            ->postJson("/api/admin/chat/{$conversation->id}/send", [
                'attachment' => UploadedFile::fake()->image('report.jpg'),
            ])->assertCreated();

        $message = Message::whereNotNull('attachment_path')->firstOrFail();
        $this->assertSame('local', $message->attachment_disk);
        Storage::disk('local')->assertExists($message->attachment_path);
    }

    public function test_attachment_url_is_signed(): void
    {
        Storage::fake('local');
        $conversation = $this->createConversation();

        $response = $this->actingAs($this->admin)
            ->postJson("/api/admin/chat/{$conversation->id}/send", [
                'attachment' => UploadedFile::fake()->image('report.jpg'),
            ])->assertCreated();

        $url = $response->json('data.attachment.url');
        $this->assertStringContainsString('/api/chat/attachments/', $url);
        $this->assertStringContainsString('signature=', $url);
    }

    public function test_signed_attachment_route_serves_file(): void
    {
        Storage::fake('local');
        $conversation = $this->createConversation();

        $response = $this->actingAs($this->admin)
            ->postJson("/api/admin/chat/{$conversation->id}/send", [
                'attachment' => UploadedFile::fake()->image('report.jpg'),
            ])->assertCreated();

        $url = str_replace(config('app.url'), '', $response->json('data.attachment.url'));

        $this->get($url)->assertOk();
    }

    public function test_unsigned_attachment_route_is_forbidden(): void
    {
        Storage::fake('local');
        $conversation = $this->createConversation();

        $this->actingAs($this->admin)
            ->postJson("/api/admin/chat/{$conversation->id}/send", [
                'attachment' => UploadedFile::fake()->image('report.jpg'),
            ])->assertCreated();

        $message = Message::whereNotNull('attachment_path')->firstOrFail();

        $this->getJson("/api/chat/attachments/{$message->id}")->assertForbidden();
    }
}
