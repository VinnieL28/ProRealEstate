<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'App' }}</title>
    <style>
        :root{
            --bg:#05070c; --panel:#0f1620; --text:#e4e9f5; --muted:#8b93a7; --brand:#0dd5d8; --brand-2:#18a4c0; --border:#1a2334; --link:#4cc9f0;
            --sidebar-bg:#050608; --sidebar-hover:#161c23; --sidebar-active:#f7f7f7;
        }
        [data-theme="light"]{
            --bg:#f1f5f9; --panel:#ffffff; --text:#0f172a; --muted:#475569; --border:#dbe2ef; --link:#0ea5e9;
            --sidebar-bg:#f8fafc; --sidebar-hover:#e2e8f0; --sidebar-active:#0f172a;
        }
        html,body{height:100%}
        body{
            margin:0;
            background:radial-gradient(circle at 20% 20%, rgba(13,213,216,.15), transparent 45%), #020408;
            color:var(--text);
            font-family: system-ui,-apple-system, Segoe UI, Roboto, Arial, sans-serif;
        }
        .shell{display:grid; grid-template-columns: 92px 1fr; min-height:100vh;}
        .sidebar{
            background:var(--sidebar-bg);
            border-right:1px solid rgba(255,255,255,.04);
            padding:1.6rem .55rem;
            position:sticky;
            top:0;
            height:100vh;
            display:flex;
            flex-direction:column;
            align-items:center;
            gap:1.5rem;
        }
        .brand{
            width:48px;
            height:48px;
            border-radius:16px;
            background:var(--sidebar-hover);
            display:flex;
            align-items:center;
            justify-content:center;
            color:var(--brand);
            box-shadow:0 6px 20px rgba(0,0,0,.45);
        }
        .brand-icon svg{width:26px; height:26px; stroke:currentColor; stroke-width:1.8; stroke-linecap:round; stroke-linejoin:round; fill:none;}
        .nav{
            display:flex;
            flex-direction:column;
            align-items:center;
            gap:.65rem;
            flex:1;
        }
        .nav a{
            width:52px;
            height:52px;
            border-radius:18px;
            display:flex;
            align-items:center;
            justify-content:center;
            text-decoration:none;
            color:#9da3b4;
            position:relative;
            border:1px solid transparent;
            transition:.2s ease;
        }
        .nav a:focus-visible{
            outline:2px solid var(--brand);
            outline-offset:2px;
        }
        .nav-icon svg{
            width:22px;
            height:22px;
            stroke:currentColor;
            stroke-width:1.8;
            stroke-linecap:round;
            stroke-linejoin:round;
            fill:none;
        }
        .nav a:hover{background:var(--sidebar-hover); color:#d5dae6;}
        .nav a.active{
            background:var(--sidebar-active);
            color:#050608;
            border-color:transparent;
            box-shadow:0 10px 20px rgba(0,0,0,.4);
        }
        .nav a.active::before{
            content:'';
            position:absolute;
            left:-14px;
            width:4px;
            height:50%;
            border-radius:0 4px 4px 0;
            background:#ffffff;
        }
        .nav-label{
            position:absolute;
            left:64px;
            background:#111827;
            color:#f8fafc;
            font-size:.65rem;
            padding:.18rem .5rem;
            border-radius:999px;
            letter-spacing:.08em;
            text-transform:uppercase;
            opacity:0;
            transform:translateX(-6px);
            pointer-events:none;
            transition:.15s ease;
            white-space:nowrap;
        }
        .nav a:hover .nav-label,
        .nav a:focus-visible .nav-label{opacity:1; transform:translateX(0);}
        .content{padding:2rem 2.5rem;}
        .nav-menu{position:relative;width:52px;}
        .nav-panel{
            position:absolute;
            left:52px;
            top:0;
            background:#f8fafc;
            border-radius:18px;
            padding:1rem;
            width:200px;
            color:#0f172a;
            box-shadow:0 25px 45px rgba(15,23,42,.3);
            opacity:0;
            transform:translateX(-6px);
            pointer-events:none;
            transition:.2s ease;
            z-index:5;
        }
        .nav-panel::before{
            content:'';
            position:absolute;
            left:-10px;
            top:24px;
            width:0;
            height:0;
            border-top:10px solid transparent;
            border-bottom:10px solid transparent;
            border-right:10px solid #f8fafc;
        }
        .nav-menu:hover .nav-panel,
        .nav-menu:focus-within .nav-panel{
            opacity:1;
            transform:translateX(0);
            pointer-events:auto;
        }
        .nav-panel h4{
            margin:0 0 .4rem;
            font-size:.8rem;
            text-transform:uppercase;
            letter-spacing:.1em;
            color:#475569;
        }
        .nav-panel a{
            display:flex;
            align-items:center;
            gap:.4rem;
            padding:.35rem .4rem;
            border-radius:10px;
            color:#0f172a;
            text-decoration:none;
            font-size:.85rem;
            width:auto;
            height:auto;
            position:static;
            border:none;
            box-shadow:none;
        }
        .nav-panel a.is-active{
            background:#e0f2f1;
            color:#00796b;
            font-weight:600;
        }
        .topbar{display:flex; justify-content:flex-end; gap:.5rem; margin-bottom:1.5rem;}
        .btn{
            background:#111827;
            color:#fff;
            border:1px solid #1f2937;
            padding:.5rem .85rem;
            border-radius:999px;
            text-decoration:none;
            cursor:pointer;
            transition:.2s ease;
        }
        .btn:hover{background:#1e2533;}
        .card{background:var(--panel); border:1px solid var(--border); border-radius:16px; padding:1.25rem;}
        .grid{display:grid; gap:1.2rem;}
        .grid-4{grid-template-columns: repeat(4, minmax(0,1fr));}
        table{width:100%; border-collapse:collapse;}
        th,td{padding:.7rem .5rem; border-bottom:1px solid var(--border); text-align:left;}
        th{color:#b9c4f5}
        .flash{background:#DCFCE7; color:#065f46; border:1px solid #A7F3D0; padding:.6rem .75rem; border-radius:10px; margin-bottom:1rem;}
        label{font-weight:600}
        input[type=text],input[type=email],input[type=tel],input[type=number],textarea{
            width:100%;
            background:transparent;
            color:var(--text);
            border:1px solid var(--border);
            border-radius:8px;
            padding:.55rem .65rem;
        }
        form .row{display:grid; grid-template-columns: repeat(2,1fr); gap:.85rem}
        .muted{color:var(--muted)}
        .sr-only{
            position:absolute;
            width:1px;
            height:1px;
            padding:0;
            margin:-1px;
            overflow:hidden;
            clip:rect(0,0,0,0);
            white-space:nowrap;
            border:0;
        }
        @media (max-width: 1024px){
            .grid-4{grid-template-columns: repeat(2,minmax(0,1fr));}
        }
        @media (max-width: 800px){
            .shell{grid-template-columns: 80px 1fr;}
            .content{padding:1.5rem;}
        }
        @media (max-width: 640px){
            .shell{grid-template-columns:1fr;}
            .sidebar{
                position:relative;
                height:auto;
                flex-direction:row;
                justify-content:center;
                padding:1rem;
            }
            .nav{flex-direction:row; flex-wrap:wrap; justify-content:center;}
            .nav a{width:48px; height:48px;}
            .nav-label{display:none;}
        }
    </style>
    <script>
        (function(){
            const saved = localStorage.getItem('theme') || 'dark';
            document.documentElement.setAttribute('data-theme', saved);
            window.toggleTheme = function(){
                const cur = document.documentElement.getAttribute('data-theme');
                const next = cur === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', next);
                localStorage.setItem('theme', next);
            }
        })();
    </script>
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <div class="brand" aria-label="Real Estate CRM">
                <span class="brand-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M3.5 11.5 12 4l8.5 7.5" />
                        <path d="M6 10.25V20h5v-6h2v6h5v-9.75" />
                    </svg>
                </span>
                <span class="sr-only">Real Estate CRM</span>
            </div>
            <nav class="nav">
                <a href="{{ route('dashboard.index') }}" class="{{ request()->routeIs('dashboard.index') ? 'active' : '' }}" aria-label="Dashboard">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M3.5 10.5 12 3.5 20.5 10.5" />
                            <path d="M6 9.75V20.5h5.25v-6h2.5v6h5.25V9.75" />
                        </svg>
                    </span>
                    <span class="nav-label">Dashboard</span>
                </a>
                <div class="nav-menu">
                    <a href="{{ route('leads.active') }}" class="{{ request()->routeIs('leads.*') ? 'active' : '' }}" aria-label="Leads">
                        <span class="nav-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="9" cy="9" r="3.2" />
                                <circle cx="17" cy="6.8" r="2.5" />
                                <path d="M3.5 20.5a6 6 0 0 1 11 0" />
                                <path d="M13 14.75a4.75 4.75 0 0 1 7.5 3.95V20.5" />
                            </svg>
                        </span>
                        <span class="nav-label">Leads</span>
                    </a>
                    <div class="nav-panel">
                        <h4>Leads</h4>
                        <a href="{{ route('leads.active') }}" class="{{ request()->routeIs('leads.active') ? 'is-active' : '' }}">
                            <svg viewBox="0 0 20 20" width="16" height="16" aria-hidden="true">
                                <circle cx="10" cy="10" r="8" stroke="#0ea5e9" stroke-width="2" fill="none"/>
                                <path d="M10 5v5l3 2" stroke="#0ea5e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                            </svg>
                            Active Leads
                        </a>
                        <a href="{{ route('leads.warm') }}" class="{{ request()->routeIs('leads.warm') ? 'is-active' : '' }}">
                            <svg viewBox="0 0 20 20" width="16" height="16" aria-hidden="true">
                                <path d="M10 3.5 5 7v6l5 3.5 5-3.5V7Z" stroke="#0ea5e9" stroke-width="1.5" fill="none"/>
                                <circle cx="10" cy="10" r="1" fill="#0ea5e9"/>
                            </svg>
                            Warm Leads
                        </a>
                        <a href="#" onclick="return false;">
                            <svg viewBox="0 0 20 20" width="16" height="16" aria-hidden="true">
                                <path d="M6 5h8l2 3-6 7-6-7z" stroke="#94a3b8" fill="none"/>
                                <circle cx="10" cy="7" r="1" fill="#94a3b8"/>
                            </svg>
                            Dead Leads
                        </a>
                        <a href="#" onclick="return false;">
                            <svg viewBox="0 0 20 20" width="16" height="16" aria-hidden="true">
                                <path d="M4 7h6l4 6h-6z" stroke="#94a3b8" fill="none"/>
                                <circle cx="6" cy="6" r="1" fill="#94a3b8"/>
                            </svg>
                            Referred To Agent
                        </a>
                    </div>
                </div>
                <a href="{{ route('properties.index') }}" class="{{ request()->is('properties*') ? 'active' : '' }}" aria-label="Properties">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M5 21V10l7-5 7 5v11" />
                            <path d="M9.5 21v-6.25h5V21" />
                            <path d="M9.5 12.5h5" />
                        </svg>
                    </span>
                    <span class="nav-label">Properties</span>
                </a>
                <a href="{{ route('communications.index') }}" class="{{ request()->is('communications*') ? 'active' : '' }}" aria-label="Communications">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M5 6.25h14a2 2 0 0 1 2 2v6.5a2 2 0 0 1-2 2h-5.5L9 20.5v-3.75H5a2 2 0 0 1-2-2V8.25a2 2 0 0 1 2-2Z" />
                            <path d="M8 10.25h8" />
                            <path d="M8 13.25h4.5" />
                        </svg>
                    </span>
                    <span class="nav-label">Communications</span>
                </a>
                <a href="{{ route('settings.index') }}" class="{{ request()->is('settings*') ? 'active' : '' }}" aria-label="Settings">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="12" r="3.2" />
                            <path d="m6 14.5-1.3.75 1.3 2.25 1.5-.35 1.4 1.55-.35 1.5 2.25 1.3.75-1.3h2l.75 1.3 2.25-1.3-.35-1.5 1.45-1.55 1.5.35 1.3-2.25-1.3-.75v-2l1.3-.75-1.3-2.25-1.5.35-1.45-1.55.35-1.5-2.25-1.3-.75 1.3h-2l-.75-1.3-2.25 1.3.35 1.5-1.4 1.55-1.5-.35-1.3 2.25 1.3.75z" />
                        </svg>
                    </span>
                    <span class="nav-label">Settings</span>
                </a>
            </nav>
        </aside>
        <main class="content">
            <div class="topbar">
                <button class="btn" onclick="toggleTheme()">Toggle Theme</button>
            </div>
            @if(session('ok'))
                <div class="flash">{{ session('ok') }}</div>
            @endif
            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>
</body>
</html>
