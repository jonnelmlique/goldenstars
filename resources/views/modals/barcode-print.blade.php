<div class="p-6">
    <h2 class="text-xl font-bold mb-6 text-center text-gray-900 dark:text-white">
        Barcode Label
    </h2>

    <div class="barcode-grid">
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
    </div>

    <div class="flex justify-center space-x-3 mt-6">
        <a href="{{ route('print.barcode', ['id' => $inventory->id]) }}" target="_blank"
            class="px-4 py-2 bg-primary-600 text-white rounded-lg shadow hover:bg-primary-700 transition-colors flex items-center space-x-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z"
                    clip-rule="evenodd" />
            </svg>
            <span>Print Barcode</span>
        </a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('print-barcodes-btn').addEventListener('click', function () {
            const printContent = document.getElementById('barcode-print-container').innerHTML;
            const originalContent = document.body.innerHTML;

            // Create print-only stylesheet
            const style = `
            <style>
                @page { margin: 1cm; size: portrait; }
                body { font-family: Arial, sans-serif; }
                .print-hidden { display: none !important; }
                
                .print-title {
                    text-align: center;
                    font-size: 16pt;
                    margin-bottom: 1cm;
                    color: #333;
                }
                
                .barcode-grid {
                    display: grid;
                    grid-template-columns: repeat(2, 1fr);
                    gap: 0.5cm;
                }
                
                .barcode-card {
                    border: 1px solid #ddd;
                    break-inside: avoid;
                    page-break-inside: avoid;
                    background: white;
                }
                
                .barcode-header {
                    background: #f8f8f8;
                    padding: 0.3cm 0;
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
                    padding: 0.5cm;
                    text-align: center;
                }
                
                .barcode-image img {
                    max-width: 100%;
                    height: auto;
                }
                
                .barcode-details {
                    padding: 0.3cm;
                    border-top: 1px solid #eee;
                }
                
                .barcode-name {
                    font-weight: bold;
                    font-size: 9pt;
                    margin-bottom: 0.2cm;
                }
                
                .barcode-info {
                    display: grid;
                    grid-template-columns: repeat(2, 1fr);
                    font-size: 8pt;
                    color: #555;
                }
            </style>
        `;

            // Replace content with just what we want to print
            document.body.innerHTML = style + printContent;

            // Print
            window.print();

            // Restore original content
            document.body.innerHTML = originalContent;
        });
    });
</script>

<style>
    /* Styles for the UI in Filament */
    .barcode-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }

    .barcode-card {
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        overflow: hidden;
        background: white;
    }

    .dark .barcode-card {
        border-color: #374151;
        background: #1f2937;
    }

    .barcode-header {
        background: #f9fafb;
        padding: 0.75rem 0;
        text-align: center;
        border-bottom: 1px solid #f3f4f6;
    }

    .dark .barcode-header {
        background: #111827;
        border-color: #1f2937;
    }

    .barcode-label {
        font-family: ui-monospace, monospace;
        font-weight: 600;
        color: #4b5563;
    }

    .dark .barcode-label {
        color: #d1d5db;
    }

    .barcode-image {
        background: white;
        padding: 1rem;
        text-align: center;
    }

    .barcode-details {
        padding: 0.75rem;
        border-top: 1px solid #f3f4f6;
    }

    .dark .barcode-details {
        border-color: #1f2937;
    }

    .barcode-name {
        font-weight: 600;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
        color: #1f2937;
    }

    .dark .barcode-name {
        color: #f3f4f6;
    }

    .barcode-info {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        font-size: 0.75rem;
        color: #6b7280;
    }

    .dark .barcode-info {
        color: #9ca3af;
    }

    @media print {}

    .print-hidden {
        display: none !important;
    }
    }
</style>