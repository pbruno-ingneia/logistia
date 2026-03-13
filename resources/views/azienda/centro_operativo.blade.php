@include('azienda.common.header')

<style>
    .driver-card {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        transition: all 0.3s ease;
        overflow: hidden;
    }
    .driver-card:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    .driver-card.in-corso {
        border-color: #ffc107;
        border-left: 5px solid #ffc107;
    }
    .driver-card.completato {
        border-color: #198754;
        border-left: 5px solid #198754;
    }
    .driver-card.offline {
        opacity: 0.6;
    }

    .status-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
    }
    .status-dot.online {
        background: #198754;
        box-shadow: 0 0 8px rgba(25, 135, 84, 0.6);
        animation: pulse-green 2s infinite;
    }
    .status-dot.offline {
        background: #dc3545;
    }
    .status-dot.idle {
        background: #6c757d;
    }

    @keyframes pulse-green {
        0% { box-shadow: 0 0 4px rgba(25, 135, 84, 0.4); }
        50% { box-shadow: 0 0 12px rgba(25, 135, 84, 0.8); }
        100% { box-shadow: 0 0 4px rgba(25, 135, 84, 0.4); }
    }

    .progress-consegne {
        height: 8px;
        border-radius: 4px;
        overflow: hidden;
        background: #e9ecef;
    }
    .progress-consegne .bar {
        height: 100%;
        border-radius: 4px;
        transition: width 0.5s ease;
    }

    .mini-order {
        padding: 6px 10px;
        border-radius: 6px;
        margin-bottom: 4px;
        font-size: 0.8rem;
        border-left: 3px solid transparent;
    }
    .mini-order.completato { background: #d1e7dd; border-left-color: #198754; }
    .mini-order.in_corso { background: #fff3cd; border-left-color: #ffc107; }
    .mini-order.assegnato { background: #cff4fc; border-left-color: #0dcaf0; }
    .mini-order.pianificato { background: #f8f9fa; border-left-color: #6c757d; }
    .mini-order.annullato { background: #f8d7da; border-left-color: #dc3545; text-decoration: line-through; }

    .stat-card-big {
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        color: white;
    }
    .stat-card-big h2 {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 0;
    }

    .live-badge {
        animation: pulse-red 1.5s infinite;
    }
    @keyframes pulse-red {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }

    #mappaOperativa {
        height: 400px;
        border-radius: 12px;
        border: 2px solid #e9ecef;
    }

    .avatar-autista {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #e9ecef;
    }
    .avatar-placeholder {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.1rem;
        color: white;
    }

    .next-delivery {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 8px;
        padding: 8px 12px;
        margin-top: 8px;
    }
</style>

<div class="page-content">
    <div class="container-fluid">

        <!-- Header -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-1">🎯 Centro Operativo Autisti</h4>
                        <p class="text-muted mb-0">
                            Monitoraggio in tempo reale — {{ date('d/m/Y') }}
                            <span class="badge bg-danger live-badge ms-2" id="liveBadge">● LIVE</span>
                            <small class="text-muted ms-2" id="lastRefresh">Aggiornato ora</small>
                        </p>
                    </div>
                    <div>
                        <button class="btn btn-success me-2" onclick="refreshDati()" id="btnRefresh">
                            <i class="ri-refresh-line"></i> Aggiorna
                        </button>
                        <button class="btn btn-primary" onclick="toggleAutoRefresh()" id="btnAutoRefresh">
                            <i class="ri-play-line"></i> Auto-Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiche Globali -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="stat-card-big" style="background: linear-gradient(135deg, #4361ee, #3a0ca3);">
                    <h2 id="stat_attivi">{{ $stats->autisti_attivi }}</h2>
                    <small>Autisti Attivi</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card-big" style="background: linear-gradient(135deg, #06d6a0, #118ab2);">
                    <h2 id="stat_online">{{ $stats->autisti_online }}</h2>
                    <small>Online Ora</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card-big" style="background: linear-gradient(135deg, #f72585, #b5179e);">
                    <h2 id="stat_totale">{{ $stats->totale_consegne }}</h2>
                    <small>Consegne Oggi</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card-big" style="background: linear-gradient(135deg, #198754, #20c997);">
                    <h2 id="stat_completate">{{ $stats->completate }}</h2>
                    <small>Completate</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card-big" style="background: linear-gradient(135deg, #ffc107, #fd7e14);">
                    <h2 id="stat_in_corso">{{ $stats->in_corso }}</h2>
                    <small>In Corso</small>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stat-card-big" style="background: linear-gradient(135deg, #6c757d, #495057);">
                    <h2 id="stat_rimanenti">{{ $stats->rimanenti }}</h2>
                    <small>Da Fare</small>
                </div>
            </div>
        </div>

        <!-- Progresso Globale -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-medium">Progresso Giornata</span>
                            <span class="fw-bold fs-5" id="stat_percentuale">{{ $stats->percentuale_globale }}%</span>
                        </div>
                        <div class="progress" style="height: 12px; border-radius: 6px;">
                            <div class="progress-bar bg-success" id="progressBar" role="progressbar"
                                 style="width: {{ $stats->percentuale_globale }}%; border-radius: 6px;"
                                 aria-valuenow="{{ $stats->percentuale_globale }}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">
                                <span class="text-success">✅ {{ $stats->completate }}</span> completate su {{ $stats->totale_consegne }}
                            </small>
                            @if($stats->annullate > 0)
                                <small class="text-danger">❌ {{ $stats->annullate }} annullate</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mappa Operativa -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="ri-map-2-line me-2"></i>Mappa Flotta</h5>
                        <div>
                            <button class="btn btn-sm btn-outline-primary" onclick="centraMappaAutisti()">
                                <i class="ri-focus-3-line"></i> Centra Tutti
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="mappaOperativa"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Autisti CON ordini oggi -->
        @if($autistiConOrdini->count() > 0)
            <div class="row mb-3">
                <div class="col-12">
                    <h5 class="text-muted"><i class="ri-truck-line me-1"></i> Autisti in Servizio Oggi ({{ $autistiConOrdini->count() }})</h5>
                </div>
            </div>

            <div class="row" id="containerAutisti">
                @foreach($autistiConOrdini as $autista)
                    <div class="col-xl-4 col-lg-6 mb-4" id="card-autista-{{ $autista->id }}">
                        <div class="driver-card card h-100 {{ $autista->in_corso > 0 ? 'in-corso' : ($autista->percentuale == 100 ? 'completato' : '') }} {{ !$autista->is_online ? 'offline' : '' }}">
                            <div class="card-body">
                                <!-- Header autista -->
                                <div class="d-flex align-items-center mb-3">

                                    <div class="avatar-placeholder me-2" style="width: 35px; height: 35px; font-size: 0.8rem; background: {{ '#' . substr(md5($autista->id), 0, 6) }};">
                                        {{ strtoupper(substr($autista->nome, 0, 1) . substr($autista->cognome, 0, 1)) }}
                                    </div>

                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">
                                            {{ $autista->nome }} {{ $autista->cognome }}
                                            <span class="status-dot {{ $autista->is_online ? 'online' : 'offline' }} ms-1"></span>
                                        </h6>
                                        <small class="text-muted">
                                            @if($autista->targa)
                                                🚛 {{ $autista->targa }}
                                                @if($autista->nome_mezzo) — {{ $autista->nome_mezzo }} @endif
                                            @else
                                                <span class="text-warning">Nessun mezzo</span>
                                            @endif
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        @if($autista->is_online && $autista->lat)
                                            <button class="btn btn-sm btn-outline-primary" onclick="centraAutista({{ $autista->lat }}, {{ $autista->lng }}, {{ $autista->id }})" title="Trova sulla mappa">
                                                <i class="ri-map-pin-line"></i>
                                            </button>
                                        @endif
                                        @if($autista->telefono)
                                            <a href="tel:{{ $autista->telefono }}" class="btn btn-sm btn-outline-success" title="Chiama">
                                                <i class="ri-phone-line"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                <!-- Stats rapide -->
                                <div class="row text-center mb-3">
                                    <div class="col-3">
                                        <div class="fw-bold fs-5 text-primary">{{ $autista->totale_oggi }}</div>
                                        <small class="text-muted">Totale</small>
                                    </div>
                                    <div class="col-3">
                                        <div class="fw-bold fs-5 text-success">{{ $autista->completati }}</div>
                                        <small class="text-muted">Fatte</small>
                                    </div>
                                    <div class="col-3">
                                        <div class="fw-bold fs-5 text-warning">{{ $autista->in_corso }}</div>
                                        <small class="text-muted">In Corso</small>
                                    </div>
                                    <div class="col-3">
                                        <div class="fw-bold fs-5 text-secondary">{{ $autista->rimanenti }}</div>
                                        <small class="text-muted">Rimaste</small>
                                    </div>
                                </div>

                                <!-- Barra progresso -->
                                <div class="progress-consegne mb-2">
                                    <div class="bar bg-success" style="width: {{ $autista->percentuale }}%;"></div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">Completamento</small>
                                    <small class="fw-bold">{{ $autista->percentuale }}%</small>
                                </div>

                                <!-- Posizione attuale -->
                                @if($autista->is_online)
                                    <div class="mt-2">
                                        <small>
                                            @if($autista->is_moving)
                                                <span class="text-success">🟢 In movimento</span>
                                                — {{ round($autista->speed) }} km/h
                                            @else
                                                <span class="text-warning">🟡 Fermo</span>
                                            @endif
                                            <span class="text-muted ms-1">
                                                — agg. {{ \Carbon\Carbon::parse($autista->ultimo_aggiornamento)->diffForHumans() }}
                                            </span>
                                        </small>
                                    </div>
                                @else
                                    <div class="mt-2">
                                        <small class="text-danger">🔴 Offline
                                            @if($autista->ultimo_aggiornamento)
                                                — ultimo segnale {{ \Carbon\Carbon::parse($autista->ultimo_aggiornamento)->diffForHumans() }}
                                            @endif
                                        </small>
                                    </div>
                                @endif

                                <!-- Prossima consegna -->
                                @if($autista->prossima_consegna)
                                    <div class="next-delivery">
                                        <small class="text-muted d-block">📍 Prossima consegna:</small>
                                        <strong>{{ $autista->prossima_consegna->cliente_nome ?? 'Cliente' }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ Str::limit($autista->prossima_consegna->indirizzo_consegna, 40) }}
                                            @if($autista->prossima_consegna->ora_ritiro)
                                                — ore {{ date('H:i', strtotime($autista->prossima_consegna->ora_ritiro)) }}
                                            @endif
                                        </small>
                                    </div>
                                @elseif($autista->percentuale == 100)
                                    <div class="alert alert-success py-1 px-2 mt-2 mb-0">
                                        <small>✅ Tutte le consegne completate!</small>
                                    </div>
                                @endif

                                <!-- Lista ordini (collapsible) -->
                                <div class="mt-3">
                                    <a class="text-decoration-none small" data-bs-toggle="collapse" href="#ordini-{{ $autista->id }}">
                                        <i class="ri-list-unordered"></i> Vedi dettaglio ordini ({{ $autista->totale_oggi }})
                                    </a>
                                    <div class="collapse mt-2" id="ordini-{{ $autista->id }}">
                                        @foreach($autista->ordini as $ordine)
                                            <div class="mini-order {{ $ordine->stato }}">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong>{{ $ordine->numero_ordine }}</strong>
                                                        — {{ $ordine->cliente_nome ?? 'N/A' }}
                                                    </div>
                                                    <span class="badge
                                                        @if($ordine->stato == 'completato') bg-success
                                                        @elseif($ordine->stato == 'in_corso') bg-warning
                                                        @elseif($ordine->stato == 'assegnato') bg-info
                                                        @elseif($ordine->stato == 'annullato') bg-danger
                                                        @else bg-secondary @endif"
                                                          style="font-size: 0.7rem;">
                                                        {{ ucfirst(str_replace('_', ' ', $ordine->stato)) }}
                                                    </span>
                                                </div>
                                                <small class="text-muted">
                                                    📍 {{ Str::limit($ordine->indirizzo_consegna, 35) }}
                                                    @if($ordine->ora_ritiro) — {{ date('H:i', strtotime($ordine->ora_ritiro)) }} @endif
                                                </small>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="ri-calendar-line text-muted" style="font-size: 48px;"></i>
                            <h5 class="text-muted mt-3">Nessun ordine assegnato per oggi</h5>
                            <p class="text-muted">Crea nuovi ordini e assegnali agli autisti dalla pagina Ordini di Trasporto.</p>
                            <a href="/azienda/ordini-trasporto" class="btn btn-primary">
                                <i class="ri-add-line"></i> Vai agli Ordini
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Autisti SENZA ordini oggi -->
        @if($autistiSenzaOrdini->count() > 0)
            <div class="row mb-3">
                <div class="col-12">
                    <a class="text-decoration-none text-muted" data-bs-toggle="collapse" href="#autistiLiberi">
                        <h6><i class="ri-user-line me-1"></i> Autisti senza consegne oggi ({{ $autistiSenzaOrdini->count() }}) ▾</h6>
                    </a>
                </div>
            </div>
            <div class="collapse" id="autistiLiberi">
                <div class="row">
                    @foreach($autistiSenzaOrdini as $autista)
                        <div class="col-xl-3 col-md-4 col-sm-6 mb-3">
                            <div class="card border">
                                <div class="card-body py-2 px-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-placeholder me-2" style="width: 35px; height: 35px; font-size: 0.8rem; background: {{ '#' . substr(md5($autista->id), 0, 6) }};">
                                            {{ strtoupper(substr($autista->nome, 0, 1) . substr($autista->cognome, 0, 1)) }}
                                        </div>
                                        <div>
                                            <strong class="small">{{ $autista->nome }} {{ $autista->cognome }}</strong><br>
                                            <small class="text-muted">
                                                @if($autista->is_online)
                                                    <span class="status-dot online" style="width:8px;height:8px;"></span> Online
                                                @else
                                                    <span class="status-dot offline" style="width:8px;height:8px;"></span> Offline
                                                @endif
                                                — Nessun ordine
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</div>



<!-- Leaflet CSS/JS per la mappa -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // ==========================================
    // MAPPA OPERATIVA
    // ==========================================
    let map;
    let markers = {};
    let autoRefreshInterval = null;
    let isAutoRefresh = false;

    // Dati iniziali autisti dal server
    const autistiIniziali = @json($autistiConOrdini->values());

    document.addEventListener('DOMContentLoaded', function() {
        initMappa();
        caricaMarkerIniziali();
    });

    function initMappa() {
        map = L.map('mappaOperativa').setView([41.9028, 12.4964], 6); // Centro Italia

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);
    }

    function creaIconaMarker(autista) {
        const color = autista.is_moving ? '#198754' : (autista.is_online ? '#ffc107' : '#dc3545');
        const iniziali = (autista.nome?.charAt(0) || '') + (autista.cognome?.charAt(0) || '');

        return L.divIcon({
            className: 'custom-marker',
            html: `<div style="
                background: ${color};
                width: 36px; height: 36px;
                border-radius: 50%;
                border: 3px solid white;
                box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                display: flex; align-items: center; justify-content: center;
                color: white; font-weight: 700; font-size: 12px;
            ">${iniziali}</div>`,
            iconSize: [36, 36],
            iconAnchor: [18, 18]
        });
    }

    function creaPopupContent(autista) {
        const completati = autista.completati || 0;
        const totale = autista.totale_oggi || 0;
        const speed = autista.speed || 0;
        const targa = autista.targa || 'N/A';
        const nome = autista.nome + ' ' + (autista.cognome || '');

        let prossimaHtml = '';
        if (autista.prossima_consegna) {
            prossimaHtml = '📍 Prossima: ' + (autista.prossima_consegna.cliente_nome || 'Cliente');
        } else if (autista.percentuale == 100) {
            prossimaHtml = '✅ Tutte completate!';
        }

        return `
            <div style="min-width: 200px;">
                <strong>${nome}</strong><br>
                <small>🚛 ${targa}</small><br>
                <hr style="margin: 5px 0;">
                <small>
                    ✅ ${completati}/${totale} consegne<br>
                    ${autista.is_moving ? '🟢 In movimento — ' + Math.round(speed) + ' km/h' : '🟡 Fermo'}<br>
                    ${prossimaHtml}
                </small>
            </div>
        `;
    }

    function caricaMarkerIniziali() {
        const bounds = [];

        autistiIniziali.forEach(autista => {
            if (autista.lat && autista.lng) {
                const icon = creaIconaMarker(autista);
                const marker = L.marker([autista.lat, autista.lng], { icon })
                    .addTo(map)
                    .bindPopup(creaPopupContent(autista));

                markers[autista.id] = marker;
                bounds.push([autista.lat, autista.lng]);
            }
        });

        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [30, 30], maxZoom: 13 });
        }
    }

    function centraAutista(lat, lng, id) {
        if (map && lat && lng) {
            map.setView([lat, lng], 15);
            if (markers[id]) markers[id].openPopup();
        }
    }

    function centraMappaAutisti() {
        const bounds = [];
        Object.values(markers).forEach(m => {
            bounds.push(m.getLatLng());
        });
        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [30, 30], maxZoom: 13 });
        }
    }

    // ==========================================
    // AUTO-REFRESH
    // ==========================================
    function toggleAutoRefresh() {
        const btn = document.getElementById('btnAutoRefresh');
        if (isAutoRefresh) {
            clearInterval(autoRefreshInterval);
            isAutoRefresh = false;
            btn.innerHTML = '<i class="ri-play-line"></i> Auto-Refresh';
            btn.className = 'btn btn-primary';
            document.getElementById('liveBadge').style.display = 'none';
        } else {
            autoRefreshInterval = setInterval(refreshDati, 30000); // Ogni 30 sec
            isAutoRefresh = true;
            btn.innerHTML = '<i class="ri-pause-line"></i> Stop';
            btn.className = 'btn btn-danger';
            document.getElementById('liveBadge').style.display = 'inline';
            refreshDati();
        }
    }

    function refreshDati() {
        const btn = document.getElementById('btnRefresh');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>';
        btn.disabled = true;

        fetch('/azienda/centro-operativo/live')
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    aggiornaStats(data.stats);
                    aggiornaMarker(data.autisti);
                    aggiornaCard(data.autisti);
                    document.getElementById('lastRefresh').textContent = 'Aggiornato alle ' + data.timestamp;
                }
            })
            .catch(err => console.error('Errore refresh:', err))
            .finally(() => {
                btn.innerHTML = '<i class="ri-refresh-line"></i> Aggiorna';
                btn.disabled = false;
            });
    }

    function aggiornaStats(stats) {
        document.getElementById('stat_attivi').textContent = stats.attivi;
        document.getElementById('stat_online').textContent = stats.online;
        document.getElementById('stat_totale').textContent = stats.totale;
        document.getElementById('stat_completate').textContent = stats.completate;
        document.getElementById('stat_in_corso').textContent = stats.in_corso;
        document.getElementById('stat_rimanenti').textContent = stats.rimanenti;
        document.getElementById('stat_percentuale').textContent = stats.percentuale + '%';
        document.getElementById('progressBar').style.width = stats.percentuale + '%';
    }

    function aggiornaMarker(autisti) {
        autisti.forEach(a => {
            if (a.lat && a.lng) {
                if (markers[a.id]) {
                    // Aggiorna posizione
                    markers[a.id].setLatLng([a.lat, a.lng]);
                    // Aggiorna icona (colore in base allo stato)
                    markers[a.id].setIcon(creaIconaMarker(a));
                    // Aggiorna popup
                    markers[a.id].setPopupContent(creaPopupContent(a));
                } else {
                    // Crea nuovo marker se non esiste
                    const icon = creaIconaMarker(a);
                    const marker = L.marker([a.lat, a.lng], { icon })
                        .addTo(map)
                        .bindPopup(creaPopupContent(a));
                    markers[a.id] = marker;
                }
            }
        });
    }

    function aggiornaCard(autisti) {
        autisti.forEach(a => {
            const card = document.getElementById('card-autista-' + a.id);
            if (!card) return;

            // Aggiorna numeri nelle stat (col-3 con fw-bold fs-5)
            const stats = card.querySelectorAll('.row.text-center .fw-bold.fs-5');
            if (stats.length >= 4) {
                stats[0].textContent = a.totale_oggi;
                stats[1].textContent = a.completati;
                stats[2].textContent = a.in_corso;
                stats[3].textContent = a.rimanenti;
            }

            // Aggiorna barra progresso
            const bar = card.querySelector('.progress-consegne .bar');
            if (bar) bar.style.width = a.percentuale + '%';

            // Aggiorna percentuale testo
            const percTexts = card.querySelectorAll('.d-flex.justify-content-between small.fw-bold, .d-flex.justify-content-between .fw-bold');
            percTexts.forEach(el => {
                if (el.textContent.includes('%')) {
                    el.textContent = a.percentuale + '%';
                }
            });

            // Aggiorna classe card (bordo colorato)
            card.querySelector('.driver-card').classList.remove('in-corso', 'completato', 'offline');
            if (a.in_corso > 0) {
                card.querySelector('.driver-card').classList.add('in-corso');
            } else if (a.percentuale == 100) {
                card.querySelector('.driver-card').classList.add('completato');
            }
            if (!a.is_online) {
                card.querySelector('.driver-card').classList.add('offline');
            }

            // Aggiorna status dot
            const statusDot = card.querySelector('.status-dot');
            if (statusDot) {
                statusDot.classList.remove('online', 'offline');
                statusDot.classList.add(a.is_online ? 'online' : 'offline');
            }
        });
    }
</script>

@include('azienda.common.footer')