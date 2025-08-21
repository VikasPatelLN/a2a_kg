<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Mention extends Model
{
    protected $fillable=['document_id','entity_id','text','offsets','features'];
    protected $casts=['offsets'=>'array','features'=>'array'];
    public function document(){return $this->belongsTo(Document::class);}    
    public function entity(){return $this->belongsTo(Entity::class);}    
}
