<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->name }} — Closed</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #0f1a2e; color: #e8e4dc; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2rem; }
        .card { background: #162240; border: 1px solid rgba(201,168,76,0.2); border-radius: 12px; padding: 3rem 2rem; text-align: center; max-width: 420px; }
        h1 { font-family: 'Playfair Display', serif; color: #c9a84c; font-size: 1.5rem; margin-bottom: 0.5rem; }
        p { color: #8899bb; margin-top: 0.75rem; }
        a { color: #c9a84c; }
    </style>
</head>
<body>
    <div class="card">
        <div style="font-size:2.5rem;margin-bottom:1rem">🔒</div>
        <h1>{{ $event->name }}</h1>
        <p>This event is no longer accepting payments.</p>
        <p style="margin-top:1.5rem"><a href="/">← Return to {{ app('tenant')->name }}</a></p>
    </div>
</body>
</html>
