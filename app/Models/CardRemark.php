<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardRemark extends Model
{
    use HasFactory;

    protected $fillable = [
        'message',
        'user_id',
        'card_id',
    ];

    /**
     * Get the user who created the remark.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the card associated with the remark.
     */
    public function card()
    {
        return $this->belongsTo(Card::class);
    }
}