<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class TicketsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithColumnWidths, WithCustomStartCell
{
    protected $tickets;
    protected $dateRange;

    public function __construct($tickets, $dateRange)
    {
        $this->tickets = $tickets;
        $this->dateRange = $dateRange;
    }

    public function startCell(): string
    {
        return 'A5'; // Start data from row 5 to accommodate header
    }

    public function collection()
    {
        return $this->tickets;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Title',
            'Description',
            'Status',
            'Priority',
            'Category',
            'Building',
            'Department',
            'Requestor',
            'Assignee',
            'Created',
            'Updated',
        ];
    }

    public function map($ticket): array
    {
        return [
            $ticket->id,
            $ticket->title,
            $ticket->description,
            strtoupper($ticket->status),
            strtoupper($ticket->priority),
            $ticket->category->name,
            $ticket->building->name,
            $ticket->department->name,
            'requestor' => $ticket->requested_by ?? $ticket->requestor->name,
            $ticket->assignee?->name ?? 'Unassigned',
            $ticket->created_at->format('M d, Y h:i A'),
            $ticket->updated_at->format('M d, Y h:i A'),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,  // ID
            'B' => 30, // Title
            'C' => 40, // Description
            'D' => 15, // Status
            'E' => 15, // Priority
            'F' => 20, // Category
            'G' => 20, // Building
            'H' => 20, // Department
            'I' => 20, // Requestor
            'J' => 20, // Assignee
            'K' => 20, // Created
            'L' => 20, // Updated
        ];
    }

    public function title(): string
    {
        return 'Ticket Report';
    }

    public function styles(Worksheet $sheet)
    {
        // Header styling
        $sheet->mergeCells('A1:L1');
        $sheet->mergeCells('A2:L2');
        $sheet->mergeCells('A3:L3');

        $sheet->setCellValue('A1', config('app.name'));
        $sheet->setCellValue('A2', 'Ticket Report');
        $sheet->setCellValue('A3', sprintf(
            'From: %s To: %s',
            Carbon::parse($this->dateRange['from'])->format('M d, Y'),
            Carbon::parse($this->dateRange['until'])->format('M d, Y')
        ));

        // Header text styling
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('A2:L3')->applyFromArray([
            'font' => ['size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Table header styling
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F3F4F6'],
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A5:L5')->applyFromArray($headerStyle);

        // Data styling
        $dataRange = 'A6:L' . (5 + $this->tickets->count());
        $sheet->getStyle($dataRange)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true,
            ],
        ]);

        return [];
    }
}
