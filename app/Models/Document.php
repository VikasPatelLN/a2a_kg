<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable=['source_id','external_id','title','content','metadata'];
    protected $casts=['metadata'=>'array'];
    public function source(){return $this->belongsTo(Source::class);}    
    public function mentions(){return $this->hasMany(Mention::class);}    
}
