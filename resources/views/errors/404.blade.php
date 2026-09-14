<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lost at Sea — 404 | HARBORLINE PROVISIONS</title>
    <style>
        :root {
            --teal: #0B7A75;
            --abyss: #1A3A3A;
            --cream: #F4F1DE;
            --halftone: #E9C46A;
        }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--abyss);
            font-family: 'Georgia', 'Playfair Display', serif;
            color: var(--cream);
            text-align: center;
        }
        .card {
            background: rgba(11, 122, 117, 0.15);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(244, 241, 222, 0.15);
            border-radius: 12px;
            padding: 3rem 4rem;
        }
        .eyebrow {
            text-transform: uppercase;
            letter-spacing: 0.25em;
            font-size: 0.75rem;
            color: var(--halftone);
        }
        h1 {
            font-size: 4rem;
            font-style: italic;
            margin: 0.5rem 0;
        }
        p {
            font-family: 'Inter', sans-serif;
            color: #FAFAFA;
            opacity: 0.85;
        }
        a.btn {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 0.75rem 2rem;
            background: var(--teal);
            color: var(--cream);
            text-decoration: none;
            border-radius: 999px;
            border: 1px solid var(--cream);
            font-family: 'Inter', sans-serif;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="eyebrow">Signal Lost</div>
        <h1>404 — Vessel Not Found</h1>
        <p>This route has drifted off the charts, or the connection to the fleet was lost.</p>
        <a class="btn" href="/">Return to Harbor</a>
    </div>
</body>
</html>
