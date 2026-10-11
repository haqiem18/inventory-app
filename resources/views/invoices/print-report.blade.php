<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Laporan Ringkasan' }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 11px; color: #333; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h2 { margin: 0; color: #2c3e50; }
        .header h3 { margin: 5px 0; color: #34495e; }
        .header p { margin: 0; font-size: 11px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #2c3e50; color: white; padding: 8px; text-align: left; border: 1px solid #2c3e50; }
        td { padding: 6px 8px; border: 1px solid #ddd; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .signature { margin-top: 40px; float: right; text-align: right; }
        .no-print { margin-top: 20px; padding: 8px 16px; cursor: pointer; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <h2>DC JB PRINTING</h2>
        <h3>{{ $title ?? 'Laporan Transaksi' }}</h3>
        <p>Dicetak pada: {{ date('d M Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                @foreach($columns as $col)
                    <th>{{ $col }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($records as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                @foreach($rowCallback($item) as $cell)
                    <td>{!! $cell !!}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature">
        <p>Mengetahui,</p>
        <br><br><br>
        <p><strong>Management</strong></p>
    </div>

    <button class="no-print" onclick="window.print()">Cetak Ulang</button>

</body>
</html>