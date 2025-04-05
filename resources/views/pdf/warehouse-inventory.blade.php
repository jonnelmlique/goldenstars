<!DOCTYPE html>
<html>

<head>
    <title>Warehouse Inventory Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            table-layout: auto;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            word-wrap: break-word;
        }

        th {
            background-color: #f4f4f4;
            font-size: 11px;
        }

        td {
            font-size: 11px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .date,
        .date-range {
            margin-bottom: 10px;
            font-size: 11px;
        }

        h2 {
            margin: 0 0 10px 0;
            font-size: 18px;
        }

        .barcode {
            width: 100px;
            height: 30px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Warehouse Inventory Report</h2>
        <div class="date">Generated on: {{ $date }}</div>
        <span class="filter-label">Date Range:</span>
        <span>{{ $date_from ?? 'All time' }} - {{ $date_to ?? 'Present' }}</span>
        <span class="filter-label">Location:</span>
        <span>{{ $data['location'] ?? 'All Locations' }}</span>
    </div>


    <table>
        <thead>
            <tr>
                <th>Item Number</th>
                <th>Barcode</th>
                <th>Item Name</th>
                <th>Location</th>
                <th>Batch Number</th>
                <th>BOM Unit</th>
                <th>Physical Inventory</th>
                <th>Reserved</th>
                <th>Actual Count</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inventories as $item)
                <tr>
                    <td>{{ $item->item_number }}</td>
                    <td><img class="barcode" src="{{ $item->getBarcode(1, 30) }}" alt="{{ $item->item_number }}"></td>
                    <td>{{ $item->item_name }}</td>
                    <td>{{ $item->location_code }}</td>
                    <td>{{ $item->batch_number }}</td>
                    <td>{{ $item->bom_unit }}</td>
                    <td>{{ $item->physical_inventory }}</td>
                    <td>{{ $item->physical_reserved }}</td>
                    <td>{{ $item->actual_count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>