<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Stock;
use App\Models\Stockline;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $stocks=Stock::with('article')->get();
            return response()->json($stocks,200);
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
            $stock = new Stock([
                "articleID" => $request->input('articleID'),
                "emplacementID" => $request->input('emplacementID'),
                "qtestock" => $request->input('qtestock')
            ]);

            $oldstock = Stock::where('emplacementID', $request->input('emplacementID'))
            ->where('articleID', $request->input('articleID'))
            ->first();

            if ($oldstock) {
                $oldstock->update([
                    "qtestock" => $stock->qtestock + $oldstock->qtestock
                ]);
            }
            else{
                $stock->save();
            }
             
    
            $stockline = new Stockline([
                "articleID" => $stock->articleID, 
                "qte" => $stock->qtestock,
                "date" => now()
            ]);
            $stockline->save();
            return response()->json([
                'stock' => $stock,
                'stockline' => $stockline
            ], 201);
    
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Insertion impossible',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $stock=Stock::findOrFail($id);
            return response()->json($stock);
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
        $stock=Stock::findorFail($id);
        $newstock = new Stock([
            "articleID" => $request->input('articleID'),
            "emplacementID" => $request->input('emplacementID'),
            "qtestock" => $request->input('qtestock')
        ]);
        $stockline = new Stockline([
            "articleID" => $stock->articleID, 
            "qte" => $newstock->qtestock - $stock->qtestock,
            "date" => now()
        ]);
        $stock->update([
            "emplacementID" => $request->input('emplacementID'),
            "qtestock" => $request->input('qtestock')
        ]);
        $stockline->save();
        return response()->json([
            'stock' => $stock,
            'stockline' => $stockline
        ]);
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
        $stock=Stock::findOrFail($id);

        $stockline = new Stockline([
            "articleID" => $stock->articleID, 
            "qte" =>  - $stock->qtestock,
            "date" => now()
        ]);

        $stockline->save();

        $stock->delete();
        return response()->json("Stock supprimée avec succes");
        } catch (\Exception $e) {
        return response()->json("probleme de suppression de Stock {$e->getMessage()}");
        }
    }

    public function checkOrder($id)
{
    try {
        $order = Order::with('order_lines')->findOrFail($id);
        $orderlines = $order->order_lines;

        $articleIDs = $orderlines->pluck('articleID')->toArray();
        $stocks = Stock::
            whereIn('articleID', $articleIDs)
            ->get()
            ->groupBy('articleID'); 

        $orderStatus = []; 
        $stocksChanged = [];

        foreach ($orderlines as $line) {
            $articleID = $line->articleID;
            $matchingStocks = $stocks->get($articleID, collect()); 

            $qtedemander = 0;
            $positions = [];
            $totalAvailableInStock = 0; 
            
            $missingArticles = [];

            foreach ($matchingStocks as $stock) {
                if ($qtedemander < $line->qte_d) {
                    
                    $qtedisponible = min($stock->qtestock, $line->qte_d - $qtedemander);

                    $stock->qtestock -= $qtedisponible;
                    $stocksChanged[]= [$stock];

                    $positions[] = [
                        'articleID' => $articleID,
                        'emplacement' => $stock->emplacementID,
                        'disponible' => $qtedisponible,
                        'qterest' => $stock->qtestock
                    ];

                    $qtedemander += $qtedisponible;
                }

                $totalAvailableInStock += $stock->qtestock;
            }

            
                $qtenondisponible = $line->qte_d - $qtedemander;
                if($qtenondisponible != 0)
                {
                    $missingArticles[] = [
                        'articleID' => $articleID,
                        'missing_quantity' => $qtenondisponible
                    ];
            }

            $orderStatus[] = [
                'orderline' => [
                    'id' => $line->id,
                    'orderID' => $line->orderID,
                    'articleID' => $line->articleID,
                    'qte' => $line->qte,
                    'qte_d' => $line->qte_d,
                    'unitprice' => $line->unitprice,
                    'linetotal' => $line->linetotal,
                ],
                'positions' => $positions,
                'missing' => $missingArticles,
                'total_remaining_stock' => $totalAvailableInStock
            ];
        }

        return response()->json([
            'order' => $order,
            'order_status' => $orderStatus,
            'stock_modifier' => $stocksChanged
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Order check failed',
            'details' => $e->getMessage()
        ], 500);
    }
}


public function processOrder(Request $request)
{
    try {
        $checkOrderResult = $request->all();

        if (!isset($checkOrderResult['order']) || !isset($checkOrderResult['order_status']) || !isset($checkOrderResult['missing_articles'])) {
            return response()->json([
                'error' => 'Invalid data format or missing required data'
            ], 400);
        }

        $order = (object) $checkOrderResult['order'];
        $orderStatus = $checkOrderResult['order_status'];
        $missingArticles = $checkOrderResult['missing_articles'];

        foreach ($orderStatus as $status) {
            $line = (object) $status['line'];  
            $positions = $status['positions']; 

            foreach ($positions as $position) {
                $position = (object) $position;

                $stock = Stock::where('articleID', $position->articleID)
                              ->whereHas('emplacement', function ($query) use ($position) {
                                  $query->where('pos', $position->emplacement);
                              })
                              ->first();

                if ($stock) {
                    $quantityToDeduct = $position->number_of_order;
                    $stock->qtestock -= $quantityToDeduct;
                    $stock->save(); 

                    $stockline = new Stockline([
                        'articleID' => $position->articleID,
                        'qte' => -$quantityToDeduct, 
                        'date' => now(),
                    ]);
                    $stockline->save();
                } else {
                    return response()->json([
                        'error' => 'Stock not found for article ' . $position->articleID
                    ], 404);
                }
            }

            $line->qte_fulfilled = $line->qte;
            $line->save();
        }

        if (count($missingArticles) === 0) {
            $order->status = 'fulfilled';
        } else {
            $order->status = 'partial';
        }

        $order->save();

        return response()->json([
            'order' => $order,
            'order_status' => $orderStatus,
            'missing_articles' => $missingArticles
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Order processing failed',
            'details' => $e->getMessage()
        ], 500);
    }
}


public function manque($num)
{
    try {

        if (!is_numeric($num)) {
            return response()->json([
                'error' => 'Invalid input values. Both values must be positive numbers.'
            ], 400);
        }

        $filteredStocks = Stock::select('articleID', DB::raw('SUM(qtestock) as total_qtestock'))
            ->groupBy('articleID')  
            ->havingRaw('SUM(qtestock) < ?', [$num]) 
            ->get();

        return response()->json([
            'filtered_stocks' => $filteredStocks,
            'total_articles' => $filteredStocks->count()
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to filter and group stocks',
            'details' => $e->getMessage()
        ], 500);
    }
}



}