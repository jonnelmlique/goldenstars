<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Forms\Form;
use Filament\Forms;
use Illuminate\Validation\ValidationException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Illuminate\Support\Facades\Auth;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;

class Login extends BaseLogin
{
    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('login')
                ->label('Username or Email')
                ->required()
                ->autocomplete('username')
                ->placeholder('Enter your username or email'),
            Forms\Components\TextInput::make('password')
                ->label('Password')
                ->password()
                ->revealable()
                ->required(),
            Forms\Components\Checkbox::make('remember')
                ->label('Remember me'),
        ]);
    }

    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();

        $fieldType = filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Check if user exists first
        $user = \App\Models\User::where($fieldType, $data['login'])->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'data.login' => 'No account found with this ' . $fieldType,
            ]);
        }

        // Attempt authentication
        if (
            !Auth::attempt([
                $fieldType => $data['login'],
                'password' => $data['password'],
            ], $data['remember'] ?? false)
        ) {
            throw ValidationException::withMessages([
                'data.password' => 'Incorrect password',
            ]);
        }

        session()->regenerate();
        return app(LoginResponse::class);
    }
}
