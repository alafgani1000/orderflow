<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $storeOwnerId = auth()->user()->getStoreOwnerId();
        $query = Customer::where('user_id', $storeOwnerId);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->withCount('orders')->orderBy('name')->paginate(20)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->user()->getStoreOwnerId();

        $customer = Customer::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'display_text' => $customer->name.($customer->phone ? ' ('.$customer->phone.')' : ''),
                ],
                'message' => __('Pelanggan berhasil ditambahkan.'),
            ], 201);
        }

        return redirect()->route('customers.index')
            ->with('success', __('Pelanggan berhasil ditambahkan.'));
    }

    public function show(Customer $customer)
    {
        $this->authorize('view', $customer);

        $orders = $customer->orders()
            ->with('payments')
            ->orderByDesc('created_at')
            ->get();

        return view('customers.show', compact('customer', 'orders'));
    }

    public function edit(Customer $customer)
    {
        $this->authorize('update', $customer);

        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $this->authorize('update', $customer);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.show', $customer)
            ->with('success', __('Data pelanggan berhasil diperbarui.'));
    }

    public function destroy(Customer $customer)
    {
        $this->authorize('delete', $customer);

        if ($customer->orders()->exists()) {
            return back()->with('error', __('Pelanggan tidak dapat dihapus karena memiliki pesanan.'));
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', __('Pelanggan berhasil dihapus.'));
    }
}
