<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductAvailability;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductAvailabilityController extends Controller
{
    /**
     * List all availability for a specific product
     */
    public function index(Product $product)
    {
        $this->authorize('update', $product);

        $availabilities = $product->availabilityUnits()
            ->orderBy('parent_unit')
            ->orderBy('unit_name')
            ->paginate(20);

        return Inertia::render('Admin/Products/Availability', [
            'product' => [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
            ],
            'availabilities' => $availabilities,
        ]);
    }

    /**
     * Store new availability unit
     */
    public function store(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'parent_unit' => 'required|string|max:100',
            'unit_name' => 'required|string|max:100',
            'unit_code' => 'required|string|max:50|unique:product_availability,unit_code',
            'max_capacity' => 'required|integer|min:1',
            'description' => 'nullable|string|max:500',
            'status' => 'in:available,unavailable,maintenance',
        ]);

        $validated['product_id'] = $product->id;
        $validated['status'] = $validated['status'] ?? 'available';

        ProductAvailability::create($validated);

        return back()->with('success', 'Availability unit created successfully');
    }

    /**
     * Update availability unit
     */
    public function update(Request $request, Product $product, ProductAvailability $availability)
    {
        $this->authorize('update', $product);

        if ($availability->product_id !== $product->id) {
            abort(404);
        }

        $validated = $request->validate([
            'parent_unit' => 'required|string|max:100',
            'unit_name' => 'required|string|max:100',
            'unit_code' => 'required|string|max:50|unique:product_availability,unit_code,' . $availability->id,
            'max_capacity' => 'required|integer|min:1',
            'description' => 'nullable|string|max:500',
            'status' => 'in:available,unavailable,maintenance',
        ]);

        $availability->update($validated);

        return back()->with('success', 'Availability unit updated successfully');
    }

    /**
     * Delete availability unit
     */
    public function destroy(Product $product, ProductAvailability $availability)
    {
        $this->authorize('update', $product);

        if ($availability->product_id !== $product->id) {
            abort(404);
        }

        // Check if it's used in any booking
        if ($availability->bookingUnits()->exists()) {
            return back()->with('error', 'Cannot delete availability unit that is used in bookings');
        }

        $availability->delete();

        return back()->with('success', 'Availability unit deleted successfully');
    }

    /**
     * Bulk update status
     */
    public function bulkUpdateStatus(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:product_availability,id',
            'status' => 'required|in:available,unavailable,maintenance',
        ]);

        ProductAvailability::whereIn('id', $validated['ids'])
            ->where('product_id', $product->id)
            ->update(['status' => $validated['status']]);

        return back()->with('success', count($validated['ids']) . ' availability units updated successfully');
    }
}
