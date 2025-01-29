<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'cheque_no',
        'bank_id',
        'passenger_ids',
        'total',
        'tickets',
        'details',
    ];

    protected $casts = [
        'passenger_ids' => 'array', // Cast to array to ensure proper conversion
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function cards()
    {
        return $this->hasMany(Card::class);
    }
    // public function tickets()
    // {
    //     return $this->belongsToMany(CardPassenger::class, 'card_passenger_payment')->whereNotNull('issue_date');
    // }
    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(CardPassenger::class, 'payment_passenger')
            ->withTimestamps(); // Include timestamps if needed
    }
    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }


}
