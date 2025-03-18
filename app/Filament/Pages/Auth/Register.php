<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms;
use Filament\Pages\Auth\Register as BaseRegister;

class Register extends BaseRegister
{
    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('email')->required()->email(),
                Forms\Components\TextInput::make('password')->required()->password(),
                Forms\Components\TextInput::make('password_confirmation')
                    ->required()
                    ->password()
                    ->same('password'),
            ]);
    }


}
