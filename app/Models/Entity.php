<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    protected $fillable=['type','name','aliases','properties'];
    protected $casts=['aliases'=>'array','properties'=>'array'];
    public function subjects(){return $this->hasMany(Triple::class,'subject_id');}
    public function objects(){return $this->hasMany(Triple::class,'object_id');}
    public function mentions(){return $this->hasMany(Mention::class);}    
}
