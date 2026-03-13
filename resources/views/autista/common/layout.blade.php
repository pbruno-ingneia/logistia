<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2c3e50">
    <title>Logistia - @yield('title', 'Area Autista')</title>

    <!-- ========== PWA ========== -->
    <link rel="manifest" href="/manifest.json">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Logistia">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/icons/icon-32.png">
    <!-- ========== FINE PWA ========== -->

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Remix Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #3498db;
            --primary-dark: #2c3e50;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --bg-light: #f8f9fa;
            --text-dark: #2c3e50;
            --text-muted: #6c757d;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.08);
            --shadow-md: 0 4px 15px rgba(0,0,0,0.1);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        .bottom-nav .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: var(--text-muted);
            padding: 6px 10px;
            border-radius: var(--radius-md);
            transition: all 0.3s ease;
            min-width: 0;
        }

        .bottom-nav .nav-item i {
            font-size: 1.3rem;
            margin-bottom: 2px;
        }

        .bottom-nav .nav-item span {
            font-size: 0.65rem;
            font-weight: 500;
            white-space: nowrap;
        }
        body {
            background: var(--bg-light);
            min-height: 100vh;
            padding-bottom: 80px;
        }

        /* ========== HEADER ========== */
        .header-autista {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-color) 100%);
            color: white;
            padding: 15px 20px;
            padding-top: calc(15px + env(safe-area-inset-top));
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow-md);
        }

        .header-autista .logo {
            font-size: 1.4rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-autista .logo i {
            font-size: 1.6rem;
        }

        .header-autista .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-autista .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .header-autista .vehicle-badge {
            background: rgba(255,255,255,0.2);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* ========== BOTTOM NAVIGATION ========== */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 -2px 20px rgba(0,0,0,0.1);
            z-index: 1000;
            padding: 8px 0;
            padding-bottom: calc(8px + env(safe-area-inset-bottom));
        }

        .bottom-nav .nav-items {
            display: flex;
            justify-content: space-around;
            align-items: center;
            max-width: 500px;
            margin: 0 auto;
        }

        .bottom-nav .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: var(--text-muted);
            padding: 8px 16px;
            border-radius: var(--radius-md);
            transition: all 0.3s ease;
        }

        .bottom-nav .nav-item.active {
            color: var(--primary-color);
            background: rgba(52, 152, 219, 0.1);
        }

        .bottom-nav .nav-item i {
            font-size: 1.5rem;
            margin-bottom: 4px;
        }

        .bottom-nav .nav-item span {
            font-size: 0.7rem;
            font-weight: 500;
        }

        /* ========== MAIN CONTENT ========== */
        .main-content {
            padding: 20px;
            max-width: 600px;
            margin: 0 auto;
        }

        /* ========== CARDS ========== */
        .card-custom {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            border: none;
            margin-bottom: 16px;
            overflow: hidden;
        }

        .card-custom .card-header {
            background: transparent;
            border-bottom: 1px solid #eee;
            padding: 16px 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-custom .card-body {
            padding: 20px;
        }

        /* ========== STAT CARDS ========== */
        .stat-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 20px;
            box-shadow: var(--shadow-sm);
            text-align: center;
        }

        .stat-card.primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, #2980b9 100%);
            color: white;
        }

        .stat-card.success {
            background: linear-gradient(135deg, var(--success-color) 0%, #1e8449 100%);
            color: white;
        }

        .stat-card .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
            opacity: 0.9;
        }

        .stat-card .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1;
        }

        .stat-card .stat-label {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-top: 5px;
        }

        /* ========== BUTTONS ========== */
        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-color) 0%, #2980b9 100%);
            border: none;
            color: white;
            padding: 12px 24px;
            border-radius: var(--radius-md);
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            color: white;
        }

        .btn-success-custom {
            background: linear-gradient(135deg, var(--success-color) 0%, #1e8449 100%);
            border: none;
            color: white;
            padding: 12px 24px;
            border-radius: var(--radius-md);
            font-weight: 600;
        }

        .btn-outline-custom {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            padding: 10px 22px;
            border-radius: var(--radius-md);
            font-weight: 600;
        }

        /* ========== TRACKING STATUS ========== */
        .tracking-status {
            position: fixed;
            bottom: 90px;
            left: 50%;
            transform: translateX(-50%);
            padding: 12px 25px;
            border-radius: 30px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 999;
            box-shadow: var(--shadow-md);
        }

        .tracking-status.active {
            background: var(--success-color);
            color: white;
        }

        .tracking-status.inactive {
            background: var(--danger-color);
            color: white;
        }

        .tracking-status .pulse {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: white;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
        }

        /* ========== LISTS ========== */
        .list-item {
            display: flex;
            align-items: center;
            padding: 16px;
            background: white;
            border-radius: var(--radius-md);
            margin-bottom: 10px;
            box-shadow: var(--shadow-sm);
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
        }

        .list-item:hover {
            transform: translateX(5px);
            box-shadow: var(--shadow-md);
        }

        .list-item .icon {
            width: 50px;
            height: 50px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 15px;
        }

        .list-item .icon.blue { background: rgba(52, 152, 219, 0.1); color: var(--primary-color); }
        .list-item .icon.green { background: rgba(39, 174, 96, 0.1); color: var(--success-color); }
        .list-item .icon.orange { background: rgba(243, 156, 18, 0.1); color: var(--warning-color); }
        .list-item .icon.red { background: rgba(231, 76, 60, 0.1); color: var(--danger-color); }

        .list-item .content {
            flex: 1;
        }

        .list-item .content .title {
            font-weight: 600;
            margin-bottom: 3px;
        }

        .list-item .content .subtitle {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .list-item .arrow {
            color: var(--text-muted);
            font-size: 1.2rem;
        }

        /* ========== BADGES ========== */
        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-status.pending { background: rgba(243, 156, 18, 0.1); color: var(--warning-color); }
        .badge-status.active { background: rgba(52, 152, 219, 0.1); color: var(--primary-color); }
        .badge-status.completed { background: rgba(39, 174, 96, 0.1); color: var(--success-color); }

        /* ========== EMPTY STATE ========== */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 4rem;
            opacity: 0.3;
            margin-bottom: 15px;
        }

        /* ========== UTILITIES ========== */
        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .divider {
            height: 1px;
            background: #eee;
            margin: 20px 0;
        }

        .fw-500 { font-weight: 500; }

        /* ========== PWA: Banner installazione ========== */
        .pwa-install-banner {
            position: fixed;
            bottom: 90px;
            left: 10px;
            right: 10px;
            background: white;
            border-radius: var(--radius-lg);
            padding: 15px;
            box-shadow: var(--shadow-md);
            z-index: 998;
            display: none;
            animation: slideUp 0.3s ease;
        }
        .pwa-install-banner.show { display: block; }
        @keyframes slideUp {
            from { transform: translateY(100px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* ========== PWA: Banner offline ========== */
        .offline-banner {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: var(--danger-color);
            color: white;
            text-align: center;
            padding: 8px;
            padding-top: calc(8px + env(safe-area-inset-top));
            z-index: 9999;
            font-size: 0.85rem;
            display: none;
        }
        .offline-banner.show { display: block; }

        /* ========== RESPONSIVE ========== */
        @media (min-width: 768px) {
            .main-content {
                padding: 30px;
            }
            .stat-card .stat-value {
                font-size: 3rem;
            }
        }

        /* ========== ANIMATIONS ========== */
        .fade-in {
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    @yield('styles')
</head>
<body>

@php
    // Recupera dispositivo tracking per l'utente corrente
    $dispositivoTracking = null;
    if(session('utente')) {
        $dispositivoTracking = \DB::table('dispositivi_tracking')
            ->where('id_utente', session('utente')->id)
            ->where('is_active', 1)
            ->first();
    }
@endphp

        <!-- PWA: Banner Offline -->
<div class="offline-banner" id="offlineBanner">
    <i class="ri-wifi-off-line"></i> Connessione assente - Modalità offline
</div>

<!-- Header -->
<header class="header-autista">
    <div class="d-flex justify-content-between align-items-center">
        <div class="logo">
            <img src="/base_icon_transparent_background.png" alt="Logistia" style="height: 30px;">
            <span>Logistia</span>
        </div>
        <div class="user-info">
            @if($dispositivoTracking)
                <div class="vehicle-badge">
                    <i class="ri-car-line"></i>
                    {{ $dispositivoTracking->targa_mezzo ?? 'N/D' }}
                </div>
            @endif
            <div class="user-avatar">
                {{ strtoupper(substr(session('utente')->nome ?? 'U', 0, 1)) }}
            </div>
        </div>
    </div>
</header>

<!-- Main Content -->
<main class="main-content">
    @yield('content')
</main>

<!-- PWA: Banner Installazione -->
<div class="pwa-install-banner" id="installBanner">
    <div class="d-flex align-items-center">
        <div class="me-3">
            <i class="ri-download-2-line" style="font-size: 2rem; color: var(--primary-color);"></i>
        </div>
        <div class="flex-grow-1">
            <strong>Installa Logistia</strong>
            <div class="small text-muted">Accesso rapido dalla home</div>
        </div>
        <button class="btn btn-primary btn-sm me-2" id="installBtn">Installa</button>
        <button class="btn btn-link btn-sm text-muted p-0" id="dismissBtn">
            <i class="ri-close-line" style="font-size: 1.3rem;"></i>
        </button>
    </div>
</div>

<!-- Bottom Navigation -->
<nav class="bottom-nav">
    <div class="nav-items">
        <a href="/autista/dashboard" class="nav-item {{ request()->is('autista/dashboard') ? 'active' : '' }}">
            <i class="ri-home-5-line"></i>
            <span>Home</span>
        </a>
        <a href="/autista/piano-giornaliero" class="nav-item {{ request()->is('autista/consegne*') ? 'active' : '' }}">
            <i class="ri-file-list-3-line"></i>
            <span>Consegne</span>
        </a>
        <a href="/autista/tracking" class="nav-item {{ request()->is('autista/tracking') ? 'active' : '' }}">
            <i class="ri-gps-line"></i>
            <span>Tracking</span>
        </a>
        <a href="/autista/rifornimenti" class="nav-item {{ request()->is('autista/rifornimenti*') ? 'active' : '' }}">
            <i class="ri-gas-station-line"></i>
            <span>Carburante</span>
        </a>
        <a href="/autista/storico" class="nav-item {{ request()->is('autista/storico*') ? 'active' : '' }} d-none">
            <i class="ri-history-line"></i>
            <span>Storico</span>
        </a>
        <a href="/autista/profilo" class="nav-item {{ request()->is('autista/profilo') ? 'active' : '' }}">
            <i class="ri-user-3-line"></i>
            <span>Profilo</span>
        </a>
    </div>
</nav>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- CSRF Token e Utility -->
<script>
    window.csrfToken = '{{ csrf_token() }}';

    async function fetchApi(url, options = {}) {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken,
                'Accept': 'application/json'
            }
        };
        const response = await fetch(url, { ...defaultOptions, ...options });
        return response.json();
    }
</script>

<!-- ========== PWA SERVICE WORKER ========== -->
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then(reg => console.log('✅ PWA: Service Worker registrato'))
                .catch(err => console.log('❌ PWA: Errore SW:', err));
        });
    }

    let deferredPrompt;
    const installBanner = document.getElementById('installBanner');
    const installBtn = document.getElementById('installBtn');
    const dismissBtn = document.getElementById('dismissBtn');

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        if (!localStorage.getItem('pwa-dismissed')) {
            installBanner.classList.add('show');
        }
    });

    installBtn?.addEventListener('click', async () => {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        console.log('PWA installazione:', outcome);
        deferredPrompt = null;
        installBanner.classList.remove('show');
    });

    dismissBtn?.addEventListener('click', () => {
        installBanner.classList.remove('show');
        localStorage.setItem('pwa-dismissed', 'true');
    });

    const offlineBanner = document.getElementById('offlineBanner');
    function updateOnlineStatus() {
        if (navigator.onLine) {
            offlineBanner.classList.remove('show');
        } else {
            offlineBanner.classList.add('show');
        }
    }
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
    updateOnlineStatus();
</script>

<!-- ========== TRACKING PERSISTENTE ========== -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/nosleep/0.12.0/NoSleep.min.js"></script>
<script>
    (function() {
        const DEVICE_TOKEN = '{{ $dispositivoTracking->device_token ?? "" }}';
        const SEND_INTERVAL = 30000;
        const STORAGE_KEY = 'logistia_tracking_active';

        if (!DEVICE_TOKEN) {
            console.log('[Tracking] Nessun dispositivo configurato');
            return;
        }

        let watchId = null;
        let sendTimer = null;
        let lastPosition = null;
        let noSleep = null;

        function isTrackingActive() {
            return localStorage.getItem(STORAGE_KEY) === 'true';
        }

        function setTrackingState(active) {
            localStorage.setItem(STORAGE_KEY, active ? 'true' : 'false');
        }

        async function sendPosition(position) {
            if (!position || !position.coords) return;

            try {
                const response = await fetch('/api/tracking/position', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.csrfToken
                    },
                    body: JSON.stringify({
                        device_token: DEVICE_TOKEN,
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                        speed: (position.coords.speed || 0) * 3.6,
                        heading: position.coords.heading || 0,
                        accuracy: position.coords.accuracy || 0,
                        altitude: position.coords.altitude || 0,
                        battery: null,
                        timestamp: Math.floor(Date.now() / 1000)
                    })
                });

                const data = await response.json();
                if (data.success) {
                    console.log('[Tracking] ✅ Posizione inviata');
                } else {
                    console.warn('[Tracking] ❌ Errore:', data.error);
                }
            } catch (error) {
                console.error('[Tracking] ❌ Errore invio:', error);
            }
        }

        function startTracking() {
            if (!navigator.geolocation) {
                console.error('[Tracking] Geolocation non supportata');
                return false;
            }

            if (!noSleep) noSleep = new NoSleep();
            noSleep.enable();

            watchId = navigator.geolocation.watchPosition(
                (position) => {
                    lastPosition = position;
                    if (typeof window.updateTrackingUI === 'function') {
                        window.updateTrackingUI(position);
                    }
                },
                (error) => console.warn('[Tracking] Errore GPS:', error.message),
                { enableHighAccuracy: true, maximumAge: 5000, timeout: 15000 }
            );

            sendTimer = setInterval(() => {
                if (lastPosition) sendPosition(lastPosition);
            }, SEND_INTERVAL);

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    lastPosition = position;
                    sendPosition(position);
                },
                () => {},
                { enableHighAccuracy: true }
            );

            setTrackingState(true);
            updateStatusBar(true);
            console.log('[Tracking] 🟢 Avviato');
            return true;
        }

        function stopTracking() {
            if (watchId) {
                navigator.geolocation.clearWatch(watchId);
                watchId = null;
            }

            if (sendTimer) {
                clearInterval(sendTimer);
                sendTimer = null;
            }

            if (noSleep) noSleep.disable();

            if (lastPosition) sendPosition(lastPosition);

            setTrackingState(false);
            updateStatusBar(false);
            console.log('[Tracking] 🔴 Fermato');
        }

        function updateStatusBar(active) {
            let statusBar = document.getElementById('persistentTrackingStatus');

            if (!statusBar) {
                statusBar = document.createElement('div');
                statusBar.id = 'persistentTrackingStatus';
                statusBar.style.cssText = `
                position: fixed;
                bottom: 70px;
                left: 50%;
                transform: translateX(-50%);
                padding: 8px 20px;
                border-radius: 25px;
                font-size: 14px;
                font-weight: 500;
                z-index: 9999;
                display: flex;
                align-items: center;
                gap: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.2);
                transition: all 0.3s ease;
            `;
                document.body.appendChild(statusBar);
            }

            if (active) {
                statusBar.style.background = '#27ae60';
                statusBar.style.color = 'white';
                statusBar.innerHTML = '<span style="display:inline-block;width:10px;height:10px;background:white;border-radius:50%;animation:pulseTracking 1.5s infinite;"></span> Tracking attivo';
                statusBar.style.display = 'flex';
            } else {
                statusBar.style.display = 'none';
            }
        }

        // Stile animazione
        if (!document.getElementById('trackingPulseStyle')) {
            const style = document.createElement('style');
            style.id = 'trackingPulseStyle';
            style.textContent = '@keyframes pulseTracking { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }';
            document.head.appendChild(style);
        }

        // Esponi funzioni globali
        window.trackingManager = {
            start: startTracking,
            stop: stopTracking,
            isActive: isTrackingActive,
            getLastPosition: () => lastPosition
        };

        // Auto-ripristina tracking se era attivo
        document.addEventListener('DOMContentLoaded', () => {
            if (isTrackingActive()) {
                console.log('[Tracking] Ripristino tracking attivo...');
                startTracking();
            }
        });

        // Prima di chiudere pagina, invia ultima posizione
        window.addEventListener('beforeunload', () => {
            if (isTrackingActive() && lastPosition) {
                const data = JSON.stringify({
                    device_token: DEVICE_TOKEN,
                    lat: lastPosition.coords.latitude,
                    lng: lastPosition.coords.longitude,
                    speed: (lastPosition.coords.speed || 0) * 3.6,
                    accuracy: lastPosition.coords.accuracy || 0,
                    timestamp: Math.floor(Date.now() / 1000)
                });
                navigator.sendBeacon('/api/tracking/position', new Blob([data], {type: 'application/json'}));
            }
        });
    })();
</script>
<!-- ========== FINE TRACKING PERSISTENTE ========== -->

@yield('scripts')
</body>
</html>