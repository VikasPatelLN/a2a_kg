<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable=['user_id','question','analysis'];
    protected $casts=['analysis'=>'array'];
    public function answers(){return $this->hasMany(Answer::class);}
    public function answer() {
        return $this->hasOne(Answer::class);
    }
}
