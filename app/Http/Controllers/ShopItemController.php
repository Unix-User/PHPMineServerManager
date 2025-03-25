<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\ShopItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Cache, Storage, Log};
use Inertia\Inertia;
use Illuminate\Support\Str;

class ShopItemController extends Controller
{
    protected $rules = [
        'name' => 'required',
        'description' => 'required',
        'price' => 'required|numeric',
        'item_photo_path' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'link' => 'nullable|url|max:2048',
    ];

    protected function getImageUrl($path): string
    {
        return $path && Storage::disk('public')->exists($path)
            ? asset('storage/' . $path)
            : asset('storage/shop-item-photos/default.png');
    }

    protected function handleImageUpload($image, $oldPath = null): string
    {
        Storage::disk('public')->makeDirectory('shop-item-photos');
        
        if ($oldPath) {
            $this->deleteImageIfNotDefault($oldPath);
        }

        if (!$image->isValid()) {
            throw new \Exception('Arquivo de imagem inválido');
        }

        $fileName = Str::uuid() . '.' . $image->getClientOriginalExtension();
        $path = $image->storeAs('shop-item-photos', $fileName, 'public');

        if (!$path) {
            throw new \Exception('Erro ao fazer upload da imagem');
        }

        return $path;
    }

    public function index()
    {
        $shopItems = Cache::remember('shopItems', 3600, function() {
            return ShopItem::all()->map(fn($item) => tap($item, function($item) {
                $item->image = $this->getImageUrl($item->item_photo_path);
            }));
        });
        
        return Inertia::render('ShopItems', compact('shopItems'));
    }

    public function getTopThreeItems()
    {
        return Cache::remember('topThreeShopItems', 3600, function() {
            return ShopItem::take(3)->get()->map(function($item) {
                $item->image = $this->getImageUrl($item->item_photo_path);
                return $item;
            });
        });
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate($this->rules);
        
        try {
            if ($request->hasFile('item_photo_path')) {
                $validatedData['item_photo_path'] = $this->handleImageUpload($request->file('item_photo_path'));
            } else {
                $validatedData['item_photo_path'] = 'shop-item-photos/default.png';
            }

            ShopItem::create($validatedData);
            Cache::forget('shopItems');
            
            return redirect()->route('shop')->with('success', 'Item criado com sucesso!');
            
        } catch (\Exception $e) {
            return back()->withErrors(['item_photo_path' => $e->getMessage()]);
        }
    }

    public function show(int $id)
    {
        $shopItem = Cache::remember('shopItem_' . $id, 3600, fn() => ShopItem::findOrFail($id));
        return Inertia::render('ShopItems/Show', compact('shopItem'));
    }

    public function update(Request $request, int $id)
    {
        $shopItem = ShopItem::findOrFail($id);
        $validatedData = $request->validate($this->rules);

        try {
            if ($request->hasFile('item_photo_path')) {
                $validatedData['item_photo_path'] = $this->handleImageUpload(
                    $request->file('item_photo_path'),
                    $shopItem->item_photo_path
                );
            } elseif ($request->has('remove_image')) {
                $this->deleteImageIfNotDefault($shopItem->item_photo_path);
                $validatedData['item_photo_path'] = 'shop-item-photos/default.png';
            } else {
                unset($validatedData['item_photo_path']);
            }

            $shopItem->update($validatedData);
            Cache::forget('shopItems');
            Cache::forget('shopItem_' . $id);
            
            return redirect()->route('shop')->with('success', 'Item atualizado com sucesso!');
            
        } catch (\Exception $e) {
            return back()->withErrors(['item_photo_path' => $e->getMessage()]);
        }
    }

    public function destroy(int $id)
    {
        $shopItem = ShopItem::findOrFail($id);
        
        $this->deleteImageIfNotDefault($shopItem->item_photo_path);
        $shopItem->delete();
        
        Cache::forget('shopItems');
        Cache::forget('shopItem_' . $id);
        
        return redirect()->route('shop')->with('success', 'Item removido com sucesso!');
    }

    public function buy(int $id)
    {
        $shopItem = ShopItem::findOrFail($id);
        $uuid = Str::uuid();
        $linkComUuid = $shopItem->link . '?src=' . $uuid;

        // Registra a tentativa de compra no log
        Log::info('Solicitação de compra iniciada', [
            'shop_item_id' => $shopItem->id,
            'user_id' => auth()->id(),
            'uuid' => $uuid,
            'price' => $shopItem->price
        ]);

        if (empty($shopItem->link)) {
            // Registra o erro de link ausente
            Log::error('Tentativa de compra sem link de pagamento', [
                'shop_item_id' => $shopItem->id,
                'user_id' => auth()->id()
            ]);
            return back()->withErrors(['error' => 'Este item não possui um link de pagamento configurado']);
        }

        try {
            // Cria um registro de compra pendente para consulta posterior pelo webhook
            Purchase::create([
                'shop_item_id' => $shopItem->id,
                'uuid' => $uuid,
                'status' => 'pending',
                'order_id' => 'pendente',
                'order_ref' => null,
                'order_status' => null,
                'customer_email' => auth()->user()->email,
                'product_type' => null,
                'amount' => (int) $shopItem->price, // Converte para inteiro
                'user_id' => auth()->id()
            ]);

            // Registra a criação do registro de compra
            Log::info('Registro de compra criado com sucesso', [
                'shop_item_id' => $shopItem->id,
                'uuid' => $uuid,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'payment_link' => $linkComUuid
            ]);

        } catch (\Exception $e) {
            // Registra qualquer erro durante o processo
            Log::error('Erro ao processar compra', [
                'shop_item_id' => $shopItem->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'stacktrace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Ocorreu um erro ao processar sua compra']);
        }
    }

    protected function deleteImageIfNotDefault($path): void
    {
        if ($path && Storage::disk('public')->exists($path) && $path !== 'shop-item-photos/default.png') {
            Storage::disk('public')->delete($path);
        }
    }
}