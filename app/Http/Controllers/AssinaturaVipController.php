<?php

namespace App\Http\Controllers;

use App\Models\AssinaturaVip;
use App\Models\ShopItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class AssinaturaVipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shopItems = Cache::get('shopItems', fn() => ShopItem::all());
        return Inertia::render('AssinaturaVip', ['shopItems' => $shopItems]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(AssinaturaVip $assinaturaVip)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AssinaturaVip $assinaturaVip)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AssinaturaVip $assinaturaVip)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AssinaturaVip $assinaturaVip)
    {
        //
    }
}
