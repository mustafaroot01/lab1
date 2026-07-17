<?php

namespace Tests\Feature;

use App\Models\Chat\Conversation;
use App\Models\Chat\Message;
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

        $this->admin = User::factory()->create(['role' => 'admin', 'phone' => '07700000001']);
        $this->patient = User::factory()->create(['role' => 'user', 'phone' => '07700000002']);
    }

    private function createConversation(array $attributes = []): Conversation
    {
        return Conversation::create(array_merge(['user_id' => $this->patient->id], $attributes));
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
        Message::create(['conversation_id' => $conversation->id, 'sender_id' => $this->patient->id, 'body' => 'رسالة 1']);
        Message::create(['conversation_id' => $conversation->id, 'sender_id' => $this->patient->id, 'body' => 'رسالة 2']);
        Message::create(['conversation_id' => $conversation->id, 'sender_id' => $this->patient->id, 'body' => 'رسالة 3']);

        $response = $this->actingAs($this->admin)->getJson('/api/admin/chat');

        $response->assertOk();
        $this->assertSame(3, $response->json('conversations.0.unread_count'));
    }

    // ─── Admin: عرض المحادثة والرسائل ────────────────────────────────────

    public function test_admin_can_view_conversation_with_messages(): void
    {
        $conversation = $this->createConversation();
        Message::create(['conversation_id' => $conversation->id, 'sender_id' => $this->patient->id, 'body' => 'مرحبا']);

        $this->actingAs($this->admin)
            ->getJson("/api/admin/chat/{$conversation->id}")
            ->assertOk()
            ->assertJsonPath('conversation.id', $conversation->id)
            ->assertJsonPath('messages.0.body', 'مرحبا');
    }

    public function test_viewing_conversation_marks_it_read_for_admin(): void
    {
        $conversation = $this->createConversation();
        $message = Message::create(['conversation_id' => $conversation->id, 'sender_id' => $this->patient->id, 'body' => 'مرحبا']);

        $this->actingAs($this->admin)->getJson("/api/admin/chat/{$conversation->id}");

        $this->assertSame($message->id, $conversation->fresh()->admin_last_read_message_id);
    }

    public function test_messages_cursor_pagination(): void
    {
        $conversation = $this->createConversation();
        for ($i = 1; $i <= 40; $i++) {
            Message::create(['conversation_id' => $conversation->id, 'sender_id' => $this->patient->id, 'body' => "رسالة $i"]);
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
        Storage::fake('public');
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

    public function test_patient_cannot_send_to_closed_conversation(): void
    {
        $this->createConversation(['status' => 'closed']);

        $this->actingAs($this->patient)
            ->postJson('/api/mobile/chat/send', ['body' => 'مرحبا'])
            ->assertUnprocessable();
    }

    public function test_patient_mobile_cursor_pagination(): void
    {
        $conversation = $this->createConversation();
        for ($i = 1; $i <= 35; $i++) {
            Message::create(['conversation_id' => $conversation->id, 'sender_id' => $this->admin->id, 'body' => "رد $i"]);
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
        $message = Message::create(['conversation_id' => $conversation->id, 'sender_id' => $this->admin->id, 'body' => 'رد']);

        $this->actingAs($this->patient)->getJson('/api/mobile/chat');

        $this->assertSame($message->id, $conversation->fresh()->patient_last_read_message_id);
    }
}
