<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderLines extends Model
{
    use HasFactory;
    protected $fillable = [
        'linetotal','unitprice','orderID','qte','articleID','qte_d'
    ];
    public function order()
    {
        return $this->belongsTo(Order::class,"orderID");
    }
    public function article()
    {
        return $this->belongsTo(Article::class,"articleID","refEHK");
    }
}