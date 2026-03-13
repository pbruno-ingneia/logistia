@include('azienda.common.header')

<div class="page-content">
    <div class="container-fluid">

        <!-- Header -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">📊 Report Chilometri Flotta</h4>
                    <div class="page-title-right">
                        <a href="/azienda/tracking" class="btn btn-secondary">
                            <i class="ri-arrow-left-line"></i> Torna alla Mappa
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtri Periodo -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Da</label>
                                <input type="date" name="da" class="form-control" value="{{ $dataInizio }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">A</label>
                                <input type="date" name="a" class="form-control" value="{{ $dataFine }}">
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-filter-line"></i> Filtra
                                </button>
                                <a href="?da={{ date('Y-m-01') }}&a={{ date('Y-m-d') }}" class="btn btn-outline-secondary">
                                    Questo Mese
                                </a>
                                <a href="?da={{ date('Y-m-d', strtotime('-7 days')) }}&a={{ date('Y-m-d') }}" class="btn btn-outline-secondary">
                                    Ultimi 7 giorni
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafico Km Giornalieri -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">📈 Km Giornalieri Totali Flotta</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="graficoKm" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Riepilogo per Mezzo -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">🚛 Riepilogo per Mezzo</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                <tr>
                                    <th>Mezzo</th>
                                    <th>Targa</th>
                                    <th class="text-end">Km Totali</th>
                                    <th class="text-end">Ore Movimento</th>
                                    <th class="text-end">Vel. Media</th>
                                    <th class="text-end">Vel. Max</th>
                                    <th class="text-end">Giorni Attivi</th>
                                    <th class="text-end">Km/Giorno</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php $totaleKm = 0; $totaleOre = 0; @endphp
                                @forelse($report as $r)
                                    @php
                                        $totaleKm += $r->km_totali;
                                        $totaleOre += $r->minuti_movimento;
                                        $kmGiorno = $r->giorni_attivi > 0 ? $r->km_totali / $r->giorni_attivi : 0;
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $r->nome }}</strong></td>
                                        <td>{{ $r->targa }}</td>
                                        <td class="text-end">
                                                <span class="badge bg-primary fs-6">
                                                    {{ number_format($r->km_totali, 1, ',', '.') }} km
                                                </span>
                                        </td>
                                        <td class="text-end">
                                            {{ number_format($r->minuti_movimento / 60, 1, ',', '.') }} h
                                        </td>
                                        <td class="text-end">
                                            {{ number_format($r->velocita_media, 1, ',', '.') }} km/h
                                        </td>
                                        <td class="text-end">
                                            @if($r->velocita_max > 130)
                                                <span class="text-danger">
                                                        <i class="ri-alarm-warning-line"></i>
                                                        {{ number_format($r->velocita_max, 0) }} km/h
                                                    </span>
                                            @else
                                                {{ number_format($r->velocita_max, 0) }} km/h
                                            @endif
                                        </td>
                                        <td class="text-end">{{ $r->giorni_attivi }}</td>
                                        <td class="text-end">
                                            {{ number_format($kmGiorno, 1, ',', '.') }} km
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            Nessun dato disponibile per il periodo selezionato
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                                @if(count($report) > 0)
                                    <tfoot class="table-dark">
                                    <tr>
                                        <th colspan="2">TOTALE FLOTTA</th>
                                        <th class="text-end">{{ number_format($totaleKm, 1, ',', '.') }} km</th>
                                        <th class="text-end">{{ number_format($totaleOre / 60, 1, ',', '.') }} h</th>
                                        <th colspan="4"></th>
                                    </tr>
                                    </tfoot>
                                @endif
                            </table>
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
        const kmGiornalieri = @json($kmGiornalieri);

        if (kmGiornalieri.length === 0) {
            document.getElementById('graficoKm').parentElement.innerHTML =
                '<p class="text-center text-muted py-5">Nessun dato per il grafico</p>';
            return;
        }

        const labels = kmGiornalieri.map(d => {
            const date = new Date(d.data);
            return date.toLocaleDateString('it-IT', { day: '2-digit', month: 'short' });
        });

        const data = kmGiornalieri.map(d => parseFloat(d.km_totali));

        new Chart(document.getElementById('graficoKm'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Km percorsi',
                    data: data,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Chilometri' }
                    }
                }
            }
        });
    });
</script>

@include('azienda.common.footer')
