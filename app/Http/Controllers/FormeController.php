<?php

namespace App\Http\Controllers;

use App\Models\Forme;
use Illuminate\Http\Request;

class FormeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $formes=Forme::all();
            return response()->json($formes,200);
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
            $forme=new Forme([
            "designation"=>$request->input("designation"),
            "refforme"=>$request->input("refforme"),
            "imageforme"=>$request->input("imageforme")
            ]);
            $forme->save();
            return response()->json($forme);
            
            
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
            $forme=Forme::findOrFail($id);
            return response()->json($forme);
            } catch (\Exception $e) {
            return response()->json("Sélection impossible {$e->getMessage()}");
            }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,$id)
    {
        try {
            $forme=Forme::findorFail($id);
            $forme->update($request->all());
            return response()->json($forme);
            } catch (\Exception $e) {
            return response()->json("Modification impossible {$e->getMessage()}");
            }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $forme=Forme::findOrFail($id);
            $forme->delete();
            return response()->json("Forme supprimée avec succes");
            } catch (\Exception $e) {
            return response()->json("Suppression impossible {$e->getMessage()}");
            }
    }

}