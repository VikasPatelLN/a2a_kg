<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    protected $fillable=['user_id','type','label','config'];
    protected $casts=['config'=>'array'];
    public function documents(){return $this->hasMany(Document::class);}    
}
