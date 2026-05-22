<?php

use App\Models\DataExport;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('expired data exports are pruned with their files', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $expiredExport = DataExport::query()->create([
        'user_id' => $user->id,
        'type' => DataExport::TYPE_SANTRI,
        'name' => 'Export Lama',
        'status' => DataExport::STATUS_COMPLETED,
        'disk' => 'local',
        'filename' => 'data-santri-lama.csv',
        'row_count' => 10,
        'expires_at' => now()->subDay(),
    ]);
    $expiredPath = 'exports/'.$expiredExport->id.'/data-santri-lama.csv';
    Storage::disk('local')->put($expiredPath, 'expired export');
    $expiredExport->forceFill(['path' => $expiredPath])->save();

    $activeExport = DataExport::query()->create([
        'user_id' => $user->id,
        'type' => DataExport::TYPE_SANTRI,
        'name' => 'Export Aktif',
        'status' => DataExport::STATUS_COMPLETED,
        'disk' => 'local',
        'filename' => 'data-santri-aktif.csv',
        'row_count' => 10,
        'expires_at' => now()->addDay(),
    ]);
    $activePath = 'exports/'.$activeExport->id.'/data-santri-aktif.csv';
    Storage::disk('local')->put($activePath, 'active export');
    $activeExport->forceFill(['path' => $activePath])->save();

    $processingExport = DataExport::query()->create([
        'user_id' => $user->id,
        'type' => DataExport::TYPE_SANTRI,
        'name' => 'Export Masih Jalan',
        'status' => DataExport::STATUS_PROCESSING,
        'disk' => 'local',
        'filename' => 'data-santri-processing.csv',
        'row_count' => 10,
        'expires_at' => now()->subDay(),
    ]);
    $processingPath = 'exports/'.$processingExport->id.'/data-santri-processing.csv';
    Storage::disk('local')->put($processingPath, 'processing export');
    $processingExport->forceFill(['path' => $processingPath])->save();

    $this->artisan('exports:prune')
        ->expectsOutput('Data export pruning completed. Pruned: 1.')
        ->assertSuccessful();

    expect(DataExport::query()->whereKey($expiredExport->id)->exists())->toBeFalse();
    expect(DataExport::query()->whereKey($activeExport->id)->exists())->toBeTrue();
    expect(DataExport::query()->whereKey($processingExport->id)->exists())->toBeTrue();
    Storage::disk('local')->assertMissing($expiredPath);
    Storage::disk('local')->assertExists($activePath);
    Storage::disk('local')->assertExists($processingPath);
});

test('data export prune dry run does not delete records or files', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $export = DataExport::query()->create([
        'user_id' => $user->id,
        'type' => DataExport::TYPE_SANTRI_INVOICES,
        'name' => 'Export Dry Run',
        'status' => DataExport::STATUS_COMPLETED,
        'disk' => 'local',
        'filename' => 'tagihan-dry-run.csv',
        'row_count' => 3,
        'expires_at' => now()->subDay(),
    ]);
    $path = 'exports/'.$export->id.'/tagihan-dry-run.csv';
    Storage::disk('local')->put($path, 'dry run export');
    $export->forceFill(['path' => $path])->save();

    $this->artisan('exports:prune --dry-run')
        ->expectsOutput('1 expired data export(s) would be pruned.')
        ->assertSuccessful();

    expect(DataExport::query()->whereKey($export->id)->exists())->toBeTrue();
    Storage::disk('local')->assertExists($path);
});
