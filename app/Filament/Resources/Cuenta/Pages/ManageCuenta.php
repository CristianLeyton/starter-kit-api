<?php

namespace App\Filament\Resources\Cuenta\Pages;

use App\Filament\Resources\Cuenta\CuentaResource;
use Filament\Resources\Pages\ManageRecords;

class ManageCuenta extends ManageRecords
{
    protected static string $resource = CuentaResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
