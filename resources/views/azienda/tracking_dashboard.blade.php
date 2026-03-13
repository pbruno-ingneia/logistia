@include('azienda.common.header')

<div class="page-content">
    <div class="container-fluid">

        <!-- Header -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">🚛 Tracking Flotta Live</h4>
                    <div class="page-title-right">
                        <button class="btn btn-success" onclick="toggleLiveTracking()" id="liveBtn">
                            <i class="ri-play-line" id="liveIcon"></i> Avvia Live
                        </button>
                        <button class="btn btn-info" onclick="refreshData()">
                            <i class="ri-refresh-line"></i> Aggiorna
                        </button>
                        <a href="/azienda/tracking/report-km" class="btn btn-primary">
                            <i class="ri-bar-chart-line"></i> Report Km
                        </a>
                        <a href="/azienda/tracking/dispositivi" class="btn btn-secondary">
                            <i class="ri-settings-3-line"></i> Dispositivi
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiche -->
        <div class="row mb-4">
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <span class="text-muted mb-1 d-block">Mezzi Totali</span>
                                <h4 class="mb-0 text-primary" id="statTotali">{{ $stats['totali'] }}</h4>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-primary-subtle rounded">
                                    <i class="ri-truck-line text-primary fs-4"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <span class="text-muted mb-1 d-block">In Movimento</span>
                                <h4 class="mb-0 text-success" id="statMovimento">{{ $stats['in_movimento'] }}</h4>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-success-subtle rounded">
                                    <i class="ri-steering-line text-success fs-4"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <span class="text-muted mb-1 d-block">Fermi</span>
                                <h4 class="mb-0 text-warning" id="statFermi">{{ $stats['fermi'] }}</h4>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-warning-subtle rounded">
                                    <i class="ri-parking-line text-warning fs-4"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <span class="text-muted mb-1 d-block">Offline</span>
                                <h4 class="mb-0 text-danger" id="statOffline">{{ $stats['offline'] }}</h4>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-danger-subtle rounded">
                                    <i class="ri-wifi-off-line text-danger fs-4"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-8">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <span class="mb-1 d-block opacity-75">Km Percorsi Oggi (flotta)</span>
                                <h3 class="mb-0" id="statKmOggi">{{ number_format($stats['km_oggi'], 1) }} km</h3>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-white rounded">
                                    <i class="ri-road-map-line text-primary fs-4"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Mappa -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            🗺️ Mappa Live
                            <span class="badge bg-secondary ms-2" id="statusMappa">Pronto</span>
                        </h5>
                        <small class="text-muted" id="lastUpdate">Ultimo aggiornamento: --</small>
                    </div>
                    <div class="card-body p-0">
                        <div id="map" style="height: 550px; width: 100%;"></div>
                    </div>
                </div>
            </div>

            <!-- Lista Mezzi -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            📋 Mezzi
                            <span class="badge bg-primary ms-2">{{ count($mezzi) }}</span>
                        </h5>
                    </div>
                    <div class="card-body p-0" style="max-height: 550px; overflow-y: auto;">
                        <div class="list-group list-group-flush" id="listaMezzi">
                            @forelse($mezzi as $mezzo)
                                <div class="list-group-item list-group-item-action mezzo-item"
                                     data-id="{{ $mezzo->id }}"
                                     onclick="centraMezzo({{ $mezzo->lat ?? 'null' }}, {{ $mezzo->lng ?? 'null' }}, {{ $mezzo->id }})"
                                     style="cursor: pointer;">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            @if($mezzo->lat)
                                                @if($mezzo->is_moving)
                                                    <span class="avatar-title bg-success rounded-circle" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                                                        <i class="ri-truck-line text-white"></i>
                                                    </span>
                                                @else
                                                    <span class="avatar-title bg-warning rounded-circle" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                                                        <i class="ri-parking-box-line text-white"></i>
                                                    </span>
                                                @endif
                                            @else
                                                <span class="avatar-title bg-secondary rounded-circle" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                                                    <i class="ri-wifi-off-line text-white"></i>
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $mezzo->nome }}</h6>
                                            <p class="mb-0 text-muted small">
                                                <strong>{{ $mezzo->targa }}</strong>
                                            </p>
                                        </div>
                                        <div class="flex-shrink-0 text-end">
                                            @if($mezzo->lat)
                                                <span class="badge {{ $mezzo->is_moving ? 'bg-success' : 'bg-warning' }}">
                                                    {{ $mezzo->speed ? round($mezzo->speed) : 0 }} km/h
                                                </span>
                                                <br>
                                                <small class="text-muted">
                                                    {{ round($kmOggiPerMezzo[$mezzo->id] ?? 0, 1) }} km oggi
                                                </small>
                                            @else
                                                <span class="badge bg-secondary">Offline</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="list-group-item text-center text-muted py-5">
                                    <i class="ri-truck-line fs-1 mb-2 d-block"></i>
                                    Nessun mezzo con tracking attivo
                                    <br>
                                    <a href="/azienda/tracking/dispositivi" class="btn btn-sm btn-primary mt-3">
                                        Configura Dispositivi
                                    </a>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Leaflet CSS e JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    let map;
    let markers = {};
    let liveInterval;
    let isLiveActive = false;

    const mezziIniziali = @json($mezzi);

    document.addEventListener('DOMContentLoaded', function() {
        initMap();
        caricaMezzi(mezziIniziali);
    });

    function initMap() {
        map = L.map('map').setView([41.9028, 12.4964], 6);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);
    }

    function caricaMezzi(mezzi) {
        Object.values(markers).forEach(m => map.removeLayer(m));
        markers = {};

        const bounds = [];

        mezzi.forEach(mezzo => {
            if (mezzo.lat && mezzo.lng) {
                const isMoving = mezzo.is_moving;
                const color = isMoving ? '#28a745' : '#ffc107';

                const icon = L.divIcon({
                    html: `
                    <div style="
                        background: ${color};
                        width: 36px;
                        height: 36px;
                        border-radius: 50%;
                        border: 3px solid white;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    ">
                        <i class="ri-truck-line" style="color: white; font-size: 16px;"></i>
                    </div>
                `,
                    className: 'custom-marker',
                    iconSize: [36, 36],
                    iconAnchor: [18, 18]
                });

                const marker = L.marker([mezzo.lat, mezzo.lng], { icon: icon }).addTo(map);

                marker.bindPopup(`
                <div style="min-width: 200px;">
                    <h6 class="mb-2"><strong>${mezzo.nome}</strong></h6>
                    <p class="mb-1"><strong>Targa:</strong> ${mezzo.targa}</p>
                    <p class="mb-1"><strong>Velocità:</strong> ${Math.round(mezzo.speed || 0)} km/h</p>
                    <p class="mb-1"><strong>Stato:</strong> ${isMoving ? '🟢 In movimento' : '🟡 Fermo'}</p>
                    <p class="mb-0"><strong>Km totali:</strong> ${Number(mezzo.km_attuali || 0).toLocaleString('it-IT')}</p>
                </div>
            `);

                markers[mezzo.id] = marker;
                bounds.push([mezzo.lat, mezzo.lng]);
            }
        });

        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [30, 30] });
        }

        document.getElementById('lastUpdate').textContent =
            'Ultimo aggiornamento: ' + new Date().toLocaleTimeString('it-IT');
    }

    function centraMezzo(lat, lng, id) {
        if (lat && lng) {
            map.setView([lat, lng], 15);
            if (markers[id]) {
                markers[id].openPopup();
            }
        }
    }

    function toggleLiveTracking() {
        const btn = document.getElementById('liveBtn');

        if (isLiveActive) {
            clearInterval(liveInterval);
            isLiveActive = false;
            btn.innerHTML = '<i class="ri-play-line"></i> Avvia Live';
            btn.className = 'btn btn-success';
            document.getElementById('statusMappa').textContent = 'Pausa';
            document.getElementById('statusMappa').className = 'badge bg-secondary ms-2';
        } else {
            liveInterval = setInterval(refreshData, 10000);
            isLiveActive = true;
            btn.innerHTML = '<i class="ri-pause-line"></i> Stop Live';
            btn.className = 'btn btn-danger';
            document.getElementById('statusMappa').textContent = 'Live';
            document.getElementById('statusMappa').className = 'badge bg-success ms-2';
            refreshData();
        }
    }

    function refreshData() {
        fetch('/azienda/tracking/live-positions')
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    aggiornaMappa(data.posizioni);
                    aggiornaStats(data.posizioni);
                    aggiornaListaMezzi(data.posizioni);  // <-- AGGIUNGI QUESTA RIGA
                }
            })
            .catch(err => console.error('Errore refresh:', err));
    }

    // AGGIUNGI QUESTA FUNZIONE
    function aggiornaListaMezzi(posizioni) {
        posizioni.forEach(pos => {
            const item = document.querySelector(`.mezzo-item[data-id="${pos.id}"]`);
            if (!item) return;

            // Aggiorna icona stato
            const iconDiv = item.querySelector('.flex-shrink-0.me-3 span');
            if (pos.lat) {
                if (pos.is_moving) {
                    iconDiv.className = 'avatar-title bg-success rounded-circle';
                    iconDiv.style = 'width:40px;height:40px;display:flex;align-items:center;justify-content:center;';
                    iconDiv.innerHTML = '<i class="ri-truck-line text-white"></i>';
                } else {
                    iconDiv.className = 'avatar-title bg-warning rounded-circle';
                    iconDiv.style = 'width:40px;height:40px;display:flex;align-items:center;justify-content:center;';
                    iconDiv.innerHTML = '<i class="ri-parking-box-line text-white"></i>';
                }
            }

            // Aggiorna velocità e km
            const badgeDiv = item.querySelector('.flex-shrink-0.text-end');
            if (badgeDiv && pos.lat) {
                const speed = Math.round(pos.speed || 0);
                const kmOggi = parseFloat(pos.km_oggi || 0).toFixed(1);
                const badgeClass = pos.is_moving ? 'bg-success' : 'bg-warning';

                badgeDiv.innerHTML = `
                <span class="badge ${badgeClass}">
                    ${speed} km/h
                </span>
                <br>
                <small class="text-muted">
                    ${kmOggi} km oggi
                </small>
            `;
            }
        });
    }

    function aggiornaMappa(posizioni) {
        posizioni.forEach(pos => {
            if (pos.lat && pos.lng && markers[pos.id]) {
                markers[pos.id].setLatLng([pos.lat, pos.lng]);

                const color = pos.is_moving ? '#28a745' : '#ffc107';
                markers[pos.id].setIcon(L.divIcon({
                    html: `
                    <div style="
                        background: ${color};
                        width: 36px; height: 36px;
                        border-radius: 50%;
                        border: 3px solid white;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                        display: flex; align-items: center; justify-content: center;
                    ">
                        <i class="ri-truck-line" style="color: white; font-size: 16px;"></i>
                    </div>
                `,
                    className: 'custom-marker',
                    iconSize: [36, 36],
                    iconAnchor: [18, 18]
                }));
            }
        });

        document.getElementById('lastUpdate').textContent =
            'Ultimo aggiornamento: ' + new Date().toLocaleTimeString('it-IT');
    }

    function aggiornaStats(posizioni) {
        const inMovimento = posizioni.filter(p => p.is_moving).length;
        const fermi = posizioni.filter(p => p.lat && !p.is_moving).length;
        const offline = posizioni.filter(p => !p.online).length;
        const kmOggi = posizioni.reduce((sum, p) => sum + (parseFloat(p.km_oggi) || 0), 0);

        document.getElementById('statMovimento').textContent = inMovimento;
        document.getElementById('statFermi').textContent = fermi;
        document.getElementById('statOffline').textContent = offline;
        document.getElementById('statKmOggi').textContent = kmOggi.toFixed(1) + ' km';
    }
</script>

@include('azienda.common.footer')
