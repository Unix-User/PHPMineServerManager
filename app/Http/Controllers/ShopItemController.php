<?php

namespace App\Http\Controllers;

use App\Models\ShopItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ShopItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('perPage', 10);
        $page = $request->get('page', 1);

        $items = Cache::remember("shop.items.page.$page", 60, function () use ($perPage, $page) {
            return ShopItem::query()
                ->orderBy('created_at', 'desc')
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();
        });

        return response()->json([
            'items' => $items,
            'page' => $page,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // TODO: Implement store method
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // TODO: Implement show method
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // TODO: Implement update method
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = ShopItem::findOrFail($id);
        $item->delete();

        Cache::tags('shop.items')->flush();

        return redirect()->back();
    }
}



