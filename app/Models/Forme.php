<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Forme extends Model
{
    use HasFactory;
    protected $fillable = [
    'designation','imageforme','refforme'
    ];
    public function articles()
    {
        return $this->hasMany(Article::class,"formeID", 'refforme');
    }
}