<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory ;
    protected $fillable = [
        'articleID','emplacementID','qtestock'
    ];
    public function article()
    {
        return $this->belongsTo(Article::class,"articleID","refEHK");
    }
    public function emplacement()
    {
        return $this->belongsTo(Emplacement::class,"emplacementID" ,"pos");
    }
}