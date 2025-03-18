<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Models\Ticket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = 'Tickets';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')
                ->required()
                ->maxLength(255),
            Forms\Components\Textarea::make('description')
                ->required(),
            Forms\Components\Grid::make(3)
                ->schema([
                    Forms\Components\Select::make('priority')
                        ->options([
                            'low' => 'Low',
                            'medium' => 'Medium',
                            'high' => 'High',
                        ])
                        ->required(),
                    Forms\Components\Select::make('building_id')
                        ->label('Building')
                        ->relationship('building', 'name')
                        ->required()
                        ->default(fn() => auth()->user()->building_id),
                    Forms\Components\Select::make('department_id')
                        ->label('Department')
                        ->relationship('department', 'name')
                        ->required()
                        ->default(fn() => auth()->user()->department_id),
                ]),
            Forms\Components\Select::make('category_id')
                ->relationship('category', 'name')
                ->required(),
            Forms\Components\Select::make('assignee_id')
                ->relationship('assignee', 'name', function ($query) {
                    return $query->whereHas('department', function ($q) {
                        $q->where('code', 'IT');
                    });
                })
                ->label('Assign To')
                ->visible(fn() => auth()->user()->hasPermission('tickets.assign')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => strtoupper($state))
                    ->color(fn(string $state): string => match ($state) {
                        'high' => 'danger',
                        'medium' => 'warning',
                        'low' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => strtoupper($state))
                    ->color(fn(string $state): string => match ($state) {
                        'open' => 'info',
                        'in_progress' => 'warning',
                        'resolved' => 'success',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category'),
                Tables\Columns\TextColumn::make('building.name')
                    ->label('Building')
                    ->searchable(),
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Department')
                    ->searchable(),
                Tables\Columns\TextColumn::make('requestor.name')
                    ->label('Requestor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('assignee.name')
                    ->label('Assignee')
                    ->default('Unassigned')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M d, Y h:i A')
                    ->timezone('Asia/Manila')
                    ->formatStateUsing(function ($state) {
                        $date = \Carbon\Carbon::parse($state);
                        if ($date->isToday()) {
                            return $date->diffForHumans();
                        }
                        return $date->format('M d, Y h:i A');
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('view')
                        ->icon('heroicon-o-eye')
                        ->modalContent(fn(Ticket $record) => view(
                            'filament.resources.tickets.view',
                            ['ticket' => $record]
                        ))
                        ->modalSubmitAction(false)
                        ->modalCancelAction(false),
                    Tables\Actions\EditAction::make()
                        ->icon('heroicon-o-pencil'),
                    Tables\Actions\Action::make('assign')
                        ->icon('heroicon-o-user-plus')
                        ->form([
                            Forms\Components\Select::make('assignee_id')
                                ->relationship('assignee', 'name', function ($query) {
                                    return $query->whereHas('department', function ($q) {
                                        $q->where('code', 'IT');
                                    });
                                })
                                ->required()
                                ->label('Assign To'),
                        ])
                        ->action(function (Ticket $record, array $data): void {
                            $record->update([
                                'assignee_id' => $data['assignee_id'],
                                'status' => 'open'
                            ]);
                        })
                        ->visible(
                            fn(Ticket $record) =>
                            $record->status === 'open' &&
                            auth()->user()->hasPermission('tickets.assign') &&
                            is_null($record->assignee_id)
                        )
                        ->requiresConfirmation(),
                    Tables\Actions\Action::make('mark_in_progress')
                        ->icon('heroicon-o-play')
                        ->action(fn(Ticket $record) => $record->update(['status' => 'in_progress']))
                        ->visible(fn(Ticket $record) => $record->status === 'open' && auth()->user()->id === $record->assignee_id),
                    Tables\Actions\Action::make('mark_resolved')
                        ->icon('heroicon-o-check')
                        ->action(fn(Ticket $record) => $record->update(['status' => 'resolved']))
                        ->visible(fn(Ticket $record) => $record->status === 'in_progress' && auth()->user()->id === $record->assignee_id),
                    Tables\Actions\Action::make('mark_completed')
                        ->icon('heroicon-o-check-circle')
                        ->form([
                            Forms\Components\Select::make('rating')
                                ->options([
                                    1 => '⭐ Poor',
                                    2 => '⭐⭐ Fair',
                                    3 => '⭐⭐⭐ Good',
                                    4 => '⭐⭐⭐⭐ Very Good',
                                    5 => '⭐⭐⭐⭐⭐ Excellent',
                                ])
                                ->required(),
                            Forms\Components\Textarea::make('comment')
                                ->label('Feedback'),
                        ])
                        ->action(function (Ticket $record, array $data): void {
                            $record->update(['status' => 'completed']);
                            $record->rating()->create($data);
                        })
                        ->visible(fn(Ticket $record) => $record->canBeMarkedAsCompleted() && auth()->user()->id === $record->requestor_id),
                ])
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'resolved' => 'Resolved',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                    ]),
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('requestor')
                    ->relationship('requestor', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('assignee')
                    ->relationship('assignee', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function ($query, array $data): void {
                        $query->when(
                            $data['created_from'],
                            fn($query, $date) => $query->whereDate('created_at', '>=', $date)
                        )->when(
                                $data['created_until'],
                                fn($query, $date) => $query->whereDate('created_at', '<=', $date)
                            );
                    })
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        // If user doesn't have full access permission, show only their tickets
        if (!auth()->user()->hasPermission('tickets.view.all')) {
            $query->where('requestor_id', auth()->id());
        }

        // Order by latest first
        return $query->latest();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasPermission('tickets.view');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasPermission('tickets.create');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasPermission('tickets.edit');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasPermission('tickets.delete');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
        ];
    }
}
