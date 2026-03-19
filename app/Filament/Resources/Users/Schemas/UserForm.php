<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Maksde\Helpers\Filament\Resources\Schemas\Forms\DateTimeForm;
use Maksde\Helpers\Filament\Resources\Schemas\Infolists\CreateUpdateTextEntry;
use Maksde\Support\Contracts\Validation\EmailValidate;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            ...CreateUpdateTextEntry::make(),
            TextInput::make('name')
                ->required()
                ->label('Имя'),
            TextInput::make('email')
                ->rule(new EmailValidate)
                ->required()
                ->label('E-mail'),
            TextInput::make('password')
                ->hidden(static fn ($record) => $record)
                ->label('Пароль')
                ->password()
                ->revealable(filament()->arePasswordsRevealable())
                ->required(static fn ($record): bool => ! $record)
                ->rule(Password::default())
                ->dehydrated(static fn ($state): bool => filled($state))
                ->dehydrateStateUsing(Hash::make(...))
                ->same('passwordConfirmation')
                ->validationAttribute(__('filament-panels::pages/auth/register.form.password.validation_attribute')),
            TextInput::make('passwordConfirmation')
                ->hidden(static fn ($record): mixed => $record)
                ->label('Подтверждение пароля')
                ->password()
                ->revealable(filament()->arePasswordsRevealable())
                ->required(static fn ($record): bool => ! $record)
                ->dehydrated(false),
            DateTimeForm::make('email_verified_at', 'Верифицирован'),
            Select::make('roles')
                ->multiple()
                ->preload()
                ->relationship('roles', 'name')
                ->label('Роли'),
        ]);
    }
}
