@include('azienda.common.header')

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">🚛 Tracking Flotta Live</h4>

                    @if($hasFlottaEnabled)
                        <div class="page-title-right">
                            <button class="btn btn-success" onclick="toggleLiveTracking()" id="liveBtn">
                                <i class="ri-play-line" id="liveIcon"></i> Avvia Live
                            </button>
                            <button class="btn btn-info" onclick="testConnection()">
                                <i class="ri-wifi-line"></i> Test API
                            </button>
                            <button class="btn btn-warning" onclick="refreshData()">
                                <i class="ri-refresh-line"></i> Aggiorna
                            </button>
                        </div>
                    @else
                        <div class="page-title-right">
                            <span class="badge bg-warning">Servizio non attivo</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if(!$hasFlottaEnabled)
            <!-- Messaggio per aziende senza FlottaInCloud -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-warning">
                        <div class="card-body text-center py-5">
                            <div class="avatar-lg mx-auto mb-4">
                                <div class="avatar-title bg-warning-subtle text-warning rounded-circle">
                                    <i class="ri-truck-line font-size-48"></i>
                                </div>
                            </div>
                            <h4 class="text-warning mb-3">FlottaInCloud non attivo</h4>
                            <p class="text-muted mb-4">
                                Il servizio di tracking della flotta non è ancora stato attivato per la tua azienda.<br>
                                Contatta l'amministratore del sistema per maggiori informazioni.
                            </p>
                            <div class="row justify-content-center">
                                <div class="col-md-8">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="text-primary mb-3">🚀 Cosa potrai fare con FlottaInCloud:</h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <ul class="list-unstyled text-muted">
                                                        <li><i class="ri-check-line text-success"></i> Tracking in tempo reale</li>
                                                        <li><i class="ri-check-line text-success"></i> Storico percorsi</li>
                                                        <li><i class="ri-check-line text-success"></i> Controllo velocità</li>
                                                    </ul>
                                                </div>
                                                <div class="col-md-6">
                                                    <ul class="list-unstyled text-muted">
                                                        <li><i class="ri-check-line text-success"></i> Geofencing</li>
                                                        <li><i class="ri-check-line text-success"></i> Report automatici</li>
                                                        <li><i class="ri-check-line text-success"></i> Notifiche real-time</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-primary" onclick="contattaAmministratore()">
                                <i class="ri-mail-line"></i> Richiedi Attivazione
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Contenuto normale per aziende con FlottaInCloud -->

            <!-- Status Panel -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <span class="text-muted mb-3 lh-1 d-block">Dispositivi Totali</span>
                                    <h4 class="mb-3 text-primary" id="dispositiviTotali">{{ $stats['totali'] ?? 0 }}</h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm rounded-circle bg-primary">
                                    <span class="avatar-title rounded-circle bg-primary">
                                        <i class="ri-truck-line font-size-24 text-white"></i>
                                    </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <span class="text-muted mb-3 lh-1 d-block">In Movimento</span>
                                    <h4 class="mb-3 text-success" id="dispositiviInMovimento">{{ $stats['in_movimento'] ?? 0 }}</h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm rounded-circle bg-success">
                                    <span class="avatar-title rounded-circle bg-success">
                                        <i class="ri-speed-line font-size-24 text-white"></i>
                                    </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <span class="text-muted mb-3 lh-1 d-block">Fermi</span>
                                    <h4 class="mb-3 text-warning" id="dispositiviFermi">{{ $stats['fermi'] ?? 0 }}</h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm rounded-circle bg-warning">
                                    <span class="avatar-title rounded-circle bg-warning">
                                        <i class="ri-pause-line font-size-24 text-white"></i>
                                    </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <span class="text-muted mb-3 lh-1 d-block">Connessi</span>
                                    <h4 class="mb-3 text-info" id="dispositiviConnessi">{{ $stats['connessi'] ?? 0 }}</h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm rounded-circle bg-info">
                                    <span class="avatar-title rounded-circle bg-info">
                                        <i class="ri-wifi-line font-size-24 text-white"></i>
                                    </span>
                                    </div>
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
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                🗺️ Mappa Live
                                <span class="badge bg-success ms-2" id="statusMappa">Offline</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="map" style="height: 500px; width: 100%; background: #f0f0f0; border-radius: 8px;"></div>
                        </div>
                    </div>
                </div>

                <!-- Lista Dispositivi -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                📡 Dispositivi GPS
                                <span class="badge bg-primary ms-2">{{ count($posizioni ?? []) }}</span>
                            </h5>
                        </div>
                        <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                            <div id="listaDispositivi">
                                @if(isset($posizioni) && count($posizioni) > 0)
                                    @foreach($posizioni as $dispositivo)
                                        <div class="d-flex align-items-center border-bottom pb-3 mb-3" data-dispositivo="{{ $dispositivo['imei'] ?? $loop->index }}">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="avatar-sm">
                                            <span class="avatar-title rounded-circle status-indicator
                                                {{ ($dispositivo['moving'] ?? false) ? 'bg-success' : 'bg-warning' }}"
                                                  id="status-{{ $dispositivo['imei'] ?? $loop->index }}">
                                                <i class="ri-truck-line font-size-16 text-white"></i>
                                            </span>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">{{ $dispositivo['name'] ?? 'Dispositivo ' . ($loop->index + 1) }}</h6>
                                                <p class="text-muted mb-1 small">{{ $dispositivo['imei'] ?? 'N/A' }}</p>
                                                <div class="row g-1">
                                                    <div class="col-6">
                                                        <small class="text-muted">
                                                            <i class="ri-speed-line"></i>
                                                            {{ round($dispositivo['speed'] ?? 0) }} km/h
                                                        </small>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted">
                                                            <i class="ri-satellite-line"></i>
                                                            {{ $dispositivo['satellites'] ?? 0 }} sat
                                                        </small>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted">
                                                            <i class="ri-compass-line"></i>
                                                            {{ $dispositivo['heading'] ?? 0 }}°
                                                        </small>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="{{ ($dispositivo['is_connected'] ?? false) ? 'text-success' : 'text-danger' }}">
                                                            <i class="ri-wifi-line"></i>
                                                            {{ ($dispositivo['is_connected'] ?? false) ? 'Online' : 'Offline' }}
                                                        </small>
                                                    </div>
                                                </div>
                                                <small class="text-muted">
                                                    Aggiornato: {{ isset($dispositivo['timestamp_position']) ? date('H:i:s', $dispositivo['timestamp_position']/1000) : 'N/A' }}
                                                </small>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <button class="btn btn-outline-primary btn-sm" onclick="centraMappa({{ $dispositivo['lat'] ?? 0 }}, {{ $dispositivo['lng'] ?? 0 }})">
                                                    <i class="ri-focus-line"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-center text-muted py-4">
                                        <i class="ri-car-line font-size-48 mb-3"></i>
                                        <p>Nessun dispositivo disponibile</p>
                                        <button class="btn btn-outline-primary btn-sm" onclick="refreshData()">
                                            <i class="ri-refresh-line"></i> Ricarica
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabella Dettagli -->
            @if(isset($posizioni) && count($posizioni) > 0)
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">📊 Dettagli Dispositivi</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered datatable w-100">
                                        <thead>
                                        <tr>
                                            <th>Nome</th>
                                            <th>IMEI</th>
                                            <th>Stato</th>
                                            <th>Velocità</th>
                                            <th>Posizione</th>
                                            <th>Satelliti</th>
                                            <th>Odometro</th>
                                            <th>Ultimo Aggiornamento</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($posizioni as $dispositivo)
                                            <tr>
                                                <td>
                                                    <strong>{{ $dispositivo['name'] ?? 'N/A' }}</strong>
                                                    @if(isset($dispositivo['numeric_label']))
                                                        <br><small class="text-muted">Etichetta: {{ $dispositivo['numeric_label'] }}</small>
                                                    @endif
                                                </td>
                                                <td><code>{{ $dispositivo['imei'] ?? 'N/A' }}</code></td>
                                                <td>
                                                    <div class="d-flex flex-column gap-1">
                                                <span class="badge {{ ($dispositivo['moving'] ?? false) ? 'bg-success' : 'bg-warning' }}">
                                                    {{ ($dispositivo['moving'] ?? false) ? 'In movimento' : 'Fermo' }}
                                                </span>
                                                        <span class="badge {{ ($dispositivo['is_connected'] ?? false) ? 'bg-info' : 'bg-danger' }}">
                                                    {{ ($dispositivo['is_connected'] ?? false) ? 'Connesso' : 'Disconnesso' }}
                                                </span>
                                                        <span class="badge {{ ($dispositivo['is_power_on'] ?? false) ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ ($dispositivo['is_power_on'] ?? false) ? 'Acceso' : 'Spento' }}
                                                </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <strong>{{ round($dispositivo['speed'] ?? 0) }} km/h</strong>
                                                    <br><small class="text-muted">Direzione: {{ $dispositivo['heading'] ?? 0 }}°</small>
                                                </td>
                                                <td>
                                                    <small>
                                                        Lat: {{ number_format($dispositivo['lat'] ?? 0, 6) }}<br>
                                                        Lng: {{ number_format($dispositivo['lng'] ?? 0, 6) }}<br>
                                                        Alt: {{ $dispositivo['altitude'] ?? 0 }}m
                                                    </small>
                                                </td>
                                                <td>
                                            <span class="badge {{ ($dispositivo['satellites'] ?? 0) >= 4 ? 'bg-success' : 'bg-warning' }}">
                                                {{ $dispositivo['satellites'] ?? 0 }} satelliti
                                            </span>
                                                </td>
                                                <td>
                                                    {{ number_format(($dispositivo['odometer'] ?? 0) / 1000, 2) }} km
                                                </td>
                                                <td>
                                                    @if(isset($dispositivo['timestamp_position']))
                                                        {{ date('d/m/Y H:i:s', $dispositivo['timestamp_position']/1000) }}
                                                    @else
                                                        N/A
                                                    @endif
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
            @endif
        @endif
    </div>
</div>

<!-- Leaflet CSS e JS per la mappa (solo se abilitato) -->
@if($hasFlottaEnabled)
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

    <script>
        let map;
        let markers = [];
        let liveInterval;
        let isLiveActive = false;
        const flottaEnabled = {{ $hasFlottaEnabled ? 'true' : 'false' }};

        // Inizializza la mappa solo se abilitato
        document.addEventListener('DOMContentLoaded', function() {
            if (flottaEnabled) {
                initMap();

                @if(isset($posizioni) && count($posizioni) > 0)
                const dispositivi = @json($posizioni);
                console.log('Dispositivi caricati:', dispositivi);
                caricaDispositivi(dispositivi);
                @endif
            }
        });

        function initMap() {
            map = L.map('map').setView([41.9028, 12.4964], 6);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            document.getElementById('statusMappa').textContent = 'Online';
            document.getElementById('statusMappa').className = 'badge bg-success ms-2';
        }

        function caricaDispositivi(dispositivi) {
            if (!flottaEnabled || !map) return;

            // Pulisci i marker esistenti
            markers.forEach(marker => map.removeLayer(marker));
            markers = [];

            if (!dispositivi || dispositivi.length === 0) return;

            dispositivi.forEach(function(dispositivo, index) {
                if (dispositivo.lat && dispositivo.lng) {
                    const icon = L.divIcon({
                        html: `<div style="background: ${dispositivo.moving ? '#28a745' : '#ffc107'};
                              width: 20px; height: 20px; border-radius: 50%;
                              border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);
                              display: flex; align-items: center; justify-content: center;">
                         <i class="ri-truck-line" style="color: white; font-size: 10px;"></i>
                       </div>`,
                        className: 'custom-div-icon',
                        iconSize: [20, 20],
                        iconAnchor: [10, 10]
                    });

                    const marker = L.marker([dispositivo.lat, dispositivo.lng], {icon: icon}).addTo(map);

                    const popupContent = `
                <div style="min-width: 200px;">
                    <h6><strong>${dispositivo.name || 'Dispositivo ' + (index + 1)}</strong></h6>
                    <p><strong>IMEI:</strong> ${dispositivo.imei || 'N/A'}</p>
                    <p><strong>Velocità:</strong> ${Math.round(dispositivo.speed || 0)} km/h</p>
                    <p><strong>Stato:</strong> ${dispositivo.moving ? '🟢 In movimento' : '🟡 Fermo'}</p>
                    <p><strong>Connessione:</strong> ${dispositivo.is_connected ? '🟢 Online' : '🔴 Offline'}</p>
                    <p><strong>Satelliti:</strong> ${dispositivo.satellites || 0}</p>
                    <p><strong>Ultimo aggiornamento:</strong><br>
                       ${dispositivo.timestamp_position ? new Date(dispositivo.timestamp_position).toLocaleString('it-IT') : 'N/A'}</p>
                </div>
            `;

                    marker.bindPopup(popupContent);
                    markers.push(marker);
                }
            });

            // Centra la mappa su tutti i marker
            if (markers.length > 0) {
                const group = new L.featureGroup(markers);
                map.fitBounds(group.getBounds().pad(0.1));
            }
        }

        function centraMappa(lat, lng) {
            if (flottaEnabled && map && lat && lng) {
                map.setView([lat, lng], 15);
            }
        }

        function toggleLiveTracking() {
            if (!flottaEnabled) {
                alert('FlottaInCloud non è attivo per la tua azienda');
                return;
            }

            const btn = document.getElementById('liveBtn');
            const icon = document.getElementById('liveIcon');

            if (isLiveActive) {
                clearInterval(liveInterval);
                isLiveActive = false;
                btn.innerHTML = '<i class="ri-play-line" id="liveIcon"></i> Avvia Live';
                btn.className = 'btn btn-success';
            } else {
                liveInterval = setInterval(refreshData, 30000);
                isLiveActive = true;
                btn.innerHTML = '<i class="ri-pause-line" id="liveIcon"></i> Ferma Live';
                btn.className = 'btn btn-danger';
            }
        }

        function refreshData() {
            if (!flottaEnabled) {
                alert('FlottaInCloud non è attivo per la tua azienda');
                return;
            }

            console.log('Aggiornamento dati...');

            fetch('/azienda/flotta/live-positions')
                .then(response => response.json())
                .then(data => {
                    if (data.enabled === false) {
                        alert('FlottaInCloud non è attivo per la tua azienda');
                        return;
                    }

                    if (data && Array.isArray(data)) {
                        caricaDispositivi(data);
                        aggiornaStatistiche(data);
                        aggiornaTabellaDispositivi(data);
                    }
                })
                .catch(error => {
                    console.error('Errore aggiornamento:', error);
                });
        }

        function testConnection() {
            if (!flottaEnabled) {
                alert('FlottaInCloud non è attivo per la tua azienda');
                return;
            }

            const btn = event.target;
            btn.disabled = true;
            btn.innerHTML = '<i class="ri-loader-line"></i> Testing...';

            fetch('/azienda/flotta/test-connection')
                .then(response => response.json())
                .then(data => {
                    if (data.enabled === false) {
                        alert('❌ FlottaInCloud non è attivo per la tua azienda');
                        return;
                    }

                    if (data.success) {
                        let message = `✅ CONNESSIONE RIUSCITA!\n\n`;
                        message += `📡 Dispositivi trovati: ${data.devices_count}\n`;
                        message += `📊 Dati presenti: ${data.has_data ? 'SÌ' : 'NO'}\n`;
                        message += `⏰ Timestamp: ${new Date(data.timestamp).toLocaleString('it-IT')}\n`;

                        if (data.sample_data) {
                            message += `\n🚛 Esempio dispositivo:\n`;
                            message += `   Nome: ${data.sample_data.name || 'N/A'}\n`;
                            message += `   IMEI: ${data.sample_data.imei || 'N/A'}\n`;
                            message += `   Stato: ${data.sample_data.moving ? 'In movimento' : 'Fermo'}\n`;
                            message += `   Velocità: ${data.sample_data.speed || 0} km/h\n`;
                            message += `   Connesso: ${data.sample_data.is_connected ? 'Sì' : 'No'}\n`;
                        }

                        alert(message);

                        if (data.has_data) {
                            refreshData();
                        }
                    } else {
                        alert(`❌ CONNESSIONE FALLITA!\n\nErrore: ${data.error}`);
                    }
                })
                .catch(error => {
                    alert(`❌ ERRORE DI RETE!\n\n${error.message}`);
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ri-wifi-line"></i> Test API';
                });
        }

        function aggiornaStatistiche(dispositivi) {
            const stats = {
                totali: dispositivi.length,
                in_movimento: dispositivi.filter(d => d.moving).length,
                fermi: dispositivi.filter(d => !d.moving).length,
                connessi: dispositivi.filter(d => d.is_connected).length
            };

            document.getElementById('dispositiviTotali').textContent = stats.totali;
            document.getElementById('dispositiviInMovimento').textContent = stats.in_movimento;
            document.getElementById('dispositiviFermi').textContent = stats.fermi;
            document.getElementById('dispositiviConnessi').textContent = stats.connessi;
        }

        function aggiornaTabellaDispositivi(dispositivi) {
            const container = document.getElementById('listaDispositivi');
            if (!container) return;

            let html = '';

            dispositivi.forEach((dispositivo, index) => {
                html += `
            <div class="d-flex align-items-center border-bottom pb-3 mb-3">
                <div class="flex-shrink-0 me-3">
                    <div class="avatar-sm">
                        <span class="avatar-title rounded-circle ${dispositivo.moving ? 'bg-success' : 'bg-warning'}">
                            <i class="ri-truck-line font-size-16 text-white"></i>
                        </span>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1">${dispositivo.name || 'Dispositivo ' + (index + 1)}</h6>
                    <p class="text-muted mb-1 small">${dispositivo.imei || 'N/A'}</p>
                    <div class="row g-1">
                        <div class="col-6">
                            <small class="text-muted">
                                <i class="ri-speed-line"></i> ${Math.round(dispositivo.speed || 0)} km/h
                            </small>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">
                                <i class="ri-satellite-line"></i> ${dispositivo.satellites || 0} sat
                            </small>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">
                                <i class="ri-compass-line"></i> ${dispositivo.heading || 0}°
                            </small>
                        </div>
                        <div class="col-6">
                            <small class="${dispositivo.is_connected ? 'text-success' : 'text-danger'}">
                                <i class="ri-wifi-line"></i> ${dispositivo.is_connected ? 'Online' : 'Offline'}
                            </small>
                        </div>
                    </div>
                    <small class="text-muted">
                        Aggiornato: ${dispositivo.timestamp_position ? new Date(dispositivo.timestamp_position).toLocaleTimeString('it-IT') : 'N/A'}
                    </small>
                </div>
                <div class="flex-shrink-0">
                    <button class="btn btn-outline-primary btn-sm" onclick="centraMappa(${dispositivo.lat || 0}, ${dispositivo.lng || 0})">
                        <i class="ri-focus-line"></i>
                    </button>
                </div>
            </div>
        `;
            });

            container.innerHTML = html;
        }
    </script>
@else
    <script>
        // Funzioni vuote per aziende senza FlottaInCloud
        function contattaAmministratore() {
            alert('Contatta l\'amministratore del sistema per richiedere l\'attivazione di FlottaInCloud');
        }
    </script>
@endif

@include('azienda.common.footer')