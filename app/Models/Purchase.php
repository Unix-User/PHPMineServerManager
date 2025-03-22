<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ShopItem;
use App\Models\User;

class Purchase extends Model
{
    use HasFactory;
    protected $table = 'purchases';
    protected $fillable = ['user_id', 'shop_item_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shopItem()
    {
        return $this->belongsTo(ShopItem::class);
    }

}