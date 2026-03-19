@extends('autista.common.layout')

@section('title', 'Completa Ordine')

@section('styles')
    <style>
        .ddt-preview {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 11px;
            max-height: 350px;
            overflow-y: auto;
        }
        .ddt-header {
            text-align: center;
            padding: 10px;
            border-bottom: 2px solid #333;
        }
        .ddt-header h5 {
            margin: 0;
            font-size: 14px;
            font-weight: bold;
        }
        .ddt-box {
            border: 1px solid #333;
            margin: 8px;
            padding: 8px;
        }
        .ddt-box-header {
            background: #e9e9e9;
            margin: -8px -8px 8px -8px;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            border-bottom: 1px solid #333;
        }
        .ddt-box .nome { font-weight: bold; font-size: 12px; }
        .ddt-numero { text-align: center; }
        .ddt-numero .numero { font-size: 22px; font-weight: bold; }
        .btn-completa {
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 10px;
        }
        .foto-preview-item {
            position: relative;
            display: inline-block;
        }
        .foto-preview-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #dee2e6;
        }
        .foto-preview-item .remove-foto {
            position: absolute;
            top: -6px;
            right: -6px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #dc3545;
            color: white;
            border: none;
            font-size: 11px;
            line-height: 1;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
@endsection

@section('content')
    <div class="fade-in">

        <!-- Header -->
        <div class="d-flex align-items-center mb-3">
            <a href="/autista/consegne" class="btn btn-outline-secondary btn-sm me-3">
                <i class="ri-arrow-left-line"></i>
            </a>
            <div>
                <h5 class="mb-0">Completa Ordine</h5>
                <small class="text-muted">#{{ $ordine->numero_ordine }}</small>
            </div>
        </div>

        @if($miaTappa)
        <!-- Info Tappa (staffetta) -->
        <div class="card mb-3 border-primary">
            <div class="card-header bg-primary bg-opacity-10 d-flex align-items-center gap-2">
                <i class="ri-route-line text-primary fs-5"></i>
                <div>
                    <strong>
                        Tappa {{ $miaTappa->numero_tappa }}
                        @if($totaleTappe > 1) / {{ $totaleTappe }} @endif
                    </strong>
                    @if($prossimaTappa)
                        <small class="d-block text-muted">Passa il carico al prossimo autista</small>
                    @else
                        <small class="d-block text-success fw-semibold">Tappa finale — consegna al destinatario</small>
                    @endif
                </div>
            </div>
            <div class="card-body py-2">
                <div class="d-flex gap-3 align-items-start">
                    <div class="flex-fill">
                        <small class="text-muted text-uppercase" style="font-size:9px;font-weight:700;">Ritiro</small>
                        <div class="small fw-semibold">{{ $ordine->indirizzo_ritiro }}</div>
                    </div>
                    <i class="ri-arrow-right-line text-muted mt-2"></i>
                    <div class="flex-fill">
                        <small class="text-muted text-uppercase" style="font-size:9px;font-weight:700;">Consegna</small>
                        <div class="small fw-semibold">{{ $ordine->indirizzo_consegna }}</div>
                    </div>
                </div>
                @if($prossimaTappa && ($prossimaTappa->autista_nome || $prossimaTappa->autista_cognome))
                <div class="mt-2 p-2 bg-light rounded d-flex align-items-center gap-2">
                    <i class="ri-user-received-line text-primary fs-5"></i>
                    <div>
                        <small class="text-muted" style="font-size:10px;">Consegni a:</small>
                        <div class="fw-bold">{{ $prossimaTappa->autista_nome }} {{ $prossimaTappa->autista_cognome }}</div>
                        @if($prossimaTappa->indirizzo_ritiro)
                            <small class="text-muted">📍 {{ $prossimaTappa->indirizzo_ritiro }}</small>
                        @endif
                    </div>
                </div>
                @endif
                @if($miaTappa->note)
                    <div class="mt-2 small text-muted"><i class="ri-sticky-note-line me-1"></i>{{ $miaTappa->note }}</div>
                @endif
            </div>
        </div>
        @endif

        <!-- Anteprima DDT Compatta -->
        <div class="card mb-3">
            <div class="card-header bg-dark text-white py-2">
                <small class="mb-0"><i class="ri-file-text-line me-1"></i> DDT {{ $ddt->numero_documento }}</small>
            </div>
            <div class="card-body p-0">
                <div class="ddt-preview">

                    <div class="ddt-header">
                        <h5>DOCUMENTO DI TRASPORTO</h5>
                        <small class="text-muted">D.D.T. ai sensi D.P.R. 472/96</small>
                    </div>

                    <div class="row g-0 p-2">
                        <div class="col-8 pe-1">
                            <div class="ddt-box">
                                <div class="ddt-box-header">Mittente</div>
                                <div class="nome">{{ $azienda->ragione_sociale ?? $azienda->nome ?? '' }}</div>
                                <small>{{ $ddt->mittente_indirizzo ?? '' }}</small>
                            </div>
                        </div>
                        <div class="col-4 ps-1">
                            <div class="ddt-box ddt-numero">
                                <div class="ddt-box-header">Doc. N.</div>
                                <div class="numero">{{ $ddt->numero_documento }}</div>
                                <small>{{ date('d/m/Y', strtotime($ddt->data_documento)) }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="row g-0 px-2">
                        <div class="col-6 pe-1">
                            <div class="ddt-box">
                                <div class="ddt-box-header">Destinatario</div>
                                <div class="nome">{{ $ddt->destinatario_nome }}</div>
                                <small>{{ $ddt->destinatario_indirizzo }}</small>
                            </div>
                        </div>
                        <div class="col-6 ps-1">
                            <div class="ddt-box">
                                <div class="ddt-box-header">Destinazione</div>
                                <small>{{ $ordine->indirizzo_consegna }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="ddt-box mx-2">
                        <div class="ddt-box-header">Merce</div>
                        <div class="row">
                            <div class="col-6">
                                <small><strong>{{ $ddt->descrizione_merce }}</strong></small>
                            </div>
                            <div class="col-2 text-center">
                                <small class="text-muted">Colli</small><br>
                                <strong>{{ $ddt->numero_colli ?? '-' }}</strong>
                            </div>
                            <div class="col-2 text-center">
                                <small class="text-muted">Peso</small><br>
                                <strong>{{ $ddt->peso_lordo ? $ddt->peso_lordo . ' kg' : '-' }}</strong>
                            </div>
                            <div class="col-2 text-center">
                                <small class="text-muted">Valore</small><br>
                                <strong>{{ $ddt->valore_merce ? '€' . number_format($ddt->valore_merce, 0) : '-' }}</strong>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        @if($ordine->pedane_da_ritirare > 0)
        <!-- Sezione Pedane -->
        <div class="card mb-3 border-warning">
            <div class="card-header bg-warning bg-opacity-10 d-flex align-items-center gap-2">
                <i class="ri-stack-line text-warning fs-5"></i>
                <div>
                    <strong>Ritiro Pedane</strong>
                    <small class="d-block text-muted">Previste {{ $ordine->pedane_da_ritirare }} pedane in reso</small>
                </div>
            </div>
            <div class="card-body">
                <label class="form-label fw-semibold">Quante pedane hai ritirato?</label>
                <div class="d-flex align-items-center gap-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cambiaQuantitaPedane(-1)">
                        <i class="ri-subtract-line"></i>
                    </button>
                    <input type="number" id="input_pedane_ritirate" class="form-control text-center fw-bold fs-5"
                           value="{{ $ordine->pedane_da_ritirare }}" min="0"
                           style="width: 90px;" oninput="aggiornaBadgePedane()">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cambiaQuantitaPedane(1)">
                        <i class="ri-add-line"></i>
                    </button>
                    <span id="badge_pedane" class="badge bg-success ms-2">
                        <i class="ri-check-line"></i> OK
                    </span>
                </div>
                <div class="mt-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="chk_nessuna_pedana"
                               onchange="nessunaPedana(this)">
                        <label class="form-check-label text-danger" for="chk_nessuna_pedana">
                            Nessuna pedana ritirata (0)
                        </label>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Sezione Foto / Documenti -->
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="ri-camera-line fs-5"></i>
                <strong>Foto / Documenti</strong>
                <small class="text-muted ms-1">(opzionale)</small>
            </div>
            <div class="card-body">
                <div id="foto-preview" class="d-flex flex-wrap gap-2 mb-3" style="min-height: 20px;"></div>

                <div class="d-flex gap-2">
                    <label class="btn btn-outline-primary btn-sm flex-fill text-center mb-0">
                        <i class="ri-camera-line me-1"></i> Scatta Foto
                        <input type="file" id="input_foto_camera" accept="image/*" capture="camera" multiple class="d-none" onchange="aggiungiAnteprima(this)">
                    </label>
                    <label class="btn btn-outline-secondary btn-sm flex-fill text-center mb-0">
                        <i class="ri-folder-image-line me-1"></i> Galleria
                        <input type="file" id="input_foto_galleria" accept="image/*,application/pdf" multiple class="d-none" onchange="aggiungiAnteprima(this)">
                    </label>
                </div>
                <small class="text-muted d-block mt-2">Le foto verranno caricate al momento del completamento</small>
            </div>
        </div>

        <!-- Pulsante Completa -->
        <div class="mb-5 pb-3">
            @if($miaTappa && $prossimaTappa)
            <button class="btn btn-primary btn-completa w-100" onclick="completaOrdine(this)">
                <i class="ri-arrow-right-circle-line me-2"></i>
                PASSA CARICO A {{ strtoupper($prossimaTappa->autista_nome ?? 'PROSSIMO AUTISTA') }}
            </button>
            @else
            <button class="btn btn-success btn-completa w-100" onclick="completaOrdine(this)">
                <i class="ri-checkbox-circle-line me-2"></i>
                COMPLETA ORDINE
            </button>
            @endif
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        const idDdt    = {{ $ddt->id }};
        const idOrdine = {{ $ordine->id }};
        const pedaneDaRitirare  = {{ intval($ordine->pedane_da_ritirare ?? 0) }};
        const hasProssimaTappa  = {{ $prossimaTappa ? 'true' : 'false' }};
        const btnTestoDefault   = hasProssimaTappa
            ? '<i class="ri-arrow-right-circle-line me-2"></i> PASSA CARICO A {{ strtoupper($prossimaTappa->autista_nome ?? "PROSSIMO AUTISTA") }}'
            : '<i class="ri-checkbox-circle-line me-2"></i> COMPLETA ORDINE';

        // ── Pedane ──────────────────────────────────────────
        function cambiaQuantitaPedane(delta) {
            const input = document.getElementById('input_pedane_ritirate');
            if (!input) return;
            input.value = Math.max(0, parseInt(input.value || 0) + delta);
            aggiornaBadgePedane();
        }

        function nessunaPedana(chk) {
            const input = document.getElementById('input_pedane_ritirate');
            if (!input) return;
            if (chk.checked) {
                input.value = 0;
                input.disabled = true;
            } else {
                input.value = pedaneDaRitirare;
                input.disabled = false;
            }
            aggiornaBadgePedane();
        }

        function aggiornaBadgePedane() {
            const input = document.getElementById('input_pedane_ritirate');
            const badge = document.getElementById('badge_pedane');
            if (!input || !badge) return;
            const val = parseInt(input.value || 0);
            if (val === 0) {
                badge.className = 'badge bg-danger ms-2';
                badge.innerHTML = '<i class="ri-close-line"></i> Nessuna';
            } else if (val < pedaneDaRitirare) {
                badge.className = 'badge bg-warning text-dark ms-2';
                badge.innerHTML = '<i class="ri-error-warning-line"></i> Parziale';
            } else {
                badge.className = 'badge bg-success ms-2';
                badge.innerHTML = '<i class="ri-check-line"></i> OK';
            }
        }

        // ── Foto ─────────────────────────────────────────────
        const fotoSelezionate = [];

        function aggiungiAnteprima(input) {
            const preview = document.getElementById('foto-preview');
            Array.from(input.files).forEach(file => {
                fotoSelezionate.push(file);
                const idx = fotoSelezionate.length - 1;

                const wrap = document.createElement('div');
                wrap.className = 'foto-preview-item';
                wrap.dataset.idx = idx;

                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        wrap.innerHTML = `
                            <img src="${e.target.result}" alt="foto">
                            <button class="remove-foto" onclick="rimuoviFoto(${idx})">×</button>`;
                    };
                    reader.readAsDataURL(file);
                } else {
                    wrap.innerHTML = `
                        <div class="d-flex align-items-center justify-content-center bg-light border rounded"
                             style="width:80px;height:80px;font-size:11px;text-overflow:ellipsis;overflow:hidden;padding:4px;text-align:center;">
                            <i class="ri-file-pdf-line fs-4 text-danger d-block"></i>
                            <span>${file.name.substring(0, 15)}</span>
                        </div>
                        <button class="remove-foto" onclick="rimuoviFoto(${idx})">×</button>`;
                }

                preview.appendChild(wrap);
            });
            input.value = '';
        }

        function rimuoviFoto(idx) {
            fotoSelezionate[idx] = null;
            const el = document.querySelector(`[data-idx="${idx}"]`);
            if (el) el.remove();
        }

        // ── Completa ordine ──────────────────────────────────
        async function completaOrdine(btn) {
            if (!confirm('Confermi di completare questo ordine?')) return;

            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Completamento...';
            btn.disabled = true;

            // 1. Carica eventuali foto
            const fotoValide = fotoSelezionate.filter(f => f !== null);
            if (fotoValide.length > 0) {
                const formData = new FormData();
                fotoValide.forEach(f => formData.append('foto[]', f));
                formData.append('_token', window.csrfToken);
                try {
                    await fetch('/autista/consegna/' + idOrdine + '/upload-foto', {
                        method: 'POST',
                        body: formData
                    });
                } catch(e) {
                    console.warn('Upload foto fallito:', e);
                }
            }

            // 2. Completa l'ordine
            const inputPedane    = document.getElementById('input_pedane_ritirate');
            const pedaneRitirate = inputPedane ? parseInt(inputPedane.value || 0) : null;

            fetch('/autista/ordine/completa', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify({
                    id_ordine: idOrdine,
                    pedane_ritirate: pedaneRitirate
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect || '/autista/ordine/' + idOrdine + '/completato';
                } else {
                    alert('Errore: ' + (data.message || 'Riprova'));
                    btn.innerHTML = btnTestoDefault;
                    btn.disabled = false;
                }
            })
            .catch(() => {
                alert('Errore di connessione');
                btn.innerHTML = '<i class="ri-checkbox-circle-line me-2"></i> COMPLETA ORDINE';
                btn.disabled = false;
            });
        }
    </script>
@endsection