<?php

namespace App\Filament\Resources\WarehouseInventoryResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TransfersRelationManager extends RelationManager
{
    protected static string $relationship = 'warehouseTransfers';
    protected static ?string $title = 'Transfer Timeline';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with(['fromShelf.location.building', 'toShelf.location.building']))
            ->columns([
                Tables\Columns\Layout\View::make('filament.tables.columns.transfer-timeline'),
            ])
            ->defaultSort('created_at', 'desc')
            ->contentGrid([
                'md' => 1,
                'xl' => 1,
            ])
            ->poll('5s'); // Add polling every 5 seconds
    }
}
