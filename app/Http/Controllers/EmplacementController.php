<?php

namespace App\Http\Controllers;

use App\Models\Emplacement;
use Illuminate\Http\Request;

class EmplacementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $pos=Emplacement::all();
            return response()->json($pos);
        } catch (\Exception $e) {
            return response()->json("probleme de récupération de la liste des pos");
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $pos=new Emplacement([
            "pos"=>$request->input("pos")
            ]);
            $pos->save();
            
            return response()->json($pos);
            
            } catch (\Exception $e) {
            return response()->json("insertion impossible");
            }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $pos=Emplacement::findOrFail($id);
            return response()->json($pos);
            } catch (\Exception $e) {
            return response()->json("probleme de récupération des données");
            }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $pos=Emplacement::findorFail($id);
            $pos->update($request->all());
            return response()->json($pos);
            } catch (\Exception $e) {
            return response()->json("probleme de modification");
            }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $pos=Emplacement::findOrFail($id);
            $pos->delete();
            return response()->json("pos supprimée avec succes");
            } catch (\Exception $e) {
            return response()->json("probleme de suppression de pos");
            }
    }
}