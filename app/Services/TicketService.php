<?php

namespace App\Services;

use App\Models\TicketSale;
use App\Models\TicketSaleItem;
use App\Models\TicketType;
use App\Models\Visit;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Events\VisitorCheckedIn;

class TicketService
{
    /**
     * Create a ticket sale, compute prices server-side and create per-ticket visits
     * @param array $payload
     * @param \Illuminate\Contracts\Auth\Authenticatable|null $user
     * @return TicketSale
     */
    public function createSale(array $payload, $user): TicketSale
    {
        return DB::transaction(function () use ($payload, $user) {
            $sale = TicketSale::create([
                'invoice_no' => $this->generateInvoiceNo(),
                'status' => 'open',
                'created_by' => $user?->id,
            ]);

            $total = 0;

            foreach ($payload['items'] as $item) {
                $type = TicketType::find($item['ticket_type_id']);
                if (! $type) {
                    throw new \RuntimeException('Ticket type not found: ' . $item['ticket_type_id']);
                }
                $quantity = (int) ($item['quantity'] ?? 1);
                $unitPrice = $type->price;
                $subtotal = bcmul((string)$unitPrice, (string)$quantity, 2);

                $saleItem = TicketSaleItem::create([
                    'ticket_sale_id' => $sale->id,
                    'ticket_type_id' => $type->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                // create per-visitor visit rows so each ticket is individually trackable
                for ($i = 0; $i < $quantity; $i++) {
                    Visit::create([
                        'ticket_sale_id' => $sale->id,
                        'ticket_sale_item_id' => $saleItem->id,
                        'visit_token' => (string) Str::uuid(),
                        'status' => 'available',
                    ]);
                }

                $total = bcadd((string)$total, (string)$subtotal, 2);
            }

            $sale->total_amount = $total;
            $sale->save();

            return $sale->load('items', 'items.ticketType');
        });
    }

    public function generateInvoiceNo(): string
    {
        return 'INV-' . strtoupper(Str::random(8));
    }

    /**
     * Atomic check-in by token. Prevents reuse via conditional update.
     * @throws \DomainException
     */
    public function checkInByToken(string $token, int $staffId): Visit
    {
        return DB::transaction(function () use ($token, $staffId) {
            $updated = DB::table('visits')
                ->where('visit_token', $token)
                ->where('status', 'available')
                ->update([
                    'status' => 'checked_in',
                    'checked_in_at' => now(),
                    'checked_in_by' => $staffId,
                ]);

            if ($updated === 0) {
                throw new \DomainException('Ticket invalid, already used, or revoked');
            }

            $visit = Visit::where('visit_token', $token)->first();

            // Dispatch event for real-time update (listener should be queued)
            VisitorCheckedIn::dispatch($visit);

            return $visit;
        });
    }
}
