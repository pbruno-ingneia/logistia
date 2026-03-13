@extends('autista.common.layout')

@section('title', 'Tracking GPS')

@section('styles')
    <style>
        .tracking-map {
            height: 300px;
            border-radius: var(--radius-lg);
            overflow: hidden;
            margin-bottom: 20px;
            background: #e9ecef;
        }

        .tracking-controls {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .tracking-controls .btn {
            flex: 1;
            padding: 15px;
            font-size: 1.1rem;
        }

        .speed-display {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-color) 100%);
            color: white;
            border-radius: var(--radius-lg);
            padding: 25px;
            text-align: center;
            margin-bottom: 20px;
        }

        .speed-value {
            font-size: 4rem;
            font-weight: 700;
            line-height: 1;
        }

        .speed-unit {
            font-size: 1.2rem;
            opacity: 0.8;
        }

        .location-info {
            background: white;
            border-radius: var(--radius-lg);
            padding: 20px;
            box-shadow: var(--shadow-sm);
        }

        .location-info .row-info {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }

        .location-info .row-info:last-child {
            border-bottom: none;
        }

        .location-info .label {
            color: var(--text-muted);
        }

        .location-info .value {
            font-weight: 600;
        }

        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }

        .status-indicator.active {
            background: var(--success-color);
            animation: pulse 1.5s infinite;
        }

        .status-indicator.inactive {
            background: var(--text-muted);
        }

        #map {
            width: 100%;
            height: 100%;
        }
    </style>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endsection

@section('content')
    <div class="fade-in">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">
                <i class="ri-gps-line text-primary me-2"></i>
                Tracking GPS
            </h4>
            <span class="badge-status" id="trackingBadge">
            <span class="status-indicator inactive" id="statusIndicator"></span>
            <span id="statusText">Non attivo</span>
        </span>
        </div>

        <!-- Mappa -->
        <div class="tracking-map" id="mapContainer">
            <div id="map"></div>
        </div>

        <!-- Velocità -->
        <div class="speed-display">
            <div class="speed-value" id="currentSpeed">0</div>
            <div class="speed-unit">km/h</div>
        </div>

        <!-- Controlli -->
        <div class="tracking-controls">
            <button class="btn btn-success-custom" id="btnStart" onclick="startTracking()">
                <i class="ri-play-circle-line me-2"></i>
                Avvia
            </button>
            <button class="btn btn-outline-custom" id="btnStop" onclick="stopTracking()" disabled>
                <i class="ri-stop-circle-line me-2"></i>
                Ferma
            </button>
        </div>

        <!-- Info posizione -->
        <div class="location-info">
            <div class="row-info">
                <span class="label"><i class="ri-map-pin-line me-2"></i>Latitudine</span>
                <span class="value" id="latitude">--</span>
            </div>
            <div class="row-info">
                <span class="label"><i class="ri-map-pin-line me-2"></i>Longitudine</span>
                <span class="value" id="longitude">--</span>
            </div>
            <div class="row-info">
                <span class="label"><i class="ri-compass-3-line me-2"></i>Precisione</span>
                <span class="value" id="accuracy">--</span>
            </div>
            <div class="row-info">
                <span class="label"><i class="ri-route-line me-2"></i>Km oggi</span>
                <span class="value text-primary" id="kmToday">{{ number_format($kmOggi ?? 0, 2) }} km</span>
            </div>
            <div class="row-info">
                <span class="label"><i class="ri-time-line me-2"></i>Ultimo aggiornamento</span>
                <span class="value" id="lastUpdate">--</span>
            </div>
        </div>

        <!-- Info dispositivo -->
        @if(isset($dispositivo) && $dispositivo)
            <div class="card-custom mt-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="ri-information-line text-primary me-3" style="font-size: 1.5rem;"></i>
                        <div>
                            <small class="text-muted">Dispositivo</small>
                            <div class="fw-500">{{ $dispositivo->nome ?? $dispositivo->nome_mezzo ?? 'Mezzo' }} - {{ $dispositivo->targa ?? $dispositivo->targa_mezzo ?? 'N/D' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let map = null;
        let marker = null;

        // Inizializza mappa
        function initMap() {
            map = L.map('map').setView([41.9028, 12.4964], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);

            const truckIcon = L.divIcon({
                html: '<i class="ri-truck-line" style="font-size: 30px; color: #3498db;"></i>',
                className: 'truck-marker',
                iconSize: [30, 30],
                iconAnchor: [15, 15]
            });

            marker = L.marker([41.9028, 12.4964], { icon: truckIcon }).addTo(map);
        }

        // Aggiorna UI - chiamata dal trackingManager nel layout
        window.updateTrackingUI = function(position) {
            const { latitude, longitude, accuracy, speed } = position.coords;

            document.getElementById('latitude').textContent = latitude.toFixed(6);
            document.getElementById('longitude').textContent = longitude.toFixed(6);
            document.getElementById('accuracy').textContent = (accuracy || 0).toFixed(0) + ' m';
            document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString('it-IT');

            let speedKmh = (speed || 0) * 3.6;
            document.getElementById('currentSpeed').textContent = Math.round(speedKmh);

            if (map && marker) {
                map.setView([latitude, longitude], 16);
                marker.setLatLng([latitude, longitude]);
            }
        };

        // Avvia tracking
        function startTracking() {
            if (window.trackingManager) {
                window.trackingManager.start();
                updateButtons(true);
            }
        }

        // Ferma tracking
        function stopTracking() {
            if (window.trackingManager) {
                window.trackingManager.stop();
                updateButtons(false);
            }
        }

        // Aggiorna pulsanti e badge in pagina
        function updateButtons(isActive) {
            document.getElementById('btnStart').disabled = isActive;
            document.getElementById('btnStop').disabled = !isActive;

            const indicator = document.getElementById('statusIndicator');
            const statusText = document.getElementById('statusText');
            const badge = document.getElementById('trackingBadge');

            if (isActive) {
                indicator.classList.remove('inactive');
                indicator.classList.add('active');
                statusText.textContent = 'Attivo';
                badge.classList.add('active');
            } else {
                indicator.classList.remove('active');
                indicator.classList.add('inactive');
                statusText.textContent = 'Non attivo';
                badge.classList.remove('active');
            }
        }

        // Inizializza
        document.addEventListener('DOMContentLoaded', () => {
            initMap();

            // Controlla se tracking già attivo
            setTimeout(() => {
                if (window.trackingManager && window.trackingManager.isActive()) {
                    updateButtons(true);
                    const lastPos = window.trackingManager.getLastPosition();
                    if (lastPos) {
                        updateTrackingUI(lastPos);
                    }
                }
            }, 100);

            // Posizione iniziale per mappa
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => updateTrackingUI(position),
                    (error) => console.warn('Errore posizione iniziale:', error)
                );
            }
        });
    </script>
@endsection