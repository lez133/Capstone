<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Activity Logs</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size:12px; }
        table { width:100%; border-collapse: collapse; }
        th, td { border:1px solid #ddd; padding:6px; vertical-align: top; }
        th { background:#f4f4f4; text-align:left; }
        .meta { font-size:10px; color:#444; word-break:break-all; }
    </style>
</head>
<body>
    <h4>Activity Logs (last {{ $days }} days)</h4>
    <table>
        <thead>
            <tr><th>#</th><th>User</th><th>Action</th><th>Subject</th><th>Meta</th><th>When</th></tr>
        </thead>
        <tbody>
            @foreach($logs as $l)
                <tr>
                    <td>{{ $l['id'] }}</td>
                    <td>{{ $l['user'] }}</td>
                    <td>{{ $l['action'] }}</td>
                    <td>{{ $l['subject'] }}</td>
                    <td class="meta">{{ $l['meta'] }}</td>
                    <td>{{ $l['when'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
