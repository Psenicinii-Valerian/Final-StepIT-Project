<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BidLog extends Model
{
    use HasFactory;

    protected $table = 'bid_log';

    protected $fillable = [
        'car_id',
        'bidder_id',
        'listing_id',
    ];
}
