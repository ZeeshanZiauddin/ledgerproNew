<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardItinerary extends Model
{
    use HasFactory;
    protected $table = 'card_itineraries';
    protected $fillable = [
        'itinerary',
        'card_id',
    ];

    public function card()
    {
        return $this->belongsTo(Card::class, 'card_id');
    }

}