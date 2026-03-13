{{-- resources/views/azienda/reports_tms.blade.php --}}
@include('azienda.common.header')

<div class="page-content">
    <div class="container-fluid">

        <!-- Titolo pagina -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">
                        <i class="ri-file-chart-line me-2"></i>
                        Report TMS
                    </h4>
                </div>
            </div>
        </div>

        <!-- Filtri -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Data Inizio</label>
                                <input type="date" class="form-control" id="dataInizio" value="{{ date('Y-m-d', strtotime('-30 days')) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Data Fine</label>
                                <input type="date" class="form-control" id="dataFine" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-primary w-100" onclick="aggiornaReport()">
                                    <i class="ri-refresh-line me-1"></i> Aggiorna
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="row mb-4" id="kpiCards">
            <div class="col-lg-2 col-md-4 col-6 mb-3">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="ri-truck-line text-primary" style="font-size: 2rem;"></i>
                        <h3 class="mt-2 mb-0" id="kpiMezzi">-</h3>
                        <small class="text-muted">Mezzi Attivi</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6 mb-3">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="ri-file-list-3-line text-success" style="font-size: 2rem;"></i>
                        <h3 class="mt-2 mb-0" id="kpiOrdini">-</h3>
                        <small class="text-muted">Ordini Periodo</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6 mb-3">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="ri-checkbox-circle-line text-info" style="font-size: 2rem;"></i>
                        <h3 class="mt-2 mb-0" id="kpiCompletati">-</h3>
                        <small class="text-muted">Completati</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6 mb-3">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="ri-money-euro-circle-line text-warning" style="font-size: 2rem;"></i>
                        <h3 class="mt-2 mb-0" id="kpiFatturato">-</h3>
                        <small class="text-muted">Fatturato €</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6 mb-3">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="ri-route-line text-danger" style="font-size: 2rem;"></i>
                        <h3 class="mt-2 mb-0" id="kpiKm">-</h3>
                        <small class="text-muted">Km Totali</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6 mb-3">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="ri-tools-line text-secondary" style="font-size: 2rem;"></i>
                        <h3 class="mt-2 mb-0" id="kpiCosti">-</h3>
                        <small class="text-muted">Costi €</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Cards -->
        <div class="row">

            <!-- Utilizzo Mezzi -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="ri-truck-line text-primary me-2"></i>
                            Utilizzo Mezzi
                        </h5>
                        <button class="btn btn-sm btn-outline-primary" onclick="esportaReport('mezzi')">
                            <i class="ri-download-line"></i> Excel
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th>Mezzo</th>
                                    <th>Targa</th>
                                    <th class="text-center">Ordini</th>
                                    <th class="text-end">Fatturato</th>
                                </tr>
                                </thead>
                                <tbody id="tabellaUtilizzoMezzi">
                                <tr><td colspan="4" class="text-center text-muted py-4">Caricamento...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Autisti -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="ri-user-star-line text-success me-2"></i>
                            Performance Autisti
                        </h5>
                        <button class="btn btn-sm btn-outline-success" onclick="esportaReport('autisti')">
                            <i class="ri-download-line"></i> Excel
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th>Autista</th>
                                    <th class="text-center">Ordini</th>
                                    <th class="text-end">Km</th>
                                    <th class="text-end">Fatturato</th>
                                </tr>
                                </thead>
                                <tbody id="tabellaPerformanceAutisti">
                                <tr><td colspan="4" class="text-center text-muted py-4">Caricamento...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Andamento Ordini -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="ri-line-chart-line text-info me-2"></i>
                            Andamento Ordini
                        </h5>
                        <button class="btn btn-sm btn-outline-info" onclick="esportaReport('ordini')">
                            <i class="ri-download-line"></i> Excel
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th>Data</th>
                                    <th class="text-center">Totale</th>
                                    <th class="text-center">Completati</th>
                                    <th class="text-end">Fatturato</th>
                                </tr>
                                </thead>
                                <tbody id="tabellaAndamentoOrdini">
                                <tr><td colspan="4" class="text-center text-muted py-4">Caricamento...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Costi Operativi -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="ri-tools-line text-warning me-2"></i>
                            Costi Manutenzioni
                        </h5>
                        <button class="btn btn-sm btn-outline-warning" onclick="esportaReport('costi')">
                            <i class="ri-download-line"></i> Excel
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th>Mezzo</th>
                                    <th>Tipo</th>
                                    <th class="text-center">Interventi</th>
                                    <th class="text-end">Costo</th>
                                </tr>
                                </thead>
                                <tbody id="tabellaCostiOperativi">
                                <tr><td colspan="4" class="text-center text-muted py-4">Caricamento...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Km GPS -->
            <div class="col-lg-12 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="ri-gps-line text-danger me-2"></i>
                            Chilometri GPS per Mezzo
                        </h5>
                        <button class="btn btn-sm btn-outline-danger" onclick="esportaReport('km')">
                            <i class="ri-download-line"></i> Excel
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th>Mezzo</th>
                                    <th>Targa</th>
                                    <th class="text-center">Giorni Attivi</th>
                                    <th class="text-end">Km GPS</th>
                                    <th class="text-end">Media Km/Giorno</th>
                                </tr>
                                </thead>
                                <tbody id="tabellaKmGps">
                                <tr><td colspan="5" class="text-center text-muted py-4">Caricamento...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<script>
    // Variabile globale per i dati
    let reportData = {};

    // Carica tutti i report
    async function caricaReport() {
        const dataInizio = document.getElementById('dataInizio').value;
        const dataFine = document.getElementById('dataFine').value;

        try {
            const response = await fetch('/azienda/api/reports-tms', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ data_inizio: dataInizio, data_fine: dataFine })
            });

            const result = await response.json();

            if (result.success) {
                reportData = result.data;
                aggiornaUI();
            } else {
                console.error('Errore:', result.message);
            }
        } catch (error) {
            console.error('Errore caricamento report:', error);
        }
    }

    // Aggiorna l'interfaccia con i dati
    function aggiornaUI() {
        // KPI
        document.getElementById('kpiMezzi').textContent = reportData.kpi?.mezzi_attivi || 0;
        document.getElementById('kpiOrdini').textContent = reportData.kpi?.totale_ordini || 0;
        document.getElementById('kpiCompletati').textContent = reportData.kpi?.ordini_completati || 0;
        document.getElementById('kpiFatturato').textContent = formatNumber(reportData.kpi?.fatturato_totale || 0);
        document.getElementById('kpiKm').textContent = formatNumber(reportData.kpi?.km_totali || 0);
        document.getElementById('kpiCosti').textContent = formatNumber(reportData.kpi?.costi_totali || 0);

        // Tabella Utilizzo Mezzi
        const mezziHtml = reportData.utilizzo_mezzi?.length > 0
            ? reportData.utilizzo_mezzi.map(m => `
            <tr>
                <td><strong>${m.nome || 'N/D'}</strong></td>
                <td><span class="badge bg-primary">${m.targa || 'N/D'}</span></td>
                <td class="text-center">${m.ordini_completati || 0}</td>
                <td class="text-end">€ ${formatNumber(m.fatturato || 0)}</td>
            </tr>
        `).join('')
            : '<tr><td colspan="4" class="text-center text-muted py-4">Nessun dato</td></tr>';
        document.getElementById('tabellaUtilizzoMezzi').innerHTML = mezziHtml;

        // Tabella Performance Autisti
        const autistiHtml = reportData.performance_autisti?.length > 0
            ? reportData.performance_autisti.map(a => `
            <tr>
                <td><strong>${a.nome} ${a.cognome || ''}</strong></td>
                <td class="text-center">${a.ordini_completati || 0}</td>
                <td class="text-end">${formatNumber(a.km_percorsi || 0)} km</td>
                <td class="text-end">€ ${formatNumber(a.fatturato || 0)}</td>
            </tr>
        `).join('')
            : '<tr><td colspan="4" class="text-center text-muted py-4">Nessun dato</td></tr>';
        document.getElementById('tabellaPerformanceAutisti').innerHTML = autistiHtml;

        // Tabella Andamento Ordini
        const ordiniHtml = reportData.andamento_ordini?.length > 0
            ? reportData.andamento_ordini.map(o => `
            <tr>
                <td>${formatDate(o.data)}</td>
                <td class="text-center">${o.totale || 0}</td>
                <td class="text-center"><span class="badge bg-success">${o.completati || 0}</span></td>
                <td class="text-end">€ ${formatNumber(o.fatturato || 0)}</td>
            </tr>
        `).join('')
            : '<tr><td colspan="4" class="text-center text-muted py-4">Nessun dato</td></tr>';
        document.getElementById('tabellaAndamentoOrdini').innerHTML = ordiniHtml;

        // Tabella Costi Operativi
        const costiHtml = reportData.costi_operativi?.length > 0
            ? reportData.costi_operativi.map(c => `
            <tr>
                <td><strong>${c.nome_mezzo || 'N/D'}</strong></td>
                <td>${c.tipo || 'Generico'}</td>
                <td class="text-center">${c.interventi || 0}</td>
                <td class="text-end text-danger">€ ${formatNumber(c.costo || 0)}</td>
            </tr>
        `).join('')
            : '<tr><td colspan="4" class="text-center text-muted py-4">Nessun dato</td></tr>';
        document.getElementById('tabellaCostiOperativi').innerHTML = costiHtml;

        // Tabella Km GPS
        const kmHtml = reportData.km_gps?.length > 0
            ? reportData.km_gps.map(k => `
            <tr>
                <td><strong>${k.nome_mezzo || 'N/D'}</strong></td>
                <td><span class="badge bg-primary">${k.targa || 'N/D'}</span></td>
                <td class="text-center">${k.giorni_attivi || 0}</td>
                <td class="text-end">${formatNumber(k.km_totali || 0)} km</td>
                <td class="text-end">${formatNumber(k.media_giornaliera || 0)} km</td>
            </tr>
        `).join('')
            : '<tr><td colspan="5" class="text-center text-muted py-4">Nessun dato GPS</td></tr>';
        document.getElementById('tabellaKmGps').innerHTML = kmHtml;
    }

    // Formatta numeri
    function formatNumber(num) {
        return parseFloat(num || 0).toLocaleString('it-IT', { maximumFractionDigits: 0 });
    }

    // Formatta date
    function formatDate(dateStr) {
        if (!dateStr) return 'N/D';
        const date = new Date(dateStr);
        return date.toLocaleDateString('it-IT', { day: '2-digit', month: 'short' });
    }

    // Aggiorna report (pulsante)
    function aggiornaReport() {
        caricaReport();
    }


    // Esporta report in CSV (si apre con Excel)
    async function esportaReport(tipo) {
        const dataInizio = document.getElementById('dataInizio').value;
        const dataFine = document.getElementById('dataFine').value;

        try {
            const response = await fetch('/azienda/api/reports-tms/export', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    tipo: tipo,
                    data_inizio: dataInizio,
                    data_fine: dataFine
                })
            });

            if (response.ok) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `report_${tipo}_${dataInizio}_${dataFine}.csv`;  // ← CAMBIATO QUI
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
            } else {
                alert('Errore durante l\'esportazione');
            }
        } catch (error) {
            console.error('Errore export:', error);
            alert('Errore durante l\'esportazione');
        }
    }

    // Carica al load della pagina
    document.addEventListener('DOMContentLoaded', function() {
        caricaReport();
    });
</script>

@include('azienda.common.footer')