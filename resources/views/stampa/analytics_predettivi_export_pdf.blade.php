<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Report Predittivi &amp; Forecast</title>
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
        .bg-warning { background-color: #ffc107; color: #333 !important; }
        .bg-info { background-color: #0dcaf0; }

        /* Info boxes using tables */
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .info-table td { padding: 8px 12px; border: 1px solid #dee2e6; vertical-align: top; }
        .info-label { font-weight: bold; color: #6c757d; font-size: 9px; }
        .info-value { font-size: 14px; font-weight: bold; }
        .info-value-primary { color: #0d6efd; }
        .info-value-success { color: #198754; }
        .info-value-danger { color: #dc3545; }
        .info-value-warning { color: #ffc107; }

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

        .box-green { background-color: #d1e7dd; padding: 8px; margin-bottom: 5px; }
        .box-blue { background-color: #cfe2ff; padding: 8px; margin-bottom: 5px; }
        .box-red { background-color: #f8d7da; padding: 8px; margin-bottom: 5px; }

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
    <h1>Report Predittivi &amp; Forecast</h1>
    <div class="periodo">
        Generato il: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
        &nbsp;&nbsp;|&nbsp;&nbsp;
        Dati storici: ultimi {{ count($previsioniDomanda['dati_storici']) }} mesi
    </div>
</div>

<!-- Trend Identificato -->
<div class="section">
    <div class="section-title bg-primary">Previsioni Domanda - Prossimi 3 Mesi</div>

    <table class="info-table">
        <tr>
            <td style="width: 50%;">
                <div class="info-label">TREND ORDINI</div>
                <div class="info-value info-value-primary">
                    {{ $previsioniDomanda['trend_ordini'] > 0 ? '+' : '' }}{{ number_format($previsioniDomanda['trend_ordini'], 1) }} ordini/mese
                </div>
            </td>
            <td style="width: 50%;">
                <div class="info-label">TREND FATTURATO</div>
                <div class="info-value info-value-success">
                    {{ $previsioniDomanda['trend_fatturato'] > 0 ? '+' : '' }}&euro; {{ number_format($previsioniDomanda['trend_fatturato'], 0, ',', '.') }}/mese
                </div>
            </td>
        </tr>
    </table>

    <!-- Tabella Previsioni -->
    <table class="data-table">
        <thead>
        <tr>
            <th>Periodo</th>
            <th>Ordini Previsti</th>
            <th>Fatturato Previsto</th>
            <th>Capacita Richiesta</th>
        </tr>
        </thead>
        <tbody>
        @foreach($previsioniDomanda['previsioni'] as $previsione)
            <tr>
                <td><strong>{{ $previsione['mese'] }} {{ $previsione['anno'] }}</strong></td>
                <td>{{ $previsione['ordini_previsti'] }}</td>
                <td class="text-right text-success">
                    <strong>&euro; {{ number_format($previsione['fatturato_previsto'], 0, ',', '.') }}</strong>
                </td>
                <td>{{ ceil($previsione['ordini_previsti'] / 25) }} mezzi</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <!-- Dati Storici -->
    <br>
    <strong>Dati Storici (ultimi {{ count($previsioniDomanda['dati_storici']) }} mesi):</strong>
    <table class="data-table">
        <thead>
        <tr>
            <th>Mese</th>
            <th>Anno</th>
            <th>N. Ordini</th>
            <th>Fatturato</th>
        </tr>
        </thead>
        <tbody>
        @foreach($previsioniDomanda['dati_storici'] as $dato)
            <tr>
                <td>{{ $dato->mese }}</td>
                <td>{{ $dato->anno }}</td>
                <td>{{ $dato->numero_ordini }}</td>
                <td class="text-right">&euro; {{ number_format($dato->fatturato, 0, ',', '.') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<!-- Forecast Carburante -->
<div class="section">
    <div class="section-title bg-warning">Forecast Costi Carburante</div>

    <table class="info-table">
        <tr>
            <td style="width: 25%;">
                <div class="info-label">COSTO MENSILE ATTUALE</div>
                <div class="info-value info-value-warning">&euro; {{ number_format($forecastCarburante['costo_mensile_attuale'], 0) }}</div>
            </td>
            <td style="width: 25%;">
                <div class="info-label">KM MEDI MENSILI</div>
                <div class="info-value">{{ number_format($forecastCarburante['km_medi_mensili']) }}</div>
            </td>
            <td style="width: 25%;">
                <div class="info-label">PREZZO DIESEL</div>
                <div class="info-value">&euro; {{ $forecastCarburante['prezzo_diesel_attuale'] }}/L</div>
            </td>
            <td style="width: 25%;">
                <div class="info-label">CONSUMO MEDIO</div>
                <div class="info-value">{{ $forecastCarburante['consumo_medio'] }} L/100km</div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
        <tr>
            <th>Scenario</th>
            <th>Variazione Prezzo</th>
            <th>Costo Mensile Previsto</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td class="text-success"><strong>Ottimistico</strong></td>
            <td>-5%</td>
            <td class="text-right text-success"><strong>&euro; {{ number_format($forecastCarburante['scenari']['ottimistico'], 0) }}</strong></td>
        </tr>
        <tr>
            <td style="color: #0d6efd;"><strong>Realistico</strong></td>
            <td>+5%</td>
            <td class="text-right" style="color: #0d6efd;"><strong>&euro; {{ number_format($forecastCarburante['scenari']['realistico'], 0) }}</strong></td>
        </tr>
        <tr>
            <td class="text-danger"><strong>Pessimistico</strong></td>
            <td>+15%</td>
            <td class="text-right text-danger"><strong>&euro; {{ number_format($forecastCarburante['scenari']['pessimistico'], 0) }}</strong></td>
        </tr>
        </tbody>
    </table>
</div>

<!-- Stagionalita -->
<div class="section">
    <div class="section-title bg-info">Analisi Stagionalita - Pattern Annuali</div>

    @php
        $fatturatoPicco = $analisiStagionalita->max('fatturato_medio');
        $fatturatoMinimo = $analisiStagionalita->min('fatturato_medio');
        $mesePicco = $analisiStagionalita->where('fatturato_medio', $fatturatoPicco)->first();
        $meseMinimo = $analisiStagionalita->where('fatturato_medio', $fatturatoMinimo)->first();
        $variazioneStagionale = $fatturatoMinimo > 0 ? (($fatturatoPicco - $fatturatoMinimo) / $fatturatoMinimo) * 100 : 0;
    @endphp

    <table class="info-table">
        <tr>
            <td style="width: 33%; background-color: #d1e7dd;">
                <div class="info-label">PICCO STAGIONALE</div>
                <div class="info-value info-value-success">{{ $mesePicco->mese_nome ?? 'N/A' }}</div>
                <div style="font-size: 9px;">&euro; {{ number_format($fatturatoPicco ?? 0, 0) }} fatturato medio</div>
            </td>
            <td style="width: 33%; background-color: #fff3cd;">
                <div class="info-label">PERIODO MINIMO</div>
                <div class="info-value info-value-warning">{{ $meseMinimo->mese_nome ?? 'N/A' }}</div>
                <div style="font-size: 9px;">&euro; {{ number_format($fatturatoMinimo ?? 0, 0) }} fatturato medio</div>
            </td>
            <td style="width: 33%; background-color: #cfe2ff;">
                <div class="info-label">VARIAZIONE STAGIONALE</div>
                <div class="info-value info-value-primary">{{ number_format($variazioneStagionale, 1) }}%</div>
                <div style="font-size: 9px;">Differenza tra picco e minimo</div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
        <tr>
            <th>Mese</th>
            <th>Fatturato Medio</th>
            <th>Ordini Totali</th>
        </tr>
        </thead>
        <tbody>
        @foreach($analisiStagionalita as $item)
            <tr>
                <td><strong>{{ $item->mese_nome ?? 'Mese ' . $item->mese }}</strong></td>
                <td class="text-right">&euro; {{ number_format($item->fatturato_medio, 0, ',', '.') }}</td>
                <td>{{ $item->ordini_totali }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div class="footer">
    Logistia - Report Predittivi &amp; Forecast | Generato il {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
</div>

</body>
</html>