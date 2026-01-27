<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;

class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Home;
    
    protected static ?string $title = 'Inicio';
    
    protected static ?string $navigationLabel = 'Inicio';
    
    protected static ?int $navigationSort = 0;
    

}