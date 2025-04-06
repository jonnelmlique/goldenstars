<!DOCTYPE html>
<html>

<head>
    <title>Barcode Label - {{ $inventory->item_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .barcode-container {
            width: 300px;
            padding: 15px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
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
            font-size: 14px;
            line-height: 1.5;
        }

        .item-number {
            font-weight: bold;
            font-size: 16px;
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
        <button onclick="window.print()">Print Barcode</button>
        <button onclick="window.close()">Close</button>
    </div>

    <div class="barcode-container">
        <div class="barcode-image">
            <img src="{{ $inventory->getBarcode(2, 80) }}" alt="{{ $inventory->item_number }}">
        </div>
        <div class="item-details">
            <div class="item-number">{{ $inventory->item_number }}</div>
            <div>{{ $inventory->item_name }}</div>
            <div>Batch: {{ $inventory->batch_number }}</div>
            <div>Location: {{ $inventory->location_code }}</div>
        </div>
    </div>
</body>

</html>