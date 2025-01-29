<?php

namespace App\Filament\Resources\CardResource\Pages;

use App\Filament\Resources\CardResource;
use App\Models\CardItinerary;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCard extends CreateRecord
{
    protected static string $resource = CardResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Create the card
        $card = static::getModel()::create($data);

        if ($card) {
            // Check if itineraries are provided
            if (!empty($data['itinerary_ids'])) {
                // Ensure itinerary IDs are an array
                $itineraryIds = is_array($data['itinerary_ids']) ? $data['itinerary_ids'] : json_decode($data['itinerary_ids'], true);

                // Update all itineraries with the new card ID
                CardItinerary::whereIn('id', $itineraryIds)->update(['card_id' => $card->id]);
            }
        }

        return $card;
    }

}