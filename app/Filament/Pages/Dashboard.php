<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Panel;
use Illuminate\Contracts\View\View;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.dashboard';
    public function getHeader(): ?View
    {
        return view('filament.pages.partials.dashboard.header');
    }
}
