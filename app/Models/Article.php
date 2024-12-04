<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;
    protected $fillable = [
        'designation','marque','accessoire','refOrigine','matiere',
        'prixHT','imageart','categorieID','formeID','refEHK'
    ];
    public function categorie()
    {
        return $this->belongsTo(Categorie::class,"categorieID", 'refcategorie');
    }
    public function forme()
    {
        return $this->belongsTo(Forme::class,"formeID", 'refforme');
    }
    public function stocks()
    {
        return $this->hasMany(Stock::class,"articleID",'refEHK');
    }
    public function order_lines()
    {
        return $this->hasMany(OrderLines::class ,"articleID",'refEHK');
    }
}