<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use App\Jobs\QaAgentJob;

class QaController extends Controller
{
    public function ask(Request $request)
    {
        $data = $request->validate(['q'=>'required|string|min:3']);
        $question = Question::create(['user_id'=>1, 'question'=>$data['q']]);
        QaAgentJob::dispatchSync($question);
        return redirect()->route('dashboard')->with('status','Answer generated');
    }
}
