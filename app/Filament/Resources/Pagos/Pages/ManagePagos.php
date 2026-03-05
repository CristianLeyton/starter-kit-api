<?php

namespace App\Filament\Resources\Pagos\Pages;

use App\Filament\Resources\Pagos\PagoResource;
use App\Models\Movement;
use App\Models\Payment;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePagos extends ManageRecords
{
    protected static string $resource = PagoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Pago')
                ->modalHeading('Registrar pago')
                ->mutateFormDataBeforeCreate(function (array $data): array {
                    $data['user_id'] = auth()->id();
                    return $data;
                })
                ->after(function (Payment $record): void {
                    Movement::create([
                        'client_id' => $record->client_id,
                        'type' => Movement::TYPE_PAGO,
                        'amount' => $record->amount,
                        'movementable_id' => $record->id,
                        'movementable_type' => Payment::class,
                    ]);
                })
                ->createAnother(false),
        ];
    }
}
