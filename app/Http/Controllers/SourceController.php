<?php

namespace App\Http\Controllers;

use App\Models\Source;
use App\Jobs\CrawlerAgentJob;
use Illuminate\Http\Request;

class SourceController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => 'required|min:3',
            'start_url' => 'required|url',
            'depth' => 'nullable|integer|min:0|max:5',
            'pages' => 'nullable|integer|min:1|max:500',
        ]);
        $src = Source::create([
            'user_id' => 1, // optional auth; adjust if using auth
            'type' => 'web',
            'label' => $data['label'],
            'config' => ['start_url'=>$data['start_url']],
        ]);
        $depth = $data['depth'] ?? 2; $pages = $data['pages'] ?? 50;
        // sync queue: will run within request; okay for small crawls
        CrawlerAgentJob::dispatchSync($src, $depth, $pages);
        return redirect()->route('dashboard')->with('status','Crawl completed');
    }
}
