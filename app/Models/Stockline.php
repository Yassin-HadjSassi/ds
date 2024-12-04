<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stockline extends Model
{
    use HasFactory ;
    protected $fillable = [
        'articleID','qte','date'
    ];
    public function article()
    {
        return $this->belongsTo(Article::class,"articleID","refEHK");
    }
}