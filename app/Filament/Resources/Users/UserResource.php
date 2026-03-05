<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserGroup;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'usuario';
    protected static ?string $pluralModelLabel = 'Usuarios';
    protected static bool $hasTitleCaseModelLabel = false;
    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('username')
                    ->label('Usuario')
                    ->minLength(3)
                    ->maxLength(255)
                    ->required()
                    ->unique()
                    ->validationMessages([
                        'min' => 'El nombre de usuario debe tener al menos :min caracteres.',
                        'required' => 'El nombre de usuario es obligatorio.',
                        'max' => 'El nombre de usuario no debe exceder los :max caracteres.',
                        'unique' => 'El nombre de usuario ya está en uso.',
                    ]),
                TextInput::make('password')
                    ->password()
                    ->required()
                    ->label('Contraseña')
                    ->hiddenOn('edit')
                    ->validationMessages(['required' => 'El campo contraseña es obligatorio.']),
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->validationMessages(['required' => 'El campo nombre es obligatorio.']),
                TextInput::make('lastname')
                    ->label('Apellido')
                    ->maxLength(255)
                    ->nullable()
                    ->validationMessages([
                        'max' => 'El apellido no debe exceder los :max caracteres.',
                    ]),
                TextInput::make('email')
                    ->label('Correo')
                    ->email()
                    ->nullable()
                    ->unique()
                    ->validationMessages([
                        'unique' => 'El correo ya está en uso.',
                    ]),
                Select::make('rol')
                    ->label('Rol')
                    ->relationship(
                        name: 'roles',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn($query) => $query->orderBy('name')
                    )
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        return match ($record->name) {
                            'admin'  => 'Administrador',
                            'editor' => 'Editor',
                            'user'   => 'Usuario',
                            default  => ucfirst($record->name),
                        };
                    })
                    ->required()
                    ->native(false)
                    ->validationMessages([
                        'required' => 'El campo rol es obligatorio.',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('username')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->getStateUsing(fn($record) => $record->name . ' ' . $record->lastname)
                    ->sortable()
                    ->searchable()
                    ->visibleFrom('sm'),
                TextColumn::make('email')
                    ->label('Email')
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('roles.name')
                    ->label('Rol')
                    ->badge()
                    ->searchable()
                    ->color(
                        fn($state) => match ($state) {
                            'admin' => 'info',
                            'editor' => 'success',
                            'user' => 'gray',
                            default => 'gray',
                        }
                    )
                    ->formatStateUsing(fn($state) => ucfirst($state)),
            ])
            ->recordClasses(fn($record) => $record->id === 1 ? 'bg-gray-100 opacity-70 dark:bg-gray-800' : '')
            ->recordAction(null)
            ->filters([
                TrashedFilter::make(),
            ])
            ->hiddenFilterIndicators()
            ->deferFilters(false)
            ->recordActions([
                EditAction::make()->button()->size('xs')->hiddenLabel()->extraAttributes(['title' => 'Editar'])->disabled(fn(User $record): bool => $record->id === 1),
                Action::make('resetPassword')
                    ->label('Restablecer contraseña')
                    ->icon(Heroicon::LockOpen)
                    ->color('warning')
                    ->size('xs')
                    ->action(function (User $record) {
                        $newPassword = $record->username;
                        $record->password = bcrypt($newPassword);
                        $record->save();

                        // Aquí puedes agregar lógica para notificar al usuario sobre su nueva contraseña
                        Notification::make()
                            ->title('Contraseña restablecida')
                            ->body('El nombre de usuario y la nueva contraseña es: ' . $newPassword)
                            ->success()
                            ->icon('heroicon-o-lock-open')
                            ->iconColor('warning')
                            ->duration(3000)
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->disabled(fn(User $record): bool => $record->id === 1)
                    ->button()
                    ->hiddenLabel()
                    ->extraAttributes([
                        'title' => 'Restablecer contraseña',
                    ]),
                DeleteAction::make()->button()->size('xs')->hiddenLabel()->extraAttributes(['title' => 'Eliminar'])->disabled(fn(User $record): bool => $record->id === 1),
                ForceDeleteAction::make()->button()->size('xs')->hiddenLabel()->extraAttributes(['title' => 'Eliminar permanentemente'])->disabled(fn(User $record): bool => $record->id === 1),
                RestoreAction::make()->button()->size('xs')->hiddenLabel()->extraAttributes(['title' => 'Restaurar'])->disabled(fn(User $record): bool => $record->id === 1),
            ])
            /* ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]) */;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUsers::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
