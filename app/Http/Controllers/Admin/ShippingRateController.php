<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingRate;
use Illuminate\Http\Request;

class ShippingRateController extends Controller
{
    public function index()
    {
        $rates = ShippingRate::orderBy('zone')->get();
        return view('admin.shipping_rates.index', compact('rates'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'zone'               => 'required|string|max:50',
            'label'              => 'required|string|max:100',
            'base_price'         => 'required|integer|min:0',
            'price_per_kg'       => 'nullable|integer|min:0',
            'free_above'         => 'nullable|integer|min:0',
            'estimated_days_min' => 'required|integer|min:1',
            'estimated_days_max' => 'required|integer|min:1',
            'is_active'          => 'nullable|boolean',
            'notes'              => 'nullable|string|max:500',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        ShippingRate::create($data);
        return back()->with('success', 'Zone de livraison ajoutée.');
    }

    public function update(Request $request, ShippingRate $rate)
    {
        $data = $request->validate([
            'zone'               => 'required|string|max:50',
            'label'              => 'required|string|max:100',
            'base_price'         => 'required|integer|min:0',
            'price_per_kg'       => 'nullable|integer|min:0',
            'free_above'         => 'nullable|integer|min:0',
            'estimated_days_min' => 'required|integer|min:1',
            'estimated_days_max' => 'required|integer|min:1',
            'is_active'          => 'nullable|boolean',
            'notes'              => 'nullable|string|max:500',
        ]);
        $data['is_active']    = $request->boolean('is_active', true);
        $data['price_per_kg'] = $data['price_per_kg'] ?? 0;
        $data['free_above']   = $data['free_above'] ?? 0;
        $rate->update($data);
        return back()->with('success', 'Tarif mis à jour.');
    }

    public function destroy(ShippingRate $rate)
    {
        $rate->delete();
        return back()->with('success', 'Zone supprimée.');
    }
}
