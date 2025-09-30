<!DOCTYPE html>
<html>
<head>
    <title>Debug Service Types</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Service Types Debug</h2>

        <h3>Service Types Array:</h3>
        <pre>{{ json_encode($serviceTypes, JSON_PRETTY_PRINT) }}</pre>

        <h3>HTML Select:</h3>
        <select id="service_type" class="form-select" name="service_type" required>
            <option value="">Select Your Service</option>
            @foreach($serviceTypes as $value => $details)
                <option value="{{ $value }}"
                        data-type="{{ $details['label'] }}"
                        data-has-weight="{{ $details['has_weight'] ? 'true' : 'false' }}"
                        data-base-price="{{ $details['base_price'] }}">
                    {{ $details['label'] }}
                </option>
            @endforeach
        </select>

        <div class="mt-3">
            <button onclick="testDropdown()" class="btn btn-primary">Test Dropdown</button>
        </div>

        <div id="output" class="mt-3"></div>
    </div>

    <script>
        function testDropdown() {
            const select = document.getElementById('service_type');
            const output = document.getElementById('output');

            let html = '<h4>Options found:</h4><ul>';
            for (let i = 0; i < select.options.length; i++) {
                const option = select.options[i];
                html += `<li>Value: ${option.value}, Text: ${option.text}, Type: ${option.dataset.type}, HasWeight: ${option.dataset.hasWeight}, BasePrice: ${option.dataset.basePrice}</li>`;
            }
            html += '</ul>';

            output.innerHTML = html;
        }
    </script>
</body>
</html>
