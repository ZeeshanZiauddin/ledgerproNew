<?php
namespace App\Filament\Widgets;

use Guava\Calendar\Widgets\CalendarWidget;
use App\Models\CardReminder;
use Guava\Calendar\ValueObjects\Event;

class RemindersCalender extends CalendarWidget
{
    protected string $calendarView = 'dayGridMonth';
    protected static ?int $sort = 3;
    protected bool $eventClickEnabled = true;
    protected ?string $defaultEventClickAction = 'edit';

    public function getEvents(array $fetchInfo = []): array
    {
        // Fetch all reminders without filtering by date
        return CardReminder::all()
            ->map(fn(CardReminder $reminder) => $reminder->toEvent())
            ->toArray();
    }

}