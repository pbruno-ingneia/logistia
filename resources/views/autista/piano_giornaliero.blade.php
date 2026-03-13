@extends('autista.common.layout')

@section('title', 'Piano Giornaliero')

@section('styles')
    <style>
        /* ===== PIANO GIORNALIERO ===== */
        .piano-header {
            display: flex; align-items: center; gap: 12px; margin-bottom: 20px;
        }
        .piano-header h4 { margin: 0; font-weight: 700; font-size: 1.2rem; }
        .piano-header .subtitle { font-size: 0.82rem; color: var(--text-muted); margin-top: 2px; }

        /* Date */
        .date-bar {
            display: flex; align-items: center; gap: 10px; margin-bottom: 16px;
        }
        .date-bar input[type="date"] {
            flex: 1; border: 2px solid #e2e8f0; border-radius: var(--radius-md);
            padding: 10px 14px; font-size: 0.95rem; font-weight: 600;
            font-family: inherit; color: var(--text-dark); background: white;
        }
        .date-bar input:focus { border-color: var(--primary-color); outline: none; }

        /* Stats */
        .piano-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 16px; }
        .p-stat {
            background: white; border-radius: var(--radius-lg); padding: 16px 10px;
            text-align: center; box-shadow: var(--shadow-sm);
        }
        .p-stat .val { font-size: 1.7rem; font-weight: 800; line-height: 1; }
        .p-stat .val.blue { color: var(--primary-color); }
        .p-stat .val.orange { color: var(--warning-color); }
        .p-stat .val.green { color: var(--success-color); }
        .p-stat .lbl { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); margin-top: 4px; }

        /* Origin input */
        .origin-section {
            background: white; border-radius: var(--radius-lg); padding: 16px;
            box-shadow: var(--shadow-sm); margin-bottom: 16px;
        }
        .origin-label { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); margin-bottom: 8px; display: flex; align-items: center; gap: 6px; }
        .origin-input {
            width: 100%; border: 2px solid #e2e8f0; border-radius: var(--radius-md);
            padding: 14px 16px; font-family: inherit; font-size: 1rem; font-weight: 500;
            color: var(--text-dark); background: var(--bg-light); transition: border-color .2s;
        }
        .origin-input:focus { border-color: var(--primary-color); outline: none; background: white; }
        .origin-input::placeholder { color: #94a3b8; font-weight: 400; }
        .pac-container { z-index: 99999 !important; }

        /* CTA */
        .btn-ottimizza {
            width: 100%; padding: 16px; border: none; border-radius: var(--radius-lg);
            font-family: inherit; font-size: 1rem; font-weight: 700; color: white; cursor: pointer;
            background: linear-gradient(135deg, var(--primary-color) 0%, #2980b9 100%);
            box-shadow: 0 4px 18px rgba(52,152,219,0.3);
            display: flex; align-items: center; justify-content: center; gap: 10px;
            transition: transform .12s; margin-bottom: 16px;
            -webkit-tap-highlight-color: transparent;
        }
        .btn-ottimizza:active { transform: scale(0.97); }
        .btn-ottimizza:disabled { opacity: .5; cursor: not-allowed; transform: none; }

        /* Route summary */
        .route-box {
            background: rgba(52,152,219,0.08); border: 1px solid rgba(52,152,219,0.2);
            border-radius: var(--radius-lg); padding: 16px; margin-bottom: 16px; display: none;
        }
        .route-box.show { display: block; animation: fadeSlide .35s ease; }
        .route-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .route-item { display: flex; align-items: center; gap: 10px; }
        .route-icon {
            width: 40px; height: 40px; border-radius: var(--radius-sm); background: white;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; color: var(--primary-color); box-shadow: var(--shadow-sm); flex-shrink: 0;
        }
        .route-val { font-size: 1rem; font-weight: 700; color: var(--primary-dark); line-height: 1.1; }
        .route-lbl { font-size: 0.65rem; color: var(--text-muted); font-weight: 500; }

        /* Tabs */
        .piano-tabs { display: flex; gap: 6px; margin-bottom: 16px; }
        .piano-tab {
            flex: 1; padding: 12px 6px; border: 2px solid transparent;
            background: white; border-radius: var(--radius-md);
            font-family: inherit; font-size: 0.82rem; font-weight: 600;
            color: var(--text-muted); cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 6px;
            transition: all .2s; -webkit-tap-highlight-color: transparent;
        }
        .piano-tab.active { color: var(--primary-color); border-color: var(--primary-color); background: rgba(52,152,219,0.06); }
        .piano-tab i { font-size: 1.1rem; }
        .tab-panel { display: none; }
        .tab-panel.active { display: block; animation: fadeSlide .3s ease; }
        @keyframes fadeSlide { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

        /* Delivery cards */
        .d-card {
            background: white; border-radius: var(--radius-lg); padding: 16px;
            box-shadow: var(--shadow-sm); margin-bottom: 12px; display: flex; gap: 14px;
        }
        .d-card .badge-num {
            width: 36px; height: 36px; border-radius: var(--radius-sm);
            background: var(--primary-color); color: white;
            font-size: 0.9rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .d-card .d-body { flex: 1; min-width: 0; }
        .d-card .d-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px; }
        .d-card .d-cliente { font-size: 1rem; font-weight: 700; }
        .d-card .d-ordine { font-size: 0.7rem; color: var(--text-muted); background: var(--bg-light); padding: 3px 8px; border-radius: 6px; font-weight: 600; white-space: nowrap; }
        .d-card .d-row { display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: #64748b; margin-top: 4px; }
        .d-card .d-row i { color: #94a3b8; width: 16px; text-align: center; flex-shrink: 0; }
        .d-card .d-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; padding-top: 10px; border-top: 1px solid #f1f5f9; }
        .d-chip { display: inline-flex; align-items: center; gap: 4px; font-size: 0.75rem; color: #64748b; font-weight: 500; }
        .d-card .d-actions { display: flex; gap: 8px; margin-top: 12px; }
        .d-card .d-actions a {
            flex: 1; padding: 10px 12px; border-radius: var(--radius-sm);
            text-decoration: none; font-size: 0.82rem; font-weight: 600;
            display: flex; align-items: center; justify-content: center; gap: 6px;
            -webkit-tap-highlight-color: transparent;
        }
        .btn-nav { background: var(--primary-color); color: white; }
        .btn-nav:hover { color: white; }
        .btn-call { background: var(--bg-light); color: var(--text-dark); border: 1px solid #e2e8f0; }

        /* Loading cards */
        .l-card {
            background: white; border-radius: var(--radius-lg); padding: 16px;
            box-shadow: var(--shadow-sm); margin-bottom: 12px; display: flex; gap: 14px;
        }
        .l-card .badge-num {
            width: 36px; height: 36px; border-radius: var(--radius-sm);
            background: var(--warning-color); color: white;
            font-size: 0.9rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .l-card .l-body { flex: 1; min-width: 0; }
        .l-tag {
            display: inline-block; font-size: 0.65rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .04em; padding: 3px 8px; border-radius: 6px; margin-bottom: 6px;
        }
        .l-tag.t-first { background: #fff7ed; color: #ea580c; }
        .l-tag.t-mid { background: var(--bg-light); color: var(--text-muted); }
        .l-tag.t-last { background: #f0fdf4; color: var(--success-color); }

        /* Truck viz */
        .truck-wrap {
            background: white; border-radius: var(--radius-lg);
            border: 2px dashed #e2e8f0; padding: 18px; margin-bottom: 16px; box-shadow: var(--shadow-sm);
        }
        .truck-top { text-align: center; margin-bottom: 12px; }
        .truck-top i { font-size: 2rem; color: #94a3b8; }
        .truck-top span { display: block; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--text-muted); margin-top: 2px; }
        .truck-edge {
            text-align: center; font-size: 0.65rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .08em; padding: 8px; border-radius: var(--radius-sm);
        }
        .truck-edge.fondo { background: var(--bg-light); color: var(--text-muted); margin-bottom: 10px; }
        .truck-edge.porta { background: #f0fdf4; color: var(--success-color); margin-top: 10px; }
        .truck-row {
            background: var(--bg-light); border-radius: var(--radius-sm); padding: 10px 14px;
            margin-bottom: 6px; display: flex; align-items: center; gap: 10px;
            border-left: 3px solid var(--warning-color);
        }
        .truck-row .t-num { width: 24px; height: 24px; border-radius: 6px; background: var(--warning-color); color: white; font-size: 0.72rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .truck-row .t-name { font-size: 0.82rem; font-weight: 600; flex: 1; }
        .truck-row .t-merce { font-size: 0.7rem; color: var(--text-muted); }
        .truck-row .t-del { font-size: 0.6rem; font-weight: 700; color: var(--primary-color); background: rgba(52,152,219,0.1); padding: 2px 6px; border-radius: 4px; }
        .truck-arrow { text-align: center; color: var(--text-muted); font-size: 0.8rem; margin: 2px 0; opacity: .4; }

        /* Info banner */
        .info-box {
            padding: 14px 16px; border-radius: var(--radius-lg); margin-bottom: 16px;
            background: #fffbeb; border: 1px solid #fde68a;
            display: flex; align-items: flex-start; gap: 12px;
            font-size: 0.82rem; color: #92400e; line-height: 1.5;
        }
        .info-box i { font-size: 1.3rem; color: var(--warning-color); flex-shrink: 0; margin-top: 1px; }

        /* Map */
        #mappa-percorso {
            height: 380px; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);
            overflow: hidden; background: #e2e8f0;
        }
        @media (min-width: 768px) { #mappa-percorso { height: 500px; } }

        /* Section head */
        .sec-head { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: var(--text-muted); margin-bottom: 12px; margin-top: 4px; }

        /* Overlay */
        .overlay-load {
            position: fixed; inset: 0; background: rgba(44,62,80,0.75);
            backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);
            display: none; align-items: center; justify-content: center;
            z-index: 9999; flex-direction: column; gap: 18px;
        }
        .overlay-load.show { display: flex; }
        .ol-spinner { width: 44px; height: 44px; border: 3px solid rgba(255,255,255,0.2); border-top-color: white; border-radius: 50%; animation: olSpin .7s linear infinite; }
        .ol-text { color: white; font-size: 0.95rem; font-weight: 600; }
        @keyframes olSpin { to { transform: rotate(360deg); } }

        /* FAB */
        .fab-go {
            position: fixed; bottom: calc(80px + env(safe-area-inset-bottom) + 10px); right: 20px;
            width: 56px; height: 56px; border-radius: 50%;
            background: var(--success-color); color: white; border: none;
            font-size: 1.4rem; box-shadow: 0 4px 18px rgba(39,174,96,0.35);
            display: none; align-items: center; justify-content: center; z-index: 90; cursor: pointer;
            -webkit-tap-highlight-color: transparent;
        }
        .fab-go.show { display: flex; }
        .fab-go:active { transform: scale(0.92); }

        /* GPS Button */
        .origin-row { display: flex; gap: 10px; align-items: stretch; }
        .origin-row .origin-input { flex: 1; }
        .btn-gps {
            width: 52px; border: 2px solid #e2e8f0; border-radius: var(--radius-md);
            background: white; color: var(--primary-color); font-size: 1.3rem;
            display: flex; align-items: center; justify-content: center; cursor: pointer;
            transition: all .2s; flex-shrink: 0;
        }
        .btn-gps:active { background: var(--primary-color); color: white; border-color: var(--primary-color); }
        .btn-gps.loading { opacity: .5; pointer-events: none; }
        .btn-gps.success { border-color: var(--success-color); color: var(--success-color); }

        /* Status badge on d-card */
        .d-stato {
            padding: 3px 10px; border-radius: 12px; font-size: 0.7rem;
            font-weight: 700; white-space: nowrap; text-transform: uppercase; letter-spacing: .03em;
        }
        .d-stato.pianificato, .d-stato.assegnato { background: rgba(243,156,18,0.12); color: var(--warning-color); }
        .d-stato.in_corso { background: rgba(52,152,219,0.12); color: var(--primary-color); }
        .d-stato.completato { background: rgba(39,174,96,0.12); color: var(--success-color); }
        .d-stato.annullato { background: rgba(231,76,60,0.12); color: #e74c3c; }

        /* Signature */
        .signature-container {
            border: 2px dashed #ddd; border-radius: 8px; background: #fafafa;
            overflow: hidden; position: relative;
        }
        .signature-canvas { width: 100%; height: 120px; display: block; touch-action: none; }
        .signature-fullscreen-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: white; z-index: 9999; display: none; flex-direction: column;
        }
        .signature-fullscreen-overlay.active { display: flex; }
        .signature-fullscreen-header {
            background: var(--primary-color); color: white; padding: 15px 20px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .signature-fullscreen-header h5 { margin: 0; font-size: 1.1rem; }
        .signature-fullscreen-header .btn-close-fs {
            background: rgba(255,255,255,0.2); border: none; color: white;
            width: 40px; height: 40px; border-radius: 50%; font-size: 1.3rem;
            display: flex; align-items: center; justify-content: center;
        }
        .signature-fullscreen-body {
            flex: 1; display: flex; flex-direction: column; padding: 20px; background: #f5f5f5;
        }
        .signature-fullscreen-canvas-container {
            flex: 1; background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden; display: flex; align-items: center; justify-content: center;
        }
        .signature-fullscreen-canvas { width: 100%; height: 100%; display: block; touch-action: none; }
        .signature-fullscreen-hint { text-align: center; padding: 15px; color: #666; font-size: 0.9rem; }
        .signature-fullscreen-footer {
            display: flex; gap: 10px; padding: 15px 20px; background: white; border-top: 1px solid #eee;
        }
        .signature-fullscreen-footer .btn-fs {
            flex: 1; padding: 14px 20px; border-radius: 10px; border: none; font-weight: 600;
            font-size: 1rem; display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .signature-fullscreen-footer .btn-clear { background: #f0f0f0; color: #666; }
        .signature-fullscreen-footer .btn-confirm { background: linear-gradient(135deg, #27ae60 0%, #1e8449 100%); color: white; }
        .btn-expand-signature {
            position: absolute; top: 5px; right: 5px; background: rgba(0,0,0,0.5);
            border: none; color: white; width: 30px; height: 30px; border-radius: 6px;
            display: flex; align-items: center; justify-content: center; font-size: 1rem;
            cursor: pointer; z-index: 10;
        }
    </style>
@endsection

@section('content')
    <div class="fade-in">

        <!-- Loading Overlay -->
        <div class="overlay-load" id="loadingOverlay">
            <div class="ol-spinner"></div>
            <p class="ol-text" id="loadingText">Calcolo percorso ottimizzato...</p>
        </div>

        <!-- Piano Header -->
        <div class="piano-header">
            <div>
                <h4><i class="ri-route-line text-primary"></i> Piano Giornaliero</h4>
                <div class="subtitle">
                    @if($mezzo) {{ $mezzo->nome }} &bull; {{ $mezzo->targa }} @else Nessun mezzo assegnato @endif
                </div>
            </div>
        </div>

        <!-- Data -->
        <div class="date-bar">
            <i class="ri-calendar-line" style="font-size:1.2rem; color:var(--primary-color);"></i>
            <input type="date" id="dataGiorno" value="{{ $data }}" onchange="cambiaData(this.value)">
        </div>

        <!-- Stats -->
        <div class="piano-stats">
            <div class="p-stat"><div class="val blue" id="statConsegne">{{ count($ordini) }}</div><div class="lbl">Consegne</div></div>
            <div class="p-stat"><div class="val orange" id="statKm">--</div><div class="lbl">Km totali</div></div>
            <div class="p-stat"><div class="val green" id="statTempo">--</div><div class="lbl">Tempo</div></div>
        </div>

        @if(count($ordini) > 0)

            <!-- Partenza -->
            <div class="origin-section">
                <div class="origin-label"><i class="ri-map-pin-2-fill"></i> Punto di partenza</div>
                <div class="origin-row">
                    <input class="origin-input" type="text" id="puntoPartenza" placeholder="Inserisci indirizzo di partenza..." autocomplete="off">
                    <button class="btn-gps" id="btnGps" onclick="usaPosizioneAttuale()" title="Usa posizione attuale">
                        <i class="ri-crosshair-2-line" id="gpsIcon"></i>
                    </button>
                </div>
            </div>

            <!-- CTA -->
            <button class="btn-ottimizza" id="btnOttimizza" onclick="ottimizzaPercorso()">
                <i class="ri-route-line"></i> Calcola Percorso Ottimizzato
            </button>

            <!-- Route Summary -->
            <div class="route-box" id="routeSummary">
                <div class="route-grid">
                    <div class="route-item">
                        <div class="route-icon"><i class="ri-pin-distance-line"></i></div>
                        <div><div class="route-val" id="summaryKm">--</div><div class="route-lbl">Distanza</div></div>
                    </div>
                    <div class="route-item">
                        <div class="route-icon"><i class="ri-time-line"></i></div>
                        <div><div class="route-val" id="summaryTempo">--</div><div class="route-lbl">Tempo guida</div></div>
                    </div>
                    <div class="route-item">
                        <div class="route-icon"><i class="ri-stack-line"></i></div>
                        <div><div class="route-val">{{ count($ordini) }}</div><div class="route-lbl">Fermate</div></div>
                    </div>
                    <div class="route-item">
                        <div class="route-icon"><i class="ri-gas-station-line"></i></div>
                        <div><div class="route-val" id="summaryCarburante">--</div><div class="route-lbl">Carburante</div></div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="piano-tabs">
                <button class="piano-tab active" onclick="switchTab('percorso')"><i class="ri-route-line"></i> Percorso</button>
                <button class="piano-tab" onclick="switchTab('carico')"><i class="ri-truck-line"></i> Carico</button>
                <button class="piano-tab" onclick="switchTab('mappa')"><i class="ri-map-2-line"></i> Mappa</button>
            </div>

            <!-- TAB: PERCORSO -->
            <div class="tab-panel active" id="panelPercorso">
                <div class="sec-head">Ordine consegne</div>
                <div id="listaConsegne">
                    @foreach($ordini as $index => $ordine)
                        <div class="d-card" data-id="{{ $ordine->id }}"
                             data-indirizzo="{{ $ordine->indirizzo_consegna }}"
                             data-cliente="{{ $ordine->cliente_nome }}"
                             data-merce="{{ $ordine->descrizione_merce }}"
                             data-peso="{{ $ordine->peso_kg }}"
                             data-ordine="{{ $ordine->numero_ordine }}"
                             data-telefono="{{ $ordine->cliente_telefono ?? '' }}"
                             data-stato="{{ $ordine->stato ?? 'pianificato' }}">
                            <div class="badge-num">{{ $index + 1 }}</div>
                            <div class="d-body">
                                <div class="d-top">
                                    <div class="d-cliente">{{ $ordine->cliente_nome ?? 'Cliente' }}</div>
                                    <div style="display:flex; gap:6px; align-items:center;">
                                        @php
                                            $stato = $ordine->stato ?? 'pianificato';
                                            $statoLabel = ['pianificato'=>'Pianificato','assegnato'=>'Assegnato','in_corso'=>'In corso','completato'=>'Completato','annullato'=>'Annullato'][$stato] ?? $stato;
                                        @endphp
                                        <span class="d-stato {{ $stato }}">{{ $statoLabel }}</span>
                                        <div class="d-ordine">{{ $ordine->numero_ordine }}</div>
                                    </div>
                                </div>
                                <div class="d-row"><i class="ri-map-pin-line"></i> <span>{{ $ordine->indirizzo_consegna }}</span></div>
                                <div class="d-row"><i class="ri-box-3-line"></i> <span>{{ $ordine->descrizione_merce }}</span></div>
                                @if($ordine->ora_consegna)
                                    <div class="d-row"><i class="ri-time-line"></i> <span>Entro le {{ $ordine->ora_consegna }}</span></div>
                                @endif
                                <div class="d-chips">
                                    @if($ordine->peso_kg)<div class="d-chip"><i class="ri-scales-line"></i> {{ $ordine->peso_kg }} kg</div>@endif
                                </div>
                                <div class="d-actions">
                                    @if($stato == 'pianificato' || $stato == 'assegnato')
                                        <a href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($ordine->indirizzo_consegna) }}" target="_blank" class="btn-nav"><i class="ri-navigation-line"></i> Naviga</a>
                                        @if($ordine->cliente_telefono)
                                            <a href="tel:{{ $ordine->cliente_telefono }}" class="btn-call"><i class="ri-phone-line"></i> Chiama</a>
                                        @endif
                                        <a href="#" class="btn-nav" style="background:var(--success-color);" onclick="event.preventDefault();iniziaConsegnaPiano({{ $ordine->id }})"><i class="ri-play-line"></i> Inizia</a>
                                    @elseif($stato == 'in_corso')
                                        <a href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($ordine->indirizzo_consegna) }}" target="_blank" class="btn-nav"><i class="ri-navigation-line"></i> Naviga</a>
                                        <a href="#" class="btn-call" onclick="event.preventDefault();apriUploadFoto({{ $ordine->id }})" style="flex:none;width:40px;"><i class="ri-camera-line"></i></a>
                                        <a href="#" class="btn-nav" style="background:var(--success-color);" onclick="event.preventDefault();completaConsegnaPiano({{ $ordine->id }})"><i class="ri-check-line"></i> Completa</a>
                                        <a href="#" class="btn-call" onclick="event.preventDefault();rinviaConsegnaPiano({{ $ordine->id }},'{{ addslashes($ordine->numero_ordine) }}')" title="Rinvia"><i class="ri-calendar-todo-line" style="color:var(--warning-color);"></i></a>
                                        <a href="#" class="btn-call" onclick="event.preventDefault();annullaConsegnaPiano({{ $ordine->id }},'{{ addslashes($ordine->numero_ordine) }}')" title="Annulla"><i class="ri-close-circle-line" style="color:#e74c3c;"></i></a>
                                    @elseif($stato == 'completato')
                                        <a href="/autista/ordine/{{ $ordine->id }}/completato" class="btn-nav" style="background:var(--success-color);"><i class="ri-file-text-line"></i> Vedi DDT</a>
                                        <a href="/autista/ordine/{{ $ordine->id }}/completato" class="btn-call" style="flex:none;width:40px;"><i class="ri-share-line" style="color:var(--success-color);"></i></a>
                                    @elseif($stato == 'annullato')
                                        <span class="btn-call" style="opacity:.5;text-align:center;"><i class="ri-close-circle-line" style="color:#e74c3c;"></i> Annullata</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- TAB: CARICO -->
            <div class="tab-panel" id="panelCarico">
                <div class="info-box">
                    <i class="ri-lightbulb-line"></i>
                    <div><strong>Piano di carico:</strong> segui l'ordine numerico. Carica per primo la merce dell'ultima consegna (fondo furgone), per ultimo la merce della prima consegna (vicino alla porta).</div>
                </div>
                <div class="truck-wrap">
                    <div class="truck-top"><i class="ri-truck-line"></i><span>Vano di carico</span></div>
                    <div class="truck-edge fondo"><i class="ri-arrow-up-s-line"></i> Cabina / Fondo</div>
                    <div id="truckSlots">
                        <div style="text-align:center; padding:30px 0; color:var(--text-muted);">
                            <i class="ri-route-line" style="font-size:2rem; opacity:.4;"></i>
                            <p style="font-size:0.85rem; margin-top:8px;">Calcola il percorso per generare il piano</p>
                        </div>
                    </div>
                    <div class="truck-edge porta"><i class="ri-arrow-down-s-line"></i> Porta Posteriore</div>
                </div>
                <div id="listaCarico"></div>
            </div>

            <!-- TAB: MAPPA -->
            <div class="tab-panel" id="panelMappa">
                <div class="sec-head">Mappa percorso</div>
                <div id="mappa-percorso"></div>
                <div id="mapPlaceholder" style="text-align:center; padding:40px 20px;">
                    <i class="ri-map-2-line" style="font-size:2.5rem; color:#d0d0d0;"></i>
                    <p style="font-size:0.85rem; color:var(--text-muted); margin-top:8px;">Calcola il percorso per visualizzare la mappa</p>
                </div>
            </div>

            <!-- FAB Navigate -->
            <button class="fab-go" id="fabNavigate" onclick="apriNavigazioneTotale()" title="Apri navigazione completa"><i class="ri-navigation-fill"></i></button>

            <!-- Modal Annulla Consegna -->
            <div class="modal fade" id="annullaModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content" style="border-radius: var(--radius-lg);">
                        <div class="modal-header border-0">
                            <h5 class="modal-title text-danger"><i class="ri-close-circle-line me-2"></i>Annulla Consegna</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="annullaConsegnaId">
                            <div class="alert alert-warning"><i class="ri-error-warning-line me-2"></i>Stai per annullare l'ordine <strong id="annullaNumOrd"></strong></div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Motivo annullamento <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="motivoAnnullamento" rows="2" placeholder="Motivo..." required></textarea>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold mb-0"><i class="ri-user-line me-1"></i>Firma Cliente</label>
                                    <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="clearSigAnn('Cliente')"><i class="ri-delete-bin-line"></i></button>
                                </div>
                                <div class="signature-container">
                                    <button type="button" class="btn-expand-signature" onclick="openFsSig('AnnCliente')" title="Ingrandisci"><i class="ri-fullscreen-line"></i></button>
                                    <canvas id="sigAnnCliente" class="signature-canvas"></canvas>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold mb-0"><i class="ri-steering-line me-1"></i>Firma Autista</label>
                                    <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="clearSigAnn('Autista')"><i class="ri-delete-bin-line"></i></button>
                                </div>
                                <div class="signature-container">
                                    <button type="button" class="btn-expand-signature" onclick="openFsSig('AnnAutista')" title="Ingrandisci"><i class="ri-fullscreen-line"></i></button>
                                    <canvas id="sigAnnAutista" class="signature-canvas"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Indietro</button>
                            <button type="button" class="btn btn-danger" onclick="confermaAnnullamento()"><i class="ri-close-circle-line me-1"></i>Conferma</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Rinvia Consegna -->
            <div class="modal fade" id="rinviaModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content" style="border-radius: var(--radius-lg);">
                        <div class="modal-header border-0">
                            <h5 class="modal-title text-warning"><i class="ri-calendar-todo-line me-2"></i>Rinvia Consegna</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="rinviaConsegnaId">
                            <div class="alert alert-info"><i class="ri-information-line me-2"></i>Stai per rinviare l'ordine <strong id="rinviaNumOrd"></strong></div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nuova data <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="nuovaDataConsegna" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nuova ora (opzionale)</label>
                                <input type="time" class="form-control" id="nuovaOraConsegna">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Motivo rinvio <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="motivoRinvio" rows="2" placeholder="Motivo..." required></textarea>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold mb-0"><i class="ri-user-line me-1"></i>Firma Cliente</label>
                                    <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="clearSigRin('Cliente')"><i class="ri-delete-bin-line"></i></button>
                                </div>
                                <div class="signature-container">
                                    <button type="button" class="btn-expand-signature" onclick="openFsSig('RinCliente')" title="Ingrandisci"><i class="ri-fullscreen-line"></i></button>
                                    <canvas id="sigRinCliente" class="signature-canvas"></canvas>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold mb-0"><i class="ri-steering-line me-1"></i>Firma Autista</label>
                                    <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="clearSigRin('Autista')"><i class="ri-delete-bin-line"></i></button>
                                </div>
                                <div class="signature-container">
                                    <button type="button" class="btn-expand-signature" onclick="openFsSig('RinAutista')" title="Ingrandisci"><i class="ri-fullscreen-line"></i></button>
                                    <canvas id="sigRinAutista" class="signature-canvas"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Indietro</button>
                            <button type="button" class="btn btn-warning" onclick="confermaRinvio()"><i class="ri-calendar-todo-line me-1"></i>Conferma</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Upload Foto -->
            <div class="modal fade" id="modalUploadFoto" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="border-radius: var(--radius-lg);">
                        <div class="modal-header border-0">
                            <h5 class="modal-title"><i class="ri-camera-line text-primary me-2"></i>Aggiungi Foto</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <input type="hidden" id="foto_id_ordine">
                            <div class="mb-3">
                                <select id="foto_tipo" class="form-select">
                                    <option value="merce">📦 Merce</option>
                                    <option value="ricevuta">📄 Ricevuta</option>
                                    <option value="danno">⚠️ Danno</option>
                                    <option value="altro">📎 Altro</option>
                                </select>
                            </div>
                            <label class="btn btn-primary btn-lg w-100 mb-3" style="padding:15px;">
                                <i class="ri-camera-line me-2"></i> Scatta Foto
                                <input type="file" accept="image/*" capture="environment" onchange="uploadFotoSingola(this)" style="display:none;">
                            </label>
                            <label class="btn btn-outline-secondary w-100 mb-3" style="padding:12px;">
                                <i class="ri-image-line me-2"></i> Galleria
                                <input type="file" accept="image/*" onchange="uploadFotoSingola(this)" style="display:none;">
                            </label>
                            <div id="foto_upload_status"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fullscreen Signature Overlay -->
            <div class="signature-fullscreen-overlay" id="sigFullscreen">
                <div class="signature-fullscreen-header">
                    <h5 id="fsSigTitle">Firma</h5>
                    <button class="btn-close-fs" onclick="closeFsSig()"><i class="ri-close-line"></i></button>
                </div>
                <div class="signature-fullscreen-body">
                    <div class="signature-fullscreen-canvas-container">
                        <canvas id="fsSigCanvas" class="signature-fullscreen-canvas"></canvas>
                    </div>
                    <div class="signature-fullscreen-hint"><i class="ri-hand-coin-line me-1"></i>Firma con il dito nell'area sopra</div>
                </div>
                <div class="signature-fullscreen-footer">
                    <button class="btn-fs btn-clear" onclick="clearFsSig()"><i class="ri-delete-bin-line"></i> Cancella</button>
                    <button class="btn-fs btn-confirm" onclick="confirmFsSig()"><i class="ri-check-line"></i> Conferma</button>
                </div>
            </div>

        @else
            <div class="empty-state" style="padding:60px 20px;">
                <i class="ri-inbox-unarchive-line"></i>
                <p>Nessuna consegna assegnata per il <strong>{{ \Carbon\Carbon::parse($data)->format('d/m/Y') }}</strong></p>
            </div>
        @endif

    </div>
@endsection

@section('scripts')
    <!-- Google Maps JS API -->
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB0Kta9cMMAOEcpcGl0hwXij0I6_gqWeLM&loading=async&libraries=places&callback=initGoogleMaps"></script>

    <script>
        let consegneOttimizzate = [];
        let pianoCarico = [];
        let mapInstance = null;
        let googleReady = false;
        let geocoder = null;
        window._consegneCompletate = [];

        function initGoogleMaps() {
            googleReady = true;
            geocoder = new google.maps.Geocoder();
            const input = document.getElementById('puntoPartenza');
            if (input) {
                try {
                    const ac = new google.maps.places.Autocomplete(input, { types:['address'], componentRestrictions:{country:'it'} });
                    ac.addListener('place_changed', () => {});
                } catch(e) { console.warn('Autocomplete fallback:', e.message); }
                input.addEventListener('keydown', e => { if(e.key==='Enter') e.preventDefault(); });
            }
        }

        function switchTab(tab) {
            document.querySelectorAll('.piano-tab').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
            document.querySelector(`[onclick="switchTab('${tab}')"]`).classList.add('active');
            document.getElementById({percorso:'panelPercorso',carico:'panelCarico',mappa:'panelMappa'}[tab]).classList.add('active');
            if(tab==='mappa' && consegneOttimizzate.length>0 && !mapInstance) setTimeout(renderMappa,200);
        }

        function cambiaData(d){ window.location.href='/autista/piano-giornaliero?data='+d; }

        function haversineKm(lat1,lon1,lat2,lon2){
            const R=6371,dLat=(lat2-lat1)*Math.PI/180,dLon=(lon2-lon1)*Math.PI/180;
            const a=Math.sin(dLat/2)**2+Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLon/2)**2;
            return R*2*Math.atan2(Math.sqrt(a),Math.sqrt(1-a));
        }
        function distStrada(km){ return km*1.35; }
        function tempoMin(km){ return Math.round(km/50*60); }

        function geocodifica(addr){
            return new Promise((ok,ko)=>{
                geocoder.geocode({address:addr,region:'it'},(res,st)=>{
                    if(st==='OK'&&res[0]) ok({lat:res[0].geometry.location.lat(),lng:res[0].geometry.location.lng()});
                    else ko(new Error('Indirizzo non trovato: '+addr));
                });
            });
        }

        async function ottimizzaPercorso(){
            const partenza=document.getElementById('puntoPartenza').value.trim();
            if(!partenza){alert('Inserisci l\'indirizzo di partenza');document.getElementById('puntoPartenza').focus();return;}
            if(!googleReady){alert('Google Maps non ancora pronto');return;}

            const cards=document.querySelectorAll('#listaConsegne .d-card');
            const tutteConsegne=[];
            cards.forEach(c=>tutteConsegne.push({id:c.dataset.id,indirizzo:c.dataset.indirizzo,cliente:c.dataset.cliente,merce:c.dataset.merce,peso:c.dataset.peso,ordine:c.dataset.ordine,telefono:c.dataset.telefono,stato:c.dataset.stato||'pianificato'}));
            if(!tutteConsegne.length)return;

            // Separa: da fare vs completate/annullate
            const consegne = tutteConsegne.filter(c => c.stato !== 'completato' && c.stato !== 'annullato');
            window._consegneCompletate = tutteConsegne.filter(c => c.stato === 'completato' || c.stato === 'annullato');

            if(!consegne.length){
                // Tutte completate, mostra solo lista senza percorso
                consegneOttimizzate = [];
                renderConsegne(); updateStats(0, 0);
                hideLoading(); document.getElementById('btnOttimizza').disabled=false;
                return;
            }

            showLoading('Geocodifica indirizzi...');
            document.getElementById('btnOttimizza').disabled=true;

            try{
                const indirizzi=[partenza,...consegne.map(c=>c.indirizzo)];
                const coords=[];
                for(let i=0;i<indirizzi.length;i++){
                    document.getElementById('loadingText').textContent=`Geocodifica ${i+1}/${indirizzi.length}...`;
                    try{coords.push(await geocodifica(indirizzi[i]));}
                    catch(e){alert(e.message);hideLoading();document.getElementById('btnOttimizza').disabled=false;return;}
                    if(i<indirizzi.length-1)await new Promise(r=>setTimeout(r,200));
                }

                document.getElementById('loadingText').textContent='Ottimizzazione percorso...';

                // Nearest Neighbor
                const nv=consegne.map((_,i)=>i+1);
                const ord=[];
                let pos=0,totKm=0;

                while(nv.length){
                    let best=-1,bestD=Infinity;
                    for(const idx of nv){const d=haversineKm(coords[pos].lat,coords[pos].lng,coords[idx].lat,coords[idx].lng);if(d<bestD){bestD=d;best=idx;}}
                    const km=distStrada(bestD);totKm+=km;
                    ord.push({idx:best-1,km,coord:coords[best]});
                    nv.splice(nv.indexOf(best),1);pos=best;
                }
                totKm+=distStrada(haversineKm(coords[pos].lat,coords[pos].lng,coords[0].lat,coords[0].lng));

                consegneOttimizzate=[];
                ord.forEach((o,i)=>{
                    const c={...consegne[o.idx]};
                    c.ordine_consegna=i+1;c.distanza_km=o.km.toFixed(1);
                    c.distanza_testo=o.km.toFixed(1)+' km';c.tempo_minuti=tempoMin(o.km);
                    c.tempo_testo=c.tempo_minuti+' min';c.coord=o.coord;
                    consegneOttimizzate.push(c);
                });

                pianoCarico=[...consegneOttimizzate].reverse();
                pianoCarico.forEach((it,i)=>it.ordine_carico=i+1);

                window._coordDeposito=coords[0];
                window._coordConsegne=ord.map(o=>o.coord);

                const totMin=tempoMin(totKm);
                renderConsegne();renderCarico();updateStats(totKm,totMin);

                if(mapInstance)mapInstance=null;
                document.getElementById('routeSummary').classList.add('show');
                document.getElementById('fabNavigate').classList.add('show');
                document.getElementById('btnOttimizza').innerHTML='<i class="ri-refresh-line"></i> Ricalcola Percorso';
                hideLoading();document.getElementById('btnOttimizza').disabled=false;
            }catch(err){hideLoading();document.getElementById('btnOttimizza').disabled=false;alert('Errore: '+err.message);}
        }

        function renderConsegne(){
            const el=document.getElementById('listaConsegne');el.innerHTML='';
            const statoLabels = {pianificato:'Pianificato',assegnato:'Assegnato',in_corso:'In corso',completato:'Completato',annullato:'Annullato'};

            // Prima: consegne da fare (ottimizzate)
            consegneOttimizzate.forEach((c,i)=>{
                const st = c.stato || 'pianificato';
                let actions = '';
                if (st === 'pianificato' || st === 'assegnato') {
                    actions = `<a href="https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(c.indirizzo)}" target="_blank" class="btn-nav"><i class="ri-navigation-line"></i> Naviga</a>
                ${c.telefono?`<a href="tel:${c.telefono}" class="btn-call"><i class="ri-phone-line"></i> Chiama</a>`:''}
                <a href="#" class="btn-nav" style="background:var(--success-color);" onclick="event.preventDefault();iniziaConsegnaPiano(${c.id})"><i class="ri-play-line"></i> Inizia</a>`;
                } else if (st === 'in_corso') {
                    actions = `<a href="https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(c.indirizzo)}" target="_blank" class="btn-nav"><i class="ri-navigation-line"></i> Naviga</a>
                <a href="#" class="btn-call" onclick="event.preventDefault();apriUploadFoto(${c.id})" style="flex:none;width:40px;"><i class="ri-camera-line"></i></a>
                <a href="#" class="btn-nav" style="background:var(--success-color);" onclick="event.preventDefault();completaConsegnaPiano(${c.id})"><i class="ri-check-line"></i> Completa</a>
                <a href="#" class="btn-call" onclick="event.preventDefault();rinviaConsegnaPiano(${c.id},'${(c.ordine||'').replace(/'/g,"\\'")}')"><i class="ri-calendar-todo-line" style="color:var(--warning-color);"></i></a>
                <a href="#" class="btn-call" onclick="event.preventDefault();annullaConsegnaPiano(${c.id},'${(c.ordine||'').replace(/'/g,"\\'")}')"><i class="ri-close-circle-line" style="color:#e74c3c;"></i></a>`;
                }
                el.innerHTML+=`
        <div class="d-card" data-stato="${st}">
            <div class="badge-num">${i+1}</div>
            <div class="d-body">
                <div class="d-top"><div class="d-cliente">${c.cliente||'Cliente'}</div><div style="display:flex;gap:6px;align-items:center;"><span class="d-stato ${st}">${statoLabels[st]||st}</span><div class="d-ordine">${c.ordine||'#'+c.id}</div></div></div>
                <div class="d-row"><i class="ri-map-pin-line"></i> <span>${c.indirizzo}</span></div>
                <div class="d-row"><i class="ri-box-3-line"></i> <span>${c.merce||'--'}</span></div>
                <div class="d-chips">
                    ${c.peso?`<div class="d-chip"><i class="ri-scales-line"></i> ${c.peso} kg</div>`:''}
                    <div class="d-chip"><i class="ri-pin-distance-line"></i> ~${c.distanza_testo}</div>
                    <div class="d-chip"><i class="ri-time-line"></i> ~${c.tempo_testo}</div>
                </div>
                <div class="d-actions">${actions}</div>
            </div>
        </div>`;
            });

            // Poi: completate e annullate in fondo
            const completate = window._consegneCompletate || [];
            if (completate.length) {
                el.innerHTML += `<div class="sec-head" style="margin-top:16px;">Completate / Annullate</div>`;
                completate.forEach((c) => {
                    const st = c.stato;
                    let actions = '';
                    if (st === 'completato') {
                        actions = `<a href="/autista/ordine/${c.id}/completato" class="btn-nav" style="background:var(--success-color);"><i class="ri-file-text-line"></i> Vedi DDT</a>
                    <a href="/autista/ordine/${c.id}/completato" class="btn-call" style="flex:none;width:40px;"><i class="ri-share-line" style="color:var(--success-color);"></i></a>`;
                    } else {
                        actions = `<span class="btn-call" style="opacity:.5;text-align:center;"><i class="ri-close-circle-line" style="color:#e74c3c;"></i> Annullata</span>`;
                    }
                    el.innerHTML += `
            <div class="d-card" data-stato="${st}" style="opacity:.75;">
                <div class="badge-num" style="background:${st==='completato'?'var(--success-color)':'#e74c3c'}"><i class="${st==='completato'?'ri-check-line':'ri-close-line'}" style="font-size:.9rem;"></i></div>
                <div class="d-body">
                    <div class="d-top"><div class="d-cliente">${c.cliente||'Cliente'}</div><div style="display:flex;gap:6px;align-items:center;"><span class="d-stato ${st}">${statoLabels[st]||st}</span><div class="d-ordine">${c.ordine||'#'+c.id}</div></div></div>
                    <div class="d-row"><i class="ri-map-pin-line"></i> <span>${c.indirizzo}</span></div>
                    <div class="d-row"><i class="ri-box-3-line"></i> <span>${c.merce||'--'}</span></div>
                    <div class="d-actions">${actions}</div>
                </div>
            </div>`;
                });
            }
        }

        function renderCarico(){
            const tot=pianoCarico.length;
            const ts=document.getElementById('truckSlots');ts.innerHTML='';
            pianoCarico.forEach((c,i)=>{
                ts.innerHTML+=`
        <div class="truck-row"><div class="t-num">${i+1}</div><div style="flex:1"><div class="t-name">${c.cliente||'Cliente'}</div><div class="t-merce">${c.merce||'--'} ${c.peso?'• '+c.peso+' kg':''}</div></div><div class="t-del">Cons. #${c.ordine_consegna}</div></div>
        ${i<tot-1?'<div class="truck-arrow"><i class="ri-arrow-down-s-line"></i></div>':''}`;
            });

            const lc=document.getElementById('listaCarico');
            lc.innerHTML='<div class="sec-head">Dettaglio caricamento</div>';
            pianoCarico.forEach((c,i)=>{
                let tc,tt;
                if(i===0){tc='t-first';tt='Carica per primo (fondo furgone)';}
                else if(i===tot-1){tc='t-last';tt='Carica per ultimo (vicino alla porta)';}
                else{tc='t-mid';tt=`Carica ${i+1}\u00B0 posizione`;}
                lc.innerHTML+=`
        <div class="l-card">
            <div class="badge-num">${i+1}</div>
            <div class="l-body">
                <div class="l-tag ${tc}">${tt}</div>
                <div class="d-top"><div class="d-cliente">${c.cliente||'Cliente'}</div><div class="d-ordine">Consegna #${c.ordine_consegna}</div></div>
                <div class="d-row"><i class="ri-map-pin-line"></i> <span>${c.indirizzo}</span></div>
                <div class="d-row"><i class="ri-box-3-line"></i> <span>${c.merce||'--'}</span></div>
                ${c.peso?`<div class="d-chips"><div class="d-chip"><i class="ri-scales-line"></i> ${c.peso} kg</div></div>`:''}
            </div>
        </div>`;
            });
        }

        function updateStats(km,min){
            const k=km.toFixed(1),h=Math.floor(min/60),m=min%60;
            const t=h>0?`${h}h${m}m`:`${m}min`;
            const l=(km*12/100).toFixed(1);
            document.getElementById('statKm').textContent='~'+k;
            document.getElementById('statTempo').textContent='~'+t;
            document.getElementById('summaryKm').textContent='~'+k+' km';
            document.getElementById('summaryTempo').textContent='~'+t;
            document.getElementById('summaryCarburante').textContent='~'+l+' L';
        }

        function renderMappa(){
            if(!window._coordDeposito||!window._coordConsegne)return;
            document.getElementById('mapPlaceholder').style.display='none';
            const el=document.getElementById('mappa-percorso');el.style.display='block';
            const bounds=new google.maps.LatLngBounds();
            mapInstance=new google.maps.Map(el,{zoom:10,center:window._coordDeposito,mapTypeControl:false,streetViewControl:false,fullscreenControl:true,styles:[{featureType:'poi',stylers:[{visibility:'off'}]}]});

            const dep=new google.maps.LatLng(window._coordDeposito.lat,window._coordDeposito.lng);
            new google.maps.Marker({map:mapInstance,position:dep,icon:{path:google.maps.SymbolPath.CIRCLE,scale:14,fillColor:'#3498db',fillOpacity:1,strokeColor:'#fff',strokeWeight:3},title:'Partenza',zIndex:100});
            bounds.extend(dep);
            const path=[dep];

            window._coordConsegne.forEach((co,i)=>{
                const p=new google.maps.LatLng(co.lat,co.lng);path.push(p);bounds.extend(p);
                new google.maps.Marker({map:mapInstance,position:p,label:{text:String(i+1),color:'#fff',fontWeight:'bold',fontSize:'13px'},icon:{path:google.maps.SymbolPath.CIRCLE,scale:18,fillColor:'#f39c12',fillOpacity:1,strokeColor:'#fff',strokeWeight:2},title:consegneOttimizzate[i]?.cliente||'Consegna '+(i+1),zIndex:50});
            });
            path.push(dep);

            new google.maps.Polyline({map:mapInstance,path:path,strokeColor:'#3498db',strokeWeight:4,strokeOpacity:.7,geodesic:true,icons:[{icon:{path:google.maps.SymbolPath.FORWARD_CLOSED_ARROW,scale:3,strokeColor:'#3498db'},offset:'50%',repeat:'100px'}]});
            mapInstance.fitBounds(bounds,60);
        }

        function apriNavigazioneTotale(){
            if(!consegneOttimizzate.length)return;
            const p=encodeURIComponent(document.getElementById('puntoPartenza').value);
            const d=encodeURIComponent(consegneOttimizzate[consegneOttimizzate.length-1].indirizzo);
            let wp='';if(consegneOttimizzate.length>1)wp=consegneOttimizzate.slice(0,-1).map(c=>encodeURIComponent(c.indirizzo)).join('|');
            window.open(`https://www.google.com/maps/dir/?api=1&origin=${p}&destination=${d}&waypoints=${wp}&travelmode=driving`,'_blank');
        }

        function showLoading(t){document.getElementById('loadingText').textContent=t;document.getElementById('loadingOverlay').classList.add('show');}
        function hideLoading(){document.getElementById('loadingOverlay').classList.remove('show');}

        // ==================== GPS POSIZIONE ATTUALE ====================
        function usaPosizioneAttuale() {
            const btn = document.getElementById('btnGps');
            const icon = document.getElementById('gpsIcon');
            if (!navigator.geolocation) { alert('Geolocalizzazione non supportata'); return; }
            if (!googleReady) { alert('Google Maps non ancora pronto'); return; }

            btn.classList.add('loading');
            icon.className = 'ri-loader-4-line';

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const lat = pos.coords.latitude, lng = pos.coords.longitude;
                    geocoder.geocode({ location: { lat, lng } }, (results, status) => {
                        btn.classList.remove('loading');
                        if (status === 'OK' && results[0]) {
                            document.getElementById('puntoPartenza').value = results[0].formatted_address;
                            icon.className = 'ri-check-line';
                            btn.classList.add('success');
                            setTimeout(() => { icon.className = 'ri-crosshair-2-line'; btn.classList.remove('success'); }, 2000);
                        } else {
                            document.getElementById('puntoPartenza').value = lat.toFixed(6) + ', ' + lng.toFixed(6);
                            icon.className = 'ri-crosshair-2-line';
                        }
                    });
                },
                (err) => {
                    btn.classList.remove('loading');
                    icon.className = 'ri-crosshair-2-line';
                    const msgs = { 1: 'Permesso negato. Abilita la geolocalizzazione.', 2: 'Posizione non disponibile.', 3: 'Timeout.' };
                    alert(msgs[err.code] || 'Errore GPS');
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
            );
        }

        // ==================== AZIONI CONSEGNA ====================
        async function iniziaConsegnaPiano(id) {
            try {
                const r = await fetch('/autista/consegna/' + id + '/inizia', {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken }
                });
                const data = await r.json();
                if (data.success) location.reload();
                else alert('Errore: ' + (data.message || 'Impossibile iniziare'));
            } catch(e) { alert('Errore di connessione'); }
        }

        function completaConsegnaPiano(id) {
            window.location.href = '/autista/ordine/' + id + '/completa';
        }

        function annullaConsegnaPiano(id, numOrd) {
            document.getElementById('annullaConsegnaId').value = id;
            document.getElementById('annullaNumOrd').textContent = numOrd;
            document.getElementById('motivoAnnullamento').value = '';
            new bootstrap.Modal(document.getElementById('annullaModal')).show();
            setTimeout(() => { initSigAnn('Cliente'); initSigAnn('Autista'); }, 300);
        }

        function rinviaConsegnaPiano(id, numOrd) {
            document.getElementById('rinviaConsegnaId').value = id;
            document.getElementById('rinviaNumOrd').textContent = numOrd;
            document.getElementById('nuovaDataConsegna').value = '';
            document.getElementById('nuovaOraConsegna').value = '';
            document.getElementById('motivoRinvio').value = '';
            new bootstrap.Modal(document.getElementById('rinviaModal')).show();
            setTimeout(() => { initSigRin('Cliente'); initSigRin('Autista'); }, 300);
        }

        // ==================== UPLOAD FOTO ====================
        function apriUploadFoto(id) {
            document.getElementById('foto_id_ordine').value = id;
            document.getElementById('foto_upload_status').innerHTML = '';
            new bootstrap.Modal(document.getElementById('modalUploadFoto')).show();
        }
        function uploadFotoSingola(input) {
            if (!input.files[0]) return;
            const id = document.getElementById('foto_id_ordine').value;
            const tipo = document.getElementById('foto_tipo').value;
            const st = document.getElementById('foto_upload_status');
            st.innerHTML = '<div class="d-flex align-items-center justify-content-center gap-2 py-2"><div class="spinner-border spinner-border-sm text-primary"></div> Caricamento...</div>';
            const fd = new FormData(); fd.append('foto', input.files[0]); fd.append('tipo', tipo);
            fetch('/autista/consegna/' + id + '/upload-foto', { method: 'POST', headers: { 'X-CSRF-TOKEN': window.csrfToken }, body: fd })
                .then(r => r.json()).then(d => {
                if (d.success) { st.innerHTML = '<div class="alert alert-success py-2"><i class="ri-check-line"></i> Foto caricata!</div>'; setTimeout(() => bootstrap.Modal.getInstance(document.getElementById('modalUploadFoto'))?.hide(), 1500); }
                else st.innerHTML = '<div class="alert alert-danger py-2">' + (d.message || 'Errore') + '</div>';
            }).catch(() => st.innerHTML = '<div class="alert alert-danger py-2">Errore</div>');
            input.value = '';
        }

        // ==================== FIRMA — SISTEMA UNIFICATO ====================
        let sigC = {}, sigCtx = {}, isDrawingSig = false;

        function initSig(type) { _initSigCanvas('sig' + type, type); }
        function initSigAnn(type) { _initSigCanvas('sigAnn' + type, 'Ann' + type); }
        function initSigRin(type) { _initSigCanvas('sigRin' + type, 'Rin' + type); }

        function _initSigCanvas(canvasId, key) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;
            const container = canvas.parentElement;
            canvas.width = container.offsetWidth; canvas.height = 120;
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = 'white'; ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.strokeStyle = '#2c3e50'; ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.lineJoin = 'round';

            const nc = canvas.cloneNode(true);
            canvas.parentNode.replaceChild(nc, canvas);
            sigC[key] = nc; sigCtx[key] = nc.getContext('2d');
            sigCtx[key].fillStyle = 'white'; sigCtx[key].fillRect(0, 0, nc.width, nc.height);
            sigCtx[key].strokeStyle = '#2c3e50'; sigCtx[key].lineWidth = 2; sigCtx[key].lineCap = 'round'; sigCtx[key].lineJoin = 'round';

            nc.addEventListener('touchstart', e => { isDrawingSig = true; const p = _sigPos(e, nc); sigCtx[key].beginPath(); sigCtx[key].moveTo(p.x, p.y); }, { passive: false });
            nc.addEventListener('touchmove', e => { if (!isDrawingSig) return; e.preventDefault(); const p = _sigPos(e, nc); sigCtx[key].lineTo(p.x, p.y); sigCtx[key].stroke(); }, { passive: false });
            nc.addEventListener('touchend', () => isDrawingSig = false);
            nc.addEventListener('mousedown', e => { isDrawingSig = true; const p = _sigPos(e, nc); sigCtx[key].beginPath(); sigCtx[key].moveTo(p.x, p.y); });
            nc.addEventListener('mousemove', e => { if (!isDrawingSig) return; e.preventDefault(); const p = _sigPos(e, nc); sigCtx[key].lineTo(p.x, p.y); sigCtx[key].stroke(); });
            nc.addEventListener('mouseup', () => isDrawingSig = false);
            nc.addEventListener('mouseout', () => isDrawingSig = false);
        }

        function _sigPos(e, c) {
            const r = c.getBoundingClientRect(), sx = c.width / r.width, sy = c.height / r.height;
            const cx = e.touches ? e.touches[0].clientX : e.clientX, cy = e.touches ? e.touches[0].clientY : e.clientY;
            return { x: (cx - r.left) * sx, y: (cy - r.top) * sy };
        }

        function _clearSig(key) { if (sigC[key] && sigCtx[key]) { sigCtx[key].fillStyle = 'white'; sigCtx[key].fillRect(0, 0, sigC[key].width, sigC[key].height); } }
        function clearSig(t) { _clearSig(t); }
        function clearSigAnn(t) { _clearSig('Ann' + t); }
        function clearSigRin(t) { _clearSig('Rin' + t); }

        function _isBlank(c) { const ctx = c.getContext('2d'); const px = new Uint32Array(ctx.getImageData(0, 0, c.width, c.height).data.buffer); return !px.some(v => v !== 0xFFFFFFFF); }

        // ==================== FULLSCREEN SIGNATURE ====================
        let currentFsType = null, fsCanvas = null, fsCtx = null, isDrawingFs = false;

        function openFsSig(type) {
            currentFsType = type;
            document.querySelector('.modal-backdrop')?.classList.add('d-none');
            // Hide appropriate modal
            ['annullaModal','rinviaModal'].forEach(m => { const el = document.getElementById(m); if (el) el.style.display = 'none'; });

            const overlay = document.getElementById('sigFullscreen'); overlay.classList.add('active');
            const labels = { Cliente:'Firma Cliente', Autista:'Firma Autista', AnnCliente:'Firma Cliente', AnnAutista:'Firma Autista', RinCliente:'Firma Cliente', RinAutista:'Firma Autista' };
            document.getElementById('fsSigTitle').textContent = labels[type] || 'Firma';

            setTimeout(() => {
                _initFsCanvas();
                if (sigC[type] && !_isBlank(sigC[type])) fsCtx.drawImage(sigC[type], 0, 0, fsCanvas.width, fsCanvas.height);
            }, 100);
        }

        function _initFsCanvas() {
            fsCanvas = document.getElementById('fsSigCanvas');
            const container = fsCanvas.parentElement;
            fsCanvas.width = container.offsetWidth; fsCanvas.height = container.offsetHeight;
            fsCtx = fsCanvas.getContext('2d');
            fsCtx.fillStyle = 'white'; fsCtx.fillRect(0, 0, fsCanvas.width, fsCanvas.height);
            fsCtx.strokeStyle = '#2c3e50'; fsCtx.lineWidth = 3; fsCtx.lineCap = 'round'; fsCtx.lineJoin = 'round';

            const nc = fsCanvas.cloneNode(true); fsCanvas.parentNode.replaceChild(nc, fsCanvas);
            fsCanvas = nc; fsCtx = fsCanvas.getContext('2d');
            fsCtx.fillStyle = 'white'; fsCtx.fillRect(0, 0, fsCanvas.width, fsCanvas.height);
            fsCtx.strokeStyle = '#2c3e50'; fsCtx.lineWidth = 3; fsCtx.lineCap = 'round'; fsCtx.lineJoin = 'round';

            fsCanvas.addEventListener('touchstart', e => { isDrawingFs = true; const p = _fsPos(e); fsCtx.beginPath(); fsCtx.moveTo(p.x, p.y); }, { passive: false });
            fsCanvas.addEventListener('touchmove', e => { if (!isDrawingFs) return; e.preventDefault(); const p = _fsPos(e); fsCtx.lineTo(p.x, p.y); fsCtx.stroke(); }, { passive: false });
            fsCanvas.addEventListener('touchend', () => isDrawingFs = false);
            fsCanvas.addEventListener('mousedown', e => { isDrawingFs = true; const p = _fsPos(e); fsCtx.beginPath(); fsCtx.moveTo(p.x, p.y); });
            fsCanvas.addEventListener('mousemove', e => { if (!isDrawingFs) return; e.preventDefault(); const p = _fsPos(e); fsCtx.lineTo(p.x, p.y); fsCtx.stroke(); });
            fsCanvas.addEventListener('mouseup', () => isDrawingFs = false);
            fsCanvas.addEventListener('mouseout', () => isDrawingFs = false);
        }

        function _fsPos(e) {
            const r = fsCanvas.getBoundingClientRect(), sx = fsCanvas.width / r.width, sy = fsCanvas.height / r.height;
            const cx = e.touches ? e.touches[0].clientX : e.clientX, cy = e.touches ? e.touches[0].clientY : e.clientY;
            return { x: (cx - r.left) * sx, y: (cy - r.top) * sy };
        }

        function clearFsSig() { if (fsCtx && fsCanvas) { fsCtx.fillStyle = 'white'; fsCtx.fillRect(0, 0, fsCanvas.width, fsCanvas.height); } }

        function closeFsSig() {
            document.getElementById('sigFullscreen').classList.remove('active');
            document.querySelector('.modal-backdrop')?.classList.remove('d-none');
            // Show appropriate modal
            if (currentFsType && currentFsType.startsWith('Ann')) document.getElementById('annullaModal').style.display = 'block';
            else if (currentFsType && currentFsType.startsWith('Rin')) document.getElementById('rinviaModal').style.display = 'block';
            currentFsType = null;
        }

        function confirmFsSig() {
            if (!currentFsType || !fsCanvas) return;
            if (sigC[currentFsType] && sigCtx[currentFsType]) {
                sigCtx[currentFsType].fillStyle = 'white';
                sigCtx[currentFsType].fillRect(0, 0, sigC[currentFsType].width, sigC[currentFsType].height);
                sigCtx[currentFsType].drawImage(fsCanvas, 0, 0, sigC[currentFsType].width, sigC[currentFsType].height);
            }
            closeFsSig();
        }

        // ==================== CONFERMA ANNULLAMENTO ====================
        async function confermaAnnullamento() {
            const id = document.getElementById('annullaConsegnaId').value;
            const motivo = document.getElementById('motivoAnnullamento').value.trim();
            if (!motivo) { alert('Inserisci il motivo'); return; }
            if (!sigC['AnnCliente'] || !sigC['AnnAutista']) { alert('Canvas firme non inizializzati'); return; }
            if (_isBlank(sigC['AnnCliente'])) { alert('Inserisci la firma del cliente'); return; }
            if (_isBlank(sigC['AnnAutista'])) { alert('Inserisci la tua firma'); return; }
            try {
                const r = await fetch('/autista/consegna/' + id + '/annulla', {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken },
                    body: JSON.stringify({ motivo, firma_cliente: sigC['AnnCliente'].toDataURL('image/png'), firma_autista: sigC['AnnAutista'].toDataURL('image/png') })
                });
                const data = await r.json();
                if (data.success) { bootstrap.Modal.getInstance(document.getElementById('annullaModal')).hide(); location.reload(); }
                else alert('Errore: ' + (data.message || 'Impossibile annullare'));
            } catch(e) { alert('Errore di connessione'); }
        }

        // ==================== CONFERMA RINVIO ====================
        async function confermaRinvio() {
            const id = document.getElementById('rinviaConsegnaId').value;
            const nuovaData = document.getElementById('nuovaDataConsegna').value;
            const nuovaOra = document.getElementById('nuovaOraConsegna').value;
            const motivo = document.getElementById('motivoRinvio').value.trim();
            if (!nuovaData) { alert('Seleziona la data'); return; }
            if (!motivo) { alert('Inserisci il motivo'); return; }
            if (!sigC['RinCliente'] || !sigC['RinAutista']) { alert('Canvas firme non inizializzati'); return; }
            if (_isBlank(sigC['RinCliente'])) { alert('Inserisci la firma del cliente'); return; }
            if (_isBlank(sigC['RinAutista'])) { alert('Inserisci la tua firma'); return; }
            try {
                const r = await fetch('/autista/consegna/' + id + '/rinvia', {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken },
                    body: JSON.stringify({ nuova_data: nuovaData, nuova_ora: nuovaOra, motivo, firma_cliente: sigC['RinCliente'].toDataURL('image/png'), firma_autista: sigC['RinAutista'].toDataURL('image/png') })
                });
                const data = await r.json();
                if (data.success) { bootstrap.Modal.getInstance(document.getElementById('rinviaModal')).hide(); alert('Rinviata al ' + nuovaData); location.reload(); }
                else alert('Errore: ' + (data.message || 'Impossibile rinviare'));
            } catch(e) { alert('Errore di connessione'); }
        }
    </script>
@endsection