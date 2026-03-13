@extends('autista.common.layout')

@section('title', 'Percorso Consegne')

@section('styles')
    <style>
        /* Override layout per pagina mappa */
        .main-content {
            padding: 0 !important;
            max-width: 100% !important;
        }

        /* ===== DATE SELECTOR ===== */
        .date-selector {
            background: white;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #eee;
        }
        .date-selector input[type="date"] {
            background: var(--bg-light);
            border: 1px solid #ddd;
            color: var(--text-dark);
            border-radius: var(--radius-sm);
            padding: 6px 12px;
            font-size: 0.9rem;
        }

        /* ===== STATS BAR ===== */
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1px;
            background: #eee;
        }
        .stat-box {
            background: white;
            padding: 14px 10px;
            text-align: center;
        }
        .stat-box .val {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--primary-color);
            line-height: 1;
        }
        .stat-box .val.green { color: var(--success-color); }
        .stat-box .val.orange { color: var(--warning-color); }
        .stat-box .lbl {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        /* ===== MAPPA ===== */
        .map-container {
            position: relative;
            height: 280px;
            border-bottom: 2px solid var(--primary-color);
        }
        #map { width: 100%; height: 100%; }
        .map-overlay-btn {
            position: absolute;
            bottom: 12px;
            right: 12px;
            z-index: 10;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            padding: 10px 16px;
            font-weight: 700;
            font-size: 0.85rem;
            box-shadow: 0 4px 12px rgba(52,152,219,0.4);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .map-expand-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            z-index: 10;
            background: rgba(255,255,255,0.9);
            color: var(--text-dark);
            border: 1px solid #ddd;
            border-radius: var(--radius-sm);
            padding: 8px;
            font-size: 1.1rem;
            line-height: 1;
        }
        .map-container.expanded { height: 70vh; }

        /* ===== FUEL CARD ===== */
        .fuel-card {
            background: white;
            border: 1px solid #eee;
            border-radius: var(--radius-lg);
            padding: 14px 16px;
            margin: 16px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: var(--shadow-sm);
        }
        .fuel-icon {
            width: 44px; height: 44px;
            background: rgba(243,156,18,0.1);
            border-radius: var(--radius-md);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
        }
        .fuel-info { flex: 1; }
        .fuel-value { font-size: 1.1rem; font-weight: 800; color: var(--warning-color); }
        .fuel-label { font-size: 0.75rem; color: var(--text-muted); }

        /* ===== PIANO DI CARICO ===== */
        .carico-section {
            background: white;
            padding: 16px;
            margin: 0 16px 16px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }
        .carico-title {
            font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;
            color: var(--text-muted); margin-bottom: 10px;
            display: flex; align-items: center; gap: 6px; font-weight: 600;
        }
        .carico-visual {
            background: var(--bg-light);
            border: 2px solid #eee;
            border-radius: var(--radius-md);
            padding: 10px;
        }
        .carico-slot {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 10px; border-radius: var(--radius-sm); margin-bottom: 4px; font-size: 0.85rem;
        }
        .carico-slot:last-child { margin-bottom: 0; }
        .carico-slot .slot-pos {
            width: 28px; height: 28px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 0.75rem; flex-shrink: 0;
        }
        .carico-slot.fondo { background: rgba(231,76,60,0.06); }
        .carico-slot.fondo .slot-pos { background: var(--danger-color); color: white; }
        .carico-slot.centro { background: rgba(243,156,18,0.06); }
        .carico-slot.centro .slot-pos { background: var(--warning-color); color: white; }
        .carico-slot.porta { background: rgba(39,174,96,0.06); }
        .carico-slot.porta .slot-pos { background: var(--success-color); color: white; }
        .carico-label { font-size: 0.6rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px; margin-left: auto; }
        .slot-info { flex: 1; min-width: 0; }
        .slot-info .nome { font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .slot-info .dettaglio { font-size: 0.75rem; color: var(--text-muted); }
        .slot-badges { display: flex; gap: 4px; flex-shrink: 0; }
        .slot-badges .badge { font-size: 0.65rem; padding: 2px 6px; border-radius: 4px; font-weight: 600; }

        /* ===== LISTA CONSEGNE ===== */
        .consegne-percorso { padding: 0 16px 16px; }
        .section-title-percorso {
            font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;
            color: var(--text-muted); margin-bottom: 12px;
            display: flex; align-items: center; justify-content: space-between;
            font-weight: 600; padding: 0 4px;
        }
        .stop-card {
            background: white; border: 1px solid #eee; border-radius: var(--radius-lg);
            margin-bottom: 10px; overflow: hidden; transition: all 0.3s; box-shadow: var(--shadow-sm);
        }
        .stop-card.completata { opacity: 0.5; border-color: var(--success-color); }
        .stop-card.attiva { border-color: var(--primary-color); border-width: 2px; box-shadow: 0 0 15px rgba(52,152,219,0.15); }
        .stop-header { display: flex; align-items: center; padding: 14px 16px; gap: 12px; }
        .stop-number {
            width: 36px; height: 36px; border-radius: var(--radius-md);
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 0.95rem; flex-shrink: 0;
            background: var(--primary-color); color: white;
        }
        .stop-card.completata .stop-number { background: var(--success-color); }
        .stop-info { flex: 1; min-width: 0; }
        .stop-cliente { font-weight: 700; font-size: 0.95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--text-dark); }
        .stop-indirizzo { font-size: 0.8rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .stop-meta {
            display: flex; gap: 10px; padding: 0 16px 10px 64px;
            font-size: 0.78rem; color: var(--text-muted); flex-wrap: wrap;
        }
        .stop-meta span { display: flex; align-items: center; gap: 4px; }
        .stop-meta .time-badge {
            background: rgba(52,152,219,0.08); padding: 2px 8px;
            border-radius: 6px; color: var(--primary-color); font-weight: 600;
        }
        .stop-meta .km-badge {
            background: rgba(39,174,96,0.08); padding: 2px 8px;
            border-radius: 6px; color: var(--success-color); font-weight: 600;
        }
        .stop-actions { display: flex; border-top: 1px solid #f0f0f0; }
        .stop-actions button, .stop-actions a {
            flex: 1; padding: 12px; border: none; background: transparent;
            color: var(--text-muted); font-weight: 600; font-size: 0.82rem;
            display: flex; align-items: center; justify-content: center; gap: 6px;
            text-decoration: none; transition: all 0.2s;
        }
        .stop-actions button:hover, .stop-actions a:hover { background: var(--bg-light); }
        .stop-actions .btn-naviga { color: var(--primary-color); }
        .stop-actions .btn-completa { color: var(--success-color); }
        .stop-actions .btn-completa:hover { background: rgba(39,174,96,0.08); }
        .stop-actions > *:not(:last-child) { border-right: 1px solid #f0f0f0; }

        /* Loading */
        .loading-route { text-align: center; padding: 30px; }
        .loading-route .spinner-border { width: 3rem; height: 3rem; color: var(--primary-color); }
    </style>
@endsection

@section('content')
    <!-- Date Selector -->
    <div class="date-selector">
        <i class="ri-calendar-line" style="color:var(--primary-color);"></i>
        <input type="date" id="dataConsegne" value="{{ $dataSelezionata }}" onchange="cambiaData(this.value)">
        <span class="ms-auto" style="font-size:0.85rem;color:var(--text-muted);">
            {{ \Carbon\Carbon::parse($dataSelezionata)->locale('it')->isoFormat('dddd D MMMM') }}
        </span>
    </div>

    @if($consegne->count() > 0)
        <!-- Stats Bar -->
        <div class="stats-bar">
            <div class="stat-box"><div class="val">{{ $consegne->count() }}</div><div class="lbl">Consegne</div></div>
            <div class="stat-box"><div class="val green" id="statKm">--</div><div class="lbl">Km Totali</div></div>
            <div class="stat-box"><div class="val orange" id="statTempo">--</div><div class="lbl">Tempo Stim.</div></div>
            <div class="stat-box"><div class="val">{{ $consegne->sum('numero_colli') ?: $consegne->count() }}</div><div class="lbl">Colli Tot.</div></div>
        </div>

        <!-- Mappa -->
        <div class="map-container" id="mapContainer">
            <div id="map"></div>
            <button class="map-expand-btn" onclick="toggleMapSize()"><i class="ri-fullscreen-line" id="expandIcon"></i></button>
            <button class="map-overlay-btn" onclick="apriGoogleMapsPercorso()"><i class="ri-navigation-line"></i> Naviga Tutto</button>
        </div>

        <!-- Loading -->
        <div class="loading-route" id="loadingRoute">
            <div class="spinner-border mb-2" role="status"></div>
            <div class="text-muted">Calcolo percorso ottimale...</div>
        </div>

        <!-- Contenuto post-calcolo -->
        <div id="contentAfterCalc" style="display:none;">
            <!-- Carburante -->
            <div class="fuel-card">
                <div class="fuel-icon">⛽</div>
                <div class="fuel-info">
                    <div class="fuel-value" id="fuelEstimate">--</div>
                    <div class="fuel-label">Carburante stimato</div>
                </div>
                <div class="text-end">
                    <div style="font-size:0.85rem;font-weight:700;color:var(--danger-color);" id="fuelCost">--</div>
                    <div class="fuel-label">Costo stimato</div>
                </div>
            </div>

            <!-- Piano di Carico -->
            <div class="carico-section">
                <div class="carico-title">
                    <i class="ri-truck-line"></i> Piano di Carico
                    <span style="margin-left:auto;font-size:0.7rem;color:var(--primary-color);">
                        {{ number_format($consegne->sum('peso_kg') ?? 0, 0, ',', '.') }} kg totali
                    </span>
                </div>
                <div class="carico-visual" id="pianoCarico"></div>
                <div class="mt-2" style="font-size:0.7rem;color:var(--text-muted);">
                    <i class="ri-information-line"></i>
                    Il primo in lista va in <strong>fondo</strong> al furgone, l'ultimo vicino alla <strong>porta</strong> (primo da consegnare).
                </div>
            </div>

            <!-- Lista Consegne -->
            <div class="consegne-percorso">
                <div class="section-title-percorso">
                    <span><i class="ri-route-line me-1"></i> Ordine di Consegna</span>
                    <span id="completateCount" style="color:var(--success-color);">0/{{ $consegne->count() }}</span>
                </div>
                <div id="listaConsegne">
                    @foreach($consegne as $index => $consegna)
                        <div class="stop-card {{ $consegna->stato == 'completato' ? 'completata' : '' }}"
                             data-id="{{ $consegna->id }}"
                             data-indirizzo="{{ $consegna->indirizzo_consegna }}"
                             data-indirizzo-ritiro="{{ $consegna->indirizzo_ritiro }}"
                             data-cliente="{{ $consegna->cliente_nome ?? 'Cliente' }}"
                             data-colli="{{ $consegna->numero_colli ?? 1 }}"
                             data-peso="{{ $consegna->peso_kg ?? 0 }}"
                             data-merce="{{ $consegna->descrizione_merce ?? '' }}"
                             data-stato="{{ $consegna->stato }}"
                             id="card-{{ $consegna->id }}">
                            <div class="stop-header">
                                <div class="stop-number">
                                    @if($consegna->stato == 'completato')
                                        <i class="ri-check-line"></i>
                                    @else
                                        {{ $index + 1 }}
                                    @endif
                                </div>
                                <div class="stop-info">
                                    <div class="stop-cliente">{{ $consegna->cliente_nome ?? 'Cliente' }}</div>
                                    <div class="stop-indirizzo"><i class="ri-map-pin-line"></i> {{ $consegna->indirizzo_consegna }}</div>
                                </div>
                            </div>
                            <div class="stop-meta">
                                <span><i class="ri-box-3-line"></i> {{ $consegna->numero_colli ?? '-' }} colli</span>
                                <span><i class="ri-scales-line"></i> {{ $consegna->peso_kg ? number_format($consegna->peso_kg, 0) . ' kg' : '-' }}</span>
                                <span class="time-badge arrivo-stimato"><i class="ri-time-line"></i> --:--</span>
                                <span class="km-badge tratta-km">-- km</span>
                            </div>
                            <div class="stop-actions">
                                <a href="#" class="btn-naviga" onclick="navigaSingola('{{ addslashes($consegna->indirizzo_consegna) }}'); return false;">
                                    <i class="ri-navigation-line"></i> Naviga
                                </a>
                                @if($consegna->stato == 'completato')
                                    <span class="btn-completa" style="pointer-events:none;"><i class="ri-check-double-line"></i> Completata</span>
                                @else
                                    <button class="btn-completa" onclick="completaStop({{ $consegna->id }}, this)"><i class="ri-check-line"></i> Completa</button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="empty-state" style="padding-top:80px;">
            <i class="ri-inbox-unarchive-line"></i>
            <h5>Nessuna consegna</h5>
            <p class="text-muted">Non hai consegne per {{ \Carbon\Carbon::parse($dataSelezionata)->locale('it')->isoFormat('dddd D MMMM') }}.</p>
            <a href="/autista/dashboard" class="btn btn-primary-custom mt-2"><i class="ri-arrow-left-line me-1"></i> Dashboard</a>
        </div>
    @endif
@endsection

@section('scripts')
    @if($consegne->count() > 0)
        <script>
            const CONSUMO_MEDIO_LT_KM = {{ $consumoMedioLtKm ?? 0.12 }};
            const PREZZO_GASOLIO_LT = {{ $prezzoGasolio ?? 1.65 }};
            let consegne = @json($consegne->values());
            let ordineOttimizzato = [];
            let directionsRenderer = null;
            let map = null;

            function initMap() {
                map = new google.maps.Map(document.getElementById('map'), {
                    center: { lat: 41.9, lng: 12.5 },
                    zoom: 6,
                    disableDefaultUI: true,
                    zoomControl: true,
                    gestureHandling: 'greedy'
                });
                directionsRenderer = new google.maps.DirectionsRenderer({
                    map: map,
                    suppressMarkers: false,
                    polylineOptions: { strokeColor: '#3498db', strokeWeight: 5, strokeOpacity: 0.85 }
                });
                calcolaPercorsoOttimizzato();
            }

            function calcolaPercorsoOttimizzato() {
                const ds = new google.maps.DirectionsService();
                const indirizzi = consegne.map(c => c.indirizzo_consegna).filter(Boolean);
                if (!indirizzi.length) { nascondiLoading(); return; }

                let partenza = consegne[0].indirizzo_ritiro || indirizzi[0];

                if (indirizzi.length === 1) {
                    ds.route({ origin: partenza, destination: indirizzi[0], travelMode: 'DRIVING', region: 'it' }, (r, s) => {
                        if (s === 'OK') { directionsRenderer.setDirections(r); ordineOttimizzato = [0]; processaRisultato(r); }
                        else mostraErroreMappa(s);
                        nascondiLoading();
                    });
                    return;
                }

                let waypoints = indirizzi.slice(0, -1).map(a => ({ location: a, stopover: true }));
                ds.route({
                    origin: partenza,
                    destination: indirizzi[indirizzi.length - 1],
                    waypoints: waypoints,
                    optimizeWaypoints: true,
                    travelMode: 'DRIVING',
                    region: 'it'
                }, (r, s) => {
                    if (s === 'OK') { directionsRenderer.setDirections(r); ordineOttimizzato = r.routes[0].waypoint_order || []; processaRisultato(r); }
                    else mostraErroreMappa(s);
                    nascondiLoading();
                });
            }

            function nascondiLoading() {
                document.getElementById('loadingRoute').style.display = 'none';
                document.getElementById('contentAfterCalc').style.display = 'block';
            }

            function processaRisultato(result) {
                const legs = result.routes[0].legs;
                let totalKm = 0, totalSec = 0;
                legs.forEach(l => { totalKm += l.distance.value / 1000; totalSec += l.duration.value; });

                document.getElementById('statKm').textContent = Math.round(totalKm);
                document.getElementById('statTempo').textContent = formatDurata(totalSec);

                const litri = totalKm * CONSUMO_MEDIO_LT_KM;
                document.getElementById('fuelEstimate').textContent = litri.toFixed(1) + ' L';
                document.getElementById('fuelCost').textContent = '€ ' + (litri * PREZZO_GASOLIO_LT).toFixed(2);

                riordinaCards(ordineOttimizzato, legs);
                generaPianoCarico(ordineOttimizzato);
                aggiornaCompletateCount();
                salvaOrdinePercorso(ordineOttimizzato);
            }

            function riordinaCards(wpOrder, legs) {
                const container = document.getElementById('listaConsegne');
                const cards = Array.from(container.querySelectorAll('.stop-card'));
                if (!cards.length) return;

                let tempoAcc = 0;
                const adesso = new Date();
                let ordineFinale = [];

                if (wpOrder.length > 0) {
                    wpOrder.forEach(i => { if (cards[i]) ordineFinale.push(cards[i]); });
                    if (cards.length > wpOrder.length) ordineFinale.push(cards[cards.length - 1]);
                } else { ordineFinale = cards; }

                ordineFinale.forEach((card, idx) => {
                    const numEl = card.querySelector('.stop-number');
                    numEl.innerHTML = card.dataset.stato === 'completato' ? '<i class="ri-check-line"></i>' : (idx + 1);

                    if (legs[idx]) {
                        tempoAcc += legs[idx].duration.value;
                        const arr = new Date(adesso.getTime() + tempoAcc * 1000);
                        const ora = arr.getHours().toString().padStart(2,'0') + ':' + arr.getMinutes().toString().padStart(2,'0');
                        const km = (legs[idx].distance.value / 1000).toFixed(1);
                        const timeEl = card.querySelector('.arrivo-stimato');
                        if (timeEl) timeEl.innerHTML = '<i class="ri-time-line"></i> ~' + ora;
                        const kmEl = card.querySelector('.tratta-km');
                        if (kmEl) kmEl.textContent = km + ' km';
                        tempoAcc += 300;
                    }

                    card.classList.remove('attiva');
                    if (card.dataset.stato !== 'completato') {
                        const prec = ordineFinale.slice(0, idx);
                        if (!prec.length || prec.every(c => c.dataset.stato === 'completato')) card.classList.add('attiva');
                    }
                    container.appendChild(card);
                });
            }

            function generaPianoCarico(wpOrder) {
                const container = document.getElementById('pianoCarico');
                const cards = Array.from(document.querySelectorAll('.stop-card'));
                let ordConsegna = [];
                if (wpOrder.length > 0) {
                    wpOrder.forEach(i => { if (cards[i]) ordConsegna.push(cards[i]); });
                    if (cards.length > wpOrder.length) ordConsegna.push(cards[cards.length - 1]);
                } else { ordConsegna = cards; }

                const ordCarico = [...ordConsegna].reverse();
                const tot = ordCarico.length;
                let html = '';

                ordCarico.forEach((card, idx) => {
                    const cliente = card.dataset.cliente, colli = card.dataset.colli || '-';
                    const peso = parseFloat(card.dataset.peso) || 0, merce = card.dataset.merce || '';
                    let posClass, posLabel;
                    if (idx < Math.ceil(tot/3)) { posClass='fondo'; posLabel='FONDO'; }
                    else if (idx < Math.ceil(tot*2/3)) { posClass='centro'; posLabel='CENTRO'; }
                    else { posClass='porta'; posLabel='PORTA'; }

                    html += `<div class="carico-slot ${posClass}">
                <div class="slot-pos">${idx+1}°</div>
                <div class="slot-info"><div class="nome">${cliente}</div><div class="dettaglio">${merce?merce.substring(0,30):''} → Consegna n.${tot-idx}</div></div>
                <div class="slot-badges"><span class="badge" style="background:rgba(52,152,219,0.1);color:var(--primary-color);">${colli} 📦</span>${peso>0?`<span class="badge" style="background:rgba(243,156,18,0.1);color:var(--warning-color);">${Math.round(peso)}kg</span>`:''}</div>
                <span class="carico-label">${posLabel}</span></div>`;
                });
                container.innerHTML = html;
            }

            function completaStop(id, btn) {
                if (!confirm('Confermi la consegna completata?')) return;
                const card = document.getElementById('card-' + id);
                fetch('/autista/consegna-ordine/' + id + '/completa', {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken }
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        card.classList.add('completata'); card.dataset.stato = 'completato';
                        card.querySelector('.stop-number').innerHTML = '<i class="ri-check-line"></i>';
                        btn.innerHTML = '<i class="ri-check-double-line"></i> Completata'; btn.disabled = true; btn.style.pointerEvents = 'none';
                        aggiornaCompletateCount();
                        document.querySelectorAll('.stop-card').forEach(c => c.classList.remove('attiva'));
                        const next = Array.from(document.querySelectorAll('.stop-card')).find(c => c.dataset.stato !== 'completato');
                        if (next) next.classList.add('attiva');
                    }
                }).catch(() => alert('Errore di connessione'));
            }

            function navigaSingola(addr) { window.open('https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(addr) + '&travelmode=driving', '_blank'); }

            function apriGoogleMapsPercorso() {
                const cards = Array.from(document.querySelectorAll('.stop-card:not(.completata)'));
                if (!cards.length) return;
                const addrs = cards.map(c => c.dataset.indirizzo).filter(Boolean);
                let url = 'https://www.google.com/maps/dir/?api=1&origin=La+mia+posizione&destination=' + encodeURIComponent(addrs[addrs.length-1]);
                if (addrs.length > 1) url += '&waypoints=' + addrs.slice(0,-1).map(a => encodeURIComponent(a)).join('|');
                url += '&travelmode=driving';
                window.open(url, '_blank');
            }

            function toggleMapSize() {
                const c = document.getElementById('mapContainer'), i = document.getElementById('expandIcon');
                c.classList.toggle('expanded');
                i.className = c.classList.contains('expanded') ? 'ri-fullscreen-exit-line' : 'ri-fullscreen-line';
                google.maps.event.trigger(map, 'resize');
            }

            function cambiaData(d) { window.location.href = '/autista/percorso-consegne?data=' + d; }

            function aggiornaCompletateCount() {
                const t = document.querySelectorAll('.stop-card').length;
                const c = document.querySelectorAll('.stop-card.completata').length;
                document.getElementById('completateCount').textContent = c + '/' + t;
            }

            function salvaOrdinePercorso(ord) {
                const cards = Array.from(document.querySelectorAll('.stop-card'));
                const ids = [];
                ord.forEach(i => { if (cards[i]) ids.push(parseInt(cards[i].dataset.id)); });
                if (cards.length > ord.length) ids.push(parseInt(cards[cards.length-1].dataset.id));
                fetch('/autista/salva-ordine-percorso', {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken },
                    body: JSON.stringify({ ordine: ids, data: document.getElementById('dataConsegne').value })
                }).catch(() => {});
            }

            function formatDurata(s) { const h = Math.floor(s/3600), m = Math.floor((s%3600)/60); return h > 0 ? h+'h '+m+'m' : m+'m'; }
            function mostraErroreMappa(s) { console.error('Directions:', s); document.getElementById('statKm').textContent = 'Err'; }
        </script>
        <script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsKey }}&libraries=places&callback=initMap" async defer></script>
    @endif
@endsection