<?php

namespace App\Http\Controllers;

use App\Models\Stockline;
use Illuminate\Http\Request;

class StocklineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $stocklines=Stockline::all();
            return response()->json($stocklines,200);
            } catch (\Exception $e) {
            return response()->json("Sélection impossible {$e->getMessage()}");
            }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $stockline=new Stockline([
            "articleID"=> $request->input('articleID'),
            "qte"=> $request->input('qte'),
            "date"=> now()
            ]);
            $stockline->save();
            return response()->json($stockline);
            
            } catch (\Exception $e) {
            return response()->json("insertion impossible {$e->getMessage()}");
            }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $stockline=Stockline::findOrFail($id);
            return response()->json($stockline);
        } catch (\Exception $e) {
            return response()->json("probleme de récupération des données {$e->getMessage()}");
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
        $stockline=Stockline::findorFail($id);
        $stockline->update($request->all());
        return response()->json($stockline);
        } catch (\Exception $e) {
        return response()->json("probleme de modification {$e->getMessage()}");
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
        $stockline=Stockline::findOrFail($id);


        $stockline->delete();
        return response()->json("stockline supprimée avec succes");
        } catch (\Exception $e) {
        return response()->json("probleme de suppression de stockline {$e->getMessage()}");
        }
    }
}