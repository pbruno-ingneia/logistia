@include('azienda.common.header')

<div class="page-content">
    <div class="container-fluid">
        <!-- Titolo -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">🔮 Report Predittivi & Forecast</h4>
                    <div class="page-title-right">
                        <a href="/azienda/analytics/dashboard" class="btn btn-secondary">
                            <i class="ri-arrow-left-line"></i> Torna alla Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Previsioni Domanda -->
        <div class="row mb-4">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title text-white mb-0">
                            <i class="ri-line-chart-line me-2"></i>Previsioni Domanda - Prossimi 3 Mesi
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Trend attuale -->
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <h6><i class="ri-trending-up-line"></i> Trend Identificato</h6>
                                    <p class="mb-1">
                                        <strong>Ordini:</strong> {{ $previsioniDomanda['trend_ordini'] > 0 ? '+' : '' }}{{ number_format($previsioniDomanda['trend_ordini'], 1) }} ordini/mese
                                    </p>
                                    <p class="mb-0">
                                        <strong>Fatturato:</strong> {{ $previsioniDomanda['trend_fatturato'] > 0 ? '+' : '' }}€{{ number_format($previsioniDomanda['trend_fatturato'], 0) }}/mese
                                    </p>
                                </div>
                            </div>

                            <!-- Affidabilità previsione -->
                            <div class="col-md-6">
                                <div class="alert alert-success">
                                    <h6><i class="ri-shield-check-line"></i> Affidabilità Previsione</h6>
                                    <div class="progress mb-2">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 78%"></div>
                                    </div>
                                    <small>78% - Basata su {{ count($previsioniDomanda['dati_storici']) }} mesi di dati</small>
                                </div>
                            </div>
                        </div>

                        <!-- Previsioni -->
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                <tr>
                                    <th>Periodo</th>
                                    <th>Ordini Previsti</th>
                                    <th>Fatturato Previsto</th>
                                    <th>Variazione vs Attuale</th>
                                    <th>Capacità Richiesta</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($previsioniDomanda['previsioni'] as $previsione)
                                    <tr>
                                        <td><strong>{{ $previsione['mese'] }} {{ $previsione['anno'] }}</strong></td>
                                        <td>
                                            <span class="badge bg-primary fs-6">{{ $previsione['ordini_previsti'] }}</span>
                                        </td>
                                        <td>
                                            <span class="text-success fw-bold">€ {{ number_format($previsione['fatturato_previsto'], 0, ',', '.') }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $mediaStorica = $previsioniDomanda['dati_storici']->avg('numero_ordini');
                                                $variazione = $mediaStorica > 0 ? (($previsione['ordini_previsti'] - $mediaStorica) / $mediaStorica) * 100 : 0;
                                            @endphp
                                            <span class="badge {{ $variazione > 0 ? 'bg-success' : 'bg-warning' }}">
                                                {{ $variazione > 0 ? '+' : '' }}{{ number_format($variazione, 1) }}%
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $capacitaRichiesta = ceil($previsione['ordini_previsti'] / 25); // 25 ordini per mezzo/mese
                                            @endphp
                                            <small class="text-muted">{{ $capacitaRichiesta }} mezzi</small>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Grafico dati storici + previsioni -->
                        <div class="mt-4">
                            <canvas id="chartPrevisioni" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Forecast Carburante -->
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h5 class="card-title text-white mb-0">
                            <i class="ri-gas-station-line me-2"></i>Forecast Costi Carburante
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Dati attuali -->
                        <div class="text-center mb-4">
                            <h3 class="text-warning">€ {{ number_format($forecastCarburante['costo_mensile_attuale'], 0) }}</h3>
                            <p class="text-muted mb-1">Costo Mensile Attuale</p>
                            <small class="text-muted">
                                {{ number_format($forecastCarburante['km_medi_mensili']) }} km/mese •
                                {{ $forecastCarburante['consumo_medio'] }}L/100km •
                                €{{ $forecastCarburante['prezzo_diesel_attuale'] }}/L
                            </small>
                        </div>

                        <!-- Scenari -->
                        <h6 class="text-muted mb-3">Scenari Prossimi Mesi:</h6>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span class="text-success">🟢 Ottimistico (-5%)</span>
                                <strong class="text-success">€ {{ number_format($forecastCarburante['scenari']['ottimistico'], 0) }}</strong>
                            </div>
                            <small class="text-muted">Prezzo carburante stabile/diminuzione</small>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span class="text-primary">🔵 Realistico (+5%)</span>
                                <strong class="text-primary">€ {{ number_format($forecastCarburante['scenari']['realistico'], 0) }}</strong>
                            </div>
                            <small class="text-muted">Inflazione normale carburante</small>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span class="text-danger">🔴 Pessimistico (+15%)</span>
                                <strong class="text-danger">€ {{ number_format($forecastCarburante['scenari']['pessimistico'], 0) }}</strong>
                            </div>
                            <small class="text-muted">Crisi energetica/geopolitica</small>
                        </div>

                        <!-- Raccomandazioni -->
                        <div class="alert alert-light mt-4">
                            <h6><i class="ri-lightbulb-line"></i> Raccomandazioni</h6>
                            <ul class="mb-0 small">
                                <li>Considera contratti carburante a prezzo fisso</li>
                                <li>Ottimizza percorsi per ridurre consumi</li>
                                <li>Monitora efficienza mezzi</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analisi Stagionalità -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title text-white mb-0">
                            <i class="ri-calendar-2-line me-2"></i>Analisi Stagionalità - Pattern Annuali
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Grafico stagionalità -->
                            <div class="col-xl-8">
                                <canvas id="chartStagionalita" height="300"></canvas>
                            </div>

                            <!-- Insights stagionali -->
                            <div class="col-xl-4">
                                <h6 class="text-muted mb-3">📈 Insights Stagionali</h6>

                                @php
                                    $fatturatoPicco = $analisiStagionalita->max('fatturato_medio');
                                    $fatturatoMinimo = $analisiStagionalita->min('fatturato_medio');
                                    $mesePicco = $analisiStagionalita->where('fatturato_medio', $fatturatoPicco)->first();
                                    $meseMinimo = $analisiStagionalita->where('fatturato_medio', $fatturatoMinimo)->first();
                                @endphp

                                <div class="alert alert-success">
                                    <h6><i class="ri-arrow-up-line"></i> Picco Stagionale</h6>
                                    <p class="mb-1"><strong>{{ $mesePicco->mese_nome ?? 'N/A' }}</strong></p>
                                    <small>€ {{ number_format($fatturatoPicco ?? 0, 0) }} fatturato medio</small>
                                </div>

                                <div class="alert alert-warning">
                                    <h6><i class="ri-arrow-down-line"></i> Periodo Minimo</h6>
                                    <p class="mb-1"><strong>{{ $meseMinimo->mese_nome ?? 'N/A' }}</strong></p>
                                    <small>€ {{ number_format($fatturatoMinimo ?? 0, 0) }} fatturato medio</small>
                                </div>

                                @php
                                    $variazioneStagionale = $fatturatoMinimo > 0 ? (($fatturatoPicco - $fatturatoMinimo) / $fatturatoMinimo) * 100 : 0;
                                @endphp

                                <div class="alert alert-info">
                                    <h6><i class="ri-bar-chart-line"></i> Variazione Stagionale</h6>
                                    <h4 class="text-info">{{ number_format($variazioneStagionale, 1) }}%</h4>
                                    <small>Differenza tra picco e minimo</small>
                                </div>

                                <!-- Consigli strategici -->
                                <div class="mt-4">
                                    <h6 class="text-muted">💡 Strategie Stagionali</h6>
                                    <ul class="small">
                                        <li><strong>Periodo Alto:</strong> Massimizza capacità, prezzi premium</li>
                                        <li><strong>Periodo Basso:</strong> Manutenzione mezzi, formazione</li>
                                        <li><strong>Pianificazione:</strong> Contratti annuali bilanciati</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h5>🎯 Azioni Basate sui Dati</h5>
                        <p class="text-muted">Usa queste previsioni per ottimizzare la tua strategia</p>
                        <div class="btn-group">
                            <a href="/azienda/analytics/predittivi/export/excel" class="btn btn-success">
                                <i class="ri-file-excel-2-line"></i> Esporta Excel
                            </a>
                            <a href="/azienda/analytics/predittivi/export/pdf" class="btn btn-danger">
                                <i class="ri-file-pdf-line"></i> Esporta PDF
                            </a>
                            <a href="/azienda/ordini-trasporto" class="btn btn-warning">
                                <i class="ri-add-line"></i> Pianifica Ordini
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Dati per i grafici
        const datiStorici = @json($previsioniDomanda['dati_storici']);
        const previsioni = @json($previsioniDomanda['previsioni']);
        const stagionalita = @json($analisiStagionalita);

        // Grafico Previsioni
        const ctxPrevisioni = document.getElementById('chartPrevisioni').getContext('2d');
        new Chart(ctxPrevisioni, {
            type: 'line',
            data: {
                labels: [
                    ...datiStorici.map(d => d.mese + '/' + d.anno),
                    ...previsioni.map(p => p.mese.substring(0,3) + ' ' + p.anno)
                ],
                datasets: [{
                    label: 'Dati Storici',
                    data: [
                        ...datiStorici.map(d => d.numero_ordini),
                        ...Array(previsioni.length).fill(null)
                    ],
                    borderColor: '#0dcaf0',
                    backgroundColor: 'rgba(13, 202, 240, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Previsioni',
                    data: [
                        ...Array(datiStorici.length).fill(null),
                        ...previsioni.map(p => p.ordini_previsti)
                    ],
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    borderDash: [5, 5],
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Numero Ordini'
                        }
                    }
                }
            }
        });

        // Grafico Stagionalità
        const ctxStagionalita = document.getElementById('chartStagionalita').getContext('2d');
        new Chart(ctxStagionalita, {
            type: 'bar',
            data: {
                labels: stagionalita.map(s => s.mese_nome),
                datasets: [{
                    label: 'Fatturato Medio (€)',
                    data: stagionalita.map(s => s.fatturato_medio),
                    backgroundColor: [
                        '#e3f2fd', '#bbdefb', '#90caf9', '#64b5f6', '#42a5f5', '#2196f3',
                        '#1e88e5', '#1976d2', '#1565c0', '#0d47a1', '#82b1ff', '#448aff'
                    ],
                    borderColor: '#1976d2',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Fatturato Medio (€)'
                        }
                    }
                }
            }
        });
    });

    function esportaPrevisioni() {
        alert('Funzionalità esportazione in sviluppo - genererà un Excel con tutte le previsioni');
    }

    function creaAlert() {
        alert('Crea alert personalizzati per essere notificato quando i KPI raggiungono certe soglie');
    }
</script>

@include('azienda.common.footer')