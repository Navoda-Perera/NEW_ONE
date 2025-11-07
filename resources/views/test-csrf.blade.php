<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CSRF Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>CSRF Token Test</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Current CSRF Token:</strong> <code>{{ csrf_token() }}</code></p>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('test.csrf.submit') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="test_input" class="form-label">Test Input</label>
                                <input type="text" class="form-control" id="test_input" name="test_input" value="test data">
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Test Form</button>
                        </form>

                        <hr>

                        <h6>Session Information:</h6>
                        <ul>
                            <li><strong>Session ID:</strong> {{ session()->getId() }}</li>
                            <li><strong>Session Driver:</strong> {{ config('session.driver') }}</li>
                            <li><strong>CSRF Token in Session:</strong> {{ session()->token() }}</li>
                        </ul>

                        <div class="mt-3">
                            <a href="{{ route('admin.login') }}" class="btn btn-info">Go to Admin Login</a>
                            <a href="{{ route('pm.login') }}" class="btn btn-warning">Go to PM Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
