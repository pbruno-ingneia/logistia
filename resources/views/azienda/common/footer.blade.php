
<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <script>document.write(new Date().getFullYear())</script> © Ingenia SRL.
            </div>
            <div class="col-sm-6">
                <div class="text-sm-end d-none d-sm-block">
                    Design & Develop by Ingenia SRL
                </div>
            </div>
        </div>
    </div>
</footer>
</div>
<!--start back-to-top-->
<button onclick="topFunction()" class="btn btn-danger btn-icon" id="back-to-top">
    <i class="ri-arrow-up-line"></i>
</button>

@if(session()->has('utente'))
    @php
        // Controlla se l'utente ha un dispositivo tracking associato
        $dispositivoTracking = DB::table('dispositivi_tracking')
            ->where('id_utente', session('utente')->id)
            ->where('is_active', 1)
            ->first();
    @endphp

    @if($dispositivoTracking)
        <!-- Tracking GPS Automatico -->
        <input type="hidden" id="fleetTrackerToken" value="{{ $dispositivoTracking->device_token }}">
        <script src="/js/fleet-tracker.js"></script>
    @endif
@endif
@if(session()->has('utente'))
    @php
        // Controlla se l'utente ha un dispositivo tracking associato
        $dispositivoTracking = \DB::table('dispositivi_tracking')
            ->where('id_utente', session('utente')->id)
            ->where('is_active', 1)
            ->first();
    @endphp

    @if($dispositivoTracking)
        <input type="hidden" id="fleetTrackerToken" value="{{ $dispositivoTracking->device_token }}">
        <script src="/js/fleet-tracker.js"></script>

        <!-- NoSleep.js per iOS -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/nosleep/0.12.0/NoSleep.min.js"></script>

        <!-- Badge tracking con DEBUG -->
        <div id="trackingBadgeGlobal" style="...">
            ...
        </div>

        <script>
            // ============================================
            // NO SLEEP - Tiene schermo acceso (iOS + Android)
            // ============================================
            const noSleep = new NoSleep();
            let noSleepAttivo = false;

            function attivaNoSleep() {
                if (!noSleepAttivo) {
                    noSleep.enable();
                    noSleepAttivo = true;
                    addDebug('NoSleep ATTIVO - schermo resterà acceso');
                }
            }

            // Attiva al primo tocco (richiesto da iOS)
            document.addEventListener('touchstart', attivaNoSleep, { once: true });
            document.addEventListener('click', attivaNoSleep, { once: true });

            // Riattiva quando pagina torna visibile
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible') {
                    attivaNoSleep();
                }
            });

        <!-- Badge tracking fisso in basso -->
        <div id="trackingBadgeGlobal" style="
            position: fixed;
            bottom: 15px;
            right: 15px;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            z-index: 9999;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            background: #f39c12;
            color: white;
        ">
            <i class="ri-gps-line"></i> <span id="trackingTextGlobal">GPS...</span>
        </div>

        <script>
            // ============================================
            // WAKE LOCK - Tiene schermo acceso
            // ============================================
            let wakeLock = null;

            async function requestWakeLock() {
                try {
                    if ('wakeLock' in navigator) {
                        wakeLock = await navigator.wakeLock.request('screen');
                        console.log('[Logistia] Wake Lock ATTIVO - schermo resterà acceso');

                        wakeLock.addEventListener('release', () => {
                            console.log('[Logistia] Wake Lock rilasciato');
                            // Riprova a ottenere il wake lock
                            setTimeout(requestWakeLock, 1000);
                        });
                    } else {
                        console.log('[Logistia] Wake Lock non supportato da questo browser');
                    }
                } catch (err) {
                    console.log('[Logistia] Wake Lock errore:', err.message);
                }
            }

            // Richiedi wake lock all'avvio
            requestWakeLock();

            // Richiedi di nuovo se la pagina torna visibile
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible' && !wakeLock) {
                    requestWakeLock();
                }
            });

            // ============================================
            // AGGIORNA BADGE TRACKING
            // ============================================
            function updateTrackingBadge(status, text) {
                const badge = document.getElementById('trackingBadgeGlobal');
                const textEl = document.getElementById('trackingTextGlobal');

                if (!badge || !textEl) return;

                textEl.textContent = text;

                if (status === 'active') {
                    badge.style.background = '#27ae60';
                } else if (status === 'error') {
                    badge.style.background = '#e74c3c';
                } else {
                    badge.style.background = '#f39c12';
                }
            }

            // Controlla stato tracking ogni secondo
            setInterval(() => {
                if (typeof FleetTracker !== 'undefined') {
                    if (FleetTracker.state.isActive) {
                        updateTrackingBadge('active', 'GPS Attivo');
                    } else if (FleetTracker.state.needsSetup) {
                        updateTrackingBadge('warning', 'Setup richiesto');
                    } else {
                        updateTrackingBadge('warning', 'Connessione...');
                    }
                }
            }, 1000);

            // ============================================
            // AVVISO SE CHIUDE IL BROWSER
            // ============================================
            window.addEventListener('beforeunload', (e) => {
                if (typeof FleetTracker !== 'undefined' && FleetTracker.state.isActive) {
                    e.preventDefault();
                    e.returnValue = 'Il tracking GPS si fermerà se chiudi. Sei sicuro?';
                    return e.returnValue;
                }
            });
        </script>
    @endif
@endif
@if(session()->has('utente'))
    @php
        $dispositivoTracking = \DB::table('dispositivi_tracking')
            ->where('id_utente', session('utente')->id)
            ->where('is_active', 1)
            ->first();
    @endphp

    @if($dispositivoTracking)
        <input type="hidden" id="fleetTrackerToken" value="{{ $dispositivoTracking->device_token }}">
        <script src="/js/fleet-tracker.js"></script>

        <!-- Badge tracking con DEBUG -->
        <div id="trackingBadgeGlobal" style="
            position: fixed;
            bottom: 15px;
            right: 15px;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            z-index: 9999;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            background: #f39c12;
            color: white;
        " onclick="mostraDebug()">
            <i class="ri-gps-line"></i> <span id="trackingTextGlobal">GPS...</span>
        </div>

        <!-- Box debug (clicca sul badge per vedere) -->
        <div id="debugBox" style="
            display: none;
            position: fixed;
            bottom: 60px;
            right: 15px;
            left: 15px;
            max-height: 300px;
            overflow-y: auto;
            background: #1a1a1a;
            color: #0f0;
            padding: 15px;
            border-radius: 10px;
            font-family: monospace;
            font-size: 11px;
            z-index: 9999;
            white-space: pre-wrap;
        "></div>

        <script>
            const debugLog = [];

            function addDebug(msg) {
                const time = new Date().toLocaleTimeString('it-IT');
                debugLog.unshift(`[${time}] ${msg}`);
                if (debugLog.length > 50) debugLog.pop();

                const box = document.getElementById('debugBox');
                if (box) box.textContent = debugLog.join('\n');
            }

            function mostraDebug() {
                const box = document.getElementById('debugBox');
                box.style.display = box.style.display === 'none' ? 'block' : 'none';
            }

            // Log iniziale
            addDebug('Pagina caricata');
            addDebug('Token presente: ' + (document.getElementById('fleetTrackerToken') ? 'SI' : 'NO'));
            addDebug('Token: ' + (document.getElementById('fleetTrackerToken')?.value?.substring(0, 10) + '...'));
            addDebug('FleetTracker definito: ' + (typeof FleetTracker !== 'undefined' ? 'SI' : 'NO'));

            // Override FleetTracker per debug
            if (typeof FleetTracker !== 'undefined') {

                // Override checkStatus
                const originalCheckStatus = FleetTracker.checkStatus;
                FleetTracker.checkStatus = async function() {
                    addDebug('Chiamata API /status...');
                    try {
                        const result = await originalCheckStatus.call(this);
                        addDebug('Risposta /status: ' + JSON.stringify(result));
                        return result;
                    } catch (e) {
                        addDebug('ERRORE /status: ' + e.message);
                        throw e;
                    }
                };

                // Override sendPosition
                const originalSendPosition = FleetTracker.sendPosition;
                FleetTracker.sendPosition = async function(data) {
                    addDebug(`Invio posizione: ${data.lat?.toFixed(4)}, ${data.lng?.toFixed(4)} - ${data.speed} km/h`);
                    try {
                        const result = await originalSendPosition.call(this, data);
                        addDebug('Posizione inviata OK');
                        return result;
                    } catch (e) {
                        addDebug('ERRORE invio: ' + e.message);
                        throw e;
                    }
                };

                // Override showSetupModal
                const originalShowSetupModal = FleetTracker.showSetupModal;
                FleetTracker.showSetupModal = function() {
                    addDebug('>>> RICHIESTO SETUP KM INIZIALI');
                    originalShowSetupModal.call(this);
                };

                // Override start
                const originalStart = FleetTracker.start;
                FleetTracker.start = function() {
                    addDebug('Avvio tracking GPS...');
                    originalStart.call(this);
                };

                // Forza init manuale se non parte
                setTimeout(() => {
                    addDebug('Stato FleetTracker:');
                    addDebug('  - isActive: ' + FleetTracker.state.isActive);
                    addDebug('  - isConfigured: ' + FleetTracker.state.isConfigured);
                    addDebug('  - needsSetup: ' + FleetTracker.state.needsSetup);
                    addDebug('  - deviceToken: ' + (FleetTracker.state.deviceToken ? 'presente' : 'MANCANTE'));

                    if (!FleetTracker.state.deviceToken) {
                        const token = document.getElementById('fleetTrackerToken')?.value;
                        if (token) {
                            addDebug('Forzo init con token...');
                            FleetTracker.init(token);
                        }
                    }
                }, 2000);
            } else {
                addDebug('ERRORE: FleetTracker non caricato!');
            }

            // Aggiorna badge
            setInterval(() => {
                if (typeof FleetTracker !== 'undefined') {
                    const badge = document.getElementById('trackingBadgeGlobal');
                    const text = document.getElementById('trackingTextGlobal');

                    if (FleetTracker.state.isActive) {
                        badge.style.background = '#27ae60';
                        text.textContent = 'GPS Attivo';
                    } else if (FleetTracker.state.needsSetup) {
                        badge.style.background = '#e74c3c';
                        text.textContent = 'Setup KM!';
                    } else {
                        badge.style.background = '#f39c12';
                        text.textContent = 'Connessione...';
                    }
                }
            }, 1000);

            // Wake Lock
            async function requestWakeLock() {
                try {
                    if ('wakeLock' in navigator) {
                        const wl = await navigator.wakeLock.request('screen');
                        addDebug('Wake Lock ATTIVO');
                    }
                } catch (e) {
                    addDebug('Wake Lock errore: ' + e.message);
                }
            }
            requestWakeLock();
        </script>
    @else
        <!-- DEBUG: Nessun dispositivo trovato -->
        <div style="position:fixed;bottom:15px;right:15px;background:#e74c3c;color:white;padding:10px;border-radius:10px;font-size:12px;z-index:9999;">
            ⚠️ Nessun dispositivo tracking per utente ID: {{ session('utente')->id }}
        </div>
    @endif
@endif
<!-- JAVASCRIPT -->
<script src="/default/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/default/assets/libs/simplebar/simplebar.min.js"></script>
<script src="/default/assets/libs/node-waves/waves.min.js"></script>
<script src="/default/assets/libs/feather-icons/feather.min.js"></script>
<script src="/default/assets/js/pages/plugins/lord-icon-2.1.0.js"></script>
<script src="/default/assets/js/plugins.js"></script>

<!--jquery cdn-->
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<!--select2 cdn-->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="/default/assets/js/pages/select2.init.js"></script>

<!-- App js -->
<script src="/default/assets/js/app.js"></script>

<script src="/default/assets/js/OneSignalSDKWorker.js"></script>


<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<!--jquery cdn-->



</body>

</html>

<script type="text/javascript">
    $(document).ready(function() {
        // Reinizializza select2 quando la modal viene aperta
        $('#modal_aggiungi').on('shown.bs.modal', function () {
            $('.js-example-basic-multiple').select2({
                width: '100%'
            });
        });
    });

    $(function () {
        $('.datatable').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": false,
            "info": true,
            "autoWidth": true,
            "responsive": true,
            "scrollX": true,
            "stateSave": true,

            "oLanguage": {
                "sLengthMenu": "<span> Risultati :</span> _MENU_",
                "oPaginate": {"sFirst": "Primo", "sLast": "Ultimo", "sNext": ">", "sPrevious": "<"}
            },

            "columnDefs": [
                {targets: 'no-sort', orderable: false}
            ]
        });
    });





</script>
