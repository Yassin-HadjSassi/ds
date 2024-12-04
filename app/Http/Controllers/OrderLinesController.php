<?php

namespace App\Http\Controllers;

use App\Models\OrderLines;
use Illuminate\Http\Request;

class OrderLinesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $orderLines=OrderLines::with('article')->get();;
            return response()->json($orderLines,200);
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
            $orderline=new OrderLines([
            "articleID"=> $request->input('articleID'),
            "qte"=> $request->input('qte'),
            "qte_d"=> $request->input('qte_d'),
            "orderID"=> $request->input('orderID'),
            "unitprice"=> $request->input('unitprice'),
            "linetotal"=> $request->input('linetotal'),
            ]);
            $orderline->save();
            return response()->json($orderline);
            
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
            $orderline=OrderLines::findOrFail($id);
            return response()->json($orderline);
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
        $orderline=OrderLines::findorFail($id);
        $orderline->update($request->all());
        return response()->json($orderline);
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
        $orderline=OrderLines::findOrFail($id);


        $orderline->delete();
        return response()->json("orderline supprimée avec succes");
        } catch (\Exception $e) {
        return response()->json("probleme de suppression d'orderline {$e->getMessage()}");
        }
    }

    public function orderLinesPaginate()
    {
        try {
            $perPage = request()->input('pageSize', 5);
            $orderLines = OrderLines::paginate($perPage); // Basic pagination test
    
            return response()->json([
                'products' => $orderLines->items(),
                'totalPages' => $orderLines->lastPage(),
            ]);
        } catch (\Exception $e) {
            return response()->json("Selection impossible {$e->getMessage()}");
        }
        
    }
}