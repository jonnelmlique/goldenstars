// Add this script before the closing </body> tag
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Handle barcode scanner input (most scanners append enter/return key)
            const handleBarcodeInput = (e) => {
                if (e.keyCode === 13 && e.target.matches('.barcode-scanner-input')) {
                    e.preventDefault(); // Prevent form submission
                    // Allow the live() functionality to work
                    const changeEvent = new Event('change', { bubbles: true });
                    e.target.dispatchEvent(changeEvent);
                }
            };

            document.addEventListener('keydown', handleBarcodeInput);
        });
    </script>
@endpush