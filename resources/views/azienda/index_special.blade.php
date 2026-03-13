@include('azienda.common.header')

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0 text-white">Dashboard</h4>

                            <!-- ✅ Tab per switchare tra Planning e Calendario -->
                            <ul class="nav nav-pills nav-custom" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link {{ !request()->has('view') || request('view') != 'calendario' ? 'active' : '' }}"
                                       href="{{ url('/azienda/index') }}">
                                        <i class="ri-calendar-schedule-line"></i> Planning Dipendenti
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request('view') == 'calendario' ? 'active' : '' }}"
                                       href="{{ url('/azienda/index?view=calendario') }}">
                                        <i class="ri-calendar-2-line"></i> Calendario
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">

                        @if(request('view') == 'calendario')
                            <!-- ✅ MODALITÀ CALENDARIO -->
                            <div class="row">
                                <!-- 📅 Colonna principale per il calendario -->
                                <div class="col-lg-9">
                                    <div class="card card-h-100">
                                        <div class="card-body">
                                            <div id="calendar"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 📋 Colonna laterale con i cantieri -->
                                <div class="col-lg-3">
                                    <div class="card">
                                        <div class="card-header bg-primary text-light">
                                            <h5 class="mb-0 text-light" style="color: #f8f9fa !important;">Cantieri Attivi</h5>
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-group" id="listaCantieri">
                                                @foreach($eventi->unique('id_cantiere') as $cantiere)
                                                    <li class="list-group-item list-group-item-action cantiere-click"
                                                        data-start="{{ $cantiere['start'] }}"
                                                        data-id="{{ $cantiere['id_cantiere'] }}"
                                                        style="background-color: {{ $cantiere['color'] ?? '#f8f9fa' }};
                                color: #fff; font-weight: bold; cursor: pointer; transition: 0.3s; opacity: 0.75;">

                                                        <strong>{{ $cantiere['title'] }}</strong><br>
                                                        <small>Inizio: {{ date('d/m/Y', strtotime($cantiere['start'])) }} - Fine: {{ date('d/m/Y', strtotime($cantiere['end'])) }}</small>

                                                        <!-- Dipendenti assegnati al cantiere -->
                                                        <div class="dipendenti-container mt-2 p-2 text-dark bg-light rounded d-none">
                                                            @if(isset($dipendentiCantieri[$cantiere['id_cantiere']]) && count($dipendentiCantieri[$cantiere['id_cantiere']]) > 0)
                                                                @foreach($dipendentiCantieri[$cantiere['id_cantiere']] as $dip)
                                                                    <div class="d-flex justify-content-between align-items-center border-bottom py-1">
                                                                        <span>{{ $dip->nome }} {{ $dip->cognome }}</span>
                                                                    </div>
                                                                @endforeach
                                                            @else
                                                                <p class="text-muted">Nessun dipendente assegnato</p>
                                                            @endif
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- ✅ MODALITÀ PLANNING DIPENDENTI -->
                            <div class="mb-4">
                                <form method="GET" class="row g-3">
                                    <input type="hidden" name="view" value="planning">
                                    <div class="col-md-4">
                                        <label class="form-label">Data inizio</label>
                                        <input type="date" name="data_inizio" class="form-control" value="{{ request('data_inizio', date('Y-m-d', strtotime('monday this week'))) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Data fine</label>
                                        <input type="date" name="data_fine" class="form-control" value="{{ request('data_fine', date('Y-m-d', strtotime('friday this week'))) }}">
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary">Visualizza</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Tabella Planning Cantieri -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-planning">
                                    <thead>
                                    <tr>
                                        @foreach($giorni as $giorno)
                                            <th class="text-center planning-header">
                                                <div class="day-name">{{ strtoupper($giorno['nome']) }}</div>
                                                <div class="day-number">{{ $giorno['numero'] }}</div>
                                            </th>
                                        @endforeach
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($cantieriPerGiorno as $cantiereId => $cantiereDati)
                                        <tr class="cantiere-row">
                                            @foreach($giorni as $giorno)
                                                <td class="planning-cell" style="background-color: {{ $cantiereDati['colore'] }};">
                                                    @if(isset($cantiereDati['giorni'][$giorno['data']]))
                                                        <div class="cantiere-box">
                                                            <!-- Intestazione cantiere -->
                                                            <div class="cantiere-header">
                                                                <div class="cliente-line">CL: {{ $cantiereDati['cliente'] }}</div>
                                                                <div class="pos-line">POS: {{ $cantiereDati['posizione'] }}</div>
                                                                <div class="lav-line">LAV: {{ $cantiereDati['lavorazione'] }}</div>
                                                                <div class="resp-line">RESP: {{ $cantiereDati['responsabile'] }}</div>
                                                            </div>

                                                            <!-- Lista dipendenti -->
                                                            <div class="dipendenti-section">
                                                                @foreach($cantiereDati['giorni'][$giorno['data']]['dipendenti'] as $dipendente)
                                                                    <div class="dipendente-name">{{ $dipendente }}</div>
                                                                @endforeach
                                                            </div>

                                                            <!-- Stato se presente -->
                                                            @if(isset($cantiereDati['giorni'][$giorno['data']]['stato']) && $cantiereDati['giorni'][$giorno['data']]['stato'])
                                                                <div class="stato-badge">
                                                                    {{ $cantiereDati['giorni'][$giorno['data']]['stato'] }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach

                                    <!-- Righe speciali se presenti -->
                                    @if(isset($specialRows))
                                        @foreach($specialRows as $rowId => $rowData)
                                            <tr class="cantiere-row special-row">
                                                @foreach($giorni as $giorno)
                                                    <td class="planning-cell" style="background-color: {{ $rowData['colore'] }};">
                                                        @if(isset($rowData['giorni'][$giorno['data']]))
                                                            <div class="cantiere-box">
                                                                <div class="cantiere-header">
                                                                    @foreach($rowData['giorni'][$giorno['data']]['titoli'] as $titolo)
                                                                        <div class="special-title">{{ $titolo }}</div>
                                                                    @endforeach
                                                                </div>

                                                                @if(isset($rowData['giorni'][$giorno['data']]['dipendenti']))
                                                                    <div class="dipendenti-section">
                                                                        @foreach($rowData['giorni'][$giorno['data']]['dipendenti'] as $dipendente)
                                                                            <div class="dipendente-name">{{ $dipendente }}</div>
                                                                        @endforeach
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    @endif
                                    </tbody>
                                </table>
                            </div>

                            <!-- ✅ Sezione Dipendenti Liberi -->
                            <div class="mt-5">
                                <h5 class="mb-3 text-primary">Dipendenti Liberi per Giorno</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-dipendenti-liberi">
                                        <thead>
                                        <tr>
                                            @foreach($giorni as $giorno)
                                                <th class="text-center dipendenti-liberi-header">
                                                    <div class="day-name">{{ strtoupper($giorno['nome']) }}</div>
                                                    <div class="day-number">{{ $giorno['numero'] }}</div>
                                                </th>
                                            @endforeach
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            @foreach($giorni as $giorno)
                                                <td class="dipendenti-liberi-cell">
                                                    @if(isset($dipendentiLiberi[$giorno['data']]) && count($dipendentiLiberi[$giorno['data']]) > 0)
                                                        <div class="dipendenti-liberi-box">
                                                            <div class="liberi-count">
                                                                <strong>{{ count($dipendentiLiberi[$giorno['data']]) }} Liberi</strong>
                                                            </div>
                                                            <div class="liberi-list">
                                                                @foreach($dipendentiLiberi[$giorno['data']] as $dipendente)
                                                                    <div class="dipendente-libero">{{ $dipendente }}</div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="dipendenti-liberi-box">
                                                            <div class="no-liberi">
                                                                <em>Tutti impegnati</em>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(request('view') == 'calendario')
    <!-- Script del calendario solo se necessario -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'it',
                initialView: 'dayGridMonth',
                firstDay: 1,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                buttonText: {
                    today: 'Oggi',
                    month: 'Mese',
                    week: 'Settimana',
                    day: 'Giorno'
                },
                events: {!! json_encode($eventi) !!},

                eventClick: function(info) {
                    if (info.event.url) {
                        window.location.href = info.event.url;
                    }
                }
            });

            calendar.render();

            // Gestione clic sui cantieri
            document.querySelectorAll('.cantiere-click').forEach(item => {
                item.addEventListener('click', function() {
                    let startDate = this.getAttribute('data-start');
                    let dipendentiContainer = this.querySelector('.dipendenti-container');

                    calendar.changeView('timeGridWeek', new Date(startDate));

                    document.querySelectorAll('.dipendenti-container').forEach(el => {
                        if (el !== dipendentiContainer) {
                            el.classList.add('d-none');
                        }
                    });

                    dipendentiContainer.classList.toggle('d-none');
                });
            });
        });
    </script>
@endif

<style>
    /* Stili per le tab personalizzate */
    .nav-custom .nav-link {
        background-color: rgba(255,255,255,0.1);
        color: #fff;
        border: 1px solid rgba(255,255,255,0.2);
        margin-right: 5px;
        border-radius: 5px;
    }

    .nav-custom .nav-link:hover {
        background-color: rgba(255,255,255,0.2);
        color: #fff;
    }

    .nav-custom .nav-link.active {
        background-color: #fff;
        color: #495057;
        border-color: #fff;
    }

    /* Stili per il planning (copiati dalla view precedente) */
    .table-planning {
        border-collapse: collapse;
        width: 100%;
        font-size: 11px;
        font-family: Arial, sans-serif;
    }

    .table-planning th,
    .table-planning td {
        border: 2px solid #000;
        vertical-align: top;
        padding: 0;
    }

    .planning-header {
        background-color: #f8f9fa;
        font-weight: bold;
        text-align: center;
        padding: 8px 4px;
        min-width: 180px;
    }

    .day-name {
        font-size: 12px;
        font-weight: bold;
        margin-bottom: 2px;
    }

    .day-number {
        font-size: 14px;
        font-weight: bold;
    }

    .planning-cell {
        width: 180px;
        min-height: 120px;
        padding: 0;
    }

    .cantiere-box {
        padding: 4px;
        height: 100%;
        min-height: 120px;
    }

    .cantiere-header {
        margin-bottom: 6px;
        border-bottom: 1px solid rgba(0,0,0,0.2);
        padding-bottom: 4px;
    }

    .cliente-line,
    .pos-line,
    .lav-line,
    .resp-line {
        font-size: 10px;
        font-weight: bold;
        line-height: 1.2;
        margin-bottom: 1px;
        word-wrap: break-word;
    }

    .dipendenti-section {
        margin-top: 4px;
    }

    .dipendente-name {
        font-size: 10px;
        font-weight: bold;
        line-height: 1.3;
        margin-bottom: 1px;
        word-wrap: break-word;
    }

    .stato-badge {
        position: absolute;
        top: 2px;
        right: 2px;
        background-color: #ff9800;
        color: white;
        font-size: 8px;
        font-weight: bold;
        padding: 1px 3px;
        border-radius: 2px;
    }

    /* Stili per dipendenti liberi */
    .table-dipendenti-liberi {
        border-collapse: collapse;
        width: 100%;
        font-size: 11px;
        font-family: Arial, sans-serif;
        margin-top: 20px;
    }

    .table-dipendenti-liberi th,
    .table-dipendenti-liberi td {
        border: 2px solid #28a745;
        vertical-align: top;
        padding: 0;
    }

    .dipendenti-liberi-header {
        background-color: #d4edda;
        font-weight: bold;
        text-align: center;
        padding: 8px 4px;
        min-width: 180px;
        color: #155724;
    }

    .dipendenti-liberi-cell {
        width: 180px;
        min-height: 80px;
        padding: 0;
        background-color: #f8fff9;
    }

    .dipendenti-liberi-box {
        padding: 8px;
        height: 100%;
        min-height: 80px;
    }

    .liberi-count {
        font-size: 11px;
        font-weight: bold;
        color: #28a745;
        margin-bottom: 6px;
        text-align: center;
        border-bottom: 1px solid #28a745;
        padding-bottom: 4px;
    }

    .dipendente-libero {
        font-size: 10px;
        font-weight: bold;
        line-height: 1.3;
        margin-bottom: 2px;
        color: #155724;
        word-wrap: break-word;
    }

    .no-liberi {
        text-align: center;
        color: #6c757d;
        font-style: italic;
        margin-top: 20px;
    }
</style>

@include('azienda.common.footer')