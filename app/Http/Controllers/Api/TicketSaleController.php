<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketSaleRequest;
use App\Models\TicketSale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketSaleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);
        $sales = TicketSale::with(['items', 'items.ticketType'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($sales);
    }

    public function show(TicketSale $ticketSale): JsonResponse
    {
        $ticketSale->load(['items', 'items.ticketType']);
        return response()->json(['data' => $ticketSale]);
    }

    public function store(StoreTicketSaleRequest $request): JsonResponse
    {
        $data = $request->validatedData();
        $user = $request->user();

        try {
            $sale = app(\App\Services\TicketService::class)->createSale($data, $user);

            return response()->json(['success' => true, 'sale' => $sale], 201);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Check in a visit by token. Staff must be authenticated.
     */
    public function checkIn(Request $request): JsonResponse
    {
        $token = $request->input('token');
        if (! $token) {
            return response()->json(['success' => false, 'message' => 'token is required'], 422);
        }

        $staffId = $request->user()?->id ?? null;

        try {
            $visit = app(\App\Services\TicketService::class)->checkInByToken($token, $staffId);
            return response()->json(['success' => true, 'visit' => $visit], 200);
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }
}
