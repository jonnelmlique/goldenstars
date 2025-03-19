<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms;
use Filament\Pages\Auth\Register as BaseRegister;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Model;

class Register extends BaseRegister
{
    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('username')
                ->required()
                ->maxLength(255)
                ->alphaNum()
                ->unique('users', 'username'),
            Forms\Components\TextInput::make('email')
                ->label('Email address')
                ->email()
                ->required()
                ->maxLength(255)
                ->unique('users', 'email'),
            Forms\Components\TextInput::make('password')
                ->password()
                ->revealable()
                ->required()
                ->minLength(8),
            Forms\Components\TextInput::make('password_confirmation')
                ->password()
                ->revealable()
                ->required()
                ->minLength(8)
                ->same('password'),
        ]);
    }

    protected function handleRegistration(array $data): Model
    {
        $data['password'] = bcrypt($data['password']);
        return static::getUserModel()::create($data);
    }
}
