<?php

namespace Tests\Feature;

use App\Models\OffenseRule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentChatControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'student']);
        Role::firstOrCreate(['name' => 'staff']);

        $this->student = User::factory()->student()->create();
        $this->student->assignRole('student');

        $this->staff = User::factory()->create();
        $this->staff->assignRole('staff');

        OffenseRule::factory()->count(3)->create();
    }

    // ─── Authorization ──────────────────────────────────────────────────────

    public function test_guest_is_redirected_from_chat_endpoint(): void
    {
        $response = $this->postJson(route('student.chat'), [
            'messages' => [['role' => 'user', 'content' => 'Hello']],
            'lang' => 'en',
            'studentName' => 'Test Student',
        ]);

        $response->assertStatus(401);
    }

    public function test_staff_cannot_access_chat_endpoint(): void
    {
        $response = $this->actingAs($this->staff)->postJson(route('student.chat'), [
            'messages' => [['role' => 'user', 'content' => 'Hello']],
            'lang' => 'en',
            'studentName' => 'Test',
        ]);

        $response->assertStatus(403);
    }

    // ─── Validation ─────────────────────────────────────────────────────────

    public function test_chat_requires_messages_field(): void
    {
        $response = $this->actingAs($this->student)->postJson(route('student.chat'), [
            'lang' => 'en',
            'studentName' => 'Test Student',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['messages']);
    }

    public function test_chat_requires_valid_lang(): void
    {
        $response = $this->actingAs($this->student)->postJson(route('student.chat'), [
            'messages' => [['role' => 'user', 'content' => 'Hello']],
            'lang' => 'jp',
            'studentName' => 'Test Student',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['lang']);
    }

    public function test_chat_requires_student_name(): void
    {
        $response = $this->actingAs($this->student)->postJson(route('student.chat'), [
            'messages' => [['role' => 'user', 'content' => 'Hello']],
            'lang' => 'en',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['studentName']);
    }

    public function test_chat_rejects_invalid_message_role(): void
    {
        $response = $this->actingAs($this->student)->postJson(route('student.chat'), [
            'messages' => [['role' => 'system', 'content' => 'Hello']],
            'lang' => 'en',
            'studentName' => 'Test Student',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['messages.0.role']);
    }

    // ─── Brain Not Trained ─────────────────────────────────────────────

    public function test_chat_returns_503_when_brain_not_trained(): void
    {
        $this->mock(\App\Services\ChatBrainService::class, function ($mock) {
            $mock->shouldReceive('loadModel')->once()->andReturn(false);
        });

        $response = $this->actingAs($this->student)->postJson(route('student.chat'), [
            'messages' => [['role' => 'user', 'content' => 'What is cheating?']],
            'lang' => 'en',
            'studentName' => 'Juan Dela Cruz',
        ]);

        $response->assertStatus(503)->assertJsonFragment(['error' => 'The AI assistant brain is not trained yet. Please run php artisan jam:train to train it.']);
    }

    // ─── Successful AI Response ─────────────────────────────────────────────

    public function test_chat_returns_reply_from_brain(): void
    {
        $offense = OffenseRule::first();

        $this->mock(\App\Services\ChatBrainService::class, function ($mock) use ($offense) {
            $mock->shouldReceive('loadModel')->once()->andReturn(true);
            $mock->shouldReceive('predict')->once()->andReturn($offense->code);
        });

        $response = $this->actingAs($this->student)->postJson(route('student.chat'), [
            'messages' => [['role' => 'user', 'content' => 'What is cheating?']],
            'lang' => 'en',
            'studentName' => 'Juan Dela Cruz',
        ]);

        $response->assertStatus(200);
        $this->assertStringContainsString($offense->code, $response->json('reply'));
        $this->assertStringContainsString($offense->title, $response->json('reply'));
    }

    public function test_chat_returns_fallback_if_no_prediction(): void
    {
        $this->mock(\App\Services\ChatBrainService::class, function ($mock) {
            $mock->shouldReceive('loadModel')->once()->andReturn(true);
            $mock->shouldReceive('predict')->once()->andReturn(null);
        });

        $response = $this->actingAs($this->student)->postJson(route('student.chat'), [
            'messages' => [['role' => 'user', 'content' => 'Some unknown gibberish']],
            'lang' => 'en',
            'studentName' => 'Juan Dela Cruz',
        ]);

        $response->assertStatus(200)->assertJsonFragment(['reply' => "I'm sorry, I didn't quite catch that. Could you provide a bit more detail about the offense you're asking about?"]);
    }
}
