<!DOCTYPE html>
<html>
<head>
    <title>CSRF Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>CSRF Token Test</h1>
    
    <p><strong>CSRF Token:</strong> {{ csrf_token() }}</p>
    <p><strong>Session ID:</strong> {{ session()->getId() }}</p>
    
    <form method="POST" action="/test-csrf">
        @csrf
        <input type="text" name="test_field" placeholder="Test field" required>
        <button type="submit">Test CSRF</button>
    </form>
    
    @if(session('success'))
        <div style="color: green; margin-top: 10px;">
            {{ session('success') }}
        </div>
    @endif
    
    @if($errors->any())
        <div style="color: red; margin-top: 10px;">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif
    
    <script>
        console.log('CSRF Token from meta:', document.querySelector('meta[name="csrf-token"]').content);
        console.log('CSRF Token from form:', document.querySelector('input[name="_token"]').value);
    </script>
</body>
</html>