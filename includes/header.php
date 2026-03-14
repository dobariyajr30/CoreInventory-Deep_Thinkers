<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoreInventory <?= isset($pageTitle) ? '– '.$pageTitle : '' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Space Grotesk', 'sans-serif'], mono: ['JetBrains Mono', 'monospace'] },
                    colors: {
                        brand: { 50:'#eefbf3', 100:'#d6f5e3', 200:'#b0eaca', 300:'#7bd8aa', 400:'#45be84', 500:'#22a36a', 600:'#178455', 700:'#136847', 800:'#115839', 900:'#0e4830' },
                        dark:  { 900:'#0d0f12', 800:'#141719', 700:'#1c1f24', 600:'#252930', 500:'#2e333c', 400:'#3d4350' }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Space Grotesk', sans-serif; background: #0d0f12; color: #e2e8f0; }
        .sidebar-link { transition: all .15s; }
        .sidebar-link:hover, .sidebar-link.active { background: rgba(34,163,106,.15); color: #45be84; border-left: 3px solid #22a36a; }
        .sidebar-link { border-left: 3px solid transparent; }
        .card { background: #141719; border: 1px solid #252930; border-radius: 12px; }
        .badge-draft     { background:#1e293b; color:#94a3b8; }
        .badge-waiting   { background:#1c1a05; color:#facc15; }
        .badge-ready     { background:#0c1a2e; color:#60a5fa; }
        .badge-done      { background:#052e16; color:#4ade80; }
        .badge-canceled  { background:#1f0a0a; color:#f87171; }
        .input-field { background:#1c1f24; border:1px solid #2e333c; color:#e2e8f0; border-radius:8px; padding:8px 12px; width:100%; outline:none; transition: border-color .2s; }
        .input-field:focus { border-color:#22a36a; }
        select.input-field option { background:#1c1f24; }
        .btn-primary { background:#22a36a; color:#fff; padding:8px 18px; border-radius:8px; font-weight:600; transition: background .2s; cursor:pointer; border:none; }
        .btn-primary:hover { background:#178455; }
        .btn-secondary { background:#252930; color:#e2e8f0; padding:8px 18px; border-radius:8px; font-weight:600; transition: background .2s; cursor:pointer; border:none; }
        .btn-secondary:hover { background:#2e333c; }
        .btn-danger { background:#7f1d1d; color:#fca5a5; padding:8px 18px; border-radius:8px; font-weight:600; transition: background .2s; cursor:pointer; border:none; }
        .btn-danger:hover { background:#991b1b; }
        table { width:100%; border-collapse:collapse; }
        th { background:#1c1f24; color:#94a3b8; font-size:12px; text-transform:uppercase; letter-spacing:.05em; padding:10px 14px; text-align:left; }
        td { padding:12px 14px; border-bottom:1px solid #1c1f24; font-size:14px; }
        tr:hover td { background:rgba(255,255,255,.02); }
        ::-webkit-scrollbar { width:6px; } ::-webkit-scrollbar-track { background:#141719; } ::-webkit-scrollbar-thumb { background:#2e333c; border-radius:3px; }
    </style>
</head>
<body class="flex h-screen overflow-hidden">
