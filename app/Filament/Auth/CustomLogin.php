<?php

namespace App\Filament\Auth;    
use Filament\Pages\Auth\Login;
use Filament\Forms\Components\TextInput;

class CustomLogin extends Login
{



        protected function getEmailFormComponent(): \Filament\Schemas\Components\Component
    {
        return TextInput::make('username')
            ->label(__('Usuario / Correo'))
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'username' => $data['username'] ?? null,
            'password' => $data['password'] ?? null,
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'data.username' => __('filament-panels::auth/pages/login.messages.failed'),
        ]);
    }
}
