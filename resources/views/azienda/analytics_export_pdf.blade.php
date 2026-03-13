<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dashboard BI - Report</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #333; }

        .header {
            background-color: #0d6efd;
            color: #ffffff;
            padding: 15px 20px;
            margin-bottom: 15px;
        }
        .header h1 { font-size: 20px; margin: 0 0 5px 0; color: #ffffff; }
        .header .periodo { font-size: 11px; color: #cfe2ff; }

        .section { margin: 0 0 15px 0; }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #ffffff;
            padding: 8px 12px;
            margin-bottom: 8px;
        }
        .bg-primary { background-color: #0d6efd; }
        .bg-success { background-color: #198754; }
        .bg-warning { background-color: #ffc107; color: #333 !important; }
        .bg-secondary { background-color: #6c757d; }

        .kpi-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .kpi-table td {
            text-align: center;
            padding: 10px 5px;
            border: 1px solid #dee2e6;
            width: 16.66%;
            vertical-align: top;
        }
        .kpi-value { font-size: 16px; font-weight: bold; color: #0d6efd; margin-bottom: 3px; }
        .kpi-label { font-size: 8px; color: #6c757d; }
        .kpi-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            color: #ffffff;
            margin-top: 3px;
        }
        .badge-success { background-color: #198754; }
        .badge-danger { background-color: #dc3545; }
        .badge-secondary { background-color: #6c757d; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 9px; }
        table.data-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: left;
            padding: 5px 6px;
            border: 1px solid #dee2e6;
        }
        table.data-table td {
            padding: 4px 6px;
            border: 1px solid #dee2e6;
        }
        table.data-table tr:nth-child(even) { background-color: #f8f9fa; }

        .text-success { color: #198754; }
        .text-danger { color: #dc3545; }
        .text-right { text-align: right; }

        .footer {
            text-align: center;
            font-size: 8px;
            color: #999;
            padding: 8px;
            border-top: 1px solid #eee;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<!-- Header -->
<div class="header">
    <h1>Dashboard Business Intelligence - KPI</h1>
    <div class="periodo">
        Periodo: {{ \Carbon\Carbon::parse($dataInizio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($dataFine)->format('d/m/Y') }}
        &nbsp;&nbsp;|&nbsp;&nbsp;
        Generato il: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
    </div>
</div>

<!-- KPI Principali -->
<div class="section">
    <div class="section-title bg-primary">KPI Principali</div>
    <table class="kpi-table">
        <tr>
            <td>
                <div class="kpi-value">&euro; {{ number_format($kpiPrincipali['fatturato'], 0, ',', '.') }}</div>
                <div class="kpi-label">Fatturato Totale</div>
                @if($kpiPrincipali['crescita_fatturato'] != 0)
                    <span class="kpi-badge {{ $kpiPrincipali['crescita_fatturato'] > 0 ? 'badge-success' : 'badge-danger' }}">
                        {{ $kpiPrincipali['crescita_fatturato'] > 0 ? '+' : '' }}{{ number_format($kpiPrincipali['crescita_fatturato'], 1) }}%
                    </span>
                @endif
            </td>
            <td>
                <div class="kpi-value">&euro; {{ number_format($kpiPrincipali['margine_operativo'], 0, ',', '.') }}</div>
                <div class="kpi-label">Margine Operativo</div>
                <span class="kpi-badge badge-secondary">{{ number_format($kpiPrincipali['margine_percentuale'], 1) }}%</span>
            </td>
            <td>
                <div class="kpi-value">{{ $kpiPrincipali['numero_ordini'] }}</div>
                <div class="kpi-label">Ordini Completati</div>
                <span class="kpi-badge badge-success">{{ number_format($kpiPrincipali['tasso_completamento'], 1) }}%</span>
            </td>
            <td>
                <div class="kpi-value">&euro; {{ number_format($kpiPrincipali['ricavo_medio_ordine'], 0) }}</div>
                <div class="kpi-label">Ricavo Medio Ordine</div>
            </td>
            <td>
                <div class="kpi-value">&euro; {{ number_format($kpiPrincipali['ricavo_per_km'], 2) }}</div>
                <div class="kpi-label">Ricavo per Km</div>
            </td>
            <td>
                <div class="kpi-value">{{ number_format($kpiPrincipali['km_totali'], 0, ',', '.') }}</div>
                <div class="kpi-label">Km Percorsi</div>
            </td>
        </tr>
    </table>
</div>

<!-- Top Clienti -->
<div class="section">
    <div class="section-title bg-success">Top 10 Clienti per Fatturato</div>
    <table class="data-table">
        <thead>
        <tr>
            <th>Cliente</th>
            <th>N. Ordini</th>
            <th>Fatturato</th>
            <th>Ricavo Medio</th>
            <th>Km Totali</th>
            <th>Euro/Km</th>
        </tr>
        </thead>
        <tbody>
        @foreach($performanceClienti as $cliente)
            <tr>
                <td><strong>{{ $cliente->nome_cliente }}</strong></td>
                <td>{{ $cliente->numero_ordini }}</td>
                <td class="text-right">&euro; {{ number_format($cliente->fatturato, 0, ',', '.') }}</td>
                <td class="text-right">&euro; {{ number_format($cliente->ricavo_medio, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($cliente->km_totali, 0, ',', '.') }}</td>
                <td class="text-right">&euro; {{ number_format($cliente->ricavo_per_km, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<!-- Performance Mezzi -->
<div class="section">
    <div class="section-title bg-warning">Performance Mezzi</div>
    <table class="data-table">
        <thead>
        <tr>
            <th>Targa</th>
            <th>Mezzo</th>
            <th>Ordini</th>
            <th>Fatturato</th>
            <th>Km</th>
            <th>Costi</th>
            <th>Margine</th>
            <th>ROI %</th>
        </tr>
        </thead>
        <tbody>
        @foreach($performanceMezzi as $mezzo)
            <tr>
                <td><strong>{{ $mezzo->targa }}</strong></td>
                <td>{{ $mezzo->nome_mezzo }}</td>
                <td>{{ $mezzo->numero_ordini }}</td>
                <td class="text-right">&euro; {{ number_format($mezzo->fatturato, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($mezzo->km_totali, 0, ',', '.') }}</td>
                <td class="text-right">&euro; {{ number_format($mezzo->costi_totali, 0, ',', '.') }}</td>
                <td class="text-right {{ $mezzo->margine > 0 ? 'text-success' : 'text-danger' }}">
                    &euro; {{ number_format($mezzo->margine, 0, ',', '.') }}
                </td>
                <td class="text-right">{{ number_format($mezzo->margine_percentuale, 1) }}%</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<!-- Rotte -->
<div class="section">
    <div class="section-title bg-secondary">Top 15 Rotte per Redditivita</div>
    <table class="data-table">
        <thead>
        <tr>
            <th>Rotta</th>
            <th>N. Ordini</th>
            <th>Fatturato</th>
            <th>Km</th>
            <th>Euro/Km</th>
            <th>Margine</th>
            <th>Margine %</th>
        </tr>
        </thead>
        <tbody>
        @foreach($redditivitaRotte as $rotta)
            <tr>
                <td><strong>{{ $rotta['rotta'] }}</strong></td>
                <td>{{ $rotta['numero_ordini'] }}</td>
                <td class="text-right">&euro; {{ number_format($rotta['fatturato'], 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($rotta['km_totali'], 0, ',', '.') }}</td>
                <td class="text-right">&euro; {{ number_format($rotta['ricavo_per_km'], 2) }}</td>
                <td class="text-right {{ $rotta['margine'] > 0 ? 'text-success' : 'text-danger' }}">
                    &euro; {{ number_format($rotta['margine'], 0, ',', '.') }}
                </td>
                <td class="text-right">{{ number_format($rotta['margine_percentuale'], 1) }}%</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div class="footer">
    Logistia - Dashboard Business Intelligence | Report generato il {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
</div>

</body>
</html>