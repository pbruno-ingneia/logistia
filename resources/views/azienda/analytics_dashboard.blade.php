@include('azienda.common.header')

<div class="page-content">
    <div class="container-fluid">
        <!-- Titolo e filtri periodo -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">📊 Business Intelligence - Dashboard KPI</h4>
                    <div class="page-title-right">
                        <form method="GET" class="d-flex">
                            <input type="date" name="data_inizio" class="form-control me-2" value="{{ $dataInizio }}" style="width: 150px;">
                            <input type="date" name="data_fine" class="form-control me-2" value="{{ $dataFine }}" style="width: 150px;">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-search-line"></i> Aggiorna
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Principali -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-gradient bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title text-white mb-4">
                            🎯 KPI Principali
                            <span class="badge bg-light text-dark ms-2">
                                {{ \Carbon\Carbon::parse($dataInizio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($dataFine)->format('d/m/Y') }}
                            </span>
                        </h5>

                        <div class="row">
                            <!-- Fatturato -->
                            <div class="col-xl-2 col-md-4">
                                <div class="text-center">
                                    <h3 class="text-white">€ {{ number_format($kpiPrincipali['fatturato'], 0, ',', '.') }}</h3>
                                    <p class="text-white-50 mb-1">Fatturato Totale</p>
                                    @if($kpiPrincipali['crescita_fatturato'] != 0)
                                        <small class="badge {{ $kpiPrincipali['crescita_fatturato'] > 0 ? 'bg-success' : 'bg-danger' }}">
                                            {{ $kpiPrincipali['crescita_fatturato'] > 0 ? '+' : '' }}{{ number_format($kpiPrincipali['crescita_fatturato'], 1) }}%
                                        </small>
                                    @endif
                                </div>
                            </div>

                            <!-- Margine Operativo -->
                            <div class="col-xl-2 col-md-4">
                                <div class="text-center">
                                    <h3 class="text-white">€ {{ number_format($kpiPrincipali['margine_operativo'], 0, ',', '.') }}</h3>
                                    <p class="text-white-50 mb-1">Margine Operativo</p>
                                    <small class="badge bg-light text-dark">{{ number_format($kpiPrincipali['margine_percentuale'], 1) }}%</small>
                                </div>
                            </div>

                            <!-- Ordini -->
                            <div class="col-xl-2 col-md-4">
                                <div class="text-center">
                                    <h3 class="text-white">{{ $kpiPrincipali['numero_ordini'] }}</h3>
                                    <p class="text-white-50 mb-1">Ordini Completati</p>
                                    <small class="badge bg-success">{{ number_format($kpiPrincipali['tasso_completamento'], 1) }}% completamento</small>
                                </div>
                            </div>

                            <!-- Ricavo medio ordine -->
                            <div class="col-xl-2 col-md-4">
                                <div class="text-center">
                                    <h3 class="text-white">€ {{ number_format($kpiPrincipali['ricavo_medio_ordine'], 0) }}</h3>
                                    <p class="text-white-50 mb-1">Ricavo Medio Ordine</p>
                                </div>
                            </div>

                            <!-- Ricavo per km -->
                            <div class="col-xl-2 col-md-4">
                                <div class="text-center">
                                    <h3 class="text-white">€ {{ number_format($kpiPrincipali['ricavo_per_km'], 2) }}</h3>
                                    <p class="text-white-50 mb-1">Ricavo per Km</p>
                                </div>
                            </div>

                            <!-- Km totali -->
                            <div class="col-xl-2 col-md-4">
                                <div class="text-center">
                                    <h3 class="text-white">{{ number_format($kpiPrincipali['km_totali'], 0, ',', '.') }}</h3>
                                    <p class="text-white-50 mb-1">Km Percorsi</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Trend Mensili -->
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title text-white mb-0">
                            <i class="ri-line-chart-line me-2"></i>Trend Mensili
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="chartTrendMensili" height="300"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top 5 Clienti -->
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title text-white mb-0">
                            <i class="ri-user-star-line me-2"></i>Top 5 Clienti per Fatturato
                        </h5>
                    </div>
                    <div class="card-body">
                        @foreach($performanceClienti->take(5) as $cliente)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="mb-1">{{ Str::limit($cliente->nome_cliente, 20) }}</h6>
                                    <small class="text-muted">{{ $cliente->numero_ordini }} ordini</small>
                                </div>
                                <div class="text-end">
                                    <h6 class="mb-0 text-success">€ {{ number_format($cliente->fatturato, 0, ',', '.') }}</h6>
                                    <small class="text-muted">€ {{ number_format($cliente->ricavo_per_km, 2) }}/km</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Mezzi -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h5 class="card-title text-white mb-0">
                            <i class="ri-truck-line me-2"></i>Performance Mezzi
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                <tr>
                                    <th>Mezzo</th>
                                    <th>Ordini</th>
                                    <th>Fatturato</th>
                                    <th>Km Totali</th>
                                    <th>Costi</th>
                                    <th>Margine</th>
                                    <th>ROI %</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($performanceMezzi as $mezzo)
                                    <tr>
                                        <td>
                                            <strong>{{ $mezzo->targa }}</strong><br>
                                            <small class="text-muted">{{ $mezzo->nome_mezzo }}</small>
                                        </td>
                                        <td>{{ $mezzo->numero_ordini }}</td>
                                        <td>€ {{ number_format($mezzo->fatturato, 0, ',', '.') }}</td>
                                        <td>{{ number_format($mezzo->km_totali, 0, ',', '.') }} km</td>
                                        <td>€ {{ number_format($mezzo->costi_totali, 0, ',', '.') }}</td>
                                        <td>
                                            <span class="{{ $mezzo->margine > 0 ? 'text-success' : 'text-danger' }}">
                                                € {{ number_format($mezzo->margine, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $mezzo->margine_percentuale > 20 ? 'bg-success' : ($mezzo->margine_percentuale > 10 ? 'bg-warning' : 'bg-danger') }}">
                                                {{ number_format($mezzo->margine_percentuale, 1) }}%
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Redditività Rotte -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="card-title text-white mb-0">
                            <i class="ri-map-2-line me-2"></i>Top 15 Rotte per Redditività
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                <tr>
                                    <th>Rotta</th>
                                    <th>Ordini</th>
                                    <th>Fatturato</th>
                                    <th>Km Totali</th>
                                    <th>€/Km</th>
                                    <th>Margine</th>
                                    <th>Margine %</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($redditivitaRotte as $rotta)
                                    <tr>
                                        <td><strong>{{ $rotta['rotta'] }}</strong></td>
                                        <td>{{ $rotta['numero_ordini'] }}</td>
                                        <td>€ {{ number_format($rotta['fatturato'], 0, ',', '.') }}</td>
                                        <td>{{ number_format($rotta['km_totali'], 0, ',', '.') }}</td>
                                        <td>€ {{ number_format($rotta['ricavo_per_km'], 2) }}</td>
                                        <td>
                                            <span class="{{ $rotta['margine'] > 0 ? 'text-success' : 'text-danger' }}">
                                                € {{ number_format($rotta['margine'], 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $rotta['margine_percentuale'] > 25 ? 'bg-success' : ($rotta['margine_percentuale'] > 15 ? 'bg-warning' : 'bg-danger') }}">
                                                {{ number_format($rotta['margine_percentuale'], 1) }}%
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <!-- Link Report Avanzati -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h5>🔮 Analisi Avanzate</h5>
                        <p class="text-muted">Esplora report predittivi e esporta i dati</p>
                        <a href="/azienda/analytics/predittivi" class="btn btn-primary me-2">
                            <i class="ri-line-chart-line"></i> Report Predittivi
                        </a>
                        <a href="/azienda/analytics/export/excel?data_inizio={{ $dataInizio }}&data_fine={{ $dataFine }}" class="btn btn-success me-2">
                            <i class="ri-file-excel-2-line"></i> Esporta Excel
                        </a>
                        <a href="/azienda/analytics/export/pdf?data_inizio={{ $dataInizio }}&data_fine={{ $dataFine }}" class="btn btn-danger" target="_blank">
                            <i class="ri-file-pdf-line"></i> Esporta PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script per Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Dati trend mensili
        const trendData = @json($trendMensili);

        const ctx = document.getElementById('chartTrendMensili').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: trendData.map(item => item.periodo),
                datasets: [
                    {
                        label: 'Fatturato (€)',
                        data: trendData.map(item => item.fatturato),
                        borderColor: '#0dcaf0',
                        backgroundColor: 'rgba(13, 202, 240, 0.1)',
                        tension: 0.4,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Numero Ordini',
                        data: trendData.map(item => item.numero_ordini),
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Fatturato (€)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Numero Ordini'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });
    });
</script>

@include('azienda.common.footer')