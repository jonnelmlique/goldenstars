<?php

namespace App\Filament\Resources\WarehouseInventoryResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TransfersRelationManager extends RelationManager
{
    protected static string $relationship = 'warehouseTransfers';

    protected static ?string $title = 'Transfer History';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('from_location'),
                Tables\Columns\TextColumn::make('to_location'),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('transfer_date')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'completed' => 'success',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('notes')
                    ->wrap(),
            ])
            ->defaultSort('transfer_date', 'desc');
    }
}
