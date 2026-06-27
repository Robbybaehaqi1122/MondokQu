<?php

use App\Models\ActivityLog;
use App\Models\Santri;
use App\Models\User;
use App\Services\ActivityLogger;

test('creates activity log with full data', function (): void {
    $actor = User::factory()->create(['name' => 'Test User']);

    $logger = new ActivityLogger;
    $log = $logger->log(
        action: 'user_created',
        actor: $actor,
        target: null,
        description: 'Test description',
        properties: ['key' => 'value'],
        ipAddress: '192.168.1.1',
        userAgent: 'TestAgent/1.0',
    );

    expect($log)->toBeInstanceOf(ActivityLog::class);
    expect($log->actor_id)->toBe($actor->id);
    expect($log->actor_name)->toBe('Test User');
    expect($log->action)->toBe('user_created');
    expect($log->description)->toBe('Test description');
    expect($log->ip_address)->toBe('192.168.1.1');
    expect($log->user_agent)->toBe('TestAgent/1.0');
    expect($log->tenant_id)->toBe($actor->tenant_id);
    expect($log->properties)->toBe(['key' => 'value']);
});

test('creates activity log with minimal data uses System as actor', function (): void {
    $logger = new ActivityLogger;
    $log = $logger->log('system_action');

    expect($log->action)->toBe('system_action');
    expect($log->actor_name)->toBe('System');
    expect($log->actor_id)->toBeNull();
});

test('resolves target name for user', function (): void {
    $actor = User::factory()->create(['name' => 'Admin']);
    $target = User::factory()->create(['name' => 'John Doe', 'username' => 'johndoe']);

    $logger = new ActivityLogger;
    $log = $logger->log('user_updated', actor: $actor, target: $target);

    expect($log->target_name)->toBe('John Doe (@johndoe)');
    expect($log->target_type)->toBe(User::class);
    expect($log->target_id)->toBe($target->id);
});

test('preserves custom target_name from properties over resolved name', function (): void {
    $target = Santri::factory()->create(['full_name' => 'Ahmad Santri', 'nis' => '12345']);

    $logger = new ActivityLogger;
    $log = $logger->log(
        'santri_deleted',
        target: $target,
        properties: ['target_name' => 'Custom Override'],
    );

    expect($log->target_name)->toBe('Custom Override');
});
