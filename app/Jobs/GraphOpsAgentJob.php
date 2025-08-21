<?php

namespace App\Jobs;

use App\Models\{Entity, Triple};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class GraphOpsAgentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public bool $rebuild;
    public function __construct(bool $rebuild = true) { $this->rebuild = $rebuild; }

    public function handle(): void
    {
        // Single-flight guard to avoid overlapping rebuilds
        $lock = cache()->lock('graphops:rebuild', 120);
        if (!$lock->get()) { return; }

        $this->rebuildEntityStats();
        $this->rebuildGraphEdges();
        $this->rebuildPartOfClosure(8);

       $lock->release();
    }

    protected function rebuildEntityStats(): void
    {
        // Truncate and recompute
        DB::table('entity_stats')->truncate();
        // degree/out_degree/in_degree
        $out = Triple::select('subject_id as id', DB::raw('COUNT(*) as c'))->groupBy('subject_id')->get()->keyBy('id');
        $in  = Triple::select('object_id  as id', DB::raw('COUNT(*) as c'))->groupBy('object_id')->get()->keyBy('id');
        $mentions = DB::table('mentions')->select('entity_id as id', DB::raw('COUNT(*) as c'))->whereNotNull('entity_id')->groupBy('entity_id')->get()->keyBy('id');
        $allIds = Entity::pluck('id');
        $now = now();
        $rows = [];
        foreach ($allIds as $id) {
            $o = $out[$id]->c ?? 0; $i = $in[$id]->c ?? 0; $m = $mentions[$id]->c ?? 0; $deg = $o + $i;
            $rows[] = ['entity_id'=>$id,'degree'=>$deg,'out_degree'=>$o,'in_degree'=>$i,'mentions'=>$m,'created_at'=>$now,'updated_at'=>$now];
            if (count($rows) >= 1000) { DB::table('entity_stats')->insert($rows); $rows = []; }
        }
        if ($rows) DB::table('entity_stats')->insert($rows);
    }

    protected function rebuildGraphEdges(): void
    {
        // Undirected canonical edge list for faster BFS: (a=min(s,o), b=max(s,o)) aggregate predicates
        DB::table('graph_edges')->truncate();
        $now = now();
        // Fetch all triples in chunks to aggregate
        $agg = [];
        Triple::select('subject_id','predicate','object_id')->orderBy('id')->chunk(2000, function($batch) use (&$agg){
            foreach ($batch as $t) {
                $a = min($t->subject_id, $t->object_id);
                $b = max($t->subject_id, $t->object_id);
                $key = $a.'|'.$b;
                if (!isset($agg[$key])) { $agg[$key] = ['a'=>$a,'b'=>$b,'preds'=>[],'w'=>0]; }
                $agg[$key]['preds'][$t->predicate] = true;
                $agg[$key]['w'] += 1;
            }
        });
        $rows = [];
        foreach ($agg as $g) {
            $rows[] = [
                'a_id'=>$g['a'], 'b_id'=>$g['b'],
                'predicates'=>json_encode(array_keys($g['preds'])),
                'weight'=>$g['w'], 'created_at'=>$now, 'updated_at'=>$now
            ];
            if (count($rows) >= 1000) { DB::table('graph_edges')->insert($rows); $rows = []; }
        }
        if ($rows) DB::table('graph_edges')->insert($rows);
    }

    protected function rebuildPartOfClosure(int $maxDepth = 8): void
    {
        DB::table('partof_closure')->truncate();
        // Seed depth 1 edges
        $edges = Triple::where('predicate','part_of')->get(['subject_id','object_id']);
        $now = now();
        $rows = [];
        foreach ($edges as $e) {
            $rows[] = ['ancestor_id'=>$e->object_id,'descendant_id'=>$e->subject_id,'depth'=>1,'created_at'=>$now,'updated_at'=>$now];
            if (count($rows) >= 1000) { DB::table('partof_closure')->insert($rows); $rows = []; }
        }
        if ($rows) DB::table('partof_closure')->insert($rows);

        // Iteratively expand: ancestor -> descendant where descendant is ancestor of next level
        for ($d = 2; $d <= $maxDepth; $d++) {
            // join closure depth d-1 with base edges
            $toInsert = DB::table('partof_closure as pc')
                ->join('triples as t','pc.descendant_id','=','t.object_id')
                ->where('t.predicate','part_of')
                ->where('pc.depth', $d-1)
                ->select('pc.ancestor_id','t.subject_id as descendant_id', DB::raw($d.' as depth'))
                ->get();
            $batch = [];
            foreach ($toInsert as $r) {
                // upsert unique pairs
                $batch[] = [
                    'ancestor_id'=>$r->ancestor_id,
                    'descendant_id'=>$r->descendant_id,
                    'depth'=>$r->depth,
                    'created_at'=>now(),'updated_at'=>now()
                ];
                if (count($batch) >= 1000) { $this->insertIgnore('partof_closure',$batch); $batch = []; }
            }
            if ($batch) { $this->insertIgnore('partof_closure',$batch); }
        }
    }

    protected function insertIgnore(string $table, array $rows): void
    {
        if (empty($rows)) return;
        // MySQL-specific INSERT IGNORE for unique pairs
        $cols = array_keys($rows[0]);
        $placeholders = '(' . implode(',', array_fill(0, count($cols), '?')) . ')';
        $sql = 'INSERT IGNORE INTO `'.$table.'` (`'.implode('`,`',$cols).'`) VALUES ' . ','.join(',', array_fill(0, count($rows), $placeholders));
        $vals = [];
        foreach ($rows as $r) foreach ($cols as $c) $vals[] = $r[$c];
        DB::insert($sql, $vals);
    }
}
