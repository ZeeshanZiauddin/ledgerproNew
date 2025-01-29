<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Guava\Calendar\Contracts\Eventable;
use Guava\Calendar\ValueObjects\Event;
class CardReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'details',
        'reminder_date',
        'by_user_id',
        'for_user_id',
        'created_by',
        'card_id',
    ];

    /**
     * Relationships can be defined here, for example:
     */

    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function toEvent(): Event|array
    {
        $cardUrl = route('filament.admin.resources.cards.edit', ['record' => $this->card_id]); // Generate the URL for the card

        return Event::make()
            ->title($this->details)
            ->start($this->reminder_date)
            ->end($this->reminder_date)
            ->url($cardUrl) // Set the URL for the event
            ->classNames(['cursor-pointer']); // Optional: Add pointer cursor for better UX
    }


}