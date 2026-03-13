@include('azienda.common.header')

<div class="page-content">
    <div class="container-fluid">
        <!-- Statistiche TMS in alto -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-gradient bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="card-title text-white mb-0">
                                <i class="ri-truck-line me-2"></i>Dashboard TMS - Trasporti
                            </h4>
                            <div class="btn-group">
                                <a href="/azienda/ordini-trasporto" class="btn btn-light btn-sm">
                                    <i class="ri-add-line"></i> Nuovo Ordine
                                </a>
                                <a href="/azienda/clienti" class="btn btn-outline-light btn-sm">
                                    <i class="ri-user-2-line"></i> Clienti
                                </a>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Ordini Totali -->
                            <div class="col-xl-3 col-md-6">
                                <div class="mini-stat-item text-center">
                                    <div class="mini-stat-icon mx-auto mb-2">
                                        <i class="ri-file-list-3-line font-size-24"></i>
                                    </div>
                                    <h5 class="font-size-16 text-white">{{ $statsTMS['ordini_totali'] }}</h5>
                                    <p class="text-white-50 mb-0">Ordini Totali</p>
                                </div>
                            </div>

                            <!-- Ordini Oggi -->
                            <div class="col-xl-3 col-md-6">
                                <div class="mini-stat-item text-center">
                                    <div class="mini-stat-icon mx-auto mb-2">
                                        <i class="ri-calendar-check-line font-size-24"></i>
                                    </div>
                                    <h5 class="font-size-16 text-white">{{ $statsTMS['ordini_oggi'] }}</h5>
                                    <p class="text-white-50 mb-0">Ordini Oggi</p>
                                </div>
                            </div>

                            <!-- In Corso -->
                            <div class="col-xl-3 col-md-6">
                                <div class="mini-stat-item text-center">
                                    <div class="mini-stat-icon mx-auto mb-2">
                                        <i class="ri-truck-line font-size-24"></i>
                                    </div>
                                    <h5 class="font-size-16 text-white">{{ $statsTMS['ordini_in_corso'] }}</h5>
                                    <p class="text-white-50 mb-0">In Corso</p>
                                </div>
                            </div>

                            <!-- Fatturato Mese -->
                            <div class="col-xl-3 col-md-6">
                                <div class="mini-stat-item text-center">
                                    <div class="mini-stat-icon mx-auto mb-2">
                                        <i class="ri-money-euro-circle-line font-size-24"></i>
                                    </div>
                                    <h5 class="font-size-16 text-white">€ {{ number_format($statsTMS['fatturato_mese'], 0, ',', '.') }}</h5>
                                    <p class="text-white-50 mb-0">Fatturato Mese</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- 📅 Colonna principale per il calendario ordini -->
            <div class="col-lg-9">
                <div class="card card-h-100">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title text-white mb-0">
                            <i class="ri-calendar-2-line me-2"></i>Calendario Ordini di Trasporto
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>

            <!-- 🚛 Colonna laterale con mezzi e ordini -->
            <div class="col-lg-3">
                <!-- Card Info Mezzi -->
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <h6 class="card-title text-white mb-0">
                            <i class="ri-car-line me-2"></i>Mezzi Disponibili
                        </h6>
                    </div>
                    <div class="card-body text-center">
                        <h3 class="text-success">{{ $statsTMS['mezzi_disponibili'] }}</h3>
                        <p class="text-muted mb-2">mezzi attivi</p>
                        <a href="/azienda/mezzi" class="btn btn-outline-success btn-sm">
                            <i class="ri-settings-line"></i> Gestisci
                        </a>
                    </div>
                </div>

                <!-- Card Info Clienti -->
                <div class="card mb-3">
                    <div class="card-header bg-warning text-white">
                        <h6 class="card-title text-white mb-0">
                            <i class="ri-user-2-line me-2"></i>Clienti
                        </h6>
                    </div>
                    <div class="card-body text-center">
                        <h3 class="text-warning">{{ $statsTMS['clienti_attivi'] }}</h3>
                        <p class="text-muted mb-2">clienti registrati</p>
                        <a href="/azienda/clienti" class="btn btn-outline-warning btn-sm">
                            <i class="ri-user-add-line"></i> Gestisci
                        </a>
                    </div>
                </div>

                <!-- Ordini per Mezzo -->
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="card-title text-white mb-0">
                            <i class="ri-truck-line me-2"></i>Ordini per Mezzo
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($ordiniPerMezzo && count($ordiniPerMezzo) > 0)
                            @foreach($ordiniPerMezzo as $mezzo)
                                <div class="border-bottom mb-3 pb-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong class="text-primary">{{ $mezzo['targa'] }}</strong><br>
                                            <small class="text-muted">{{ $mezzo['mezzo_nome'] }}</small>
                                        </div>
                                        <span class="badge bg-info">{{ count($mezzo['ordini']) }}</span>
                                    </div>

                                    <!-- Lista ordini per questo mezzo -->
                                    <div class="mt-2">
                                        @foreach($mezzo['ordini']->take(3) as $ordine)
                                            <div class="d-flex justify-content-between align-items-center py-1">
                                                <small>
                                                    <strong>{{ $ordine['numero_ordine'] }}</strong><br>
                                                    {{ date('d/m', strtotime($ordine['data_ritiro'])) }} - {{ Str::limit($ordine['cliente'], 15) }}
                                                </small>
                                                <span class="badge badge-sm
                                                    @if($ordine['stato'] == 'pianificato') bg-secondary
                                                    @elseif($ordine['stato'] == 'assegnato') bg-info
                                                    @elseif($ordine['stato'] == 'in_corso') bg-warning
                                                    @elseif($ordine['stato'] == 'completato') bg-success
                                                    @else bg-danger @endif">
                                                    @if($ordine['stato'] == 'pianificato') P
                                                    @elseif($ordine['stato'] == 'assegnato') A
                                                    @elseif($ordine['stato'] == 'in_corso') C
                                                    @elseif($ordine['stato'] == 'completato') ✓
                                                    @else ✗ @endif
                                                </span>
                                            </div>
                                        @endforeach
                                        @if(count($mezzo['ordini']) > 3)
                                            <small class="text-muted">e altri {{ count($mezzo['ordini']) - 3 }}...</small>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="ri-truck-line font-size-24 mb-2"></i>
                                <p class="mb-0">Nessun ordine assegnato ai mezzi</p>
                                <a href="/azienda/ordini-trasporto" class="btn btn-sm btn-outline-primary mt-2">
                                    Crea Primo Ordine
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            buttonText: {
                today: 'Oggi',
                month: 'Mese',
                week: 'Settimana',
                list: 'Lista'
            },
            events: {!! json_encode($eventi) !!},

            eventClick: function(info) {
                if (info.event.url && !{{ isset($utente->vista_operaio) && $utente->vista_operaio == 1 ? 'true' : 'false' }}) {
                    window.location.href = info.event.url;
                } else {
                    // Mostra dettagli ordine in un popup per gli autisti
                    const props = info.event.extendedProps;
                    alert(`📦 Ordine: ${info.event.title}\n` +
                        `📍 Ritiro: ${props.ritiro}\n` +
                        `📍 Consegna: ${props.consegna}\n` +
                        `🚛 Mezzo: ${props.mezzo || 'Non assegnato'}\n` +
                        `💰 Importo: €${props.importo || '0'}`);
                }
            },

            eventDidMount: function(info) {
                // Tooltip con info aggiuntive
                info.el.title = `Cliente: ${info.event.extendedProps.cliente}\n` +
                    `Stato: ${info.event.extendedProps.stato}\n` +
                    `Mezzo: ${info.event.extendedProps.mezzo || 'Non assegnato'}`;
            }
        });

        calendar.render();
    });
</script>

<style>
    .mini-stat-item {
        padding: 10px;
    }

    .mini-stat-icon {
        width: 50px;
        height: 50px;
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .font-size-24 {
        font-size: 24px !important;
    }

    .font-size-16 {
        font-size: 16px !important;
    }

    .badge-sm {
        font-size: 0.65em;
    }

    /* Stili per gli eventi del calendario */
    .fc-event {
        border: none !important;
        font-weight: bold;
    }

    .fc-event-title {
        font-size: 12px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .mini-stat-item {
            margin-bottom: 15px;
        }
    }
</style>

@include('azienda.common.footer')