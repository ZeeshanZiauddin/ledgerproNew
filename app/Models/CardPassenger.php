<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CardPassenger extends Model
{
    use HasFactory;

    protected $fillable = [
        'record_no',
        'card_id',
        'name',
        'ticket_1',
        'ticket_2',
        'issue_date',
        'option_date',
        'pnr',
        'sale',
        'cost',
        'tax',
        'margin',
    ];

    protected static function booted()
    {
        static::saved(function ($cardPassenger) {
            // Get the related Card
            $card = $cardPassenger->card;

            // Sum the sales, costs, taxes, and margins from all passengers of the card
            $totalSale = $card->passengers->sum('sale');
            $totalCost = $card->passengers->sum('cost');
            $totalTax = $card->passengers->sum('tax');
            $totalMargin = $totalSale - ($totalCost + $totalTax);

            // Update the card's values based on the sum
            $card->update([
                'sales_price' => $totalSale,
                'net_cost' => $totalCost,
                'tax' => $totalTax,
                'margin' => $totalMargin,
            ]);
        });
    }


    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    public function airline()
    {
        return $this->belongsTo(Airline::class);
    }
    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(Payment::class, 'payment_passenger')
            ->withTimestamps();
    }
    public function paySupplier()
    {
        return $this->belongsToMany(PaySupplier::class, 'card_passenger_pay_supplier')
            ->withPivot(['amount'])
            ->withTimestamps();
    }


}