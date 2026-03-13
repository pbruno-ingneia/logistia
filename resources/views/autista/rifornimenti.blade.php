@extends('autista.common.layout')

@section('title', 'Rifornimenti')

@section('styles')
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            text-align: center;
        }

        .stat-card .icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            font-size: 20px;
        }

        .stat-card .valore {
            font-size: 1.3rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .stat-card .etichetta {
            font-size: 0.7rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-nuovo-rifornimento {
            background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 15px;
            width: 100%;
            font-weight: 600;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(230, 126, 34, 0.3);
        }

        .btn-nuovo-rifornimento:active {
            transform: scale(0.98);
        }

        .rifornimento-card {
            background: white;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            position: relative;
            overflow: hidden;
        }

        .rifornimento-card .data-badge {
            position: absolute;
            top: 0;
            right: 0;
            background: #2c3e50;
            color: white;
            padding: 4px 12px;
            border-radius: 0 12px 0 8px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .rifornimento-card .top-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
        }

        .rifornimento-card .fuel-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .rifornimento-card .fuel-icon.diesel {
            background: rgba(241, 196, 15, 0.15);
            color: #f39c12;
        }

        .rifornimento-card .fuel-icon.benzina {
            background: rgba(46, 204, 113, 0.15);
            color: #27ae60;
        }

        .rifornimento-card .fuel-icon.gpl {
            background: rgba(52, 152, 219, 0.15);
            color: #3498db;
        }

        .rifornimento-card .fuel-icon.metano {
            background: rgba(155, 89, 182, 0.15);
            color: #9b59b6;
        }

        .rifornimento-card .fuel-icon.adblue {
            background: rgba(52, 152, 219, 0.15);
            color: #2980b9;
        }

        .rifornimento-card .info-principale {
            flex: 1;
        }

        .rifornimento-card .info-principale .importo {
            font-size: 1.2rem;
            font-weight: 700;
            color: #2c3e50;
        }

        .rifornimento-card .info-principale .stazione {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .rifornimento-card .dettagli {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .rifornimento-card .dettaglio-chip {
            background: #f1f3f5;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            color: #495057;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .rifornimento-card .dettaglio-chip i {
            font-size: 12px;
            color: #adb5bd;
        }

        .rifornimento-card .pieno-badge {
            background: rgba(46, 204, 113, 0.15);
            color: #27ae60;
        }

        .rifornimento-card .parziale-badge {
            background: rgba(243, 156, 18, 0.15);
            color: #e67e22;
        }

        .rifornimento-card .consumo-badge {
            background: rgba(52, 152, 219, 0.15);
            color: #2980b9;
            font-weight: 600;
        }

        .rifornimento-card .foto-thumb {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid #ddd;
            cursor: pointer;
        }

        .rifornimento-card .note-text {
            font-size: 0.8rem;
            color: #6c757d;
            font-style: italic;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #f1f3f5;
        }

        .rifornimento-card .actions-row {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #f1f3f5;
        }

        /* Modal */
        .modal-rifornimento .modal-content {
            border-radius: 16px;
            overflow: hidden;
        }

        .modal-rifornimento .modal-header {
            background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
            color: white;
            border: none;
        }

        .form-section-title {
            font-size: 0.8rem;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 15px;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }

        .form-section-title:first-child {
            margin-top: 0;
        }

        .tipo-carburante-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }

        .tipo-carburante-option {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 10px 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .tipo-carburante-option:hover {
            border-color: #adb5bd;
        }

        .tipo-carburante-option.active {
            border-color: #e67e22;
            background: rgba(230, 126, 34, 0.08);
            color: #e67e22;
        }

        .tipo-carburante-option i {
            display: block;
            font-size: 20px;
            margin-bottom: 4px;
        }

        .pieno-toggle {
            display: flex;
            gap: 10px;
        }

        .pieno-toggle .option {
            flex: 1;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 500;
        }

        .pieno-toggle .option.active {
            border-color: #27ae60;
            background: rgba(39, 174, 96, 0.08);
            color: #27ae60;
        }

        .foto-preview {
            max-height: 150px;
            border-radius: 8px;
            margin-top: 10px;
            border: 1px solid #ddd;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #adb5bd;
        }

        .empty-state i {
            font-size: 50px;
            display: block;
            margin-bottom: 15px;
        }

        .mese-header {
            font-size: 0.85rem;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 20px 0 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e9ecef;
        }

        .mese-header:first-child {
            margin-top: 0;
        }

        /* Foto modal */
        .foto-modal img {
            width: 100%;
            border-radius: 8px;
        }
    </style>
@endsection

@section('content')
    <div class="fade-in">

        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center">
                <a href="/autista/dashboard" class="btn btn-outline-secondary btn-sm me-3">
                    <i class="ri-arrow-left-line"></i>
                </a>
                <div>
                    <h5 class="mb-0">Rifornimenti</h5>
                    <small class="text-muted">{{ $mezzo->targa ?? 'Mezzo assegnato' }}</small>
                </div>
            </div>
            <div>
                <select class="form-select form-select-sm" id="filtroMese" onchange="filtraMese()">
                    <option value="">Tutti</option>
                    @foreach($mesi_disponibili ?? [] as $mese)
                        <option value="{{ $mese->valore }}" {{ ($filtro_mese ?? '') == $mese->valore ? 'selected' : '' }}>
                            {{ $mese->etichetta }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Statistiche -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon" style="background: rgba(230, 126, 34, 0.12); color: #e67e22;">
                    <i class="ri-gas-station-line"></i>
                </div>
                <div class="valore">{{ $stats['totale_rifornimenti'] ?? 0 }}</div>
                <div class="etichetta">Rifornimenti</div>
            </div>
            <div class="stat-card">
                <div class="icon" style="background: rgba(231, 76, 60, 0.12); color: #e74c3c;">
                    <i class="ri-money-euro-circle-line"></i>
                </div>
                <div class="valore">€ {{ number_format($stats['spesa_totale'] ?? 0, 2, ',', '.') }}</div>
                <div class="etichetta">Spesa Totale</div>
            </div>
            <div class="stat-card">
                <div class="icon" style="background: rgba(52, 152, 219, 0.12); color: #3498db;">
                    <i class="ri-drop-line"></i>
                </div>
                <div class="valore">{{ number_format($stats['litri_totali'] ?? 0, 1, ',', '.') }} L</div>
                <div class="etichetta">Litri Totali</div>
            </div>
            <div class="stat-card">
                <div class="icon" style="background: rgba(46, 204, 113, 0.12); color: #27ae60;">
                    <i class="ri-speed-line"></i>
                </div>
                <div class="valore">{{ $stats['consumo_medio'] ? number_format($stats['consumo_medio'], 1, ',', '.') : '-' }} km/l</div>
                <div class="etichetta">Consumo Medio</div>
            </div>
        </div>

        <!-- Bottone Nuovo -->
        <button class="btn-nuovo-rifornimento mb-4" data-bs-toggle="modal" data-bs-target="#modalRifornimento">
            <i class="ri-gas-station-line" style="font-size: 22px;"></i>
            Nuovo Rifornimento
        </button>

        <!-- Lista Rifornimenti -->
        @if(count($rifornimenti) > 0)
            @php $meseCorrente = ''; @endphp
            @foreach($rifornimenti as $r)
                @php
                    $meseRif = \Carbon\Carbon::parse($r->data_rifornimento)->translatedFormat('F Y');
                    if ($meseRif !== $meseCorrente) {
                        $meseCorrente = $meseRif;
                        echo '<div class="mese-header"><i class="ri-calendar-line me-1"></i> ' . ucfirst($meseCorrente) . '</div>';
                    }
                @endphp

                <div class="rifornimento-card">
                    <div class="data-badge">
                        {{ date('d/m/Y', strtotime($r->data_rifornimento)) }}
                    </div>

                    <div class="top-row">
                        <div class="fuel-icon {{ $r->tipo_carburante ?? 'diesel' }}">
                            <i class="ri-gas-station-line"></i>
                        </div>
                        <div class="info-principale">
                            <div class="importo">€ {{ number_format($r->importo_totale, 2, ',', '.') }}</div>
                            @if($r->stazione_servizio)
                                <div class="stazione">
                                    <i class="ri-map-pin-line"></i> {{ $r->stazione_servizio }}
                                </div>
                            @endif
                        </div>
                        @if($r->foto_scontrino)
                            <img src="{{ asset($r->foto_scontrino) }}"
                                 alt="Scontrino"
                                 class="foto-thumb"
                                 onclick="mostraFoto('{{ asset($r->foto_scontrino) }}')">
                        @endif
                    </div>

                    <div class="dettagli">
                        <div class="dettaglio-chip">
                            <i class="ri-drop-line"></i>
                            {{ number_format($r->litri, 2, ',', '.') }} L
                        </div>
                        <div class="dettaglio-chip">
                            <i class="ri-speed-line"></i>
                            {{ number_format($r->km_rifornimento, 0, ',', '.') }} km
                        </div>
                        @if($r->prezzo_litro)
                            <div class="dettaglio-chip">
                                <i class="ri-price-tag-3-line"></i>
                                € {{ number_format($r->prezzo_litro, 3, ',', '.') }}/L
                            </div>
                        @endif
                        <div class="dettaglio-chip {{ $r->pieno ? 'pieno-badge' : 'parziale-badge' }}">
                            <i class="ri-{{ $r->pieno ? 'checkbox-circle' : 'indeterminate-circle' }}-line"></i>
                            {{ $r->pieno ? 'Pieno' : 'Parziale' }}
                        </div>
                        @if($r->consumo_calcolato)
                            <div class="dettaglio-chip consumo-badge">
                                <i class="ri-dashboard-3-line"></i>
                                {{ number_format($r->consumo_calcolato, 1, ',', '.') }} km/l
                            </div>
                        @endif
                        <div class="dettaglio-chip">
                            <i class="ri-drop-fill"></i>
                            {{ ucfirst($r->tipo_carburante ?? 'diesel') }}
                        </div>
                    </div>

                    @if($r->note)
                        <div class="note-text">
                            <i class="ri-sticky-note-line me-1"></i> {{ $r->note }}
                        </div>
                    @endif

                    <div class="actions-row">
                        <button class="btn btn-outline-primary btn-sm" onclick="modificaRifornimento({{ $r->id }})">
                            <i class="ri-edit-line"></i>
                        </button>
                        <button class="btn btn-outline-danger btn-sm" onclick="eliminaRifornimento({{ $r->id }})">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        @else
            <div class="empty-state">
                <i class="ri-gas-station-line"></i>
                <h6>Nessun rifornimento registrato</h6>
                <p class="small">Tocca il pulsante sopra per aggiungere il primo rifornimento</p>
            </div>
        @endif

    </div>

    <!-- MODAL NUOVO/MODIFICA RIFORNIMENTO -->
    <div class="modal fade modal-rifornimento" id="modalRifornimento" tabindex="-1">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">
                        <i class="ri-gas-station-line me-2"></i>
                        <span id="modalTitolo">Nuovo Rifornimento</span>
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formRifornimento">
                        <input type="hidden" id="rifornimentoId" value="">

                        <!-- Data e KM -->
                        <div class="form-section-title">
                            <i class="ri-calendar-line me-1"></i> Data e Chilometraggio
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Data</label>
                                <input type="date" class="form-control" id="dataRifornimento"
                                       value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Km Contachilometri</label>
                                <input type="number" class="form-control" id="kmRifornimento"
                                       placeholder="Es: 125430" required>
                            </div>
                        </div>

                        <!-- Tipo Carburante -->
                        <div class="form-section-title">
                            <i class="ri-drop-line me-1"></i> Tipo Carburante
                        </div>
                        <div class="tipo-carburante-grid">
                            <div class="tipo-carburante-option active" data-tipo="diesel" onclick="selezionaTipo(this)">
                                <i class="ri-drop-fill"></i>
                                Diesel
                            </div>
                            <div class="tipo-carburante-option" data-tipo="benzina" onclick="selezionaTipo(this)">
                                <i class="ri-drop-fill"></i>
                                Benzina
                            </div>
                            <div class="tipo-carburante-option" data-tipo="gpl" onclick="selezionaTipo(this)">
                                <i class="ri-drop-fill"></i>
                                GPL
                            </div>
                            <div class="tipo-carburante-option" data-tipo="metano" onclick="selezionaTipo(this)">
                                <i class="ri-drop-fill"></i>
                                Metano
                            </div>
                            <div class="tipo-carburante-option" data-tipo="adblue" onclick="selezionaTipo(this)">
                                <i class="ri-drop-fill"></i>
                                AdBlue
                            </div>
                            <div class="tipo-carburante-option" data-tipo="elettrico" onclick="selezionaTipo(this)">
                                <i class="ri-flashlight-line"></i>
                                Elettrico
                            </div>
                        </div>
                        <input type="hidden" id="tipoCarburante" value="diesel">

                        <!-- Quantità e Importo -->
                        <div class="form-section-title">
                            <i class="ri-money-euro-circle-line me-1"></i> Quantità e Costo
                        </div>
                        <div class="row g-2">
                            <div class="col-4">
                                <label class="form-label small fw-bold">Litri</label>
                                <input type="number" step="0.01" class="form-control" id="litri"
                                       placeholder="45.50" required oninput="calcolaPrezzo()">
                            </div>
                            <div class="col-4">
                                <label class="form-label small fw-bold">Importo €</label>
                                <input type="number" step="0.01" class="form-control" id="importoTotale"
                                       placeholder="75.00" required oninput="calcolaPrezzo()">
                            </div>
                            <div class="col-4">
                                <label class="form-label small fw-bold">€/Litro</label>
                                <input type="text" class="form-control" id="prezzoLitro"
                                       placeholder="Auto" readonly
                                       style="background: #f8f9fa;">
                            </div>
                        </div>

                        <!-- Pieno -->
                        <div class="form-section-title">
                            <i class="ri-gas-station-line me-1"></i> Tipo Rifornimento
                        </div>
                        <div class="pieno-toggle">
                            <div class="option active" data-pieno="1" onclick="selezionaPieno(this)">
                                <i class="ri-checkbox-circle-line"></i><br>
                                <strong>Pieno</strong><br>
                                <small class="text-muted">Serbatoio completo</small>
                            </div>
                            <div class="option" data-pieno="0" onclick="selezionaPieno(this)">
                                <i class="ri-indeterminate-circle-line"></i><br>
                                <strong>Parziale</strong><br>
                                <small class="text-muted">Rifornimento parziale</small>
                            </div>
                        </div>
                        <input type="hidden" id="pieno" value="1">

                        <!-- Stazione -->
                        <div class="form-section-title">
                            <i class="ri-map-pin-line me-1"></i> Dettagli
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Stazione di Servizio</label>
                            <input type="text" class="form-control" id="stazioneServizio"
                                   placeholder="Es: Eni Via Roma, IP Autostrada...">
                        </div>

                        <!-- Foto Scontrino -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Foto Scontrino</label>
                            <input type="file" class="form-control" id="fotoScontrino"
                                   accept="image/*" capture="environment"
                                   onchange="anteprimaFoto(this)">
                            <img id="fotoPreview" class="foto-preview d-none" src="" alt="Anteprima">
                        </div>

                        <!-- Note -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Note</label>
                            <textarea class="form-control" id="noteRifornimento" rows="2"
                                      placeholder="Note aggiuntive (opzionale)"></textarea>
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-warning text-white fw-bold" id="btnSalvaRifornimento" onclick="salvaRifornimento()">
                        <i class="ri-save-line me-1"></i>
                        Salva Rifornimento
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL FOTO -->
    <div class="modal fade foto-modal" id="fotoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header border-0 bg-dark text-white">
                    <h6 class="modal-title"><i class="ri-image-line me-2"></i>Scontrino</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-2">
                    <img id="fotoModalImg" src="" alt="Scontrino">
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        const csrfToken = window.csrfToken || '{{ csrf_token() }}';

        // Seleziona tipo carburante
        function selezionaTipo(el) {
            document.querySelectorAll('.tipo-carburante-option').forEach(o => o.classList.remove('active'));
            el.classList.add('active');
            document.getElementById('tipoCarburante').value = el.dataset.tipo;
        }

        // Seleziona pieno/parziale
        function selezionaPieno(el) {
            document.querySelectorAll('.pieno-toggle .option').forEach(o => o.classList.remove('active'));
            el.classList.add('active');
            document.getElementById('pieno').value = el.dataset.pieno;
        }

        // Calcola prezzo al litro automaticamente
        function calcolaPrezzo() {
            const litri = parseFloat(document.getElementById('litri').value);
            const importo = parseFloat(document.getElementById('importoTotale').value);

            if (litri > 0 && importo > 0) {
                const prezzoLitro = (importo / litri).toFixed(3);
                document.getElementById('prezzoLitro').value = '€ ' + prezzoLitro.replace('.', ',');
            } else {
                document.getElementById('prezzoLitro').value = '';
            }
        }

        // Anteprima foto scontrino
        function anteprimaFoto(input) {
            const preview = document.getElementById('fotoPreview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.classList.add('d-none');
            }
        }

        // Mostra foto scontrino in modal
        function mostraFoto(url) {
            document.getElementById('fotoModalImg').src = url;
            new bootstrap.Modal(document.getElementById('fotoModal')).show();
        }


        async function salvaRifornimento() {
            const data = document.getElementById('dataRifornimento').value;
            const km = document.getElementById('kmRifornimento').value;
            const litri = document.getElementById('litri').value;
            const importo = document.getElementById('importoTotale').value;

            if (!data || !km || !litri || !importo) {
                alert('Compila tutti i campi obbligatori');
                return;
            }

            const btn = document.getElementById('btnSalvaRifornimento');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Salvataggio...';
            btn.disabled = true;

            const formData = new FormData();
            formData.append('id', document.getElementById('rifornimentoId').value || '');
            formData.append('data_rifornimento', data);
            formData.append('km_rifornimento', km);
            formData.append('litri', litri);
            formData.append('importo_totale', importo);
            formData.append('tipo_carburante', document.getElementById('tipoCarburante').value);
            formData.append('pieno', document.getElementById('pieno').value);
            formData.append('stazione_servizio', document.getElementById('stazioneServizio').value);
            formData.append('note', document.getElementById('noteRifornimento').value);

            const fotoInput = document.getElementById('fotoScontrino');
            if (fotoInput.files && fotoInput.files[0]) {
                formData.append('foto_scontrino', fotoInput.files[0]);
            }

            try {
                const response = await fetch('/autista/rifornimenti/salva', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: formData
                });

                // DEBUG: vedi cosa risponde il server
                const text = await response.text();
                console.log('Status:', response.status);
                console.log('Risposta:', text);

                if (response.status !== 200) {
                    alert('Errore ' + response.status + ': ' + text.substring(0, 200));
                    btn.innerHTML = '<i class="ri-save-line me-1"></i> Salva Rifornimento';
                    btn.disabled = false;
                    return;
                }

                const result = JSON.parse(text);

                if (result.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalRifornimento')).hide();
                    location.reload();
                } else {
                    alert('Errore: ' + (result.message || 'Riprova'));
                }
            } catch (error) {
                console.error('Errore completo:', error);
                alert('Errore: ' + error.message);
            }

            btn.innerHTML = '<i class="ri-save-line me-1"></i> Salva Rifornimento';
            btn.disabled = false;
        }

        // Modifica rifornimento
        async function modificaRifornimento(id) {
            try {
                const response = await fetch('/autista/rifornimenti/' + id);
                const data = await response.json();

                if (data.success) {
                    const r = data.rifornimento;

                    document.getElementById('rifornimentoId').value = r.id;
                    document.getElementById('dataRifornimento').value = r.data_rifornimento;
                    document.getElementById('kmRifornimento').value = r.km_rifornimento;
                    document.getElementById('litri').value = r.litri;
                    document.getElementById('importoTotale').value = r.importo_totale;
                    document.getElementById('stazioneServizio').value = r.stazione_servizio || '';
                    document.getElementById('noteRifornimento').value = r.note || '';

                    // Seleziona tipo carburante
                    document.querySelectorAll('.tipo-carburante-option').forEach(o => {
                        o.classList.toggle('active', o.dataset.tipo === (r.tipo_carburante || 'diesel'));
                    });
                    document.getElementById('tipoCarburante').value = r.tipo_carburante || 'diesel';

                    // Seleziona pieno/parziale
                    document.querySelectorAll('.pieno-toggle .option').forEach(o => {
                        o.classList.toggle('active', o.dataset.pieno == r.pieno);
                    });
                    document.getElementById('pieno').value = r.pieno;

                    calcolaPrezzo();

                    document.getElementById('modalTitolo').textContent = 'Modifica Rifornimento';
                    new bootstrap.Modal(document.getElementById('modalRifornimento')).show();
                }
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore nel caricamento dei dati');
            }
        }

        // Elimina rifornimento
        async function eliminaRifornimento(id) {
            if (!confirm('Eliminare questo rifornimento?')) return;

            try {
                const response = await fetch('/autista/rifornimenti/' + id + '/elimina', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                const data = await response.json();

                if (data.success) {
                    location.reload();
                } else {
                    alert('Errore: ' + (data.message || 'Riprova'));
                }
            } catch (error) {
                console.error('Errore:', error);
                alert('Errore di connessione');
            }
        }

        // Filtro per mese
        function filtraMese() {
            const mese = document.getElementById('filtroMese').value;
            if (mese) {
                window.location.href = '/autista/rifornimenti?mese=' + mese;
            } else {
                window.location.href = '/autista/rifornimenti';
            }
        }

        // Reset form quando si apre per nuovo inserimento
        document.getElementById('modalRifornimento').addEventListener('show.bs.modal', function(e) {
            // Se non è stato impostato un ID, è un nuovo rifornimento
            if (!document.getElementById('rifornimentoId').value) {
                document.getElementById('formRifornimento').reset();
                document.getElementById('dataRifornimento').value = '{{ date("Y-m-d") }}';
                document.getElementById('tipoCarburante').value = 'diesel';
                document.getElementById('pieno').value = '1';
                document.getElementById('fotoPreview').classList.add('d-none');
                document.getElementById('prezzoLitro').value = '';
                document.getElementById('modalTitolo').textContent = 'Nuovo Rifornimento';

                document.querySelectorAll('.tipo-carburante-option').forEach(o => {
                    o.classList.toggle('active', o.dataset.tipo === 'diesel');
                });
                document.querySelectorAll('.pieno-toggle .option').forEach(o => {
                    o.classList.toggle('active', o.dataset.pieno === '1');
                });
            }
        });

        // Reset ID quando si chiude il modal
        document.getElementById('modalRifornimento').addEventListener('hidden.bs.modal', function() {
            document.getElementById('rifornimentoId').value = '';
        });
    </script>
@endsection