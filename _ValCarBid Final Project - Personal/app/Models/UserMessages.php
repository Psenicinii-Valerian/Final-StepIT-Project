<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMessages extends Model
{
    use HasFactory;

    protected $table = 'user_messages';

    protected $fillable = [
        'car_id',
        'listing_id',
        'winner_id',
        'seller_id',
        'status'
    ];
}
