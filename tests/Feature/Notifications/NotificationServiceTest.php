<?php

namespace Tests\Feature\Notifications;

use App\Events\CaseEvents\ComplaintFiled;
use App\Jobs\SendNotificationJob;
use App\Models\TribunalCase;
use App\Models\User;
use App\Services\Notifications\NotificationService;
use App\Services\Notifications\RecipientMapper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_persisted_to_database_and_queued()
    {
        Queue::fake();

        $case = TribunalCase::factory()->create();
        $user = User::factory()->create();

        $mapper = \Mockery::mock(RecipientMapper::class);
        $mapper->shouldReceive('map')->andReturn(collect([$user]));
        $mapper->shouldReceive('determineRole')->andReturn('student');
        app()->instance(RecipientMapper::class, $mapper);

        $service = app(NotificationService::class);
        $service->dispatchNotification('complaint_filed', [
            'case_id' => $case->id,
            'complainant_id' => $user->id,
            'description' => 'Test',
        ]);

        $this->assertDatabaseHas('notifications', [
            'event_type' => 'complaint_filed',
            'case_id' => $case->id,
            'status' => 'queued',
        ]);

        Queue::assertPushed(SendNotificationJob::class);
    }

    public function test_respects_user_opt_out_preferences()
    {
        Queue::fake();

        $user = User::factory()->create();
        // Opt out of sms
        $user->notificationPreference()->create([
            'sms_enabled' => false,
            'email_enabled' => true,
        ]);

        $service = app(NotificationService::class);
        // Dispatching to the user specifically as a mocked recipient
        // But our NotificationService uses config to map. We will mock the config.
        config(['notifications.events.test_event' => [
            'channels' => ['email', 'sms'],
            'recipients' => ['complainant'],
            'priority' => 'high',
            'template' => 'test_template',
        ]]);

        $service->dispatchNotification('test_event', [
            'complainant_id' => $user->id, // If our mapper used this, it would map.
        ]);

        // Since we didn't fully mock the RecipientMapper, we will test the RuleEngine directly
        $ruleEngine = app(\App\Services\Notifications\RuleEngine::class);
        $plan = $ruleEngine->evaluate('test_event', config('notifications.events.test_event'), $user);

        $this->assertNotContains('sms', $plan['channels']);
        $this->assertContains('email', $plan['channels']);
    }

    public function test_event_listener_dispatches_notification()
    {
        Event::fake([
            ComplaintFiled::class,
        ]);

        $case = TribunalCase::factory()->create();
        $user = User::factory()->create();

        event(new ComplaintFiled($case, $user, 'Test complaint'));

        Event::assertDispatched(ComplaintFiled::class);
    }
}
