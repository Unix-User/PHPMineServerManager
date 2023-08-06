<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;
    public function player()
{
    return $this->belongsTo(Player::class);
}

public function shopItem()
{
    return $this->belongsTo(ShopItem::class);
}

}
