<?php

namespace App\Filament\Pages;

use App\Models\Ticket;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TicketsExport;

class TicketReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Export Tickets Reports';
    protected static ?string $navigationGroup = 'Reports';
    protected static string $view = 'filament.pages.ticket-report';

    public $dateRange = [];
    public $selectedStatus = [];
    public $selectedPriority = [];
    public $reportLayout = 'detailed'; // Renamed from $layout to $reportLayout
    public $previewData = ['tickets' => [], 'stats' => []]; // Add preview data property

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasPermission('tickets.reports');
    }

    public function mount(): void
    {
        if (!auth()->user()->hasPermission('tickets.reports')) {
            $this->redirect('/');
        }

        $this->dateRange = [
            'from' => now()->subDays(30)->format('Y-m-d'),
            'until' => now()->format('Y-m-d'),
        ];
        $this->loadPreview(); // Initialize preview data
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Report Filters')
                ->description('Customize your report output')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\DatePicker::make('dateRange.from')
                                ->label('From Date'),
                            Forms\Components\DatePicker::make('dateRange.until')
                                ->label('To Date'),
                        ]),
                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\Select::make('selectedStatus')
                                ->multiple()
                                ->label('Status')
                                ->options([
                                    'open' => 'Open',
                                    'in_progress' => 'In Progress',
                                    'resolved' => 'Resolved',
                                    'completed' => 'Completed',
                                    'cancelled' => 'Cancelled',
                                ]),
                            Forms\Components\Select::make('selectedPriority')
                                ->multiple()
                                ->label('Priority')
                                ->options([
                                    'low' => 'Low',
                                    'medium' => 'Medium',
                                    'high' => 'High',
                                ]),
                            Forms\Components\Select::make('reportLayout') // Changed from layout to reportLayout
                                ->label('Report Layout')
                                ->options([
                                    'detailed' => 'Detailed Report',
                                    'simple' => 'Simple List',
                                    'summary' => 'Summary Only',
                                ])
                                ->default('detailed'),
                        ]),
                ])
                ->columns(1),
        ];
    }

    public function generatePDF()
    {
        $tickets = $this->getFilteredTickets();
        $stats = $this->getStats($tickets);

        $pdf = PDF::loadView('reports.tickets', [
            'tickets' => $tickets,
            'stats' => $stats,
            'dateRange' => $this->dateRange,
            'layout' => $this->reportLayout, // Changed from layout to reportLayout
            'appName' => config('app.name'),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn() => print ($pdf->output()),
            "tickets-report-" . now()->format('Y-m-d') . ".pdf"
        );
    }

    public function generateExcel()
    {
        $tickets = $this->getFilteredTickets();

        return Excel::download(
            new TicketsExport($tickets, $this->dateRange),
            'tickets-report-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function filter()
    {
        $this->loadPreview();
    }

    protected function loadPreview()
    {
        $tickets = $this->getFilteredTickets();
        $this->previewData = [
            'tickets' => $tickets,
            'stats' => $this->getStats($tickets),
        ];
    }

    protected function getFilteredTickets()
    {
        return Ticket::query()
            ->with(['category', 'requestor', 'assignee', 'building', 'department', 'rating'])
            ->select('tickets.*')  // Remove the COALESCE part
            ->leftJoin('users', 'users.id', '=', 'tickets.requestor_id')
            ->when($this->dateRange, function ($q) {
                $q->whereBetween('tickets.created_at', [
                    Carbon::parse($this->dateRange['from'])->startOfDay(),
                    Carbon::parse($this->dateRange['until'])->endOfDay(),
                ]);
            })
            ->when($this->selectedStatus, fn($q) => $q->whereIn('status', $this->selectedStatus))
            ->when($this->selectedPriority, fn($q) => $q->whereIn('priority', $this->selectedPriority))
            ->get();
    }

    protected function getStats($tickets)
    {
        return [
            'total' => $tickets->count(),
            'by_status' => $tickets->groupBy('status')
                ->map(fn($group) => $group->count()),
            'by_priority' => $tickets->groupBy('priority')
                ->map(fn($group) => $group->count()),
            'avg_resolution' => $tickets->whereNotNull('rating')
                ->avg(fn($ticket) => $ticket->created_at->diffInHours($ticket->updated_at)),
            'satisfaction' => $tickets->whereNotNull('rating')
                ->avg('rating.rating'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
