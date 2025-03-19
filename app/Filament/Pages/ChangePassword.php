<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ChangePassword extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.change-password';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('current_password')
                    ->label('Current Password')
                    ->password()
                    ->revealable()
                    ->required()
                    ->rules(['current_password']),
                Forms\Components\TextInput::make('new_password')
                    ->label('New Password')
                    ->password()
                    ->revealable()
                    ->required()
                    ->minLength(8),
                Forms\Components\TextInput::make('new_password_confirmation')
                    ->label('Confirm New Password')
                    ->password()
                    ->revealable()
                    ->required()
                    ->same('new_password'),
            ]);
    }

    public function submit()
    {
        $data = $this->form->getState();

        auth()->user()->update([
            'password' => Hash::make($data['new_password']),
        ]);

        Notification::make()
            ->title('Password updated successfully')
            ->success()
            ->send();

        $this->form->fill();
    }
}
