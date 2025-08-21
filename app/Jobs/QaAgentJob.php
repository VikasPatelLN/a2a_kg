<?php

namespace App\Jobs;

use App\Models\{Question, Answer, Entity, Triple};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class QaAgentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Question $question) {}

    public function handle(): void
    {
        $q = $this->question->question;
        $ans = ['text' => "Something went wrong.", 'support' => []];
        // Improved regex for 'why is X related to Y' questions
        if (preg_match('/why\\s+is\\s+(.+?)\\s+related\\s+to\\s+(.+?)\\??$/i', $q, $m)) {
            $ans = $this->why(trim($m[1]), trim($m[2]));
        } elseif (preg_match('/how.*process\\s+(.+)\\s+work/i', $q, $m)) {
            $ans = $this->how(trim($m[1]));
        }
        Answer::create(['question_id' => $this->question->id, 'answer' => $ans['text'], 'support' => $ans['support']]);
    }

    protected function why(string $x,string $y): array
    {
        $ex=Entity::where('name',$x)->first(); $ey=Entity::where('name',$y)->first(); if(!$ex||!$ey) return ['text'=>'Entities not found.','support'=>[]];
        // BFS up to 5 hops in MySQL via in-PHP traversal
        $path=$this->bfs($ex->id,$ey->id,5); if(!$path) return ['text'=>'No path found within 5 hops.','support'=>[]];
        $names=Entity::whereIn('id',$path)->pluck('name','id');
        $segments=[]; for($i=0;$i<count($path)-1;$i++){ $a=$path[$i];$b=$path[$i+1]; $rel=Triple::where(function($q)use($a,$b){$q->where('subject_id',$a)->where('object_id',$b);})->orWhere(function($q)use($a,$b){$q->where('subject_id',$b)->where('object_id',$a);})->first(); $segments[]=['from'=>$names[$a]??$a,'rel'=>$rel?->predicate??'related_to','to'=>$names[$b]??$b]; }
        $text="Path found:\n".collect($segments)->map(fn($s)=>"{$s['from']} -[{$s['rel']}]-> {$s['to']}")->join("\n");
        return ['text'=>$text,'support'=>['path'=>$segments]];
    }

    protected function bfs(int $start,int $goal,int $maxDepth): array
    {
        $front=[[ $start ]]; $vis=[$start=>true]; for($d=0;$d<$maxDepth;$d++){ $next=[]; foreach($front as $p){ $last=end($p); $edges=Triple::where('subject_id',$last)->orWhere('object_id',$last)->get(); foreach($edges as $e){ $n=$e->subject_id==$last?$e->object_id:$e->subject_id; if(isset($vis[$n])) continue; $vis[$n]=true; $np=array_merge($p,[$n]); if($n==$goal) return $np; $next[]=$np; } } $front=$next; } return [];
    }

    protected function how(string $process): array
    {
        $proc=Entity::where('name',$process)->first(); if(!$proc) return ['text'=>"Process '$process' not found.",'support'=>[]];
        // steps are entities with part_of-> process, ordered by properties.order
        $steps = Triple::where('predicate','part_of')->where('object_id',$proc->id)->pluck('subject_id');
        $stepEntities = Entity::whereIn('id',$steps)->get()->sortBy(fn($e)=>$e->properties['order'] ?? 9999)->pluck('name')->values()->all();
        $text = $stepEntities? 'Steps: '.implode(' → ', $stepEntities) : 'No steps recorded.';
        return ['text'=>$text,'support'=>['steps'=>$stepEntities]];
    }
}
