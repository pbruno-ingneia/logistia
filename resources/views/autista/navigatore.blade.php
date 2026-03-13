@extends('autista.common.layout')

@section('title', 'Navigatore')

@section('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body {
            padding-bottom: 0 !important;
        }

        .main-content {
            padding: 0 !important;
            max-width: 100% !important;
        }

        .bottom-nav {
            display: none;
        }

        .nav-container {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            flex-direction: column;
            z-index: 1000;
        }

        /* Header navigatore */
        .nav-header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-color) 100%);
            color: white;
            padding: 12px 15px;
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 1001;
        }

        .nav-header .back-btn {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .nav-header .title {
            flex: 1;
        }

        .nav-header .title h6 {
            margin: 0;
            font-weight: 600;
            font-size: 1rem;
        }

        .nav-header .title small {
            opacity: 0.8;
            font-size: 0.75rem;
        }

        /* Mappa */
        #map {
            flex: 1;
            width: 100%;
            z-index: 1;
        }

        /* Istruzione corrente (sopra la mappa) */
        .current-instruction-bar {
            position: absolute;
            top: 60px;
            left: 10px;
            right: 10px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            z-index: 1000;
            display: none;
            overflow: hidden;
        }

        .current-instruction-bar.active {
            display: block;
        }

        .current-instruction-bar .instruction-content {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            gap: 12px;
        }

        .current-instruction-bar .direction-icon {
            width: 50px;
            height: 50px;
            background: var(--primary-color);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .current-instruction-bar .instruction-text {
            flex: 1;
            min-width: 0;
        }

        .current-instruction-bar .instruction-main {
            font-weight: 600;
            font-size: 0.95rem;
            color: #333;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .current-instruction-bar .instruction-distance {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .current-instruction-bar .next-instruction {
            background: #f5f5f5;
            padding: 8px 15px;
            font-size: 0.8rem;
            color: #666;
            border-top: 1px solid #eee;
        }

        /* Pulsante azione */
        .action-buttons {
            padding: 15px;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
        }

        .action-buttons .btn-action {
            width: 100%;
            padding: 16px 20px;
            border-radius: 12px;
            border: none;
            font-weight: 600;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
        }

        .action-buttons .btn-google {
            background: linear-gradient(135deg, #27ae60 0%, #1e8449 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
        }

        /* Info panel in basso */
        .nav-info-panel {
            background: white;
            border-radius: 20px 20px 0 0;
            box-shadow: 0 -5px 20px rgba(0,0,0,0.15);
            z-index: 1002;
            max-height: 55vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: max-height 0.3s ease;
        }

        .nav-info-panel.collapsed {
            max-height: 45px;
        }

        .nav-info-panel .panel-toggle {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 8px 0;
            cursor: pointer;
            background: white;
            border-bottom: 1px solid #f0f0f0;
        }

        .nav-info-panel .panel-toggle .handle {
            width: 40px;
            height: 5px;
            background: #ddd;
            border-radius: 3px;
            margin-bottom: 4px;
        }

        .nav-info-panel .panel-toggle .toggle-icon {
            font-size: 1.2rem;
            color: #999;
            transition: transform 0.3s ease;
        }

        .nav-info-panel.collapsed .panel-toggle .toggle-icon {
            transform: rotate(180deg);
        }

        .nav-info-panel.collapsed .action-buttons,
        .nav-info-panel.collapsed .trip-stats,
        .nav-info-panel.collapsed .stops-toggle,
        .nav-info-panel.collapsed .stops-list {
            display: none;
        }

        /* Stats viaggio */
        .trip-stats {
            display: flex;
            justify-content: space-around;
            padding: 10px 15px 15px;
            border-bottom: 1px solid #eee;
        }

        .trip-stats .stat {
            text-align: center;
        }

        .trip-stats .stat .value {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .trip-stats .stat .label {
            font-size: 0.7rem;
            color: var(--text-muted);
        }

        /* Lista istruzioni */
        .instructions-list {
            flex: 1;
            overflow-y: auto;
            padding: 0 15px 15px;
        }

        .instruction-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
            gap: 12px;
        }

        .instruction-item:last-child {
            border-bottom: none;
        }

        .instruction-item.active {
            background: rgba(52, 152, 219, 0.05);
            margin: 0 -15px;
            padding: 12px 15px;
            border-radius: 8px;
        }

        .instruction-item .step-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: #666;
            flex-shrink: 0;
        }

        .instruction-item.active .step-icon {
            background: var(--primary-color);
            color: white;
        }

        .instruction-item .step-info {
            flex: 1;
            min-width: 0;
        }

        .instruction-item .step-text {
            font-size: 0.9rem;
            color: #333;
        }

        .instruction-item .step-distance {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        /* Lista tappe toggle */
        .stops-toggle {
            padding: 10px 15px;
            background: #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            border-top: 1px solid #eee;
        }

        .stops-list {
            max-height: 200px;
            overflow-y: auto;
            padding: 0 15px;
            display: block;
        }

        .stops-list.hidden {
            display: none;
        }

        .stop-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            gap: 10px;
        }

        .stop-item .stop-number {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.8rem;
            flex-shrink: 0;
        }

        .stop-item .stop-number.completed {
            background: var(--success-color);
        }

        .stop-item .stop-info {
            flex: 1;
            min-width: 0;
        }

        .stop-item .stop-info .address {
            font-size: 0.85rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .stop-item .cliente {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        /* Pulsante completa nella lista */
        .btn-complete-stop {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: none;
            background: rgba(39, 174, 96, 0.1);
            color: var(--success-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
            cursor: pointer;
        }

        .btn-complete-stop:active {
            background: rgba(39, 174, 96, 0.2);
        }

        /* Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.6);
            z-index: 3000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            width: 100%;
            max-width: 400px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
        }

        .modal-header h5 {
            margin: 0;
            font-size: 1.1rem;
            color: var(--primary-color);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #999;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-address {
            background: #f8f9fa;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 0.9rem;
            margin-bottom: 15px;
            color: #555;
        }

        .modal-body .form-group {
            margin-bottom: 15px;
        }

        .modal-body .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            font-size: 0.85rem;
            color: #333;
        }

        .modal-body textarea {
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 0.9rem;
            resize: none;
            font-family: inherit;
        }

        .modal-body textarea:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        /* Signature section */
        .signature-section {
            margin-bottom: 15px;
        }

        .signature-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .signature-header label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #333;
            margin: 0;
        }

        .btn-clear-signature {
            background: none;
            border: none;
            color: #e74c3c;
            font-size: 0.8rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .signature-pad-container {
            border: 2px dashed #ddd;
            border-radius: 8px;
            background: #fafafa;
            overflow: hidden;
        }

        .signature-pad {
            width: 100%;
            height: 120px;
            display: block;
            touch-action: none;
        }

        .modal-footer {
            display: flex;
            gap: 10px;
            padding: 15px 20px;
            border-top: 1px solid #eee;
        }

        .btn-modal {
            flex: 1;
            padding: 12px 20px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-modal.btn-cancel {
            background: #f0f0f0;
            color: #666;
        }

        .btn-modal.btn-confirm {
            background: linear-gradient(135deg, #27ae60 0%, #1e8449 100%);
            color: white;
        }

        /* Loading overlay */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.95);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }

        .loading-overlay .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #eee;
            border-top-color: var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Custom markers */
        .leaflet-routing-container {
            display: none !important;
        }

        .custom-marker {
            background: white;
            border-radius: 50%;
            border: 3px solid var(--primary-color);
            width: 30px !important;
            height: 30px !important;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            color: var(--primary-color);
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .custom-marker.current-location {
            background: var(--primary-color);
            color: white;
            border-color: white;
            width: 20px !important;
            height: 20px !important;
            border-width: 3px;
        }

        .custom-marker.destination {
            background: #e74c3c;
            color: white;
            border-color: white;
        }

        /* Speed indicator */
        .speed-indicator {
            position: absolute;
            top: 70px;
            right: 10px;
            background: white;
            border-radius: 10px;
            padding: 8px 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 999;
            text-align: center;
            display: none;
        }

        .speed-indicator.active {
            display: block;
        }

        .speed-indicator .speed-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            line-height: 1;
        }

        .speed-indicator .speed-unit {
            font-size: 0.7rem;
            color: #999;
        }

        /* Recenter button */
        .recenter-btn {
            position: absolute;
            bottom: 90px;
            right: 10px;
            width: 44px;
            height: 44px;
            background: white;
            border: none;
            border-radius: 50%;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: var(--primary-color);
            z-index: 999;
        }
    </style>
@endsection

@section('content')
    <div class="nav-container">
        <!-- Header -->
        <div class="nav-header">
            <button class="back-btn" onclick="exitNavigation()">
                <i class="ri-arrow-left-line"></i>
            </button>
            <div class="title">
                <h6>Percorso del giorno</h6>
                <small><span id="stopCount">{{ $consegne->where('stato', '!=', 'completato')->count() }}</span> tappe rimanenti</small>
            </div>
        </div>

        <!-- Mappa -->
        <div id="map"></div>

        <!-- Loading -->
        <div class="loading-overlay" id="loadingOverlay">
            <div class="spinner"></div>
            <p class="mt-3 text-muted" id="loadingText">Calcolo percorso ottimale...</p>
        </div>

        <!-- Istruzione corrente (sopra mappa, visibile durante navigazione) -->
        <div class="current-instruction-bar" id="currentInstructionBar">
            <div class="instruction-content">
                <div class="direction-icon" id="directionIcon">
                    <i class="ri-arrow-up-line"></i>
                </div>
                <div class="instruction-text">
                    <div class="instruction-distance" id="instructionDistance">--</div>
                    <div class="instruction-main" id="instructionMain">Calcolo percorso...</div>
                </div>
            </div>
            <div class="next-instruction" id="nextInstruction">
                Poi: --
            </div>
        </div>

        <!-- Speed indicator -->
        <div class="speed-indicator" id="speedIndicator">
            <div class="speed-value" id="speedValue">0</div>
            <div class="speed-unit">km/h</div>
        </div>

        <!-- Recenter button -->
        <button class="recenter-btn" id="recenterBtn" onclick="recenterMap()" style="display: none;">
            <i class="ri-focus-3-line"></i>
        </button>

        <!-- Info Panel -->
        <div class="nav-info-panel" id="infoPanel">
            <!-- Handle per trascinare/toggle -->
            <div class="panel-toggle" onclick="togglePanel()">
                <div class="handle"></div>
                <i class="ri-arrow-up-s-line toggle-icon" id="panelToggleIcon"></i>
            </div>

            <!-- Pulsante Google Maps -->
            <div class="action-buttons">
                <button class="btn-action btn-google" onclick="openGoogleMaps()">
                    <i class="ri-navigation-fill"></i>
                    Avvia Navigazione Google Maps
                </button>
            </div>

            <!-- Stats -->
            <div class="trip-stats">
                <div class="stat">
                    <div class="value" id="totalDistance">--</div>
                    <div class="label">Km totali</div>
                </div>
                <div class="stat">
                    <div class="value" id="totalTime">--</div>
                    <div class="label">Tempo</div>
                </div>
                <div class="stat">
                    <div class="value" id="remainingDistance">--</div>
                    <div class="label">Km rimasti</div>
                </div>
                <div class="stat">
                    <div class="value" id="completedStops">0/{{ count($consegne) }}</div>
                    <div class="label">Tappe</div>
                </div>
            </div>

            <!-- Toggle tappe -->
            <div class="stops-toggle" onclick="toggleStopsList()">
                <span><i class="ri-map-pin-line me-2"></i>Lista tappe</span>
                <i class="ri-arrow-up-s-line" id="stopsToggleIcon"></i>
            </div>

            <!-- Lista tappe -->
            <div class="stops-list" id="stopsList">
                @foreach($consegne as $index => $consegna)
                    <div class="stop-item" data-id="{{ $consegna->id }}" data-index="{{ $index }}">
                        <div class="stop-number {{ $consegna->stato == 'completato' ? 'completed' : '' }}">
                            {{ $consegna->stato == 'completato' ? '✓' : ($index + 1) }}
                        </div>
                        <div class="stop-info" style="flex: 1;">
                            <div class="address">{{ $consegna->indirizzo_consegna ?? 'N/D' }}</div>
                            @if($consegna->cliente)
                                <div class="cliente">{{ $consegna->cliente }}</div>
                            @endif
                        </div>
                        @if($consegna->stato != 'completato')
                            <button class="btn-complete-stop" onclick="openCompleteModal({{ $consegna->id }}, '{{ addslashes($consegna->indirizzo_consegna ?? '') }}')">
                                <i class="ri-check-line"></i>
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Lista istruzioni (visibile durante navigazione) -->
            <div class="instructions-list" id="instructionsList" style="display: none;">
                <!-- Popolato dinamicamente -->
            </div>
        </div>

        <!-- Modal Completa Consegna con Firme -->
        <div class="modal-overlay" id="completeModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h5><i class="ri-checkbox-circle-line me-2"></i>Completa Consegna</h5>
                    <button class="modal-close" onclick="closeCompleteModal()">
                        <i class="ri-close-line"></i>
                    </button>
                </div>

                <div class="modal-body">
                    <p class="modal-address" id="modalAddress">Indirizzo consegna</p>

                    <!-- Note -->
                    <div class="form-group">
                        <label>Note (opzionale)</label>
                        <textarea id="noteConsegna" rows="2" placeholder="Eventuali note sulla consegna..."></textarea>
                    </div>

                    <!-- Firma Cliente -->
                    <div class="signature-section">
                        <div class="signature-header">
                            <label><i class="ri-user-line me-1"></i>Firma Cliente</label>
                            <button class="btn-clear-signature" onclick="clearSignature('cliente')">
                                <i class="ri-delete-bin-line"></i> Cancella
                            </button>
                        </div>
                        <div class="signature-pad-container">
                            <canvas id="signatureCliente" class="signature-pad"></canvas>
                        </div>
                    </div>

                    <!-- Firma Autista -->
                    <div class="signature-section">
                        <div class="signature-header">
                            <label><i class="ri-steering-line me-1"></i>Firma Autista</label>
                            <button class="btn-clear-signature" onclick="clearSignature('autista')">
                                <i class="ri-delete-bin-line"></i> Cancella
                            </button>
                        </div>
                        <div class="signature-pad-container">
                            <canvas id="signatureAutista" class="signature-pad"></canvas>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn-modal btn-cancel" onclick="closeCompleteModal()">
                        Annulla
                    </button>
                    <button class="btn-modal btn-confirm" onclick="confirmComplete()">
                        <i class="ri-check-line me-1"></i>Conferma
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.min.js"></script>

    <script>
        // Variabili globali
        let map;
        let routingControl;
        let currentPositionMarker;
        let stopMarkers = [];
        let watchId;
        let currentPosition = null;
        let isNavigating = false;
        let routeInstructions = [];
        let currentInstructionIndex = 0;
        let lastSpeed = 0;

        // Dati consegne dal server
        const consegne = @json($consegne);
        const csrfToken = '{{ csrf_token() }}';

        console.log('📦 Consegne caricate:', consegne.length);

        // Inizializza
        document.addEventListener('DOMContentLoaded', async () => {
            if (consegne.length === 0) {
                hideLoading();
                alert('Nessuna consegna per oggi');
                return;
            }

            initMap();

            try {
                showLoading('Acquisizione posizione GPS...');
                await getCurrentPosition();

                showLoading('Ricerca indirizzi...');
                await geocodeAddresses();

                const validStops = consegne.filter(c => c.lat && c.lng && c.stato !== 'completato');

                if (validStops.length === 0) {
                    hideLoading();
                    alert('Nessun indirizzo valido trovato');
                    return;
                }

                showLoading('Calcolo percorso ottimale...');
                await calculateRoute();
            } catch (error) {
                console.error('Errore:', error);
                hideLoading();
            }
        });

        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }

        function showLoading(text) {
            document.getElementById('loadingOverlay').style.display = 'flex';
            document.getElementById('loadingText').textContent = text;
        }

        // Inizializza mappa
        function initMap() {
            map = L.map('map', {
                zoomControl: false
            }).setView([41.9028, 12.4964], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: ''
            }).addTo(map);

            L.control.zoom({ position: 'topright' }).addTo(map);
        }

        // Ottieni posizione
        function getCurrentPosition() {
            return new Promise((resolve) => {
                if (!navigator.geolocation) {
                    resolve(null);
                    return;
                }

                const timeout = setTimeout(() => resolve(null), 15000);

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        clearTimeout(timeout);
                        currentPosition = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };

                        const icon = L.divIcon({
                            className: 'custom-marker current-location',
                            html: '',
                            iconSize: [20, 20],
                            iconAnchor: [10, 10]
                        });

                        currentPositionMarker = L.marker([currentPosition.lat, currentPosition.lng], {
                            icon,
                            zIndexOffset: 1000
                        }).addTo(map);

                        map.setView([currentPosition.lat, currentPosition.lng], 14);
                        resolve(currentPosition);
                    },
                    () => {
                        clearTimeout(timeout);
                        resolve(null);
                    },
                    { enableHighAccuracy: true, timeout: 15000 }
                );
            });
        }

        // Geocodifica
        async function geocodeAddresses() {
            for (let i = 0; i < consegne.length; i++) {
                const c = consegne[i];
                if (!c.indirizzo_consegna || c.stato === 'completato') continue;

                try {
                    const response = await fetch(
                        `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(c.indirizzo_consegna)}&limit=1&countrycodes=it`
                    );
                    const data = await response.json();

                    if (data && data.length > 0) {
                        consegne[i].lat = parseFloat(data[0].lat);
                        consegne[i].lng = parseFloat(data[0].lon);
                    }

                    await new Promise(r => setTimeout(r, 1100));
                } catch (error) {
                    console.error('Geocoding error:', error);
                }
            }
        }

        // Calcola percorso
        async function calculateRoute() {
            const validStops = consegne.filter(c => c.lat && c.lng && c.stato !== 'completato');

            if (validStops.length === 0) {
                hideLoading();
                return;
            }

            let waypoints = [];

            if (currentPosition) {
                waypoints.push(L.latLng(currentPosition.lat, currentPosition.lng));
            }

            validStops.forEach(stop => {
                waypoints.push(L.latLng(stop.lat, stop.lng));
            });

            if (waypoints.length < 2) {
                hideLoading();
                if (validStops.length > 0) {
                    map.setView([validStops[0].lat, validStops[0].lng], 14);
                    addStopMarkers(validStops);
                }
                return;
            }

            if (routingControl) {
                map.removeControl(routingControl);
            }

            stopMarkers = [];

            routingControl = L.Routing.control({
                waypoints: waypoints,
                router: L.Routing.osrmv1({
                    serviceUrl: 'https://router.project-osrm.org/route/v1',
                    profile: 'driving'
                }),
                lineOptions: {
                    styles: [
                        { color: '#3498db', opacity: 0.8, weight: 6 },
                        { color: '#2980b9', opacity: 1, weight: 2 }
                    ]
                },
                addWaypoints: false,
                draggableWaypoints: false,
                fitSelectedRoutes: true,
                show: false,
                createMarker: function(i, wp, n) {
                    if (i === 0 && currentPosition) return null;

                    const stopIndex = currentPosition ? i - 1 : i;
                    const consegna = validStops[stopIndex];
                    if (!consegna) return null;

                    const isLast = i === n - 1;
                    const icon = L.divIcon({
                        className: `custom-marker ${isLast ? 'destination' : ''}`,
                        html: `<span>${stopIndex + 1}</span>`,
                        iconSize: [30, 30],
                        iconAnchor: [15, 15]
                    });

                    const marker = L.marker(wp.latLng, { icon });
                    marker.bindPopup(`
                    <strong>Tappa ${stopIndex + 1}</strong><br>
                    ${consegna.indirizzo_consegna}<br>
                    ${consegna.cliente ? '<small>' + consegna.cliente + '</small>' : ''}
                `);
                    stopMarkers.push(marker);
                    return marker;
                }
            }).addTo(map);

            routingControl.on('routesfound', function(e) {
                const route = e.routes[0];

                const distanceKm = (route.summary.totalDistance / 1000).toFixed(1);
                const timeMin = Math.round(route.summary.totalTime / 60);
                const timeHours = Math.floor(timeMin / 60);
                const timeRemainingMin = timeMin % 60;

                document.getElementById('totalDistance').textContent = distanceKm + ' km';
                document.getElementById('remainingDistance').textContent = distanceKm + ' km';
                document.getElementById('totalTime').textContent =
                    timeHours > 0 ? `${timeHours}h ${timeRemainingMin}m` : `${timeMin} min`;

                // Salva istruzioni
                routeInstructions = route.instructions || [];
                console.log('📍 Istruzioni percorso:', routeInstructions.length);

                // Aggiorna contatore tappe
                const completed = consegne.filter(c => c.stato === 'completato').length;
                document.getElementById('completedStops').textContent = `${completed}/${consegne.length}`;

                hideLoading();
            });

            routingControl.on('routingerror', function(e) {
                console.error('Routing error:', e);
                hideLoading();
                addStopMarkers(validStops);
                alert('Errore calcolo percorso. Tappe mostrate sulla mappa.');
            });

            setTimeout(() => {
                if (document.getElementById('loadingOverlay').style.display !== 'none') {
                    hideLoading();
                }
            }, 30000);
        }

        function addStopMarkers(stops) {
            stops.forEach((stop, i) => {
                const icon = L.divIcon({
                    className: 'custom-marker',
                    html: `<span>${i + 1}</span>`,
                    iconSize: [30, 30],
                    iconAnchor: [15, 15]
                });
                L.marker([stop.lat, stop.lng], { icon })
                    .addTo(map)
                    .bindPopup(`<strong>Tappa ${i + 1}</strong><br>${stop.indirizzo_consegna}`);
            });

            if (stops.length > 0) {
                const bounds = L.latLngBounds(stops.map(s => [s.lat, s.lng]));
                map.fitBounds(bounds, { padding: [50, 50] });
            }
        }

        // ==================== NAVIGAZIONE IN-APP ====================

        function startNavigation() {
            if (routeInstructions.length === 0) {
                alert('Nessuna istruzione disponibile. Attendi il calcolo del percorso.');
                return;
            }

            isNavigating = true;
            currentInstructionIndex = 0;

            // Mostra elementi navigazione
            document.getElementById('currentInstructionBar').classList.add('active');
            document.getElementById('speedIndicator').classList.add('active');
            document.getElementById('recenterBtn').style.display = 'flex';
            document.getElementById('floatingActions').style.display = 'none';
            document.getElementById('floatingActionsNav').style.display = 'flex';

            // Mostra lista istruzioni invece delle tappe
            document.getElementById('stopsList').style.display = 'none';
            document.getElementById('instructionsList').style.display = 'block';
            populateInstructionsList();

            // Aggiorna prima istruzione
            updateCurrentInstruction();

            // Avvia tracking preciso
            startPreciseTracking();

            // Centra mappa sulla posizione
            if (currentPosition) {
                map.setView([currentPosition.lat, currentPosition.lng], 17);
            }
        }

        function stopNavigation() {
            isNavigating = false;

            // Nascondi elementi navigazione
            document.getElementById('currentInstructionBar').classList.remove('active');
            document.getElementById('speedIndicator').classList.remove('active');
            document.getElementById('recenterBtn').style.display = 'none';
            document.getElementById('floatingActions').style.display = 'flex';
            document.getElementById('floatingActionsNav').style.display = 'none';

            // Mostra tappe invece delle istruzioni
            document.getElementById('stopsList').style.display = 'none';
            document.getElementById('instructionsList').style.display = 'none';

            // Ferma tracking preciso
            if (watchId) {
                navigator.geolocation.clearWatch(watchId);
                watchId = null;
            }

            // Zoom out
            const validStops = consegne.filter(c => c.lat && c.lng);
            if (validStops.length > 0) {
                const bounds = L.latLngBounds(validStops.map(s => [s.lat, s.lng]));
                if (currentPosition) {
                    bounds.extend([currentPosition.lat, currentPosition.lng]);
                }
                map.fitBounds(bounds, { padding: [50, 50] });
            }
        }

        function startPreciseTracking() {
            if (!navigator.geolocation) return;

            watchId = navigator.geolocation.watchPosition(
                (position) => {
                    currentPosition = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };

                    // Aggiorna marker
                    if (currentPositionMarker) {
                        currentPositionMarker.setLatLng([currentPosition.lat, currentPosition.lng]);
                    }

                    // Aggiorna velocità
                    if (position.coords.speed !== null) {
                        lastSpeed = Math.round(position.coords.speed * 3.6);
                        document.getElementById('speedValue').textContent = lastSpeed;
                    }

                    // Centra mappa durante navigazione
                    if (isNavigating) {
                        map.setView([currentPosition.lat, currentPosition.lng], map.getZoom());
                        updateNavigationProgress();
                    }
                },
                (error) => console.warn('Tracking error:', error),
                {
                    enableHighAccuracy: true,
                    maximumAge: 0,
                    timeout: 5000
                }
            );
        }

        function updateCurrentInstruction() {
            if (currentInstructionIndex >= routeInstructions.length) return;

            const instruction = routeInstructions[currentInstructionIndex];
            const nextInstruction = routeInstructions[currentInstructionIndex + 1];

            // Icona direzione
            const iconEl = document.querySelector('#directionIcon i');
            iconEl.className = getDirectionIcon(instruction.type);

            // Distanza e testo
            document.getElementById('instructionDistance').textContent = formatDistance(instruction.distance);
            document.getElementById('instructionMain').textContent = instruction.text || 'Prosegui';

            // Prossima istruzione
            if (nextInstruction) {
                document.getElementById('nextInstruction').textContent = 'Poi: ' + (nextInstruction.text || 'Prosegui');
            } else {
                document.getElementById('nextInstruction').textContent = 'Arrivo a destinazione';
            }

            // Aggiorna lista
            highlightCurrentInstruction();
        }

        function updateNavigationProgress() {
            if (!currentPosition || routeInstructions.length === 0) return;

            // Trova l'istruzione più vicina (semplificato)
            // In una versione avanzata, calcoleresti la distanza dal percorso

            // Aggiorna distanza rimanente (approssimativa)
            let remainingDist = 0;
            for (let i = currentInstructionIndex; i < routeInstructions.length; i++) {
                remainingDist += routeInstructions[i].distance || 0;
            }
            document.getElementById('remainingDistance').textContent = formatDistance(remainingDist);
        }

        function populateInstructionsList() {
            const list = document.getElementById('instructionsList');
            list.innerHTML = routeInstructions.map((inst, i) => `
            <div class="instruction-item ${i === 0 ? 'active' : ''}" data-index="${i}">
                <div class="step-icon">
                    <i class="${getDirectionIcon(inst.type)}"></i>
                </div>
                <div class="step-info">
                    <div class="step-text">${inst.text || 'Prosegui'}</div>
                    <div class="step-distance">${formatDistance(inst.distance)}</div>
                </div>
            </div>
        `).join('');
        }

        function highlightCurrentInstruction() {
            document.querySelectorAll('.instruction-item').forEach((el, i) => {
                el.classList.toggle('active', i === currentInstructionIndex);
            });

            // Scroll to current
            const activeEl = document.querySelector('.instruction-item.active');
            if (activeEl) {
                activeEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }

        function getDirectionIcon(type) {
            const icons = {
                'Straight': 'ri-arrow-up-line',
                'SlightRight': 'ri-corner-up-right-line',
                'Right': 'ri-arrow-right-line',
                'SharpRight': 'ri-corner-right-down-line',
                'TurnAround': 'ri-arrow-go-back-line',
                'SharpLeft': 'ri-corner-left-down-line',
                'Left': 'ri-arrow-left-line',
                'SlightLeft': 'ri-corner-up-left-line',
                'WaypointReached': 'ri-map-pin-line',
                'Roundabout': 'ri-refresh-line',
                'DestinationReached': 'ri-flag-line',
                'Head': 'ri-arrow-up-line',
                'Continue': 'ri-arrow-up-line',
            };
            return icons[type] || 'ri-arrow-up-line';
        }

        function formatDistance(meters) {
            if (!meters) return '--';
            if (meters < 1000) {
                return Math.round(meters) + ' m';
            }
            return (meters / 1000).toFixed(1) + ' km';
        }

        function recenterMap() {
            if (currentPosition) {
                map.setView([currentPosition.lat, currentPosition.lng], 17);
            }
        }

        // ==================== GOOGLE MAPS ====================

        function openGoogleMaps() {
            const validStops = consegne.filter(c => c.lat && c.lng && c.stato !== 'completato');

            if (validStops.length === 0) {
                alert('Nessuna tappa da navigare. Attendi il caricamento delle coordinate.');
                return;
            }

            let url;

            if (validStops.length === 1) {
                url = `https://www.google.com/maps/dir/?api=1&destination=${validStops[0].lat},${validStops[0].lng}&travelmode=driving`;
            } else {
                const destination = validStops[validStops.length - 1];
                const waypoints = validStops.slice(0, -1);
                const waypointCoords = waypoints.slice(0, 10).map(s => `${s.lat},${s.lng}`).join('|');
                url = `https://www.google.com/maps/dir/?api=1&destination=${destination.lat},${destination.lng}&waypoints=${encodeURIComponent(waypointCoords)}&travelmode=driving`;
            }

            // ✅ FIX: Usa link temporaneo invece di window.open
            const link = document.createElement('a');
            link.href = url;
            link.target = '_blank';
            link.rel = 'noopener noreferrer';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // ==================== UTILITY ====================

        function togglePanel() {
            const panel = document.getElementById('infoPanel');
            const icon = document.getElementById('panelToggleIcon');
            panel.classList.toggle('collapsed');

            if (panel.classList.contains('collapsed')) {
                icon.className = 'ri-arrow-down-s-line toggle-icon';
            } else {
                icon.className = 'ri-arrow-up-s-line toggle-icon';
            }

            // Ricalcola dimensioni mappa dopo l'animazione
            setTimeout(() => {
                if (map) {
                    map.invalidateSize();
                }
            }, 350);
        }

        function toggleStopsList() {
            const list = document.getElementById('stopsList');
            const icon = document.getElementById('stopsToggleIcon');
            list.classList.toggle('hidden');
            icon.className = list.classList.contains('hidden') ? 'ri-arrow-down-s-line' : 'ri-arrow-up-s-line';
        }

        function exitNavigation() {
            if (isNavigating) {
                stopNavigation();
            }
            window.location.href = '/autista/consegne';
        }

        // Cleanup
        window.addEventListener('beforeunload', () => {
            if (watchId) {
                navigator.geolocation.clearWatch(watchId);
            }
        });

        // ==================== MODAL COMPLETA CONSEGNA ====================

        let currentCompleteId = null;
        let signatureCanvases = {};
        let signatureContexts = {};
        let isDrawing = false;

        function openCompleteModal(id, address) {
            currentCompleteId = id;
            document.getElementById('modalAddress').textContent = address || 'Consegna';
            document.getElementById('noteConsegna').value = '';
            document.getElementById('completeModal').classList.add('active');

            // Inizializza i canvas delle firme
            setTimeout(() => {
                initSignaturePad('Cliente');
                initSignaturePad('Autista');
            }, 100);
        }

        function closeCompleteModal() {
            document.getElementById('completeModal').classList.remove('active');
            currentCompleteId = null;
        }

        function initSignaturePad(type) {
            const canvas = document.getElementById('signature' + type);
            if (!canvas) return;

            const ctx = canvas.getContext('2d');

            // Imposta dimensioni canvas
            const container = canvas.parentElement;
            canvas.width = container.offsetWidth;
            canvas.height = 120;

            // Sfondo bianco
            ctx.fillStyle = 'white';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            // Stile linea
            ctx.strokeStyle = '#333';
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';

            signatureCanvases[type] = canvas;
            signatureContexts[type] = ctx;

            // Eventi touch
            canvas.addEventListener('touchstart', (e) => handleStart(e, type), { passive: false });
            canvas.addEventListener('touchmove', (e) => handleMove(e, type), { passive: false });
            canvas.addEventListener('touchend', () => handleEnd(type));

            // Eventi mouse (per test desktop)
            canvas.addEventListener('mousedown', (e) => handleMouseStart(e, type));
            canvas.addEventListener('mousemove', (e) => handleMouseMove(e, type));
            canvas.addEventListener('mouseup', () => handleEnd(type));
            canvas.addEventListener('mouseleave', () => handleEnd(type));
        }

        function getCanvasCoords(canvas, e) {
            const rect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;

            if (e.touches) {
                return {
                    x: (e.touches[0].clientX - rect.left) * scaleX,
                    y: (e.touches[0].clientY - rect.top) * scaleY
                };
            } else {
                return {
                    x: (e.clientX - rect.left) * scaleX,
                    y: (e.clientY - rect.top) * scaleY
                };
            }
        }

        function handleStart(e, type) {
            e.preventDefault();
            isDrawing = true;
            const coords = getCanvasCoords(signatureCanvases[type], e);
            signatureContexts[type].beginPath();
            signatureContexts[type].moveTo(coords.x, coords.y);
        }

        function handleMove(e, type) {
            if (!isDrawing) return;
            e.preventDefault();
            const coords = getCanvasCoords(signatureCanvases[type], e);
            signatureContexts[type].lineTo(coords.x, coords.y);
            signatureContexts[type].stroke();
        }

        function handleEnd(type) {
            isDrawing = false;
        }

        function handleMouseStart(e, type) {
            isDrawing = true;
            const coords = getCanvasCoords(signatureCanvases[type], e);
            signatureContexts[type].beginPath();
            signatureContexts[type].moveTo(coords.x, coords.y);
        }

        function handleMouseMove(e, type) {
            if (!isDrawing) return;
            const coords = getCanvasCoords(signatureCanvases[type], e);
            signatureContexts[type].lineTo(coords.x, coords.y);
            signatureContexts[type].stroke();
        }

        function clearSignature(type) {
            const typeCap = type.charAt(0).toUpperCase() + type.slice(1);
            const canvas = signatureCanvases[typeCap];
            const ctx = signatureContexts[typeCap];
            if (canvas && ctx) {
                ctx.fillStyle = 'white';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
            }
        }

        function isCanvasBlank(canvas) {
            const ctx = canvas.getContext('2d');
            const pixelBuffer = new Uint32Array(
                ctx.getImageData(0, 0, canvas.width, canvas.height).data.buffer
            );
            // Controlla se tutti i pixel sono bianchi (0xFFFFFFFF)
            return !pixelBuffer.some(color => color !== 0xFFFFFFFF);
        }

        async function confirmComplete() {
            if (!currentCompleteId) return;

            // Verifica firme
            const canvasCliente = signatureCanvases['Cliente'];
            const canvasAutista = signatureCanvases['Autista'];

            if (!canvasCliente || !canvasAutista) {
                alert('Errore: canvas firme non inizializzati');
                return;
            }

            if (isCanvasBlank(canvasCliente)) {
                alert('Per favore, inserisci la firma del cliente');
                return;
            }

            if (isCanvasBlank(canvasAutista)) {
                alert('Per favore, inserisci la tua firma');
                return;
            }

            // Raccogli dati
            const data = {
                note: document.getElementById('noteConsegna').value,
                firma_cliente: canvasCliente.toDataURL('image/png'),
                firma_autista: canvasAutista.toDataURL('image/png')
            };

            try {
                const response = await fetch(`/autista/consegna/${currentCompleteId}/completa`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    // Aggiorna UI
                    const stopItem = document.querySelector(`.stop-item[data-id="${currentCompleteId}"]`);
                    if (stopItem) {
                        const numberEl = stopItem.querySelector('.stop-number');
                        numberEl.classList.add('completed');
                        numberEl.textContent = '✓';

                        const completeBtn = stopItem.querySelector('.btn-complete-stop');
                        if (completeBtn) completeBtn.remove();
                    }

                    // Aggiorna stato locale
                    const consegna = consegne.find(c => c.id == currentCompleteId);
                    if (consegna) consegna.stato = 'completato';

                    // Aggiorna contatore
                    const completed = consegne.filter(c => c.stato === 'completato').length;
                    document.getElementById('completedStops').textContent = `${completed}/${consegne.length}`;
                    document.getElementById('stopCount').textContent = consegne.length - completed;

                    closeCompleteModal();

                    // Ricalcola percorso
                    showLoading('Aggiornamento percorso...');
                    await calculateRoute();

                } else {
                    alert('Errore: ' + (result.message || 'Impossibile completare'));
                }
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore di connessione');
            }
        }
    </script>
@endsection