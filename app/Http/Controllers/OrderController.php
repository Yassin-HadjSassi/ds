<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderLines;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $orders=Order::with('order_lines')->get();
            return response()->json($orders,200);
            } catch (\Exception $e) {
            return response()->json("Sélection impossible {$e->getMessage()}");
            }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    // Temporarily remove validation
    // $validatedData = $request->validate([
    //     'userID' => 'required|exists:users,id',
    //     'status' => 'required|string',
    //     'totalnumber' => 'required|integer|min:1',
    //     'prixHT' => 'required|numeric|min:0',
    //     'prixtotal' => 'required|numeric|min:0',
    //     'orderLines' => 'required|array|min:1',
    //     'orderLines.*.articleID' => 'required|exists:articles,refEHK',
    //     'orderLines.*.qte' => 'required|integer|min:1',
    //     'orderLines.*.unitprice' => 'required|numeric|min:0',
    //     'orderLines.*.linetotal' => 'required|numeric|min:0',
    // ]);

    // Use request data directly (not recommended for production)
    $order = Order::create([
        'userID' => $request->userID,
        'status' => $request->status,
        'totalnumber' => $request->totalnumber,
        'prixHT' => $request->prixHT,
        'prixtotal' => $request->prixtotal,
        'date' => now()
    ]);

    foreach ($request->orderLines as $line) {
        $order->order_lines()->create([
            'articleID' => $line['articleID'],
            'qte' => $line['qte'],
            'qte_d' => $line['qte_d'],
            'unitprice' => $line['unitprice'],
            'linetotal' => $line['linetotal'],
            
        ]);
    }

    return response()->json(['message' => 'Order created successfully','order' => $order], 201);
}


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $order = Order::with('order_lines')->findOrFail($id);
            return response()->json($order);
        } catch (\Exception $e) {
            return response()->json("probleme de récupération des données {$e->getMessage()}");
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
{
    DB::beginTransaction();
    
    try {
        // Find the order
        $order = Order::findOrFail($id);

        // Update order details
        $order->update([
            'userID' => $request->userID,
            'status' => $request->status,
            'totalnumber' => $request->totalnumber,
            'prixHT' => $request->prixHT,
            'prixtotal' => $request->prixtotal,
            'date' => now()
        ]);

        // Sync the order lines (handle add, update, and delete)
        $order_lines = $request->order_lines;
        
        // Deleting removed order lines
        $existingOrderLines = $order->order_lines->pluck('id')->toArray();
        $newOrderLines = collect($order_lines)->pluck('id')->toArray();
        $deletedOrderLines = array_diff($existingOrderLines, $newOrderLines);

        // Delete removed order lines
        OrderLines::whereIn('id', $deletedOrderLines)->delete();

        // Add or update order lines
        foreach ($order_lines as $line) {
            if (isset($line['id'])) {
                // Update existing order line
                $orderLine = OrderLines::find($line['id']);
                if ($orderLine) {
                    $orderLine->update([
                        'articleID' => $line['articleID'],
                        'qte' => $line['qte'],
                        'qte_d' => $line['qte_d'],
                        'unitprice' => $line['unitprice'],
                        'linetotal' => $line['linetotal'],
                    ]);
                }
            } else {
                // Add new order line
                $order->order_lines()->create([
                    'articleID' => $line['articleID'],
                    'qte' => $line['qte'],
                    'qte_d' => $line['qte_d'],
                    'unitprice' => $line['unitprice'],
                    'linetotal' => $line['linetotal'],
                ]);
            }
        }

        DB::commit();

        return response()->json(['message' => 'Order updated successfully'], 200);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'Failed to update order', 'error' => $e->getMessage()], 500);
    }
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
{
    try {
        // Find the order by ID
        $order = Order::findOrFail($id);

        // Check if the order status is 'Order Demander'
        if ($order->status !== 'Order Demander') {
            return response()->json("Vous ne pouvez supprimer qu'une commande avec le statut 'Order Demander'.", 400);
        }

        // Delete associated order lines
        $order->order_lines()->delete(); // Assuming you have a relationship 'orderLines' defined

        // Now delete the order itself
        $order->delete();

        return response()->json("Order and associated order lines deleted successfully.");
    } catch (\Exception $e) {
        return response()->json("Problem deleting order: {$e->getMessage()}", 500);
    }
}


    public function ordersPaginate()
    {
        try {
            $perPage = request()->input('pageSize', 5);
            $orders = Order::paginate($perPage); // Basic pagination test
    
            return response()->json([
                'products' => $orders->items(),
                'totalPages' => $orders->lastPage(),
            ]);
        } catch (\Exception $e) {
            return response()->json("Selection impossible {$e->getMessage()}");
        }
        
    }

}