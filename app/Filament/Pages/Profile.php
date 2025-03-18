<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Attributes\Property;

class Profile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static bool $shouldRegisterNavigation = true;
    protected static ?string $navigationGroup = 'Account Settings';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.profile';  // Changed view path

    #[Property]
    public array $data = [];

    public function mount(): void
    {
        $user = auth()->user();
        $this->data = [
            'name' => $user->name,
            'email' => $user->email,
            'department' => $user->department?->name,
            'building' => $user->building?->name,
            'role' => $user->role?->name,
        ];
        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->label('Email Address')
                    ->email()
                    ->required(),
                Forms\Components\TextInput::make('department')
                    ->disabled(),
                Forms\Components\TextInput::make('building')
                    ->disabled(),
                Forms\Components\TextInput::make('role')
                    ->disabled(),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $validated = $this->form->getState();

        auth()->user()->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        Notification::make()
            ->title('Profile updated successfully')
            ->success()
            ->send();
    }
}
