<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardOtherSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_id',
        'supplier_id',
        'sale',
        'cost',
        'details',
        'issue_date',
    ];

    /**
     * Get the supplier that owns the CardOtherSale.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the card that owns the CardOtherSale.
     */
    public function card()
    {
        return $this->belongsTo(Card::class);
    }
}