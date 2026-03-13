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
                        Centro Report TMS
                    </h4>
                    <div class="page-title-right">
                        <button class="btn btn-outline-primary me-2" onclick="scheduleReports()">
                            <i class="ri-calendar-schedule-line"></i> Pianifica Report
                        </button>
                        <button class="btn btn-primary" onclick="exportAllReports()">
                            <i class="ri-download-cloud-line"></i> Esporta Tutti
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtri globali -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">Data Inizio</label>
                                <input type="date" class="form-control" id="globalDateStart" value="{{ date('Y-m-d', strtotime('-30 days')) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Data Fine</label>
                                <input type="date" class="form-control" id="globalDateEnd" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Categoria</label>
                                <select class="form-select" id="categoryFilter">
                                    <option value="">Tutte le categorie</option>
                                    <option value="operational">Operativi</option>
                                    <option value="financial">Finanziari</option>
                                    <option value="performance">Performance</option>
                                    <option value="compliance">Compliance</option>
                                    <option value="predictive">Predittivi</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button class="btn btn-primary w-100" onclick="updateAllReports()">
                                    <i class="ri-refresh-line"></i> Aggiorna Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- REPORT OPERATIVI -->
        <div class="row mb-4" data-category="operational">
            <div class="col-12">
                <div class="card bg-primary text-white">
                    <div class="card-header">
                        <h5 class="card-title text-white mb-0">
                            <i class="ri-truck-line me-2"></i>Report Operativi
                        </h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4" data-category="operational">
            <!-- Dashboard Dispatch -->
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card h-100 border-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">
                                    <i class="ri-dashboard-3-line text-primary me-2"></i>
                                    Dashboard Dispatch
                                </h5>
                                <p class="text-muted">Stato ordini e mezzi in tempo reale</p>
                            </div>
                            <span class="badge bg-primary">Real-time</span>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary btn-sm me-2" onclick="generateReport('dispatch', 'view')">
                                <i class="ri-eye-line"></i> Visualizza
                            </button>
                            <button class="btn btn-outline-primary btn-sm" onclick="generateReport('dispatch', 'pdf')">
                                <i class="ri-file-pdf-line"></i> PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Utilizzo Mezzi -->
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card h-100 border-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">
                                    <i class="ri-truck-line text-primary me-2"></i>
                                    Utilizzo Mezzi
                                </h5>
                                <p class="text-muted">Efficienza e saturazione flotta</p>
                            </div>
                            <span class="badge bg-info">Giornaliero</span>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary btn-sm me-2" onclick="generateReport('utilizzo_mezzi', 'view')">
                                <i class="ri-eye-line"></i> Visualizza
                            </button>
                            <button class="btn btn-outline-primary btn-sm" onclick="generateReport('utilizzo_mezzi', 'excel')">
                                <i class="ri-file-excel-line"></i> Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Autisti -->
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card h-100 border-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">
                                    <i class="ri-user-star-line text-primary me-2"></i>
                                    Performance Autisti
                                </h5>
                                <p class="text-muted">KPI e produttività conducenti</p>
                            </div>
                            <span class="badge bg-info">Settimanale</span>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary btn-sm me-2" onclick="generateReport('performance_autisti', 'view')">
                                <i class="ri-eye-line"></i> Visualizza
                            </button>
                            <button class="btn btn-outline-primary btn-sm" onclick="generateReport('performance_autisti', 'pdf')">
                                <i class="ri-file-pdf-line"></i> PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tracking Spedizioni -->
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card h-100 border-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">
                                    <i class="ri-map-pin-line text-primary me-2"></i>
                                    Tracking Spedizioni
                                </h5>
                                <p class="text-muted">Monitoraggio consegne in corso</p>
                            </div>
                            <span class="badge bg-primary">Real-time</span>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary btn-sm me-2" onclick="generateReport('tracking', 'view')">
                                <i class="ri-eye-line"></i> Visualizza
                            </button>
                            <button class="btn btn-outline-primary btn-sm" onclick="generateReport('tracking', 'json')">
                                <i class="ri-code-line"></i> API
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ottimizzazione Rotte -->
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card h-100 border-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">
                                    <i class="ri-route-line text-primary me-2"></i>
                                    Ottimizzazione Rotte
                                </h5>
                                <p class="text-muted">Analisi efficienza percorsi</p>
                            </div>
                            <span class="badge bg-info">Giornaliero</span>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary btn-sm me-2" onclick="generateReport('rotte', 'view')">
                                <i class="ri-eye-line"></i> Visualizza
                            </button>
                            <button class="btn btn-outline-primary btn-sm" onclick="generateReport('rotte', 'pdf')">
                                <i class="ri-file-pdf-line"></i> PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tempi di Servizio -->
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card h-100 border-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">
                                    <i class="ri-time-line text-primary me-2"></i>
                                    Tempi di Servizio
                                </h5>
                                <p class="text-muted">Analisi lead time e puntualità</p>
                            </div>
                            <span class="badge bg-warning">Settimanale</span>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary btn-sm me-2" onclick="generateReport('tempi_servizio', 'view')">
                                <i class="ri-eye-line"></i> Visualizza
                            </button>
                            <button class="btn btn-outline-primary btn-sm" onclick="generateReport('tempi_servizio', 'excel')">
                                <i class="ri-file-excel-line"></i> Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- REPORT FINANZIARI -->
        <div class="row mb-4" data-category="financial">
            <div class="col-12">
                <div class="card bg-success text-white">
                    <div class="card-header">
                        <h5 class="card-title text-white mb-0">
                            <i class="ri-money-dollar-circle-line me-2"></i>Report Finanziari
                        </h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4" data-category="financial">
            <!-- P&L Trasporti -->
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card h-100 border-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">
                                    <i class="ri-line-chart-line text-success me-2"></i>
                                    P&L Trasporti
                                </h5>
                                <p class="text-muted">Ricavi, costi e marginalità</p>
                            </div>
                            <span class="badge bg-warning">Mensile</span>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-success btn-sm me-2" onclick="generateReport('p_and_l', 'view')">
                                <i class="ri-eye-line"></i> Visualizza
                            </button>
                            <button class="btn btn-outline-success btn-sm" onclick="generateReport('p_and_l', 'excel')">
                                <i class="ri-file-excel-line"></i> Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ROI per Mezzo -->
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card h-100 border-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">
                                    <i class="ri-funds-line text-success me-2"></i>
                                    ROI per Mezzo
                                </h5>
                                <p class="text-muted">Redditività investimenti flotta</p>
                            </div>
                            <span class="badge bg-warning">Mensile</span>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-success btn-sm me-2" onclick="generateReport('roi_mezzi', 'view')">
                                <i class="ri-eye-line"></i> Visualizza
                            </button>
                            <button class="btn btn-outline-success btn-sm" onclick="generateReport('roi_mezzi', 'pdf')">
                                <i class="ri-file-pdf-line"></i> PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Costi Operativi -->
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card h-100 border-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">
                                    <i class="ri-gas-station-line text-success me-2"></i>
                                    Costi Operativi
                                </h5>
                                <p class="text-muted">Carburante, manutenzione, pedaggi</p>
                            </div>
                            <span class="badge bg-info">Settimanale</span>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-success btn-sm me-2" onclick="generateReport('costi_operativi', 'view')">
                                <i class="ri-eye-line"></i> Visualizza
                            </button>
                            <button class="btn btn-outline-success btn-sm" onclick="generateReport('costi_operativi', 'excel')">
                                <i class="ri-file-excel-line"></i> Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- REPORT PERFORMANCE -->
        <div class="row mb-4" data-category="performance">
            <div class="col-12">
                <div class="card bg-warning text-white">
                    <div class="card-header">
                        <h5 class="card-title text-white mb-0">
                            <i class="ri-dashboard-line me-2"></i>Report Performance
                        </h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4" data-category="performance">
            <!-- KPI Dashboard -->
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card h-100 border-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">
                                    <i class="ri-dashboard-line text-warning me-2"></i>
                                    KPI Dashboard
                                </h5>
                                <p class="text-muted">Indicatori performance chiave</p>
                            </div>
                            <span class="badge bg-primary">Real-time</span>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-warning btn-sm me-2" onclick="generateReport('kpi_dashboard', 'view')">
                                <i class="ri-eye-line"></i> Visualizza
                            </button>
                            <button class="btn btn-outline-warning btn-sm" onclick="generateReport('kpi_dashboard', 'pdf')">
                                <i class="ri-file-pdf-line"></i> PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- On-Time Delivery -->
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card h-100 border-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">
                                    <i class="ri-time-line text-warning me-2"></i>
                                    On-Time Delivery
                                </h5>
                                <p class="text-muted">Puntualità consegne</p>
                            </div>
                            <span class="badge bg-info">Giornaliero</span>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-warning btn-sm me-2" onclick="generateReport('otd', 'view')">
                                <i class="ri-eye-line"></i> Visualizza
                            </button>
                            <button class="btn btn-outline-warning btn-sm" onclick="generateReport('otd', 'excel')">
                                <i class="ri-file-excel-line"></i> Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Satisfaction -->
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card h-100 border-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">
                                    <i class="ri-emotion-happy-line text-warning me-2"></i>
                                    Customer Satisfaction
                                </h5>
                                <p class="text-muted">Feedback e valutazioni clienti</p>
                            </div>
                            <span class="badge bg-warning">Mensile</span>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-warning btn-sm me-2" onclick="generateReport('customer_satisfaction', 'view')">
                                <i class="ri-eye-line"></i> Visualizza
                            </button>
                            <button class="btn btn-outline-warning btn-sm" onclick="generateReport('customer_satisfaction', 'pdf')">
                                <i class="ri-file-pdf-line"></i> PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- REPORT COMPLIANCE -->
        <div class="row mb-4" data-category="compliance">
            <div class="col-12">
                <div class="card bg-danger text-white">
                    <div class="card-header">
                        <h5 class="card-title text-white mb-0">
                            <i class="ri-shield-check-line me-2"></i>Report Compliance
                        </h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4" data-category="compliance">
            <!-- Tempi di Guida -->
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card h-100 border-danger">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">
                                    <i class="ri-steering-line text-danger me-2"></i>
                                    Tempi di Guida
                                </h5>
                                <p class="text-muted">Controllo ore guida e riposo</p>
                            </div>
                            <span class="badge bg-info">Giornaliero</span>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-danger btn-sm me-2" onclick="generateReport('tempi_guida', 'view')">
                                <i class="ri-eye-line"></i> Visualizza
                            </button>
                            <button class="btn btn-outline-danger btn-sm" onclick="generateReport('tempi_guida', 'pdf')">
                                <i class="ri-file-pdf-line"></i> PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scadenze Patenti -->
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card h-100 border-danger">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">
                                    <i class="ri-id-card-line text-danger me-2"></i>
                                    Scadenze Patenti
                                </h5>
                                <p class="text-muted">Monitoraggio validità documenti</p>
                            </div>
                            <span class="badge bg-warning">Mensile</span>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-danger btn-sm me-2" onclick="generateReport('scadenze_patenti', 'view')">
                                <i class="ri-eye-line"></i> Visualizza
                            </button>
                            <button class="btn btn-outline-danger btn-sm" onclick="generateReport('scadenze_patenti', 'excel')">
                                <i class="ri-file-excel-line"></i> Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ADR e Merci Pericolose -->
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card h-100 border-danger">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">
                                    <i class="ri-alert-line text-danger me-2"></i>
                                    ADR e Merci Pericolose
                                </h5>
                                <p class="text-muted">Tracciamento materiali speciali</p>
                            </div>
                            <span class="badge bg-warning">Settimanale</span>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-danger btn-sm me-2" onclick="generateReport('adr', 'view')">
                                <i class="ri-eye-line"></i> Visualizza
                            </button>
                            <button class="btn btn-outline-danger btn-sm" onclick="generateReport('adr', 'pdf')">
                                <i class="ri-file-pdf-line"></i> PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- REPORT PREDITTIVI -->
        <div class="row mb-4" data-category="predictive">
            <div class="col-12">
                <div class="card bg-dark text-white">
                    <div class="card-header">
                        <h5 class="card-title text-white mb-0">
                            <i class="ri-brain-line me-2"></i>Report Predittivi & Analytics
                        </h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4" data-category="predictive">
            <!-- Previsioni Domanda -->
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card h-100 border-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">
                                    <i class="ri-line-chart-line text-dark me-2"></i>
                                    Previsioni Domanda
                                </h5>
                                <p class="text-muted">Forecast ordini e capacità</p>
                            </div>
                            <span class="badge bg-warning">Mensile</span>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-dark btn-sm me-2" onclick="generateReport('forecast_domanda', 'view')">
                                <i class="ri-eye-line"></i> Visualizza
                            </button>
                            <button class="btn btn-outline-dark btn-sm" onclick="generateReport('forecast_domanda', 'pdf')">
                                <i class="ri-file-pdf-line"></i> PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manutenzioni Predittive -->
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card h-100 border-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">
                                    <i class="ri-settings-4-line text-dark me-2"></i>
                                    Manutenzioni Predittive
                                </h5>
                                <p class="text-muted">Previsione guasti e interventi</p>
                            </div>
                            <span class="badge bg-info">Settimanale</span>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-dark btn-sm me-2" onclick="generateReport('manutenzioni_predittive', 'view')">
                                <i class="ri-eye-line"></i> Visualizza
                            </button>
                            <button class="btn btn-outline-dark btn-sm" onclick="generateReport('manutenzioni_predittive', 'excel')">
                                <i class="ri-file-excel-line"></i> Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ottimizzazione Flotta -->
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card h-100 border-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">
                                    <i class="ri-truck-line text-dark me-2"></i>
                                    Ottimizzazione Flotta
                                </h5>
                                <p class="text-muted">Suggerimenti dimensionamento</p>
                            </div>
                            <span class="badge bg-warning">Mensile</span>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-dark btn-sm me-2" onclick="generateReport('ottimizzazione_flotta', 'view')">
                                <i class="ri-eye-line"></i> Visualizza
                            </button>
                            <button class="btn btn-outline-dark btn-sm" onclick="generateReport('ottimizzazione_flotta', 'pdf')">
                                <i class="ri-file-pdf-line"></i> PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal per visualizzazione report -->
<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportModalTitle">Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="reportModalBody">
                <!-- Contenuto report -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                <button type="button" class="btn btn-primary" onclick="downloadCurrentReport()">
                    <i class="ri-download-line"></i> Scarica
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let currentReportData = null;

    function generateReport(reportType, format = 'view') {
        const dateStart = document.getElementById('globalDateStart').value;
        const dateEnd = document.getElementById('globalDateEnd').value;

        // Mostra loading
        if (format === 'view') {
            showReportModal('Generazione report in corso...', '<div class="text-center"><div class="spinner-border" role="status"></div></div>');
        }

        fetch('/azienda/report-tms/genera', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                tipo: reportType,
                formato: format,
                data_inizio: dateStart,
                data_fine: dateEnd
            })
        })
            .then(response => {
                if (format === 'pdf' || format === 'excel') {
                    return response.blob();
                }
                return response.json();
            })
            .then(data => {
                if (format === 'view') {
                    displayReportData(reportType, data);
                } else if (format === 'pdf' || format === 'excel') {
                    downloadFile(data, `${reportType}_${dateStart}_${dateEnd}.${format}`);
                }
            })
            .catch(error => {
                console.error('Errore:', error);
                if (format === 'view') {
                    showReportModal('Errore', 'Errore nella generazione del report');
                }
            });
    }

    function showReportModal(title, content) {
        document.getElementById('reportModalTitle').textContent = title;
        document.getElementById('reportModalBody').innerHTML = content;
        new bootstrap.Modal(document.getElementById('reportModal')).show();
    }

    function displayReportData(reportType, data) {
        currentReportData = data;
        let html = '';

        switch(reportType) {
            case 'dispatch':
                html = generateDispatchReport(data);
                break;
            case 'utilizzo_mezzi':
                html = generateUtilizzoMezziReport(data);
                break;
            case 'performance_autisti':
                html = generatePerformanceAutistiReport(data);
                break;
            default:
                html = '<p>Tipo di report non implementato</p>';
        }

        showReportModal(`Report ${reportType}`, html);
    }

    function generateDispatchReport(data) {
        return `
        <div class="row">
            <div class="col-12">
                <h6>Stato Ordini</h6>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Totale Ordini</th>
                                <th>Completati</th>
                                <th>In Corso</th>
                                <th>Pianificati</th>
                                <th>Fatturato</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.data.ordini_giornalieri.map(ordine => `
                                <tr>
                                    <td>${ordine.data}</td>
                                    <td>${ordine.totale_ordini}</td>
                                    <td><span class="badge bg-success">${ordine.completati}</span></td>
                                    <td><span class="badge bg-warning">${ordine.in_corso}</span></td>
                                    <td><span class="badge bg-info">${ordine.pianificati}</span></td>
                                    <td>€ ${parseFloat(ordine.fatturato_giorno).toLocaleString()}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
    }

    function generateUtilizzoMezziReport(data) {
        return `
        <div class="row">
            <div class="col-12">
                <h6>Utilizzo Mezzi</h6>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Mezzo</th>
                                <th>Targa</th>
                                <th>Ordini Assegnati</th>
                                <th>Ordini Completati</th>
                                <th>Km Totali</th>
                                <th>Ricavo Medio</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.data.utilizzo_mezzi.map(mezzo => `
                                <tr>
                                    <td>${mezzo.nome}</td>
                                    <td>${mezzo.targa}</td>
                                    <td>${mezzo.ordini_assegnati}</td>
                                    <td>${mezzo.ordini_completati}</td>
                                    <td>${parseFloat(mezzo.km_totali).toLocaleString()} km</td>
                                    <td>€ ${parseFloat(mezzo.ricavo_medio || 0).toFixed(2)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
    }

    function generatePerformanceAutistiReport(data) {
        return `
        <div class="row">
            <div class="col-12">
                <h6>Performance Autisti</h6>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Autista</th>
                                <th>Ordini Completati</th>
                                <th>Fatturato Generato</th>
                                <th>Km Percorsi</th>
                                <th>Rating Medio</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.data.performance_autisti.map(autista => `
                                <tr>
                                    <td>${autista.nome} ${autista.cognome}</td>
                                    <td>${autista.ordini_completati}</td>
                                    <td>€ ${parseFloat(autista.fatturato_generato).toLocaleString()}</td>
                                    <td>${parseFloat(autista.km_percorsi).toLocaleString()} km</td>
                                    <td>
                                        ${autista.rating_medio ?
            `<span class="badge bg-success">${parseFloat(autista.rating_medio).toFixed(1)}/5</span>` :
            '<span class="text-muted">N/A</span>'
        }
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
    }

    function downloadFile(blob, filename) {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }

    function updateAllReports() {
        // Implementa logica per aggiornare tutti i report visibili
        console.log('Aggiornamento report...');
    }

    function scheduleReports() {
        // Implementa logica per pianificare report automatici
        alert('Funzionalità pianificazione report in sviluppo');
    }

    function exportAllReports() {
        // Implementa logica per esportare tutti i report
        alert('Esportazione multipla in sviluppo');
    }

    function downloadCurrentReport() {
        if (currentReportData) {
            const blob = new Blob([JSON.stringify(currentReportData, null, 2)], {type: 'application/json'});
            downloadFile(blob, 'report_data.json');
        }
    }

    // Filtri
    function filterReports() {
        const category = document.getElementById('categoryFilter').value;
        const rows = document.querySelectorAll('[data-category]');

        rows.forEach(row => {
            if (!category || row.dataset.category === category) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>

@include('azienda.common.footer')