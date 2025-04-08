<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Models\Department;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'IT';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            Forms\Components\Textarea::make('description')
                ->maxLength(65535)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()->slideOver()
                    ->icon('heroicon-m-plus'),

            ])
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M d, Y h:i A')
                    ->timezone('Asia/Manila')
                    ->toggleable(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()->slideOver(),
                    Tables\Actions\DeleteAction::make()->slideOver(),
                ])->dropdown(true)->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->slideOver(),
                ]),
            ]);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasPermission('departments.view');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasPermission('departments.create');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasPermission('departments.edit');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasPermission('departments.delete');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepartments::route('/'),
        ];
    }
}
