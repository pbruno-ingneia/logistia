@extends('autista.common.layout')

@section('title', 'Le mie Consegne')

@section('styles')
    <style>
        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            overflow-x: auto;
            padding-bottom: 5px;
        }

        .filter-tab {
            padding: 8px 16px;
            border-radius: 20px;
            background: white;
            border: 2px solid #eee;
            font-weight: 500;
            font-size: 0.9rem;
            white-space: nowrap;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-tab.active {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .consegna-card {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .consegna-card:active {
            transform: scale(0.98);
        }

        .consegna-card .header {
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f0f0f0;
        }

        .consegna-card .header .numero {
            font-weight: 700;
            font-size: 1.1rem;
        }

        .consegna-card .body {
            padding: 15px 20px;
        }

        .consegna-card .location {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 15px;
        }

        .consegna-card .location .line {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .consegna-card .location .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 3px solid;
        }

        .consegna-card .location .dot.start {
            border-color: var(--success-color);
            background: white;
        }

        .consegna-card .location .dot.end {
            border-color: var(--danger-color);
            background: var(--danger-color);
        }

        .consegna-card .location .connector {
            width: 2px;
            height: 30px;
            background: #ddd;
            margin: 4px 0;
        }

        .consegna-card .location .addresses {
            flex: 1;
        }

        .consegna-card .location .address-item {
            margin-bottom: 12px;
        }

        .consegna-card .location .address-item:last-child {
            margin-bottom: 0;
        }

        .consegna-card .location .label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .consegna-card .location .address {
            font-weight: 500;
        }

        .consegna-card .info-row {
            display: flex;
            gap: 20px;
            padding-top: 12px;
            border-top: 1px solid #f0f0f0;
        }

        .consegna-card .info-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .consegna-card .info-item i {
            font-size: 1rem;
        }

        .consegna-card .actions {
            display: flex;
            gap: 10px;
            padding: 15px 20px;
            background: #f8f9fa;
        }

        .consegna-card .actions .btn {
            flex: 1;
            padding: 10px;
            font-size: 0.9rem;
        }

        .badge-priority {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-priority.high {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }

        .badge-priority.medium {
            background: rgba(243, 156, 18, 0.1);
            color: var(--warning-color);
        }

        .badge-priority.low {
            background: rgba(39, 174, 96, 0.1);
            color: var(--success-color);
        }

        .summary-bar {
            display: flex;
            justify-content: space-around;
            background: white;
            border-radius: var(--radius-lg);
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-sm);
        }

        .summary-item {
            text-align: center;
        }

        .summary-item .value {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .summary-item .label {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .summary-item.pending .value { color: var(--warning-color); }
        .summary-item.active .value { color: var(--primary-color); }
        .summary-item.completed .value { color: var(--success-color); }
        .summary-item.cancelled .value { color: var(--danger-color, #e74c3c); }

        /* Signature canvas */
        .signature-container {
            border: 2px dashed #ddd;
            border-radius: 8px;
            background: #fafafa;
            overflow: hidden;
            position: relative;
        }

        .signature-canvas {
            width: 100%;
            height: 120px;
            display: block;
            touch-action: none;
        }

        /* Fullscreen signature */
        .signature-fullscreen-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: white;
            z-index: 9999;
            display: none;
            flex-direction: column;
        }

        .signature-fullscreen-overlay.active {
            display: flex;
        }

        .signature-fullscreen-header {
            background: var(--primary-color);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .signature-fullscreen-header h5 {
            margin: 0;
            font-size: 1.1rem;
        }

        .signature-fullscreen-header .btn-close-fs {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .signature-fullscreen-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 20px;
            background: #f5f5f5;
        }

        .signature-fullscreen-canvas-container {
            flex: 1;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .signature-fullscreen-canvas {
            width: 100%;
            height: 100%;
            display: block;
            touch-action: none;
        }

        .signature-fullscreen-hint {
            text-align: center;
            padding: 15px;
            color: #666;
            font-size: 0.9rem;
        }

        .signature-fullscreen-footer {
            display: flex;
            gap: 10px;
            padding: 15px 20px;
            background: white;
            border-top: 1px solid #eee;
        }

        .signature-fullscreen-footer .btn-fs {
            flex: 1;
            padding: 14px 20px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .signature-fullscreen-footer .btn-clear {
            background: #f0f0f0;
            color: #666;
        }

        .signature-fullscreen-footer .btn-confirm {
            background: linear-gradient(135deg, #27ae60 0%, #1e8449 100%);
            color: white;
        }

        /* Expand button */
        .btn-expand-signature {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(0,0,0,0.5);
            border: none;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            cursor: pointer;
            z-index: 10;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
        }

        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 15px;
        }
    </style>
@endsection

@section('content')
    <div class="fade-in">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">
                <i class="ri-file-list-3-line text-primary me-2"></i>
                Consegne
            </h4>

            <div class="d-flex align-items-center gap-2">
                <span class="text-muted">{{ date('d/m/Y') }}</span>

                <a href="#" class="btn btn-outline-secondary btn-sm position-relative" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNotifiche">
                    <i class="ri-notification-3-line" style="font-size: 1.1rem;"></i>
                    <span id="badgeNotifiche" style="position:absolute;top:-5px;right:-5px;background:#e74c3c;color:white;border-radius:50%;min-width:18px;height:18px;font-size:0.65rem;display:none;align-items:center;justify-content:center;font-weight:700;">0</span>
                </a>
            </div>
        </div>

        <!-- Summary -->
        <div class="summary-bar">
            <div class="summary-item pending">
                <div class="value" id="countPending">{{ $consegne->whereIn('stato', ['pianificato', 'assegnato'])->count() }}</div>
                <div class="label">Da fare</div>
            </div>
            <div class="summary-item active">
                <div class="value" id="countActive">{{ $consegne->where('stato', 'in_corso')->count() }}</div>
                <div class="label">In corso</div>
            </div>
            <div class="summary-item completed">
                <div class="value" id="countCompleted">{{ $consegne->where('stato', 'completato')->count() }}</div>
                <div class="label">Completate</div>
            </div>
            <div class="summary-item cancelled">
                <div class="value" id="countCancelled">{{ $consegne->where('stato', 'annullato')->count() }}</div>
                <div class="label">Annullate</div>
            </div>
        </div>

        <!-- Filtri -->
        <div class="filter-tabs">
            <div class="filter-tab active" data-filter="all">Tutte</div>
            <div class="filter-tab" data-filter="pianificato">Pianificate</div>
            <div class="filter-tab d-none" data-filter="assegnato">Assegnate</div>
            <div class="filter-tab" data-filter="in_corso">In corso</div>
            <div class="filter-tab" data-filter="completato">Completate</div>
            <div class="filter-tab" data-filter="annullato">Annullate</div>
        </div>

        <!-- Lista Consegne -->
        <div id="consegneList">
            @forelse($consegne as $consegna)
                <div class="consegna-card" data-stato="{{ $consegna->stato }}">
                    <div class="header">
                        <span class="numero">#{{ $consegna->numero_ordine ?? $consegna->id }}</span>
                        @php
                            $badgeClass = 'low';
                            $badgeText = 'Pianificato';
                            switch($consegna->stato) {
                                case 'in_corso':
                                    $badgeClass = 'medium';
                                    $badgeText = 'In corso';
                                    break;
                                case 'assegnato':
                                    $badgeClass = 'medium';
                                    $badgeText = 'Assegnato';
                                    break;
                                case 'completato':
                                    $badgeClass = 'low';
                                    $badgeText = 'Completato';
                                    break;
                                case 'annullato':
                                    $badgeClass = 'high';
                                    $badgeText = 'Annullato';
                                    break;
                                default:
                                    $badgeClass = 'low';
                                    $badgeText = 'Pianificato';
                            }
                        @endphp
                        <span class="badge-priority {{ $badgeClass }}">{{ $badgeText }}</span>
                        @if($consegna->tappa_id && $consegna->totale_tappe > 1)
                            <span class="badge bg-primary ms-1" style="font-size:10px;">
                                <i class="ri-route-line"></i> T{{ $consegna->numero_tappa }}/{{ $consegna->totale_tappe }}
                            </span>
                        @endif
                        @if($consegna->tappa_stato === 'attesa')
                            <span class="badge bg-secondary ms-1" style="font-size:10px;">
                                <i class="ri-time-line"></i> In attesa
                            </span>
                        @endif
                    </div>

                    <div class="body">
                        <div class="location">
                            <div class="line">
                                <div class="dot start"></div>
                                <div class="connector"></div>
                                <div class="dot end"></div>
                            </div>
                            <div class="addresses">
                                <div class="address-item">
                                    <div class="label">Ritiro</div>
                                    <div class="address">{{ $consegna->indirizzo_ritiro ?? 'N/D' }}</div>
                                </div>
                                <div class="address-item">
                                    <div class="label">Consegna</div>
                                    <div class="address">{{ $consegna->indirizzo_consegna ?? 'N/D' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="info-row">
                            @if($consegna->data_ritiro)
                                <div class="info-item">
                                    <i class="ri-time-line"></i>
                                    Ritiro: {{ \Carbon\Carbon::parse($consegna->data_ritiro)->format('d/m') }}
                                    @if($consegna->ora_ritiro) {{ $consegna->ora_ritiro }} @endif
                                </div>
                            @endif
                            @if($consegna->cliente)
                                <div class="info-item">
                                    <i class="ri-user-line"></i>
                                    {{ Str::limit($consegna->cliente, 20) }}
                                </div>
                            @endif
                            @if($consegna->peso_kg)
                                <div class="info-item">
                                    <i class="ri-scales-line"></i>
                                    {{ $consegna->peso_kg }} kg
                                </div>
                            @endif
                        </div>

                        @if($consegna->descrizione_merce)
                            <div class="mt-2 text-muted small">
                                <i class="ri-archive-line me-1"></i>
                                {{ Str::limit($consegna->descrizione_merce, 50) }}
                            </div>
                        @endif
                    </div>

                    <div class="actions">
                        @if($consegna->tappa_stato === 'attesa')
                            {{-- Tappa in attesa: mostra info, nessuna azione --}}
                            <div class="btn btn-outline-secondary w-100" style="cursor:default;opacity:.7;">
                                <i class="ri-time-line me-1"></i>
                                In attesa del carico dal precedente autista
                            </div>

                        @elseif($consegna->stato == 'pianificato' || $consegna->stato == 'assegnato')
                            {{-- Stato: Da fare - Solo Naviga + Inizia --}}
                            <a href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($consegna->indirizzo_consegna ?? '') }}"
                               target="_blank" class="btn btn-outline-custom">
                                <i class="ri-navigation-line me-1"></i>
                                Naviga
                            </a>
                            <button class="btn btn-outline-secondary" onclick="apriUploadFoto({{ $consegna->id }})" title="Allega foto" style="flex:none!important;width:44px;padding:10px;border-radius:8px;">
                                <i class="ri-camera-line"></i>
                            </button>
                            <button class="btn btn-primary-custom" onclick="iniziaConsegna({{ $consegna->id }})">
                                <i class="ri-play-line me-1"></i>
                                Inizia
                            </button>

                        @elseif($consegna->stato == 'in_corso')
                            {{-- Stato: In corso - Naviga + Menu (Completa/Rinvia/Annulla) --}}
                            <a href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($consegna->indirizzo_consegna ?? '') }}"
                               target="_blank" class="btn btn-outline-custom">
                                <i class="ri-navigation-line me-1"></i>
                                Naviga
                            </a>
                            <button class="btn btn-outline-secondary" onclick="apriUploadFoto({{ $consegna->id }})" title="Scatta foto" style="flex:none!important;width:44px;padding:10px;border-radius:8px;">
                                <i class="ri-camera-line"></i>
                            </button>
                            <button class="btn btn-success-custom" onclick="completaConsegna({{ $consegna->id }})">
                                <i class="ri-check-line me-1"></i>
                                Completa
                            </button>
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" style="border-radius: 8px;">
                                    <i class="ri-more-2-fill"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item text-warning" href="#" onclick="event.preventDefault(); rinviaConsegna({{ $consegna->id }}, '{{ addslashes($consegna->numero_ordine) }}')">
                                            <i class="ri-calendar-todo-line me-2"></i>Rinvia
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); annullaConsegna({{ $consegna->id }}, '{{ addslashes($consegna->numero_ordine) }}')">
                                            <i class="ri-close-circle-line me-2"></i>Annulla
                                        </a>
                                    </li>
                                </ul>
                            </div>

                        @elseif($consegna->stato == 'completato')
                            <a href="/autista/ordine/{{ $consegna->id }}/completato" class="btn btn-outline-success" style="flex: 1;">
                                <i class="ri-file-text-line me-1"></i>
                                Vedi DDT
                            </a>
                            <button class="btn btn-outline-secondary" onclick="apriUploadFoto({{ $consegna->id }})" title="Allega foto" style="flex:none!important;width:44px;padding:10px;border-radius:8px;">
                                <i class="ri-camera-line"></i>
                            </button>
                            <a href="/autista/ordine/{{ $consegna->id }}/completato" class="btn btn-success-custom">
                                <i class="ri-share-line"></i>
                            </a>

                        @elseif($consegna->stato == 'annullato')
                            {{-- Stato: Annullato --}}
                            <button class="btn btn-outline-danger" disabled style="flex: 1;">
                                <i class="ri-close-circle-line me-1"></i>
                                Annullata
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="ri-inbox-line"></i>
                    <h5>Nessuna consegna</h5>
                    <p class="text-muted">Non ci sono consegne programmate per oggi</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Modal Completa Consegna -->
    <div class="modal fade" id="completaModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border-radius: var(--radius-lg);">
                <div class="modal-header border-0">
                    <h5 class="modal-title">
                        <i class="ri-checkbox-circle-line text-success me-2"></i>
                        Completa Consegna
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="consegnaId">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Note (opzionale)</label>
                        <textarea class="form-control" id="noteConsegna" rows="2"
                                  placeholder="Inserisci eventuali note sulla consegna..."></textarea>
                    </div>

                    <!-- Firma Cliente -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold mb-0">
                                <i class="ri-user-line me-1"></i>Firma Cliente
                            </label>
                            <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="clearSignature('cliente')">
                                <i class="ri-delete-bin-line"></i> Cancella
                            </button>
                        </div>
                        <div class="signature-container">
                            <button type="button" class="btn-expand-signature" onclick="openFullscreenSignature('Cliente')" title="Ingrandisci">
                                <i class="ri-fullscreen-line"></i>
                            </button>
                            <canvas id="signatureCliente" class="signature-canvas"></canvas>
                        </div>
                    </div>

                    <!-- Firma Autista -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold mb-0">
                                <i class="ri-steering-line me-1"></i>Firma Autista
                            </label>
                            <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="clearSignature('autista')">
                                <i class="ri-delete-bin-line"></i> Cancella
                            </button>
                        </div>
                        <div class="signature-container">
                            <button type="button" class="btn-expand-signature" onclick="openFullscreenSignature('Autista')" title="Ingrandisci">
                                <i class="ri-fullscreen-line"></i>
                            </button>
                            <canvas id="signatureAutista" class="signature-canvas"></canvas>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-success-custom" onclick="confermaCompletamento()">
                        <i class="ri-check-line me-1"></i>
                        Conferma
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Fullscreen Signature Overlay -->
    <div class="signature-fullscreen-overlay" id="signatureFullscreen">
        <div class="signature-fullscreen-header">
            <h5 id="fullscreenTitle">Firma Cliente</h5>
            <button class="btn-close-fs" onclick="closeFullscreenSignature()">
                <i class="ri-close-line"></i>
            </button>
        </div>
        <div class="signature-fullscreen-body">
            <div class="signature-fullscreen-canvas-container">
                <canvas id="signatureFullscreenCanvas" class="signature-fullscreen-canvas"></canvas>
            </div>
            <div class="signature-fullscreen-hint">
                <i class="ri-hand-coin-line me-1"></i>
                Firma con il dito nell'area sopra
            </div>
        </div>
        <div class="signature-fullscreen-footer">
            <button class="btn-fs btn-clear" onclick="clearFullscreenSignature()">
                <i class="ri-delete-bin-line"></i>
                Cancella
            </button>
            <button class="btn-fs btn-confirm" onclick="confirmFullscreenSignature()">
                <i class="ri-check-line"></i>
                Conferma
            </button>
        </div>
    </div>

    <!-- Modal Annulla Consegna -->
    <div class="modal fade" id="annullaModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border-radius: var(--radius-lg);">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-danger">
                        <i class="ri-close-circle-line me-2"></i>
                        Annulla Consegna
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="annullaConsegnaId">

                    <div class="alert alert-warning">
                        <i class="ri-error-warning-line me-2"></i>
                        Stai per annullare l'ordine <strong id="annullaNumeroOrdine"></strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Motivo annullamento <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="motivoAnnullamento" rows="2"
                                  placeholder="Inserisci il motivo dell'annullamento..." required></textarea>
                    </div>

                    <!-- Firma Cliente -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold mb-0">
                                <i class="ri-user-line me-1"></i>Firma Cliente
                            </label>
                            <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="clearSignatureAnnulla('cliente')">
                                <i class="ri-delete-bin-line"></i> Cancella
                            </button>
                        </div>
                        <div class="signature-container">
                            <button type="button" class="btn-expand-signature" onclick="openFullscreenSignatureAnnulla('Cliente')" title="Ingrandisci">
                                <i class="ri-fullscreen-line"></i>
                            </button>
                            <canvas id="signatureAnnullaCliente" class="signature-canvas"></canvas>
                        </div>
                    </div>

                    <!-- Firma Autista -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold mb-0">
                                <i class="ri-steering-line me-1"></i>Firma Autista
                            </label>
                            <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="clearSignatureAnnulla('autista')">
                                <i class="ri-delete-bin-line"></i> Cancella
                            </button>
                        </div>
                        <div class="signature-container">
                            <button type="button" class="btn-expand-signature" onclick="openFullscreenSignatureAnnulla('Autista')" title="Ingrandisci">
                                <i class="ri-fullscreen-line"></i>
                            </button>
                            <canvas id="signatureAnnullaAutista" class="signature-canvas"></canvas>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Indietro</button>
                    <button type="button" class="btn btn-danger" onclick="confermaAnnullamento()">
                        <i class="ri-close-circle-line me-1"></i>
                        Conferma Annullamento
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Rinvia Consegna -->
    <div class="modal fade" id="rinviaModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border-radius: var(--radius-lg);">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-warning">
                        <i class="ri-calendar-todo-line me-2"></i>
                        Rinvia Consegna
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="rinviaConsegnaId">

                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        Stai per rinviare l'ordine <strong id="rinviaNumeroOrdine"></strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nuova data consegna <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="nuovaDataConsegna" required
                               min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nuova ora (opzionale)</label>
                        <input type="time" class="form-control" id="nuovaOraConsegna">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Motivo rinvio <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="motivoRinvio" rows="2"
                                  placeholder="Inserisci il motivo del rinvio..." required></textarea>
                    </div>

                    <!-- Firma Cliente -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold mb-0">
                                <i class="ri-user-line me-1"></i>Firma Cliente
                            </label>
                            <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="clearSignatureRinvia('cliente')">
                                <i class="ri-delete-bin-line"></i> Cancella
                            </button>
                        </div>
                        <div class="signature-container">
                            <button type="button" class="btn-expand-signature" onclick="openFullscreenSignatureRinvia('Cliente')" title="Ingrandisci">
                                <i class="ri-fullscreen-line"></i>
                            </button>
                            <canvas id="signatureRinviaCliente" class="signature-canvas"></canvas>
                        </div>
                    </div>

                    <!-- Firma Autista -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold mb-0">
                                <i class="ri-steering-line me-1"></i>Firma Autista
                            </label>
                            <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="clearSignatureRinvia('autista')">
                                <i class="ri-delete-bin-line"></i> Cancella
                            </button>
                        </div>
                        <div class="signature-container">
                            <button type="button" class="btn-expand-signature" onclick="openFullscreenSignatureRinvia('Autista')" title="Ingrandisci">
                                <i class="ri-fullscreen-line"></i>
                            </button>
                            <canvas id="signatureRinviaAutista" class="signature-canvas"></canvas>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Indietro</button>
                    <button type="button" class="btn btn-warning" onclick="confermaRinvio()">
                        <i class="ri-calendar-todo-line me-1"></i>
                        Conferma Rinvio
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Upload Foto --}}
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
                            <option value="merce">📦 Merce consegnata</option>
                            <option value="ricevuta">📄 Ricevuta</option>
                            <option value="danno">⚠️ Danno</option>
                            <option value="altro">📎 Altro</option>
                        </select>
                    </div>
                    <label class="btn btn-primary-custom btn-lg w-100 mb-3" style="padding:15px;">
                        <i class="ri-camera-line me-2"></i> Scatta Foto
                        <input type="file" accept="image/*" capture="environment" onchange="uploadFotoSingola(this)" style="display:none;">
                    </label>
                    <label class="btn btn-outline-custom w-100 mb-3" style="padding:12px;">
                        <i class="ri-image-line me-2"></i> Scegli dalla galleria
                        <input type="file" accept="image/*" onchange="uploadFotoSingola(this)" style="display:none;">
                    </label>
                    <div id="foto_upload_status"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- Offcanvas Notifiche -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNotifiche">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title">
                <i class="ri-notification-3-line text-primary me-2"></i>Notifiche
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0">
            <div class="text-center py-4" id="notificheLoading">
                <div class="spinner-border spinner-border-sm text-primary"></div>
                <div class="small text-muted mt-2">Caricamento...</div>
            </div>
            <div id="notificheList"></div>
            <div class="text-center py-5" id="notificheEmpty" style="display:none;">
                <i class="ri-notification-off-line" style="font-size:2.5rem;color:#ddd;"></i>
                <p class="text-muted mt-2 mb-0">Nessuna notifica</p>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>

        // ==================== POLLING NOTIFICHE ====================
        let ultimaNotificaId = 0;
        function controllaNotifiche() {
            fetch('/autista/notifiche/nuove', { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    const badge = document.getElementById('badgeNotifiche');
                    if (data.count > 0) {
                        badge.style.display = 'flex';
                        badge.textContent = data.count;
                        if (data.notifiche && data.notifiche[0].id > ultimaNotificaId) {
                            ultimaNotificaId = data.notifiche[0].id;
                            const el = document.createElement('div');
                            el.style.cssText = 'position:fixed;top:15px;left:50%;transform:translateX(-50%);z-index:10000;min-width:300px;max-width:90vw;';
                            el.innerHTML = '<div class="toast show" role="alert" style="border-radius:12px;"><div class="toast-header bg-primary text-white"><strong class="me-auto">' + data.notifiche[0].titolo + '</strong><button type="button" class="btn-close btn-close-white" onclick="this.closest(\'div[style]\').remove()"></button></div>' + (data.notifiche[0].messaggio ? '<div class="toast-body">' + data.notifiche[0].messaggio + '</div>' : '') + '</div>';
                            document.body.appendChild(el);
                            if (navigator.vibrate) navigator.vibrate(200);
                            setTimeout(() => el.remove(), 5000);
                        }
                    } else { badge.style.display = 'none'; }
                }).catch(() => {});
        }
        controllaNotifiche();
        setInterval(controllaNotifiche, 30000);

        // ==================== UPLOAD FOTO ====================
        function apriUploadFoto(idOrdine) {
            document.getElementById('foto_id_ordine').value = idOrdine;
            document.getElementById('foto_upload_status').innerHTML = '';
            new bootstrap.Modal(document.getElementById('modalUploadFoto')).show();
        }

        function uploadFotoSingola(input) {
            if (!input.files[0]) return;
            const idOrdine = document.getElementById('foto_id_ordine').value;
            const tipo = document.getElementById('foto_tipo').value;
            const status = document.getElementById('foto_upload_status');
            status.innerHTML = '<div class="d-flex align-items-center justify-content-center gap-2 py-2"><div class="spinner-border spinner-border-sm text-primary"></div> Caricamento...</div>';

            const formData = new FormData();
            formData.append('foto', input.files[0]);
            formData.append('tipo', tipo);

            fetch('/autista/consegna/' + idOrdine + '/upload-foto', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': window.csrfToken },
                body: formData
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        status.innerHTML = '<div class="alert alert-success py-2"><i class="ri-check-line"></i> Foto caricata!</div><img src="' + data.foto.url + '" style="width:100px;height:100px;object-fit:cover;border-radius:8px;" class="mt-2">';
                        setTimeout(() => { bootstrap.Modal.getInstance(document.getElementById('modalUploadFoto'))?.hide(); }, 1500);
                    } else {
                        status.innerHTML = '<div class="alert alert-danger py-2">' + (data.message || 'Errore') + '</div>';
                    }
                })
                .catch(() => { status.innerHTML = '<div class="alert alert-danger py-2">Errore di connessione</div>'; });
            input.value = '';
        }
        // Filtri
        document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                const filter = this.dataset.filter;
                document.querySelectorAll('.consegna-card').forEach(card => {
                    const stato = card.dataset.stato;
                    let show = false;

                    if (filter === 'all') {
                        show = true;
                    } else if (filter === 'pianificato') {
                        show = (stato === 'pianificato' || stato === 'assegnato');
                    } else {
                        show = (stato === filter);
                    }

                    card.style.display = show ? 'block' : 'none';
                });
            });
        });

        // Inizia consegna
        async function iniziaConsegna(id) {
            try {
                const response = await fetch(`/autista/consegna/${id}/inizia`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.csrfToken
                    }
                });

                const data = await response.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert('Errore: ' + (data.message || 'Impossibile iniziare la consegna'));
                }
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore di connessione');
            }
        }

        function completaConsegna(id) {
            window.location.href = '/autista/ordine/' + id + '/completa';
        }


        // Canvas firma
        let signatureCanvases = {};
        let signatureContexts = {};
        let isDrawing = false;

        function initSignature(type) {
            const canvas = document.getElementById('signature' + type);
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            const container = canvas.parentElement;
            canvas.width = container.offsetWidth;
            canvas.height = 120;

            ctx.fillStyle = 'white';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.strokeStyle = '#2c3e50';
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';

            signatureCanvases[type] = canvas;
            signatureContexts[type] = ctx;

            const newCanvas = canvas.cloneNode(true);
            canvas.parentNode.replaceChild(newCanvas, canvas);
            signatureCanvases[type] = newCanvas;
            signatureContexts[type] = newCanvas.getContext('2d');

            signatureContexts[type].fillStyle = 'white';
            signatureContexts[type].fillRect(0, 0, newCanvas.width, newCanvas.height);
            signatureContexts[type].strokeStyle = '#2c3e50';
            signatureContexts[type].lineWidth = 2;
            signatureContexts[type].lineCap = 'round';
            signatureContexts[type].lineJoin = 'round';

            newCanvas.addEventListener('touchstart', (e) => startDrawing(e, type), { passive: false });
            newCanvas.addEventListener('touchmove', (e) => draw(e, type), { passive: false });
            newCanvas.addEventListener('touchend', () => stopDrawing());

            newCanvas.addEventListener('mousedown', (e) => startDrawing(e, type));
            newCanvas.addEventListener('mousemove', (e) => draw(e, type));
            newCanvas.addEventListener('mouseup', () => stopDrawing());
            newCanvas.addEventListener('mouseout', () => stopDrawing());
        }

        function startDrawing(e, type) {
            isDrawing = true;
            const pos = getPosition(e, signatureCanvases[type]);
            signatureContexts[type].beginPath();
            signatureContexts[type].moveTo(pos.x, pos.y);
        }

        function draw(e, type) {
            if (!isDrawing) return;
            e.preventDefault();
            const pos = getPosition(e, signatureCanvases[type]);
            signatureContexts[type].lineTo(pos.x, pos.y);
            signatureContexts[type].stroke();
        }

        function stopDrawing() {
            isDrawing = false;
        }

        function getPosition(e, canvas) {
            const rect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: (clientX - rect.left) * scaleX,
                y: (clientY - rect.top) * scaleY
            };
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
            return !pixelBuffer.some(color => color !== 0xFFFFFFFF);
        }

        // ==================== FULLSCREEN SIGNATURE ====================

        let currentFullscreenType = null;
        let fullscreenCanvas = null;
        let fullscreenCtx = null;
        let isDrawingFullscreen = false;

        function openFullscreenSignature(type) {
            currentFullscreenType = type;
            document.querySelector('.modal-backdrop')?.classList.add('d-none');
            document.getElementById('completaModal').style.display = 'none';

            const overlay = document.getElementById('signatureFullscreen');
            overlay.classList.add('active');

            document.getElementById('fullscreenTitle').textContent =
                type === 'Cliente' ? 'Firma Cliente' : 'Firma Autista';

            setTimeout(() => {
                initFullscreenCanvas();
                const sourceCanvas = signatureCanvases[type];
                if (sourceCanvas && !isCanvasBlank(sourceCanvas)) {
                    fullscreenCtx.drawImage(sourceCanvas, 0, 0, fullscreenCanvas.width, fullscreenCanvas.height);
                }
            }, 100);
        }

        function initFullscreenCanvas() {
            fullscreenCanvas = document.getElementById('signatureFullscreenCanvas');
            const container = fullscreenCanvas.parentElement;

            fullscreenCanvas.width = container.offsetWidth;
            fullscreenCanvas.height = container.offsetHeight;

            fullscreenCtx = fullscreenCanvas.getContext('2d');
            fullscreenCtx.fillStyle = 'white';
            fullscreenCtx.fillRect(0, 0, fullscreenCanvas.width, fullscreenCanvas.height);
            fullscreenCtx.strokeStyle = '#2c3e50';
            fullscreenCtx.lineWidth = 3;
            fullscreenCtx.lineCap = 'round';
            fullscreenCtx.lineJoin = 'round';

            const newCanvas = fullscreenCanvas.cloneNode(true);
            fullscreenCanvas.parentNode.replaceChild(newCanvas, fullscreenCanvas);
            fullscreenCanvas = newCanvas;
            fullscreenCtx = fullscreenCanvas.getContext('2d');

            fullscreenCtx.fillStyle = 'white';
            fullscreenCtx.fillRect(0, 0, fullscreenCanvas.width, fullscreenCanvas.height);
            fullscreenCtx.strokeStyle = '#2c3e50';
            fullscreenCtx.lineWidth = 3;
            fullscreenCtx.lineCap = 'round';
            fullscreenCtx.lineJoin = 'round';

            fullscreenCanvas.addEventListener('touchstart', startDrawingFullscreen, { passive: false });
            fullscreenCanvas.addEventListener('touchmove', drawFullscreen, { passive: false });
            fullscreenCanvas.addEventListener('touchend', stopDrawingFullscreen);

            fullscreenCanvas.addEventListener('mousedown', startDrawingFullscreen);
            fullscreenCanvas.addEventListener('mousemove', drawFullscreen);
            fullscreenCanvas.addEventListener('mouseup', stopDrawingFullscreen);
            fullscreenCanvas.addEventListener('mouseout', stopDrawingFullscreen);
        }

        function startDrawingFullscreen(e) {
            isDrawingFullscreen = true;
            const pos = getPositionFullscreen(e);
            fullscreenCtx.beginPath();
            fullscreenCtx.moveTo(pos.x, pos.y);
        }

        function drawFullscreen(e) {
            if (!isDrawingFullscreen) return;
            e.preventDefault();
            const pos = getPositionFullscreen(e);
            fullscreenCtx.lineTo(pos.x, pos.y);
            fullscreenCtx.stroke();
        }

        function stopDrawingFullscreen() {
            isDrawingFullscreen = false;
        }

        function getPositionFullscreen(e) {
            const rect = fullscreenCanvas.getBoundingClientRect();
            const scaleX = fullscreenCanvas.width / rect.width;
            const scaleY = fullscreenCanvas.height / rect.height;
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: (clientX - rect.left) * scaleX,
                y: (clientY - rect.top) * scaleY
            };
        }

        function clearFullscreenSignature() {
            if (fullscreenCtx && fullscreenCanvas) {
                fullscreenCtx.fillStyle = 'white';
                fullscreenCtx.fillRect(0, 0, fullscreenCanvas.width, fullscreenCanvas.height);
            }
        }

        function closeFullscreenSignature() {
            document.getElementById('signatureFullscreen').classList.remove('active');
            document.querySelector('.modal-backdrop')?.classList.remove('d-none');

            // Riapri il modal corretto
            if (currentFullscreenType && currentFullscreenType.startsWith('Annulla')) {
                document.getElementById('annullaModal').style.display = 'block';
            } else if (currentFullscreenType && currentFullscreenType.startsWith('Rinvia')) {
                document.getElementById('rinviaModal').style.display = 'block';
            } else {
                document.getElementById('completaModal').style.display = 'block';
            }

            currentFullscreenType = null;
        }

        function confirmFullscreenSignature() {
            if (!currentFullscreenType || !fullscreenCanvas) return;

            let targetCanvas, targetCtx;

            if (currentFullscreenType.startsWith('Annulla')) {
                const type = currentFullscreenType.replace('Annulla', '');
                targetCanvas = signatureAnnullaCanvases[type];
                targetCtx = signatureAnnullaContexts[type];
            } else if (currentFullscreenType.startsWith('Rinvia')) {
                const type = currentFullscreenType.replace('Rinvia', '');
                targetCanvas = signatureRinviaCanvases[type];
                targetCtx = signatureRinviaContexts[type];
            } else {
                targetCanvas = signatureCanvases[currentFullscreenType];
                targetCtx = signatureContexts[currentFullscreenType];
            }

            if (targetCanvas && targetCtx) {
                targetCtx.fillStyle = 'white';
                targetCtx.fillRect(0, 0, targetCanvas.width, targetCanvas.height);
                targetCtx.drawImage(fullscreenCanvas, 0, 0, targetCanvas.width, targetCanvas.height);
            }

            closeFullscreenSignature();
        }

        // Conferma completamento
        async function confermaCompletamento() {
            const id = document.getElementById('consegnaId').value;
            const note = document.getElementById('noteConsegna').value;

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

            const firma_cliente = canvasCliente.toDataURL('image/png');
            const firma_autista = canvasAutista.toDataURL('image/png');

            try {
                const response = await fetch(`/autista/consegna/${id}/completa`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.csrfToken
                    },
                    body: JSON.stringify({ note, firma_cliente, firma_autista })
                });

                const data = await response.json();
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('completaModal')).hide();
                    location.reload();
                } else {
                    alert('Errore: ' + (data.message || 'Impossibile completare la consegna'));
                }
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore di connessione');
            }
        }

        // ==================== ANNULLA CONSEGNA ====================

        let signatureAnnullaCanvases = {};
        let signatureAnnullaContexts = {};

        function annullaConsegna(id, numeroOrdine) {
            document.getElementById('annullaConsegnaId').value = id;
            document.getElementById('annullaNumeroOrdine').textContent = numeroOrdine;
            document.getElementById('motivoAnnullamento').value = '';
            new bootstrap.Modal(document.getElementById('annullaModal')).show();

            setTimeout(() => {
                initSignatureAnnulla('Cliente');
                initSignatureAnnulla('Autista');
            }, 300);
        }

        function initSignatureAnnulla(type) {
            const canvas = document.getElementById('signatureAnnulla' + type);
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            const container = canvas.parentElement;
            canvas.width = container.offsetWidth;
            canvas.height = 120;

            ctx.fillStyle = 'white';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.strokeStyle = '#2c3e50';
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';

            signatureAnnullaCanvases[type] = canvas;
            signatureAnnullaContexts[type] = ctx;

            const newCanvas = canvas.cloneNode(true);
            canvas.parentNode.replaceChild(newCanvas, canvas);
            signatureAnnullaCanvases[type] = newCanvas;
            signatureAnnullaContexts[type] = newCanvas.getContext('2d');

            signatureAnnullaContexts[type].fillStyle = 'white';
            signatureAnnullaContexts[type].fillRect(0, 0, newCanvas.width, newCanvas.height);
            signatureAnnullaContexts[type].strokeStyle = '#2c3e50';
            signatureAnnullaContexts[type].lineWidth = 2;
            signatureAnnullaContexts[type].lineCap = 'round';
            signatureAnnullaContexts[type].lineJoin = 'round';

            newCanvas.addEventListener('touchstart', (e) => startDrawingAnnulla(e, type), { passive: false });
            newCanvas.addEventListener('touchmove', (e) => drawAnnulla(e, type), { passive: false });
            newCanvas.addEventListener('touchend', () => isDrawing = false);

            newCanvas.addEventListener('mousedown', (e) => startDrawingAnnulla(e, type));
            newCanvas.addEventListener('mousemove', (e) => drawAnnulla(e, type));
            newCanvas.addEventListener('mouseup', () => isDrawing = false);
            newCanvas.addEventListener('mouseout', () => isDrawing = false);
        }

        function startDrawingAnnulla(e, type) {
            isDrawing = true;
            const pos = getPosition(e, signatureAnnullaCanvases[type]);
            signatureAnnullaContexts[type].beginPath();
            signatureAnnullaContexts[type].moveTo(pos.x, pos.y);
        }

        function drawAnnulla(e, type) {
            if (!isDrawing) return;
            e.preventDefault();
            const pos = getPosition(e, signatureAnnullaCanvases[type]);
            signatureAnnullaContexts[type].lineTo(pos.x, pos.y);
            signatureAnnullaContexts[type].stroke();
        }

        function clearSignatureAnnulla(type) {
            const typeCap = type.charAt(0).toUpperCase() + type.slice(1);
            const canvas = signatureAnnullaCanvases[typeCap];
            const ctx = signatureAnnullaContexts[typeCap];
            if (canvas && ctx) {
                ctx.fillStyle = 'white';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
            }
        }

        function openFullscreenSignatureAnnulla(type) {
            currentFullscreenType = 'Annulla' + type;
            document.querySelector('.modal-backdrop')?.classList.add('d-none');
            document.getElementById('annullaModal').style.display = 'none';

            const overlay = document.getElementById('signatureFullscreen');
            overlay.classList.add('active');

            document.getElementById('fullscreenTitle').textContent =
                type === 'Cliente' ? 'Firma Cliente' : 'Firma Autista';

            setTimeout(() => {
                initFullscreenCanvas();
                const sourceCanvas = signatureAnnullaCanvases[type];
                if (sourceCanvas && !isCanvasBlank(sourceCanvas)) {
                    fullscreenCtx.drawImage(sourceCanvas, 0, 0, fullscreenCanvas.width, fullscreenCanvas.height);
                }
            }, 100);
        }

        async function confermaAnnullamento() {
            const id = document.getElementById('annullaConsegnaId').value;
            const motivo = document.getElementById('motivoAnnullamento').value.trim();

            if (!motivo) {
                alert('Inserisci il motivo dell\'annullamento');
                return;
            }

            const canvasCliente = signatureAnnullaCanvases['Cliente'];
            const canvasAutista = signatureAnnullaCanvases['Autista'];

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

            const firma_cliente = canvasCliente.toDataURL('image/png');
            const firma_autista = canvasAutista.toDataURL('image/png');

            try {
                const response = await fetch(`/autista/consegna/${id}/annulla`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.csrfToken
                    },
                    body: JSON.stringify({ motivo, firma_cliente, firma_autista })
                });

                const data = await response.json();
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('annullaModal')).hide();
                    location.reload();
                } else {
                    alert('Errore: ' + (data.message || 'Impossibile annullare la consegna'));
                }
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore di connessione');
            }
        }

        // ==================== RINVIA CONSEGNA ====================

        let signatureRinviaCanvases = {};
        let signatureRinviaContexts = {};

        function rinviaConsegna(id, numeroOrdine) {
            document.getElementById('rinviaConsegnaId').value = id;
            document.getElementById('rinviaNumeroOrdine').textContent = numeroOrdine;
            document.getElementById('nuovaDataConsegna').value = '';
            document.getElementById('nuovaOraConsegna').value = '';
            document.getElementById('motivoRinvio').value = '';
            new bootstrap.Modal(document.getElementById('rinviaModal')).show();

            setTimeout(() => {
                initSignatureRinvia('Cliente');
                initSignatureRinvia('Autista');
            }, 300);
        }

        function initSignatureRinvia(type) {
            const canvas = document.getElementById('signatureRinvia' + type);
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            const container = canvas.parentElement;
            canvas.width = container.offsetWidth;
            canvas.height = 120;

            ctx.fillStyle = 'white';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.strokeStyle = '#2c3e50';
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';

            signatureRinviaCanvases[type] = canvas;
            signatureRinviaContexts[type] = ctx;

            const newCanvas = canvas.cloneNode(true);
            canvas.parentNode.replaceChild(newCanvas, canvas);
            signatureRinviaCanvases[type] = newCanvas;
            signatureRinviaContexts[type] = newCanvas.getContext('2d');

            signatureRinviaContexts[type].fillStyle = 'white';
            signatureRinviaContexts[type].fillRect(0, 0, newCanvas.width, newCanvas.height);
            signatureRinviaContexts[type].strokeStyle = '#2c3e50';
            signatureRinviaContexts[type].lineWidth = 2;
            signatureRinviaContexts[type].lineCap = 'round';
            signatureRinviaContexts[type].lineJoin = 'round';

            newCanvas.addEventListener('touchstart', (e) => startDrawingRinvia(e, type), { passive: false });
            newCanvas.addEventListener('touchmove', (e) => drawRinvia(e, type), { passive: false });
            newCanvas.addEventListener('touchend', () => isDrawing = false);

            newCanvas.addEventListener('mousedown', (e) => startDrawingRinvia(e, type));
            newCanvas.addEventListener('mousemove', (e) => drawRinvia(e, type));
            newCanvas.addEventListener('mouseup', () => isDrawing = false);
            newCanvas.addEventListener('mouseout', () => isDrawing = false);
        }

        function startDrawingRinvia(e, type) {
            isDrawing = true;
            const pos = getPosition(e, signatureRinviaCanvases[type]);
            signatureRinviaContexts[type].beginPath();
            signatureRinviaContexts[type].moveTo(pos.x, pos.y);
        }

        function drawRinvia(e, type) {
            if (!isDrawing) return;
            e.preventDefault();
            const pos = getPosition(e, signatureRinviaCanvases[type]);
            signatureRinviaContexts[type].lineTo(pos.x, pos.y);
            signatureRinviaContexts[type].stroke();
        }

        function clearSignatureRinvia(type) {
            const typeCap = type.charAt(0).toUpperCase() + type.slice(1);
            const canvas = signatureRinviaCanvases[typeCap];
            const ctx = signatureRinviaContexts[typeCap];
            if (canvas && ctx) {
                ctx.fillStyle = 'white';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
            }
        }

        function openFullscreenSignatureRinvia(type) {
            currentFullscreenType = 'Rinvia' + type;
            document.querySelector('.modal-backdrop')?.classList.add('d-none');
            document.getElementById('rinviaModal').style.display = 'none';

            const overlay = document.getElementById('signatureFullscreen');
            overlay.classList.add('active');

            document.getElementById('fullscreenTitle').textContent =
                type === 'Cliente' ? 'Firma Cliente' : 'Firma Autista';

            setTimeout(() => {
                initFullscreenCanvas();
                const sourceCanvas = signatureRinviaCanvases[type];
                if (sourceCanvas && !isCanvasBlank(sourceCanvas)) {
                    fullscreenCtx.drawImage(sourceCanvas, 0, 0, fullscreenCanvas.width, fullscreenCanvas.height);
                }
            }, 100);
        }

        async function confermaRinvio() {
            const id = document.getElementById('rinviaConsegnaId').value;
            const nuovaData = document.getElementById('nuovaDataConsegna').value;
            const nuovaOra = document.getElementById('nuovaOraConsegna').value;
            const motivo = document.getElementById('motivoRinvio').value.trim();

            if (!nuovaData) {
                alert('Seleziona la nuova data di consegna');
                return;
            }

            if (!motivo) {
                alert('Inserisci il motivo del rinvio');
                return;
            }

            const canvasCliente = signatureRinviaCanvases['Cliente'];
            const canvasAutista = signatureRinviaCanvases['Autista'];

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

            const firma_cliente = canvasCliente.toDataURL('image/png');
            const firma_autista = canvasAutista.toDataURL('image/png');

            try {
                const response = await fetch(`/autista/consegna/${id}/rinvia`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.csrfToken
                    },
                    body: JSON.stringify({
                        nuova_data: nuovaData,
                        nuova_ora: nuovaOra,
                        motivo: motivo,
                        firma_cliente: firma_cliente,
                        firma_autista: firma_autista
                    })
                });

                const data = await response.json();
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('rinviaModal')).hide();
                    alert('Consegna rinviata al ' + nuovaData);
                    location.reload();
                } else {
                    alert('Errore: ' + (data.message || 'Impossibile rinviare la consegna'));
                }
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore di connessione');
            }
        }
        // Carica notifiche nell'offcanvas quando si apre
        document.getElementById('offcanvasNotifiche')?.addEventListener('show.bs.offcanvas', function() {
            const list = document.getElementById('notificheList');
            const loading = document.getElementById('notificheLoading');
            const empty = document.getElementById('notificheEmpty');

            loading.style.display = 'block';
            list.innerHTML = '';
            empty.style.display = 'none';

            fetch('/autista/notifiche/lista', { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    loading.style.display = 'none';
                    if (!data.notifiche || data.notifiche.length === 0) {
                        empty.style.display = 'block';
                        return;
                    }
                    data.notifiche.forEach(n => {
                        list.innerHTML += `
                    <div class="p-3 border-bottom ${n.letta ? '' : 'bg-light'}" style="cursor:pointer;"
                         onclick="segnaLetta(${n.id}, this)">
                        <div class="d-flex align-items-start gap-3">
                            <div style="width:36px;height:36px;border-radius:50%;background:rgba(52,152,219,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="ri-notification-3-line text-primary"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-600" style="font-size:0.9rem;">${n.titolo}</div>
                                ${n.messaggio ? '<div class="text-muted small mt-1">' + n.messaggio + '</div>' : ''}
                                <div class="text-muted small mt-1"><i class="ri-time-line me-1"></i>${n.tempo_fa}</div>
                            </div>
                            ${!n.letta ? '<span style="width:8px;height:8px;border-radius:50%;background:var(--primary-color);flex-shrink:0;margin-top:6px;"></span>' : ''}
                        </div>
                    </div>`;
                    });

                    // Aggiorna badge
                    const badge = document.getElementById('badgeNotifiche');
                    const nonLette = data.notifiche.filter(n => !n.letta).length;
                    if (nonLette > 0) { badge.style.display = 'flex'; badge.textContent = nonLette; }
                    else { badge.style.display = 'none'; }
                })
                .catch(() => { loading.style.display = 'none'; empty.style.display = 'block'; });
        });

        function segnaLetta(id, el) {
            el.classList.remove('bg-light');
            el.querySelector('span[style*="border-radius:50%;background:var"]')?.remove();
            fetch('/autista/notifiche/' + id + '/letta', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken }
            }).catch(() => {});
        }
    </script>
@endsection