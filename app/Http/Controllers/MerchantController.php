<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Order;
use App\Http\Requests\OrderStatsRequest;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MerchantController extends Controller
{
    public function __construct(
        MerchantService $merchantService
    ) {}

    /**
     * Useful order statistics for the merchant API.
     * 
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(OrderStatsRequest $request): JsonResponse
    {
        // TODO: Complete this method
        $orders = Order::whereBetween('created_at', [$request->input('from'), $request->input('to')])->get();
        $count = count($orders);

        $noAffiliate = Order::where('affiliate_id', null)->first();

        $commission_owed = 0;
        $revenue = 0;

        foreach ($orders as $order) {
            $commission_owed += $order->commission_owed;
            $revenue += $order->subtotal;
        }

        return response()->json([
            'count' => $count,
            'commissions_owed' => $commission_owed - $noAffiliate->commission_owed,
            'revenue' => $revenue
        ]);

    }
}
