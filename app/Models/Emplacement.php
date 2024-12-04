<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emplacement extends Model
{
    use HasFactory;
    protected $fillable = [
    'pos'
    ];
    public function stocks()
    {
        return $this->hasMany(Stock::class,"stockID","pos");
    }
}