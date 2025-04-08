<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'IT';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        $isCreateForm = !$form->getRecord();

        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('username')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(User::class, 'email', ignoreRecord: true),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->required($isCreateForm)
                    ->visible($isCreateForm)
                    ->minLength(8),
                Forms\Components\Select::make('department_id')
                    ->relationship('department', 'code')
                    ->required(),
                Forms\Components\Select::make('building_id')
                    ->relationship('building', 'code')
                    ->required(),
                Forms\Components\Select::make('role_id')
                    ->relationship('role', 'code')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()->slideOver()
                    ->icon(icon: 'heroicon-m-plus'),

            ])
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('username')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('department.code')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('building.code')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('role.code')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M d, Y h:i A')
                    ->timezone('Asia/Manila')
                    ->toggleable(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->slideOver()
                        ->icon('heroicon-m-pencil-square'),
                    Tables\Actions\Action::make('change_password')
                        ->icon('heroicon-m-key')
                        ->slideOver()
                        ->form([
                            Forms\Components\TextInput::make('new_password')
                                ->label('New Password')
                                ->password()
                                ->revealable()
                                ->required()
                                ->minLength(8),
                            Forms\Components\TextInput::make('new_password_confirmation')
                                ->label('Confirm Password')
                                ->password()
                                ->revealable()
                                ->required()
                                ->same('new_password'),
                        ])
                        ->action(function (User $record, array $data): void {
                            $record->update([
                                'password' => Hash::make($data['new_password']),
                            ]);

                            Notification::make()
                                ->title('Password updated successfully')
                                ->success()
                                ->send();
                        })
                        ->modalHeading('Change Password')
                        ->modalButton('Update Password'),
                    Tables\Actions\DeleteAction::make()
                        ->slideOver()
                        ->icon('heroicon-m-trash'),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->label('Actions')
                    ->tooltip('Actions')
                    ->dropdownPlacement('bottom-end'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->slideOver(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasPermission('users.view');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasPermission('users.create');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasPermission('users.edit');
    }

    public static function canDelete(Model $record): bool
    {
        if ($record->id === 1) {
            return false;
        }

        return auth()->user()->hasPermission('users.delete');
    }
}
