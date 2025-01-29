<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefundPassenger extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_id',
        'card_passenger_id',
        'record_no',
        'name',
        'sale',
        'cost',
        'tax',
        'ref_to_cus',
        'ref_to_vendor',
        'sale_return',
        'pur_return',
        'apply_date',
        'approve_date',
        'user_id',
    ];

    public function passenger()
    {
        return $this->belongsTo(CardPassenger::class, 'card_passenger_id');
    }
    public function card()
    {
        return $this->belongsTo(Card::class, 'card_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}