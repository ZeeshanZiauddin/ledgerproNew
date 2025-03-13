<?php

namespace App\Filament\Widgets;

use App\Models\Card;
use App\Models\Inquiry;
use App\Models\Receipt;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;

class ResourceCards extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    protected ?string $heading = 'Welcome Back';

    protected function getDescription(): ?string
    {
        return auth()->check() ? auth()->user()->name . ' das' : 'Guest';
    }
    protected function getStats(): array
    {
        return [
            Stat::make('Cards', Card::count())
                ->description(new HtmlString(Card::where('created_at', '>=', \Carbon\Carbon::now()->subDays(7))->count() . ' Cards added in last 7 days'))
                ->descriptionIcon('heroicon-o-credit-card')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'onclick' => "window.location.href='" . route('filament.admin.resources.cards.create') . "'",
                ]),
            Stat::make('Cards', Inquiry::count())
                ->description(new HtmlString("<strong>" . Inquiry::where('created_at', '>=', \Carbon\Carbon::now()->subDays(7))->count() . ' Cards</strong> added in last 7 days'))
                ->descriptionIcon('heroicon-o-pencil-square')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('primary'),
            Stat::make('Cards', Receipt::count())
                ->description(new HtmlString(Inquiry::where('created_at', '>=', \Carbon\Carbon::now()->subDays(7))->count() . ' Cards added in last 7 days'))
                ->descriptionIcon('heroicon-o-pencil-square')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('primary'),
        ];
    }
}