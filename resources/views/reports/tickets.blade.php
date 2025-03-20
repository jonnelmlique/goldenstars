<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Ticket Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 20px;
        }

        .stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .stat-box {
            text-align: center;
            padding: 10px;
            background: #f3f4f6;
            border-radius: 8px;
        }

        .stat-label {
            font-size: 12px;
            color: #666;
        }

        .stat-value {
            font-size: 20px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 10px;
            /* Smaller font size to fit all columns */
        }

        th,
        td {
            padding: 6px;
            border: 1px solid #ddd;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }

        .badge {
            padding: 2px 4px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }

        .badge-high {
            background: #fee2e2;
            color: #dc2626;
        }

        .badge-medium {
            background: #fef3c7;
            color: #d97706;
        }

        .badge-low {
            background: #d1fae5;
            color: #059669;
        }

        .text-wrap {
            word-break: break-word;
            max-width: 150px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">{{ $appName }}</div>
        <div class="subtitle">Ticket Report</div>
        <div>{{ Carbon\Carbon::parse($dateRange['from'])->format('M d, Y') }} -
            {{ Carbon\Carbon::parse($dateRange['until'])->format('M d, Y') }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Description</th>
                <th>Status</th>
                <th>Priority</th>
                <th>Category</th>
                <th>Building</th>
                <th>Department</th>
                <th>Requestor</th>
                <th>Assignee</th>
                <th>Created</th>
                <th>Updated</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tickets as $ticket)
                <tr>
                    <td>#{{ $ticket->id }}</td>
                    <td class="text-wrap">{{ $ticket->title }}</td>
                    <td class="text-wrap">{{ Str::limit($ticket->description, 100) }}</td>
                    <td>
                        <span class="badge badge-{{ $ticket->status }}">
                            {{ strtoupper($ticket->status) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-{{ $ticket->priority }}">
                            {{ strtoupper($ticket->priority) }}
                        </span>
                    </td>
                    <td>{{ $ticket->category->name }}</td>
                    <td>{{ $ticket->building->name }}</td>
                    <td>{{ $ticket->department->name }}</td>
                    <td>{{ $ticket->requestor->name }}</td> {{-- Changed to use only requestor relationship --}}
                    <td>{{ $ticket->assignee?->name ?? 'Unassigned' }}</td>
                    <td>{{ $ticket->created_at->format('M d, Y h:i A') }}</td>
                    <td>{{ $ticket->updated_at->format('M d, Y h:i A') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ now()->format('M d, Y h:i A') }}
    </div>
</body>

</html>