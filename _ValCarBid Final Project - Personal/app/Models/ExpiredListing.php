<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpiredListing extends Model
{
    use HasFactory;

    protected $table = 'expired_listings';

    protected $fillable = [
        'expired_listing_id',
        'expired_car_id',
        'bid_price',
        'buy_price',
        'current_winner_id',
        'created_at',
        'expires_at',
    ];

    public $timestamps = false;
}
