<!DOCTYPE html>
<html>

<head>
    <title>Barcode Labels</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .barcode-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .barcode-container {
            padding: 15px;
            border: 1px solid #ddd;
            page-break-inside: avoid;
        }

        .barcode-image {
            width: 100%;
            text-align: center;
            margin-bottom: 10px;
        }

        .barcode-image img {
            max-width: 100%;
        }

        .item-details {
            font-size: 12px;
            line-height: 1.3;
        }

        .item-number {
            font-weight: bold;
            font-size: 14px;
        }

        .print-button {
            margin-bottom: 20px;
        }

        @media print {
            .print-button {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="print-button">
        <button onclick="window.print()">Print All Barcodes</button>
        <button onclick="window.close()">Close</button>
    </div>

    <div class="barcode-grid">
        @foreach($inventories as $inventory)
            <div class="barcode-container">
                <div class="barcode-image">
                    <img src="{{ $inventory->getBarcode(2, 60) }}" alt="{{ $inventory->item_number }}">
                </div>
                <div class="item-details">
                    <div class="item-number">{{ $inventory->item_number }}</div>
                    <div>{{ $inventory->item_name }}</div>
                    <div>Batch: {{ $inventory->batch_number }}</div>
                    <div>Location: {{ $inventory->location_code }}</div>
                </div>
            </div>
        @endforeach
    </div>
</body>

</html>