<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily CRM Digest</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f4f4f5; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 32px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .header { background: #1e293b; color: #fff; padding: 28px 32px; }
        .header h1 { margin: 0; font-size: 20px; font-weight: 600; }
        .header p { margin: 4px 0 0; font-size: 13px; color: #94a3b8; }
        .body { padding: 24px 32px; }
        .greeting { font-size: 15px; color: #374151; margin-bottom: 20px; }
        .stat-grid { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 28px; }
        .stat-card { flex: 1; min-width: 120px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; text-align: center; }
        .stat-card .num { font-size: 26px; font-weight: 700; color: #1e293b; }
        .stat-card .label { font-size: 11px; text-transform: uppercase; letter-spacing: .5px; color: #64748b; margin-top: 2px; }
        .section { margin-bottom: 24px; }
        .section h2 { font-size: 13px; text-transform: uppercase; letter-spacing: .6px; color: #64748b; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        table th { text-align: left; padding: 6px 8px; color: #6b7280; font-weight: 500; }
        table td { padding: 7px 8px; border-top: 1px solid #f1f5f9; color: #374151; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 9999px; font-size: 11px; font-weight: 600; }
        .badge-red { background: #fee2e2; color: #dc2626; }
        .badge-yellow { background: #fef3c7; color: #d97706; }
        .badge-green { background: #d1fae5; color: #059669; }
        .badge-gray { background: #f1f5f9; color: #475569; }
        .no-data { color: #9ca3af; font-size: 13px; font-style: italic; padding: 8px 0; }
        .footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px 32px; font-size: 12px; color: #9ca3af; text-align: center; }
        .cta { display: inline-block; margin-top: 20px; background: #3b82f6; color: #fff; padding: 10px 24px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 500; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>Daily CRM Digest</h1>
        <p>{{ now()->format('l, F j, Y') }}</p>
    </div>
    <div class="body">
        <p class="greeting">Hi {{ $user->name }},<br>Here's a summary of your CRM activity and what needs your attention today.</p>

        {{-- Stats row --}}
        <div class="stat-grid">
            <div class="stat-card">
                <div class="num">{{ $digest['my_open_leads'] }}</div>
                <div class="label">Open Leads</div>
            </div>
            <div class="stat-card">
                <div class="num">{{ $digest['tasks_due_today'] }}</div>
                <div class="label">Tasks Due Today</div>
            </div>
            <div class="stat-card">
                <div class="num">{{ $digest['tasks_overdue'] }}</div>
                <div class="label">Overdue Tasks</div>
            </div>
            <div class="stat-card">
                <div class="num">{{ $digest['new_leads_yesterday'] }}</div>
                <div class="label">New Leads (Yesterday)</div>
            </div>
        </div>

        {{-- Tasks Due Today --}}
        <div class="section">
            <h2>Tasks Due Today</h2>
            @if(count($digest['tasks_due_today_list']) > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>Priority</th>
                            <th>Related</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($digest['tasks_due_today_list'] as $task)
                            <tr>
                                <td>{{ $task['title'] }}</td>
                                <td>
                                    @php
                                        $pClass = match($task['priority']) {
                                            'high'   => 'badge-red',
                                            'medium' => 'badge-yellow',
                                            'low'    => 'badge-green',
                                            default  => 'badge-gray',
                                        };
                                    @endphp
                                    <span class="badge {{ $pClass }}">{{ ucfirst($task['priority']) }}</span>
                                </td>
                                <td>{{ $task['related'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="no-data">No tasks due today.</p>
            @endif
        </div>

        {{-- Overdue Tasks --}}
        @if(count($digest['overdue_tasks_list']) > 0)
        <div class="section">
            <h2>Overdue Tasks</h2>
            <table>
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Was Due</th>
                        <th>Priority</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($digest['overdue_tasks_list'] as $task)
                        <tr>
                            <td>{{ $task['title'] }}</td>
                            <td>{{ $task['due_date'] }}</td>
                            <td><span class="badge badge-red">{{ ucfirst($task['priority']) }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Hot Leads --}}
        @if(count($digest['hot_leads']) > 0)
        <div class="section">
            <h2>Hot Leads (Score &ge; 70)</h2>
            <table>
                <thead>
                    <tr>
                        <th>Lead</th>
                        <th>Score</th>
                        <th>Stage</th>
                        <th>Phone</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($digest['hot_leads'] as $lead)
                        <tr>
                            <td>{{ $lead['name'] }}</td>
                            <td><span class="badge badge-green">{{ $lead['score'] }}</span></td>
                            <td>{{ $lead['stage'] }}</td>
                            <td>{{ $lead['phone'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <a href="{{ config('app.url') }}/admin" class="cta">Open CRM Dashboard</a>
    </div>
    <div class="footer">
        You are receiving this email because daily digests are enabled for your account.<br>
        &copy; {{ now()->year }} {{ config('app.name') }}
    </div>
</div>
</body>
</html>
