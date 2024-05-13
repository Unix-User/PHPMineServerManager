<?php

namespace App\Http\Controllers;

use App\Models\ShopItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ShopItemController extends Controller
{
    protected $rules = [
        'name' => 'required',
        'description' => 'required',
        'price' => 'required|numeric',
        'item_photo_path' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shopItems = Cache::get('shopItems', fn() => ShopItem::all());
        return Inertia::render('ShopItems', ['shopItems' => $shopItems]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate($this->rules);
        $image = $request->file('item_photo_path');
        if ($image) {
            $fileName = time() . '.' . $image->getClientOriginalExtension();
            $imagePath = Storage::disk('public')->put('shop-item-photos/' . $fileName, file_get_contents($image));
            $validatedData['item_photo_path'] = $imagePath;
        }

        $shopItem = ShopItem::create($validatedData);
        Cache::forget('shopItems');
        return redirect()->route('shopItems.index')->with('message', 'Item criado com sucesso!');
    }

    /**
     * Display the specified resource.
     * @param int $id
     * @return \Inertia\Response
     */
    public function show(int $id)
    {
        $shopItem = Cache::get('shopItem_' . $id, fn() => ShopItem::findOrFail($id));
        return Inertia::render('ShopItems/Show', ['shopItem' => $shopItem]);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, int $id)
    {
        $shopItem = ShopItem::findOrFail($id);
        $validatedData = $request->validate($this->rules);
        $image = $request->file('item_photo_path');
        if ($image) {
            $fileName = time() . '.' . $image->getClientOriginalExtension();
            $imagePath = Storage::disk('public')->put('shop-item-photos/' . $fileName, file_get_contents($image));
            $validatedData['item_photo_path'] = $imagePath;
        } else if ($request->input('remove_image')) {
            $validatedData['item_photo_path'] = null;
        }

        $shopItem->update($validatedData);
        Cache::forget('shopItems');
        Cache::forget('shopItem_' . $id);
        return redirect()->route('shopItems.index');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(int $id)
    {
        $shopItem = ShopItem::findOrFail($id);
        $shopItem->delete();
        Cache::forget('shopItems');
        Cache::forget('shopItem_' . $id);
        return redirect()->route('shopItems.index');
    }
}


