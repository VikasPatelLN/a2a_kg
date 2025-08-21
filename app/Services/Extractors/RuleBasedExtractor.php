<?php

namespace App\Services\Extractors;

class RuleBasedExtractor
{
    public function extract(string $text): array
    {
        $triples=[];$entities=[];
        $patterns=[
            ['pred'=>'depends_on','regex'=>'/(.+?)\s+depends on\s+(.+?)[\.;]/i'],
            ['pred'=>'part_of','regex'=>'/(.+?)\s+is part of\s+(.+?)[\.;]/i'],
            ['pred'=>'related_to','regex'=>'/(.+?)\s+is related to\s+(.+?)[\.;]/i'],
        ];
        foreach($patterns as $p){ if(preg_match_all($p['regex'],$text,$m,PREG_SET_ORDER)){ foreach($m as $mm){ $s=trim($mm[1]);$o=trim($mm[2]); $entities[]=['name'=>$s]; $entities[]=['name'=>$o]; $triples[]=['subject'=>$s,'predicate'=>$p['pred'],'object'=>$o,'source'=>'rule']; } } }
        $seen=[]; $entities=array_values(array_filter($entities,function($e)use(&$seen){$k=mb_strtolower($e['name']); if(isset($seen[$k]))return false; $seen[$k]=1; return true;}));
        return ['entities'=>$entities,'triples'=>$triples];
    }
}
