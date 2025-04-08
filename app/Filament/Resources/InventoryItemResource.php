<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryItemResource\Pages;
use App\Models\InventoryItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class InventoryItemResource extends Resource
{
    protected static ?string $model = InventoryItem::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'IT';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('item_name')
                ->required()
                ->label('Item Name'),
            Forms\Components\TextInput::make('model')
                ->required(),
            Forms\Components\TextInput::make('serial')
                ->required()
                ->unique(ignoreRecord: true),
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('building_id')
                    ->relationship('building', 'name')
                    ->required()
                    ->preload()
                    ->searchable(),
                Forms\Components\Select::make('department_id')
                    ->relationship('department', 'name')
                    ->required()
                    ->preload()
                    ->searchable(),
            ]),
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('assigned_to')
                    ->relationship('assignedTo', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Assigned To')
                    ->placeholder('Select a user')
                    ->nullable(),
                Forms\Components\TextInput::make('custom_assigned_to')
                    ->label('Other Name')
                    ->placeholder('Enter name if not in list')
                    ->maxLength(255),
            ]),
            Forms\Components\DatePicker::make('date_transferred')
                ->label('Date Transferred'),
            Forms\Components\Toggle::make('is_defective')
                ->label('Defective')
                ->inline(false),
            Forms\Components\Textarea::make('notes')
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
                Tables\Columns\TextColumn::make('item_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('model')
                    ->searchable(),
                Tables\Columns\TextColumn::make('serial')
                    ->searchable(),
                Tables\Columns\TextColumn::make('building.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('department.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('assigned_to')
                    ->label('Assigned To')
                    ->getStateUsing(function ($record) {
                        if ($record->assigned_to && $record->assignedTo) {
                            return $record->assignedTo->name;
                        }
                        if (!empty($record->custom_assigned_to)) {
                            return $record->custom_assigned_to;
                        }
                        return 'Unassigned';
                    })
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_defective')
                    ->boolean(),
                Tables\Columns\TextColumn::make('is_defective')
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Defective' : 'Working')
                    ->badge()
                    ->color(fn(bool $state): string => $state ? 'danger' : 'success')
                    ->icon(fn(bool $state): string => $state ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle'),
                Tables\Columns\TextColumn::make('date_transferred')
                    ->date(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('building')
                    ->relationship('building', 'name'),
                Tables\Filters\SelectFilter::make('department')
                    ->relationship('department', 'name'),
                Tables\Filters\TernaryFilter::make('is_defective')
                    ->label('Defective Items'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->slideOver()
                        ->icon('heroicon-m-pencil-square'),
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
                    Tables\Actions\DeleteBulkAction::make()
                        ->modalHeading('Delete Selected Buildings')
                        ->slideOver(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventoryItems::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasPermission('inventory.view');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasPermission('inventory.create');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasPermission('inventory.edit');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasPermission('inventory.delete');
    }
}
