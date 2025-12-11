<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AllReportsExport implements FromCollection, WithHeadings
{
    protected $startDate;
    protected $endDate;

    public function __construct(string $startDate, string $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        // Build the combined rows similar to CSV stream
        $rows = new Collection();

        $bookings = \App\Models\Booking::with(['creator'])->whereBetween('checkin', [$this->startDate, $this->endDate])->get();
        $sales = \App\Models\TicketSale::with(['cashier'])->whereBetween('sale_date', [$this->startDate, $this->endDate])->get();
        $parkingTx = \App\Models\ParkingTransaction::with(['user'])->whereBetween('created_at', [$this->startDate, $this->endDate])->get();
        $parkingBookings = \App\Models\ParkingBooking::with(['user'])->whereBetween('created_at', [$this->startDate, $this->endDate])->get();
        $monitorings = \App\Models\ParkingMonitoring::with(['user'])->whereBetween('created_at', [$this->startDate, $this->endDate])->get();

        foreach ($bookings as $b) {
            $rows->push([
                'booking',
                $b->id,
                $b->booking_code,
                $b->checkin,
                $b->customer_name,
                '',
                '',
                $b->night_count ?? '',
                $b->total_amount ?? '',
                $b->status,
                '',
                $b->creator?->name ?? '',
            ]);
        }

        foreach ($sales as $s) {
            $rows->push([
                'ticket_sale',
                $s->id,
                $s->invoice_no,
                $s->sale_date,
                $s->cashier?->name ?? '',
                '',
                '',
                $s->total_qty ?? '',
                $s->net_amount ?? '',
                '',
                '',
                json_encode(['gross_amount' => $s->gross_amount, 'discount' => $s->discount_amount]),
            ]);
        }

        foreach ($parkingTx as $t) {
            $rows->push([
                'parking_transaction',
                $t->id,
                $t->transaction_code ?? '',
                $t->created_at ?? '',
                $t->user?->name ?? $t->created_by_name ?? '',
                $t->vehicle_number ?? '',
                $t->vehicle_type ?? '',
                $t->vehicle_count ?? '',
                $t->total_amount ?? '',
                $t->status ?? '',
                $t->notes ?? '',
                '',
            ]);
        }

        foreach ($parkingBookings as $b) {
            $rows->push([
                'parking_booking',
                $b->id,
                $b->booking_code ?? '',
                $b->created_at ?? '',
                $b->user?->name ?? '',
                '',
                $b->vehicle_type ?? '',
                $b->vehicle_count ?? '',
                $b->total_amount ?? '',
                $b->status ?? '',
                $b->notes ?? '',
                '',
            ]);
        }

        foreach ($monitorings as $m) {
            $rows->push([
                'parking_monitoring',
                $m->id,
                '',
                $m->created_at ?? '',
                $m->user?->name ?? '',
                '',
                $m->vehicle_type ?? '',
                $m->vehicle_count ?? '',
                $m->amount ?? '',
                $m->status ?? '',
                $m->notes ?? '',
                '',
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'source', 'id', 'code_or_invoice', 'date', 'name', 'vehicle_number', 'vehicle_type', 'qty', 'amount', 'status', 'notes', 'extra'
        ];
    }
}
