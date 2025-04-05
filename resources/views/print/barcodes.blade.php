<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Barcodes</title>
    <style>
        @page {
            margin: 1cm;
            size: portrait;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0 1cm;
        }

        .print-title {
            text-align: center;
            font-size: 16pt;
            margin: 1cm 0;
        }

        .barcode-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5cm;
        }

        .barcode-card {
            border: 1px solid #ddd;
            background: white;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .barcode-header {
            background: #f8f8f8;
            padding: 5px 0;
            text-align: center;
            border-bottom: 1px solid #eee;
        }

        .barcode-label {
            font-family: monospace;
            font-weight: bold;
            font-size: 10pt;
        }

        .barcode-image {
            background: white;
            padding: 10px;
            text-align: center;
        }

        .barcode-image img {
            max-width: 100%;
            height: auto;
        }

        .barcode-details {
            padding: 8px;
            border-top: 1px solid #eee;
        }

        .barcode-name {
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 5px;
        }

        .barcode-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            font-size: 8pt;
            color: #333;
        }

        .print-actions {
            margin: 1cm 0;
            text-align: center;
        }

        .btn {
            background: #0066cc;
            color: white;
            border: none;
            padding: 8px 16px;
            cursor: pointer;
            border-radius: 4px;
            font-size: 14px;
            margin: 0 5px;
        }

        @media print {
            .print-actions {
                display: none;
            }
        }
    </style>
</head>

<body>
    <h1 class="print-title">Barcode Labels</h1>

    <div class="barcode-grid">
        @foreach($inventories as $inventory)
            <div class="barcode-card">
                <div class="barcode-header">
                    <div class="barcode-label">{{ $inventory->item_number }}</div>
                </div>
                <div class="barcode-image">
                    <img src="{{ $inventory->getBarcodeImage(2, 80) }}" alt="{{ $inventory->item_number }}">
                </div>
                <div class="barcode-details">
                    <div class="barcode-name">{{ $inventory->item_name }}</div>
                    <div class="barcode-info">
                        <div>Batch: {{ $inventory->batch_number }}</div>
                        <div>Location: {{ $inventory->location_code }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="print-actions">
        <button class="btn" onclick="window.print()">Print All</button>
        <button class="btn" style="background: #666;" onclick="window.close()">Close</button>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function () {
            // Small timeout to ensure everything is rendered
            setTimeout(function () {
                window.print();
            }, 500);
        };
    </script>
</body>

</html>