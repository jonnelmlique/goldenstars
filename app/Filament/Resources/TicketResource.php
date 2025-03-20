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
    protected static ?string $navigationGroup = null; // Remove from group
    protected static ?int $navigationSort = 2; // Right after dashboard

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')
                ->required()
                ->maxLength(255),
            Forms\Components\Textarea::make('description')
                ->required(),
            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\Select::make('category_id')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\Select::make('priority')
                        ->options([
                            'low' => 'Low',
                            'medium' => 'Medium',
                            'high' => 'High',
                        ])
                        ->required(),
                ]),
            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\Select::make('building_id')
                        ->label('Building')
                        ->relationship('building', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\Select::make('department_id')
                        ->label('Department')
                        ->relationship('department', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                ]),
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('requestor_id')
                    ->label('Requestor')
                    ->relationship('requestor', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->default(fn() => auth()->user()->hasPermission('tickets.assign') ? null : auth()->id())
                    ->disabled(condition: fn() => !auth()->user()->hasPermission('tickets.assign')),
                Forms\Components\Select::make('assignee_id')
                    ->relationship('assignee', 'name', function ($query) {
                        return $query->whereHas('department', function ($q) {
                            $q->where('code', 'IT');
                        });
                    })
                    ->searchable()
                    ->preload()
                    ->label('Assign To')
                    ->visible(fn() => auth()->user()->hasPermission('tickets.assign')),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\Layout\Panel::make([
                        Tables\Columns\TextColumn::make('title')
                            ->size('lg')
                            ->weight('bold')
                            ->searchable()
                            ->description(fn($record) => \Illuminate\Support\Str::limit($record->description, 100)),
                        Tables\Columns\Layout\Split::make([
                            Tables\Columns\TextColumn::make('category.name')
                                ->icon('heroicon-m-tag')
                                ->iconColor('success'),
                            Tables\Columns\TextColumn::make('created_at')
                                ->since()
                                ->icon('heroicon-m-clock')
                                ->color('gray'),
                        ])->extraAttributes(['class' => 'mt-2']),
                        Tables\Columns\Layout\Stack::make([
                            Tables\Columns\Layout\Grid::make(2)->schema([
                                Tables\Columns\TextColumn::make('status')
                                    ->badge()
                                    ->icon(fn(string $state): string => match ($state) {
                                        'open' => 'heroicon-m-exclamation-circle',
                                        'in_progress' => 'heroicon-m-play',
                                        'resolved' => 'heroicon-m-check',
                                        'completed' => 'heroicon-m-check-circle',
                                        'cancelled' => 'heroicon-m-x-circle',
                                        default => 'heroicon-m-question-mark-circle',
                                    })
                                    ->formatStateUsing(fn(string $state): string => strtoupper($state))
                                    ->color(fn(string $state): string => match ($state) {
                                        'open' => 'info',
                                        'in_progress' => 'warning',
                                        'resolved' => 'success',
                                        'completed' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'gray',
                                    }),
                                Tables\Columns\TextColumn::make('priority')
                                    ->badge()
                                    ->formatStateUsing(fn(string $state): string => strtoupper($state))
                                    ->color(fn(string $state): string => match ($state) {
                                        'high' => 'danger',
                                        'medium' => 'warning',
                                        'low' => 'success',
                                        default => 'gray',
                                    }),
                            ])->extraAttributes(['class' => 'mt-2']),
                            Tables\Columns\Layout\Split::make([
                                Tables\Columns\TextColumn::make('building.name')
                                    ->icon('heroicon-m-building-office')
                                    ->iconColor('primary'),
                                Tables\Columns\TextColumn::make('department.name')
                                    ->icon('heroicon-m-academic-cap')
                                    ->iconColor('warning'),
                            ])->extraAttributes(['class' => 'mt-2']),
                            Tables\Columns\TextColumn::make('requestor.name')
                                ->label('Requestor')
                                ->icon('heroicon-m-user')
                                ->default('No Requestor')
                                ->extraAttributes(['class' => 'mt-1'])
                        ]),
                        Tables\Columns\Layout\Stack::make([
                            Tables\Columns\TextColumn::make('')
                                ->html(fn($record) => view('filament.resources.tickets.card-actions', ['record' => $record])),
                        ])->extraAttributes(['class' => 'mt-3 pt-3 border-t']),
                    ])->extraAttributes(['class' => 'bg-white dark:bg-gray-800 shadow-sm rounded-xl p-4 ring-1 ring-gray-950/5 dark:ring-white/10']),
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->icon('heroicon-m-eye')
                    ->button()
                    ->size('sm')
                    ->modalContent(fn(Ticket $record) => view(
                        'filament.resources.tickets.view',
                        ['ticket' => $record]
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->modalWidth('4xl'),
                Tables\Actions\EditAction::make()
                    ->button()
                    ->size('sm')
                    ->icon('heroicon-m-pencil'),
                Tables\Actions\Action::make('assign')
                    ->button()
                    ->size('sm')
                    ->color('info')
                    ->icon('heroicon-m-user-plus')
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
                    ->button()
                    ->size('sm')
                    ->color('warning')
                    ->icon('heroicon-m-play')
                    ->visible(fn(Ticket $record) => $record->status === 'open' && auth()->user()->id === $record->assignee_id)
                    ->action(fn(Ticket $record) => $record->update(['status' => 'in_progress'])),
                Tables\Actions\Action::make('mark_resolved')
                    ->button()
                    ->size('sm')
                    ->color('success')
                    ->icon('heroicon-m-check')
                    ->visible(fn(Ticket $record) => $record->status === 'in_progress' && auth()->user()->id === $record->assignee_id)
                    ->action(fn(Ticket $record) => $record->update(['status' => 'resolved'])),
                Tables\Actions\Action::make('mark_completed')
                    ->button()
                    ->size('sm')
                    ->color('success')
                    ->icon('heroicon-m-check-circle')
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
