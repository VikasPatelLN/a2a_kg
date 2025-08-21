<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Triple extends Model
{
    protected $fillable=['subject_id','predicate','object_id','evidence'];
    protected $casts=['evidence'=>'array'];
    public function subject(){return $this->belongsTo(Entity::class,'subject_id');}
    public function object(){return $this->belongsTo(Entity::class,'object_id');}
}
