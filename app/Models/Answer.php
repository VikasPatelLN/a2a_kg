<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $fillable=['question_id','answer','support'];
    protected $casts=['support'=>'array'];
    public function question(){return $this->belongsTo(Question::class);}    
}
