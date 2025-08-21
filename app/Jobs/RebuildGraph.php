<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class RebuildGraph implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct() {}

    public function handle(): void
    {
        $started = now();

        $update = function (string $state, ?string $msg = null) use ($started) {
            Cache::put('graphops:rebuild_status', [
                'state' => $state,
                'last_run_at' => $started->toDateTimeString(),
                'last_duration' => now()->diffInSeconds($started),
                'message' => $msg,
            ], 3600);
        };

        $update('running', 'Starting rebuild…');

        try {
            // Guard: ensure entity_stats table exists before clearing
            if (! Schema::hasTable('entity_stats')) {
                Log::warning('entity_stats table missing; skipping clear.', [
                    'db' => config('database.default'),
                ]);
            } else {
                // Safer than TRUNCATE across envs; reset auto-increment
                DB::table('entity_stats')->delete();
                try {
                    DB::statement('ALTER TABLE entity_stats AUTO_INCREMENT = 1');
                } catch (\Throwable $e) {
                    // Ignore if engine doesn't support AUTO_INCREMENT or no id column
                    Log::info('Could not reset AUTO_INCREMENT on entity_stats', ['msg' => $e->getMessage()]);
                }
            }

            // TODO: Put your graph rebuild pipeline steps here.
            // Example placeholder work with periodic cancel checks
            for ($i = 0; $i < 10; $i++) {
                if (Cache::pull('graphops:cancel', false)) {
                    $update('failed', 'Cancelled by user');
                    return;
                }
                // simulate step
                usleep(200000); // 0.2s
            }

            $update('succeeded', 'Rebuild completed');
        } catch (\Throwable $e) {
            Log::error('Rebuild failed', ['ex' => $e]);
            $update('failed', $e->getMessage());
            throw $e;
        } finally {
            Cache::forget('graphops:rebuild_running');
        }
    }
}
