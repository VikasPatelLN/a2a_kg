<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GraphOpsController extends Controller
{
    public function index()
    {
        return view('graphops.admin', [
            'status' => $this->statusPayload(),
        ]);
    }

    public function status(): JsonResponse
    {
        return response()->json($this->statusPayload());
    }

    protected function statusPayload(): array
    {
        return Cache::get('graphops:rebuild_status', [
            'state' => 'idle',
            'last_run_at' => null,
            'last_duration' => null,
            'message' => null,
        ]);
    }

    public function rebuild(Request $request): JsonResponse
    {
//        // Simple lock: add returns false if key exists
//        if (! Cache::add('graphops:rebuild_running', 1, now()->addHour())) {
//            return response()->json(['ok' => false, 'message' => 'Rebuild already running'], 409);
//        }

        // Use queue to run asynchronously
        \App\Jobs\RebuildGraph::dispatch(auth()->id());

        return response()->json(['ok' => true, 'message' => 'Rebuild started']);
    }

    public function cancel(): JsonResponse
    {
        Cache::forever('graphops:cancel', true);
        return response()->json(['ok' => true, 'message' => 'Cancel signal sent']);
    }
}
