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
            // Create and save the stock entry
            $stock = new Stock([
                "articleID" => $request->input('articleID'),
                "emplacementID" => $request->input('emplacementID'),
                "qtestock" => $request->input('qtestock')
            ]);

            $oldstock = Stock::where('emplacementID', $request->input('emplacementID'))
            ->where('articleID', $request->input('articleID'))
            ->first();

            if ($oldstock) {
            // Update the existing stock quantity
                $oldstock->update([
                    "qtestock" => $stock->qtestock + $oldstock->qtestock
                ]);
            }
            else{
                $stock->save();
                 // Save the stock first
            }
             
    
            // Create and save the stockline entry using values from $stock
             // Save the stockline
            $stockline = new Stockline([
                "articleID" => $stock->articleID,  // Access properties using '->'
                "qte" => $stock->qtestock,
                "date" => now()
            ]);
            $stockline->save();
            // Return the saved stock object with stockline info
            return response()->json([
                'stock' => $stock,
                'stockline' => $stockline
            ], 201);
    
        } catch (\Exception $e) {
            // Return the error message with a proper HTTP status code
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
            "articleID" => $stock->articleID,  // Access properties using '->'
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
            "articleID" => $stock->articleID,  // Access properties using '->'
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
        // Fetch the order and its associated lines, including the article data for each line
        $order = Order::with('order_lines')->findOrFail($id);
        $orderlines = $order->order_lines;

        // Fetch only the stock entries that are relevant to the order's articles
        $articleIDs = $orderlines->pluck('articleID')->toArray();
        $stocks = Stock::
            whereIn('articleID', $articleIDs)
            ->get()
            ->groupBy('articleID'); // Group stocks by articleID for faster lookup

        $orderStatus = []; // To store the result list of stock positions for each order line
        $stocksChanged = [];// To store the list of changed stock

        // Loop through each order line to check availability
        foreach ($orderlines as $line) {
            $articleID = $line->articleID;
            $matchingStocks = $stocks->get($articleID, collect()); // Get stocks for the current article

            // Initialize variables to track stock status
            $qtedemander = 0;
            $positions = [];
            $totalAvailableInStock = 0; // Variable to accumulate available stock for the article
            
            $missingArticles = [];

            // Loop through the matching stocks for this article
            foreach ($matchingStocks as $stock) {
                if ($qtedemander < $line->qte_d) {
                    // Calculate how much can be fulfilled from this emplacement
                    $qtedisponible = min($stock->qtestock, $line->qte_d - $qtedemander);

                    // Subtract the quantity taken from the stock at this emplacement
                    $stock->qtestock -= $qtedisponible;
                    $stocksChanged[]= [$stock];

                    // Store the position (emplacement) and available quantity after fulfilling the order
                    $positions[] = [
                        'articleID' => $articleID,
                        'emplacement' => $stock->emplacementID,
                        'disponible' => $qtedisponible,
                        'qterest' => $stock->qtestock
                    ];

                    // Update the total available stock used for fulfilling the order
                    $qtedemander += $qtedisponible;
                }

                // Add the available stock to the total available stock for this article
                $totalAvailableInStock += $stock->qtestock;
            }

            // If the order line is not fully fulfilled, track the missing quantity
            
                $qtenondisponible = $line->qte_d - $qtedemander;
                if($qtenondisponible != 0)
                {
                    $missingArticles[] = [
                        'articleID' => $articleID,
                        'missing_quantity' => $qtenondisponible
                    ];
            }

            // Add the fulfilled order line stock status to the response, including total available stock
            $orderStatus[] = [
                'orderline' => [
                    'id' => $line->id,
                    'orderID' => $line->orderID,
                    'articleID' => $line->articleID,
                    'qte' => $line->qte,
                    'qte_d' => $line->qte_d,
                    'unitprice' => $line->unitprice,
                    'linetotal' => $line->linetotal,
                     // Added total remaining stock
                ],
                'positions' => $positions,
                'missing' => $missingArticles,
                'total_remaining_stock' => $totalAvailableInStock
            ];
        }

        // Return the status of the entire order with its lines and missing articles
        return response()->json([
            'order' => $order,
            'order_status' => $orderStatus,
            'stock_modifier' => $stocksChanged
        ], 200);

    } catch (\Exception $e) {
        // Handle exceptions and return a response
        return response()->json([
            'error' => 'Order check failed',
            'details' => $e->getMessage()
        ], 500);
    }
}


public function processOrder(Request $request)
{
    try {
        // Get the checkOrder result from the request body
        $checkOrderResult = $request->all();

        // Validate the presence of required data
        if (!isset($checkOrderResult['order']) || !isset($checkOrderResult['order_status']) || !isset($checkOrderResult['missing_articles'])) {
            return response()->json([
                'error' => 'Invalid data format or missing required data'
            ], 400);
        }

        // Extract data from the checkOrder result
        $order = (object) $checkOrderResult['order'];
        $orderStatus = $checkOrderResult['order_status'];
        $missingArticles = $checkOrderResult['missing_articles'];

        // Process the order lines and positions
        foreach ($orderStatus as $status) {
            // Ensure we're working with the correct data structure
            $line = (object) $status['line'];  // Order line (ensure it's an object)
            $positions = $status['positions'];  // Stock positions that were used

            // Deduct the stock and create stockline entries for the update
            foreach ($positions as $position) {
                // Ensure we're working with the correct structure (convert position to an object if it's an array)
                $position = (object) $position;

                // Find the stock based on the articleID and emplacement
                $stock = Stock::where('articleID', $position->articleID)
                              ->whereHas('emplacement', function ($query) use ($position) {
                                  $query->where('pos', $position->emplacement);
                              })
                              ->first();

                if ($stock) {
                    // Deduct the quantity from stock
                    $quantityToDeduct = $position->number_of_order;
                    $stock->qtestock -= $quantityToDeduct;
                    $stock->save();  // Save the updated stock

                    // Create a stockline entry for the deduction
                    $stockline = new Stockline([
                        'articleID' => $position->articleID,
                        'qte' => -$quantityToDeduct, // Negative because we're removing stock
                        'date' => now(),
                    ]);
                    $stockline->save();
                } else {
                    // If no stock found, handle the error or log it
                    return response()->json([
                        'error' => 'Stock not found for article ' . $position->articleID
                    ], 404);
                }
            }

            // Update the order line with the fulfilled quantity
            $line->qte_fulfilled = $line->qte;
            $line->save();
        }

        // After processing all lines, check if there are missing articles
        if (count($missingArticles) === 0) {
            // If no articles are missing, mark the order as fulfilled
            $order->status = 'fulfilled';
        } else {
            // If there are missing articles, mark the order as partially fulfilled
            $order->status = 'partial';
        }

        // Save the updated order
        $order->save();

        // Return the updated order status
        return response()->json([
            'order' => $order,
            'order_status' => $orderStatus,
            'missing_articles' => $missingArticles
        ], 200);

    } catch (\Exception $e) {
        // Handle any errors
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

        // Fetch and filter stock entries with emplacement details
        $filteredStocks = Stock::select('articleID', DB::raw('SUM(qtestock) as total_qtestock'))
            //->where('qtestock', '>=', $num)  // Apply the 'from' condition
            ->groupBy('articleID')  // Group by articleID and emplacementID
            ->havingRaw('SUM(qtestock) < ?', [$num])  // Apply the threshold condition
            ->get();

        return response()->json([
            'filtered_stocks' => $filteredStocks,
            'total_articles' => $filteredStocks->count()
        ], 200);

    } catch (\Exception $e) {
        // Handle any errors
        return response()->json([
            'error' => 'Failed to filter and group stocks',
            'details' => $e->getMessage()
        ], 500);
    }
}



}