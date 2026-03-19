@include('azienda.common.header')

<div class="page-content">
    <div class="container-fluid">
        <!-- Titolo pagina -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">🚛 Ordini di Trasporto</h4>
                    <div class="page-title-right">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCreaOrdine">
                            <i class="ri-add-line"></i> Nuovo Ordine
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messaggi sessione -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="ri-check-line me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show">
                <i class="ri-alert-line me-2"></i>{{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="ri-error-warning-line me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Tab Filtro per Stato -->
        <div class="row mb-3">
            <div class="col-12">
                <ul class="nav nav-tabs nav-tabs-custom" id="tabStatiOrdini" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-stato="tutti" onclick="filtraPerStato('tutti', this)" type="button">
                            📦 Tutti <span class="badge bg-secondary ms-1" id="count_tutti">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-stato="pianificato" onclick="filtraPerStato('pianificato', this)" type="button">
                            📋 Pianificati <span class="badge bg-info ms-1" id="count_pianificato">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-stato="assegnato" onclick="filtraPerStato('assegnato', this)" type="button">
                            👤 Assegnati <span class="badge bg-primary ms-1" id="count_assegnato">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-stato="in_corso" onclick="filtraPerStato('in_corso', this)" type="button">
                            🚛 In Corso <span class="badge bg-warning text-dark ms-1" id="count_in_corso">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-stato="completato" onclick="filtraPerStato('completato', this)" type="button">
                            ✅ Completati <span class="badge bg-success ms-1" id="count_completato">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-stato="annullato" onclick="filtraPerStato('annullato', this)" type="button">
                            ❌ Annullati <span class="badge bg-danger ms-1" id="count_annullato">0</span>
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Tabella ordini -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover datatable w-100" id="tabellaOrdini">
                                <thead class="table-light">
                                <tr>
                                    <th>N. Ordine</th>
                                    <th>Cliente</th>
                                    <th>Data Ritiro</th>
                                    <th>Da → A</th>
                                    <th class="text-center">Colli</th>
                                    <th class="text-center">Peso</th>
                                    <th>Mezzo / Autista</th>
                                    <th>Stato</th>
                                    <th class="text-end">Importo</th>
                                    <th class="no-sort text-center">Azioni</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($ordini as $ordine)
                                    <tr data-stato="{{ $ordine->stato }}">
                                        <td>
                                            <strong class="text-primary">{{ $ordine->numero_ordine }}</strong>
                                            @if(isset($ordine->numero_ddt) && $ordine->numero_ddt)
                                                <br><small class="text-muted">DDT: {{ $ordine->numero_ddt }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $ordine->cliente_nome ?? 'N/D' }}</td>
                                        <td>
                                            <strong>{{ date('d/m/Y', strtotime($ordine->data_ritiro)) }}</strong>
                                            @if($ordine->ora_ritiro)
                                                <br><small class="text-muted">🕐 {{ date('H:i', strtotime($ordine->ora_ritiro)) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <small>
                                                <span class="text-success">📍</span> {{ Str::limit($ordine->indirizzo_ritiro, 25) }}<br>
                                                <span class="text-danger">📍</span> {{ Str::limit($ordine->indirizzo_consegna, 25) }}
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            @if(isset($ordine->numero_colli) && $ordine->numero_colli)
                                                @if(($ordine->tipo_unita ?? 'colli') === 'pedane')
                                                    <span class="badge bg-warning text-dark"><i class="ri-stack-line"></i> {{ $ordine->numero_colli }} ped.</span>
                                                @else
                                                    <span class="badge bg-secondary"><i class="ri-archive-line"></i> {{ $ordine->numero_colli }} colli</span>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if(isset($ordine->peso_kg) && $ordine->peso_kg)
                                                <strong>{{ number_format($ordine->peso_kg, 0, ',', '.') }}</strong> <small>kg</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($ordine->targa)
                                                <span class="badge bg-dark">{{ $ordine->targa }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                            @if($ordine->autista_nome)
                                                <br><small><i class="ri-user-line"></i> {{ $ordine->autista_nome }} {{ $ordine->autista_cognome }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <select class="form-select form-select-sm select-stato" style="min-width: 140px;" onchange="cambiaStato({{ $ordine->id }}, this.value, this)">
                                                <option value="pianificato" {{ $ordine->stato == 'pianificato' ? 'selected' : '' }}>📋 Pianificato</option>
                                                <option value="assegnato" {{ $ordine->stato == 'assegnato' ? 'selected' : '' }}>👤 Assegnato</option>
                                                <option value="in_corso" {{ $ordine->stato == 'in_corso' ? 'selected' : '' }}>🚛 In Corso</option>
                                                <option value="completato" {{ $ordine->stato == 'completato' ? 'selected' : '' }}>✅ Completato</option>
                                                <option value="annullato" {{ $ordine->stato == 'annullato' ? 'selected' : '' }}>❌ Annullato</option>
                                            </select>
                                        </td>
                                        <td class="text-end">
                                            @if($ordine->importo > 0)
                                                <strong class="text-success">€ {{ number_format($ordine->importo, 2, ',', '.') }}</strong>
                                                @if(isset($ordine->importo_manuale) && $ordine->importo_manuale == 0)
                                                    <br><small class="text-info"><i class="ri-calculator-line"></i> Tariffario</small>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="/azienda/ordine-trasporto/{{ $ordine->id }}" class="btn btn-info btn-sm" title="Dettaglio">
                                                    <i class="ri-eye-line"></i>
                                                </a>
                                                <button class="btn btn-warning btn-sm" onclick="modificaOrdine({{ $ordine->id }})" title="Modifica">
                                                    <i class="ri-pencil-line"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm" onclick="eliminaOrdine({{ $ordine->id }})" title="Elimina">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </div>
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
    </div>
</div>

<!-- ============================================ -->
<!-- MODAL CREA ORDINE (con tariffario integrato) -->
<!-- ============================================ -->
<div class="modal fade" id="modalCreaOrdine" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" id="formCreaOrdine">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="ri-add-line me-2"></i>Nuovo Ordine di Trasporto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">


                    {{-- Riga 1: Cliente --}}
                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-label">Cliente <span class="text-danger">*</span></label>
                            <select name="id_cliente" id="crea_id_cliente" class="form-select" required onchange="onClienteChange(this.value, 'crea')">
                                <option value="">Seleziona cliente...</option>
                                @foreach($clienti as $cliente)
                                    <option value="{{ $cliente->id }}">{{ $cliente->ragione_sociale }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Riga 1b: Date Ritiro e Consegna --}}
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <label class="form-label">Data Ritiro <span class="text-danger">*</span></label>
                            <input type="date" name="data_ritiro" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Ora Ritiro</label>
                            <input type="time" name="ora_ritiro" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Data Consegna</label>
                            <input type="date" name="data_consegna" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Ora Consegna</label>
                            <input type="time" name="ora_consegna" class="form-control">
                        </div>
                    </div>

                    {{-- Indirizzi (hidden, sincronizzati dalla prima/ultima tappa) --}}
                    <input type="hidden" name="indirizzo_ritiro"   id="crea_indirizzo_ritiro">
                    <input type="hidden" name="indirizzo_consegna" id="crea_indirizzo_consegna">

                    {{-- Riga 3: Merce + Km + Ore --}}
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label class="form-label">Descrizione Merce <span class="text-danger">*</span></label>
                            <textarea name="descrizione_merce" class="form-control" rows="2" required placeholder="Es: Pallet elettronica"></textarea>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Tipo Unità</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="tipo_unita" id="crea_tipo_colli" value="colli" checked>
                                <label class="btn btn-outline-secondary btn-sm" for="crea_tipo_colli"><i class="ri-archive-line"></i> Colli</label>
                                <input type="radio" class="btn-check" name="tipo_unita" id="crea_tipo_pedane" value="pedane">
                                <label class="btn btn-outline-secondary btn-sm" for="crea_tipo_pedane"><i class="ri-stack-line"></i> Pedane</label>
                            </div>
                            <input type="number" name="numero_colli" id="crea_numero_colli" class="form-control mt-1" min="1" placeholder="Quantità">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Peso (Kg)</label>
                            <input type="number" step="0.01" name="peso_kg" id="crea_peso_kg" class="form-control" placeholder="150" oninput="ricalcolaTariffa('crea')">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Km Totali</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="km_totali" id="crea_km_totali" class="form-control" placeholder="120" oninput="ricalcolaTariffa('crea')">
                                <button class="btn btn-outline-primary" type="button" onclick="calcolaKmAuto('crea')" title="Calcola km da Google Maps">
                                    <i class="ri-route-line"></i>
                                </button>
                            </div>
                            <small class="text-muted" id="crea_km_info" style="font-size:0.7rem;"></small>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Ore Stimate</label>
                            <input type="number" step="0.5" name="ore_stimate" id="crea_ore_stimate" class="form-control" placeholder="2.5" oninput="ricalcolaTariffa('crea')">
                        </div>
                    </div>

                    {{-- ============================== --}}
                    {{-- SEZIONE IMPORTO / TARIFFARIO --}}
                    {{-- ============================== --}}
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card border mb-0">
                                <div class="card-header py-2 d-flex justify-content-between align-items-center bg-light">
                                    <h6 class="mb-0"><i class="ri-money-euro-circle-line me-1"></i> Importo</h6>
                                    {{-- Toggle Manuale / Tariffario --}}
                                    <div class="d-flex align-items-center gap-2" id="crea_toggle_container" style="display: none !important;">
                                        <span class="text-muted small">Manuale</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" id="crea_toggle_tariffario" onchange="toggleModalitaImporto('crea')">
                                        </div>
                                        <span class="text-primary small fw-bold">Tariffario</span>
                                    </div>
                                </div>
                                <div class="card-body py-3">

                                    {{-- Input nascosto: modalita --}}
                                    <input type="hidden" name="modalita_importo" id="crea_modalita_importo" value="manuale">
                                    <input type="hidden" name="id_tariffa_applicata" id="crea_id_tariffa_applicata" value="">

                                    {{-- Pannello info tariffario (nascosto inizialmente) --}}
                                    <div id="crea_info_tariffa" style="display: none;" class="mb-3">
                                        <div class="alert alert-info mb-2 py-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="ri-file-list-3-line me-1"></i>
                                                    <strong id="crea_nome_tariffa">-</strong>
                                                    <span class="badge bg-primary ms-2" id="crea_tipo_calcolo_badge">-</span>
                                                </div>
                                                <small class="text-muted" id="crea_info_tariffa_dettaglio"></small>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Pannello Tariffario: Calcolo automatico --}}
                                    <div id="crea_pannello_tariffario" style="display: none;">
                                        {{-- Maggiorazioni --}}
                                        <div class="row mb-2">
                                            <div class="col-12">
                                                <div class="d-flex gap-3 flex-wrap">
                                                    <div class="form-check" id="crea_check_urgente_wrap" style="display:none;">
                                                        <input class="form-check-input" type="checkbox" name="urgente" value="1" id="crea_urgente" onchange="ricalcolaTariffa('crea')">
                                                        <label class="form-check-label small" for="crea_urgente">🔴 Urgente <span id="crea_urgente_pct"></span></label>
                                                    </div>
                                                    <div class="form-check" id="crea_check_festivo_wrap" style="display:none;">
                                                        <input class="form-check-input" type="checkbox" name="festivo" value="1" id="crea_festivo" onchange="ricalcolaTariffa('crea')">
                                                        <label class="form-check-label small" for="crea_festivo">📅 Festivo <span id="crea_festivo_pct"></span></label>
                                                    </div>
                                                    <div class="form-check" id="crea_check_notturno_wrap" style="display:none;">
                                                        <input class="form-check-input" type="checkbox" name="notturno" value="1" id="crea_notturno" onchange="ricalcolaTariffa('crea')">
                                                        <label class="form-check-label small" for="crea_notturno">🌙 Notturno <span id="crea_notturno_pct"></span></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Breakdown costo --}}
                                        <div class="border rounded p-2 bg-white">
                                            <table class="table table-sm mb-0" style="font-size: 0.9em;">
                                                <tbody id="crea_breakdown">
                                                <tr>
                                                    <td>Prezzo base</td>
                                                    <td class="text-end" id="crea_val_base">€ 0,00</td>
                                                </tr>
                                                <tr id="crea_row_variabile" style="display:none;">
                                                    <td id="crea_label_variabile">Costo variabile</td>
                                                    <td class="text-end" id="crea_val_variabile">€ 0,00</td>
                                                </tr>
                                                <tr id="crea_row_maggiorazioni" style="display:none;">
                                                    <td class="text-warning" id="crea_label_maggiorazioni">Maggiorazioni</td>
                                                    <td class="text-end text-warning" id="crea_val_maggiorazioni">€ 0,00</td>
                                                </tr>
                                                <tr id="crea_row_sconto" style="display:none;">
                                                    <td class="text-success" id="crea_label_sconto">Sconto</td>
                                                    <td class="text-end text-success" id="crea_val_sconto">- € 0,00</td>
                                                </tr>
                                                </tbody>
                                                <tfoot>
                                                <tr class="fw-bold border-top">
                                                    <td>TOTALE</td>
                                                    <td class="text-end text-primary fs-5" id="crea_val_totale">€ 0,00</td>
                                                </tr>
                                                </tfoot>
                                            </table>
                                        </div>

                                        <div class="mt-2">
                                            <small class="text-muted" id="crea_calcolo_descrizione"></small>
                                        </div>

                                        {{-- Input importo nascosto (valorizzato dal calcolo) --}}
                                        <input type="hidden" name="importo" id="crea_importo_calcolato" value="0">
                                    </div>

                                    {{-- Pannello Manuale: Input diretto --}}
                                    <div id="crea_pannello_manuale">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label class="form-label">Importo (€)</label>
                                                <input type="number" step="0.01" name="importo" id="crea_importo_manuale" class="form-control" placeholder="0.00">
                                            </div>
                                            <div class="col-md-8 d-flex align-items-end">
                                                <small class="text-muted" id="crea_no_tariffa_msg">
                                                    Seleziona un cliente per verificare se ha un tariffario associato.
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tappe (staffetta multi-autista) --}}
                    <div class="mt-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="ri-route-line text-primary fs-5"></i>
                            <label class="form-label fw-bold mb-0">Assegnazione Autisti / Tappe</label>
                            <small class="text-muted">(almeno 1 tappa)</small>
                        </div>
                        <div id="crea-tappe-container"></div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="aggiungiTappa('crea')">
                            <i class="ri-add-line me-1"></i> Aggiungi Tappa
                        </button>
                    </div>

                    {{-- Pedane --}}
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card border border-warning mb-0">
                                <div class="card-header py-2 bg-warning bg-opacity-10 d-flex align-items-center gap-2">
                                    <i class="ri-stack-line text-warning"></i>
                                    <span class="fw-semibold">Gestione Pedane</span>
                                    <small class="text-muted ms-1">(opzionale)</small>
                                </div>
                                <div class="card-body py-2">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label">Pedane consegnate con la merce</label>
                                            <input type="number" name="pedane_consegnate" class="form-control" min="0" value="0" placeholder="0">
                                            <small class="text-muted">Pedane che partiranno con questo ordine</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Pedane attese in reso</label>
                                            <input type="number" name="pedane_da_ritirare" class="form-control" min="0" value="0" placeholder="0">
                                            <small class="text-muted">Pedane che l'autista deve ritirare alla consegna</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Riga 5: Note --}}
                    <div class="row mt-3">
                        <div class="col-12">
                            <label class="form-label">Note</label>
                            <textarea name="note" class="form-control" rows="2" placeholder="Note aggiuntive..."></textarea>
                        </div>
                    </div>

                    {{-- Riga 6: Genera DDT automatico --}}
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card border border-primary mb-0">
                                <div class="card-body py-2">
                                    <div class="form-check d-flex align-items-center gap-2">
                                        <input class="form-check-input" type="checkbox" name="genera_ddt" value="1" id="crea_genera_ddt" checked>
                                        <label class="form-check-label fw-medium" for="crea_genera_ddt">
                                            <i class="ri-file-text-line text-primary me-1"></i>
                                            Genera DDT automaticamente con l'ordine
                                        </label>
                                        <span class="badge bg-primary ms-auto" id="badge_prossimo_ddt">
                                            {{ $prossimoDdt ?? 'DDT...' }}
                                        </span>
                                    </div>
                                    <small class="text-muted d-block mt-1 ms-4">
                                        Il DDT verrà compilato automaticamente con i dati dell'ordine.
                                        Mittente: <strong>{{ $azienda->ragione_sociale ?? 'Azienda' }}</strong> →
                                        Destinatario: dal cliente selezionato.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <input type="hidden" name="crea_ordine" value="1">

                    <button type="button" class="btn btn-success" onclick="confermaCreazione()">
                        <i class="ri-add-line me-1"></i> Crea Ordine
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- MODAL MODIFICA ORDINE (con tariffario) -->
<!-- ============================================ -->
<div class="modal fade" id="modalModificaOrdine" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" id="formModificaOrdine">
                @csrf
                <input type="hidden" id="modifica_id_ordine" name="id_ordine">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="ri-pencil-line me-2"></i>Modifica Ordine di Trasporto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    {{-- Riga 1: Numero + Cliente --}}
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Numero Ordine</label>
                            <input type="text" id="mod_numero_ordine" class="form-control" disabled>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Cliente <span class="text-danger">*</span></label>
                            <select name="id_cliente" id="mod_id_cliente" class="form-select" required onchange="onClienteChange(this.value, 'mod')">
                                <option value="">Seleziona cliente...</option>
                                @foreach($clienti as $cliente)
                                    <option value="{{ $cliente->id }}">{{ $cliente->ragione_sociale }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Riga 2: Date --}}
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <label class="form-label">Data Ritiro <span class="text-danger">*</span></label>
                            <input type="date" name="data_ritiro" id="mod_data_ritiro" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Ora Ritiro</label>
                            <input type="time" name="ora_ritiro" id="mod_ora_ritiro" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Data Consegna</label>
                            <input type="date" name="data_consegna" id="mod_data_consegna" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Ora Consegna</label>
                            <input type="time" name="ora_consegna" id="mod_ora_consegna" class="form-control">
                        </div>
                    </div>

                    {{-- Indirizzi (hidden, sincronizzati dalla prima/ultima tappa) --}}
                    <input type="hidden" name="indirizzo_ritiro"   id="mod_indirizzo_ritiro">
                    <input type="hidden" name="indirizzo_consegna" id="mod_indirizzo_consegna">

                    {{-- Riga 4: Merce + Quantità --}}
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label class="form-label">Descrizione Merce <span class="text-danger">*</span></label>
                            <textarea name="descrizione_merce" id="mod_descrizione_merce" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Tipo Unità</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="tipo_unita" id="mod_tipo_colli" value="colli">
                                <label class="btn btn-outline-secondary btn-sm" for="mod_tipo_colli"><i class="ri-archive-line"></i> Colli</label>
                                <input type="radio" class="btn-check" name="tipo_unita" id="mod_tipo_pedane" value="pedane">
                                <label class="btn btn-outline-secondary btn-sm" for="mod_tipo_pedane"><i class="ri-stack-line"></i> Pedane</label>
                            </div>
                            <input type="number" min="1" name="numero_colli" id="mod_numero_colli" class="form-control mt-1" placeholder="Quantità">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Peso (Kg)</label>
                            <input type="number" step="0.01" name="peso_kg" id="mod_peso_kg" class="form-control" oninput="ricalcolaTariffa('mod')">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Km Totali</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="km_totali" id="mod_km_totali" class="form-control" oninput="ricalcolaTariffa('mod')">
                                <button class="btn btn-outline-primary" type="button" onclick="calcolaKmAuto('mod')" title="Calcola km da Google Maps">
                                    <i class="ri-route-line"></i>
                                </button>
                            </div>
                            <small class="text-muted" id="mod_km_info" style="font-size:0.7rem;"></small>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Ore Stimate</label>
                            <input type="number" step="0.5" name="ore_stimate" id="mod_ore_stimate" class="form-control" oninput="ricalcolaTariffa('mod')">
                        </div>
                    </div>

                    {{-- SEZIONE IMPORTO / TARIFFARIO --}}
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card border mb-0">
                                <div class="card-header py-2 d-flex justify-content-between align-items-center bg-light">
                                    <h6 class="mb-0"><i class="ri-money-euro-circle-line me-1"></i> Importo</h6>
                                    <div class="d-flex align-items-center gap-2" id="mod_toggle_container" style="display: none !important;">
                                        <span class="text-muted small">Manuale</span>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" id="mod_toggle_tariffario" onchange="toggleModalitaImporto('mod')">
                                        </div>
                                        <span class="text-primary small fw-bold">Tariffario</span>
                                    </div>
                                </div>
                                <div class="card-body py-3">

                                    <input type="hidden" name="modalita_importo" id="mod_modalita_importo" value="manuale">
                                    <input type="hidden" name="id_tariffa_applicata" id="mod_id_tariffa_applicata" value="">

                                    {{-- Info tariffario --}}
                                    <div id="mod_info_tariffa" style="display: none;" class="mb-3">
                                        <div class="alert alert-info mb-2 py-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="ri-file-list-3-line me-1"></i>
                                                    <strong id="mod_nome_tariffa">-</strong>
                                                    <span class="badge bg-primary ms-2" id="mod_tipo_calcolo_badge">-</span>
                                                </div>
                                                <small class="text-muted" id="mod_info_tariffa_dettaglio"></small>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Pannello Tariffario --}}
                                    <div id="mod_pannello_tariffario" style="display: none;">
                                        <div class="row mb-2">
                                            <div class="col-12">
                                                <div class="d-flex gap-3 flex-wrap">
                                                    <div class="form-check" id="mod_check_urgente_wrap" style="display:none;">
                                                        <input class="form-check-input" type="checkbox" name="urgente" value="1" id="mod_urgente" onchange="ricalcolaTariffa('mod')">
                                                        <label class="form-check-label small" for="mod_urgente">🔴 Urgente <span id="mod_urgente_pct"></span></label>
                                                    </div>
                                                    <div class="form-check" id="mod_check_festivo_wrap" style="display:none;">
                                                        <input class="form-check-input" type="checkbox" name="festivo" value="1" id="mod_festivo" onchange="ricalcolaTariffa('mod')">
                                                        <label class="form-check-label small" for="mod_festivo">📅 Festivo <span id="mod_festivo_pct"></span></label>
                                                    </div>
                                                    <div class="form-check" id="mod_check_notturno_wrap" style="display:none;">
                                                        <input class="form-check-input" type="checkbox" name="notturno" value="1" id="mod_notturno" onchange="ricalcolaTariffa('mod')">
                                                        <label class="form-check-label small" for="mod_notturno">🌙 Notturno <span id="mod_notturno_pct"></span></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="border rounded p-2 bg-white">
                                            <table class="table table-sm mb-0" style="font-size: 0.9em;">
                                                <tbody>
                                                <tr><td>Prezzo base</td><td class="text-end" id="mod_val_base">€ 0,00</td></tr>
                                                <tr id="mod_row_variabile" style="display:none;"><td id="mod_label_variabile">Costo variabile</td><td class="text-end" id="mod_val_variabile">€ 0,00</td></tr>
                                                <tr id="mod_row_maggiorazioni" style="display:none;"><td class="text-warning" id="mod_label_maggiorazioni">Maggiorazioni</td><td class="text-end text-warning" id="mod_val_maggiorazioni">€ 0,00</td></tr>
                                                <tr id="mod_row_sconto" style="display:none;"><td class="text-success" id="mod_label_sconto">Sconto</td><td class="text-end text-success" id="mod_val_sconto">- € 0,00</td></tr>
                                                </tbody>
                                                <tfoot>
                                                <tr class="fw-bold border-top"><td>TOTALE</td><td class="text-end text-primary fs-5" id="mod_val_totale">€ 0,00</td></tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                        <div class="mt-2"><small class="text-muted" id="mod_calcolo_descrizione"></small></div>
                                        <input type="hidden" name="importo" id="mod_importo_calcolato" value="0">
                                    </div>

                                    {{-- Pannello Manuale --}}
                                    <div id="mod_pannello_manuale">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label class="form-label">Importo (€)</label>
                                                <input type="number" step="0.01" name="importo" id="mod_importo_manuale" class="form-control" placeholder="0.00">
                                            </div>
                                            <div class="col-md-8 d-flex align-items-end">
                                                <small class="text-muted" id="mod_no_tariffa_msg">Nessun tariffario associato.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tappe (staffetta multi-autista) --}}
                    <div class="mt-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="ri-route-line text-primary fs-5"></i>
                            <label class="form-label fw-bold mb-0">Assegnazione Autisti / Tappe</label>
                        </div>
                        <div id="mod-tappe-container"></div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="aggiungiTappa('mod')">
                            <i class="ri-add-line me-1"></i> Aggiungi Tappa
                        </button>
                    </div>

                    {{-- Stato --}}
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label class="form-label">Stato</label>
                            <select name="stato" id="mod_stato" class="form-select">
                                <option value="pianificato">📋 Pianificato</option>
                                <option value="assegnato">👤 Assegnato</option>
                                <option value="in_corso">🚛 In Corso</option>
                                <option value="completato">✅ Completato</option>
                                <option value="annullato">❌ Annullato</option>
                            </select>
                        </div>
                    </div>

                    {{-- Pedane --}}
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card border border-warning mb-0">
                                <div class="card-header py-2 bg-warning bg-opacity-10 d-flex align-items-center gap-2">
                                    <i class="ri-stack-line text-warning"></i>
                                    <span class="fw-semibold">Gestione Pedane</span>
                                </div>
                                <div class="card-body py-2">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label">Pedane consegnate con la merce</label>
                                            <input type="number" name="pedane_consegnate" id="mod_pedane_consegnate" class="form-control" min="0" value="0">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Pedane attese in reso</label>
                                            <input type="number" name="pedane_da_ritirare" id="mod_pedane_da_ritirare" class="form-control" min="0" value="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Note --}}
                    <div class="row mt-3">
                        <div class="col-12">
                            <label class="form-label">Note</label>
                            <textarea name="note" id="mod_note" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <input type="hidden" name="modifica_ordine" value="1">
                    <button type="submit" class="btn btn-warning"><i class="ri-save-line me-1"></i> Salva Modifiche</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Elimina Ordine -->
<div class="modal fade" id="modalEliminaOrdine" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                @csrf
                <input type="hidden" id="elimina_id_ordine" name="id_ordine">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="ri-delete-bin-line me-2"></i>Conferma Eliminazione</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3"><i class="ri-error-warning-line text-danger" style="font-size: 4rem;"></i></div>
                    <p class="text-center">Sei sicuro di voler eliminare l'ordine <strong id="elimina_numero_ordine"></strong>?</p>
                    <p class="text-danger text-center"><strong>Questa operazione non può essere annullata.</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <input type="hidden" name="elimina_ordine" value="1">
                    <button type="submit" class="btn btn-danger"><i class="ri-delete-bin-line me-1"></i> Elimina</button>
                </div>
            </form>
        </div>
    </div>
</div>



<!-- Modal Conferma Creazione con DDT -->
<div class="modal fade" id="modalConfermaDDT" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">
                    <i class="ri-file-text-line me-2"></i>Conferma Creazione Ordine
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Riepilogo ordine --}}
                <div class="mb-3">
                    <h6 class="text-muted mb-2">📦 Riepilogo Ordine</h6>
                    <div class="border rounded p-2 bg-light">
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Cliente:</small><br>
                                <strong id="conferma_cliente">-</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Data Ritiro:</small><br>
                                <strong id="conferma_data">-</strong>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12">
                                <small class="text-muted">Merce:</small><br>
                                <span id="conferma_merce">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sezione DDT --}}
                <div id="conferma_ddt_section">
                    <div class="alert alert-primary mb-0">
                        <div class="d-flex align-items-start gap-3">
                            <div class="fs-2">📄</div>
                            <div>
                                <h6 class="alert-heading mb-1">Verrà creato anche il DDT</h6>
                                <p class="mb-1">
                                    Numero documento:
                                    <strong class="fs-5 text-primary" id="conferma_numero_ddt">{{ $prossimoDdt ?? '' }}</strong>
                                </p>
                                <small class="text-muted">
                                    Mittente: <strong>{{ $azienda->ragione_sociale ?? 'Azienda' }}</strong><br>
                                    Destinatario: <strong id="conferma_ddt_destinatario">-</strong>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sezione SENZA DDT --}}
                <div id="conferma_no_ddt_section" style="display: none;">
                    <div class="alert alert-warning mb-0">
                        <i class="ri-information-line me-1"></i>
                        L'ordine verrà creato <strong>senza DDT</strong>. Potrai generarlo manualmente dalla sezione Documenti.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="ri-arrow-left-line me-1"></i> Torna indietro
                </button>
                <button type="button" class="btn btn-success" id="btnConfermaDefinitiva" onclick="inviaForm()">
                    <i class="ri-check-double-line me-1"></i> Conferma e Crea
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- JAVASCRIPT -->
<!-- ============================================ -->
<script>
    const ordiniData = @json($ordini->keyBy('id'));
    const clientiData = @json($clienti->keyBy('id'));

    // Cache tariffe caricate per evitare chiamate ripetute
    const tariffaCache = {};

    // =======================================
    // FILTRO PER STATO CON TAB
    // =======================================
    let statoAttivo = 'tutti';

    function filtraPerStato(stato, btnElement) {
        statoAttivo = stato;

        // Aggiorna tab attiva
        document.querySelectorAll('#tabStatiOrdini .nav-link').forEach(btn => btn.classList.remove('active'));
        btnElement.classList.add('active');

        // Filtra righe tabella
        const righe = document.querySelectorAll('#tabellaOrdini tbody tr');
        righe.forEach(riga => {
            if (stato === 'tutti') {
                riga.style.display = '';
            } else {
                riga.style.display = riga.getAttribute('data-stato') === stato ? '' : 'none';
            }
        });

        // Aggiorna info DataTable se presente
        aggiornaInfoTabella();
    }

    function aggiornaConteggiTab() {
        const righe = document.querySelectorAll('#tabellaOrdini tbody tr');
        const conteggi = { tutti: 0, pianificato: 0, assegnato: 0, in_corso: 0, completato: 0, annullato: 0 };

        righe.forEach(riga => {
            const stato = riga.getAttribute('data-stato');
            conteggi.tutti++;
            if (conteggi[stato] !== undefined) conteggi[stato]++;
        });

        // Aggiorna badge
        Object.keys(conteggi).forEach(stato => {
            const badge = document.getElementById('count_' + stato);
            if (badge) badge.textContent = conteggi[stato];
        });
    }

    function aggiornaInfoTabella() {
        // Se usi DataTable plugin, qui puoi aggiungere logica di refresh
        // Per ora il filtro CSS è sufficiente
    }

    // Inizializza conteggi al caricamento
    document.addEventListener('DOMContentLoaded', function() {
        aggiornaConteggiTab();
    });

    // =======================================
    // Quando cambia il cliente selezionato
    // =======================================
    function onClienteChange(idCliente, prefix) {
        if (!idCliente) {
            resetTariffaUI(prefix);
            return;
        }

        // ── Auto-fill indirizzo ritiro sulla prima tappa ───────────
        const cliente = clientiData[idCliente];
        if (cliente && cliente.indirizzo) {
            const container = document.getElementById(prefix + '-tappe-container');
            const firstDa = container?.querySelector('.tappa-item:first-child .tappa-da');
            if (firstDa && !firstDa.value) {
                firstDa.value = cliente.indirizzo;
                sincronizzaIndirizziOrdine(prefix);
            }
        }

        // Controlla cache
        if (tariffaCache[idCliente] !== undefined) {
            applicaTariffaUI(tariffaCache[idCliente], prefix);
            return;
        }

        // AJAX per ottenere la tariffa
        fetch('/azienda/get-tariffa-cliente/' + idCliente, {
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        })
            .then(r => r.json())
            .then(data => {
                tariffaCache[idCliente] = data;
                applicaTariffaUI(data, prefix);
            })
            .catch(err => {
                console.error('Errore caricamento tariffa:', err);
                resetTariffaUI(prefix);
            });
    }

    // =======================================
    // Applica UI tariffario
    // =======================================
    function applicaTariffaUI(data, prefix) {
        const toggleContainer = document.getElementById(prefix + '_toggle_container');
        const infoTariffa = document.getElementById(prefix + '_info_tariffa');
        const noTariffaMsg = document.getElementById(prefix + '_no_tariffa_msg');

        if (data.found && data.tariffa) {
            const t = data.tariffa;

            // Salva l'id della tariffa
            document.getElementById(prefix + '_id_tariffa_applicata').value = t.id;

            // Mostra toggle e info
            toggleContainer.style.cssText = '';
            infoTariffa.style.display = 'block';

            // Nomi tipi
            const tipiLabel = { fisso: 'Fisso', km: 'A Km', peso: 'A Peso', volume: 'A Volume', tempo: 'A Tempo' };

            document.getElementById(prefix + '_nome_tariffa').textContent = t.nome_tariffa;
            document.getElementById(prefix + '_tipo_calcolo_badge').textContent = tipiLabel[t.tipo_calcolo] || t.tipo_calcolo;

            // Info dettaglio
            let dettaglio = 'Base: € ' + parseFloat(t.prezzo_base).toFixed(2);
            if (t.tipo_calcolo === 'km' && t.prezzo_per_km) dettaglio += ' | € ' + parseFloat(t.prezzo_per_km).toFixed(3) + '/km';
            if (t.tipo_calcolo === 'peso' && t.prezzo_per_kg) dettaglio += ' | € ' + parseFloat(t.prezzo_per_kg).toFixed(3) + '/kg';
            if (t.tipo_calcolo === 'tempo' && t.prezzo_per_ora) dettaglio += ' | € ' + parseFloat(t.prezzo_per_ora).toFixed(2) + '/ora';
            document.getElementById(prefix + '_info_tariffa_dettaglio').textContent = dettaglio;

            // Mostra checkbox maggiorazioni se presenti
            if (t.maggiorazione_urgente > 0) {
                document.getElementById(prefix + '_check_urgente_wrap').style.display = '';
                document.getElementById(prefix + '_urgente_pct').textContent = '(+' + t.maggiorazione_urgente + '%)';
            } else {
                document.getElementById(prefix + '_check_urgente_wrap').style.display = 'none';
            }
            if (t.maggiorazione_festivo > 0) {
                document.getElementById(prefix + '_check_festivo_wrap').style.display = '';
                document.getElementById(prefix + '_festivo_pct').textContent = '(+' + t.maggiorazione_festivo + '%)';
            } else {
                document.getElementById(prefix + '_check_festivo_wrap').style.display = 'none';
            }
            if (t.maggiorazione_notturno > 0) {
                document.getElementById(prefix + '_check_notturno_wrap').style.display = '';
                document.getElementById(prefix + '_notturno_pct').textContent = '(+' + t.maggiorazione_notturno + '%)';
            } else {
                document.getElementById(prefix + '_check_notturno_wrap').style.display = 'none';
            }

            // Attiva automaticamente il tariffario
            document.getElementById(prefix + '_toggle_tariffario').checked = true;
            toggleModalitaImporto(prefix);

            // Messaggio per manuale
            noTariffaMsg.innerHTML = '<span class="text-info"><i class="ri-information-line"></i> Tariffario disponibile: usa il toggle per il calcolo automatico.</span>';

        } else {
            resetTariffaUI(prefix);
            noTariffaMsg.innerHTML = '<span class="text-muted">Nessun tariffario attivo per questo cliente.</span>';
        }
    }

    // =======================================
    // Reset UI tariffario
    // =======================================
    function resetTariffaUI(prefix) {
        document.getElementById(prefix + '_toggle_container').style.cssText = 'display: none !important;';
        document.getElementById(prefix + '_info_tariffa').style.display = 'none';
        document.getElementById(prefix + '_id_tariffa_applicata').value = '';

        // Torna a manuale
        document.getElementById(prefix + '_toggle_tariffario').checked = false;
        toggleModalitaImporto(prefix);
    }

    // =======================================
    // Toggle tra Manuale e Tariffario
    // =======================================
    function toggleModalitaImporto(prefix) {
        const usaTariffario = document.getElementById(prefix + '_toggle_tariffario').checked;
        const pannelloTariffario = document.getElementById(prefix + '_pannello_tariffario');
        const pannelloManuale = document.getElementById(prefix + '_pannello_manuale');
        const modalitaInput = document.getElementById(prefix + '_modalita_importo');

        if (usaTariffario) {
            pannelloTariffario.style.display = 'block';
            pannelloManuale.style.display = 'none';
            modalitaInput.value = 'tariffario';

            // Disabilita input manuale per evitare conflitto name
            const inputManuale = document.getElementById(prefix + '_importo_manuale');
            if (inputManuale) inputManuale.removeAttribute('name');
            const inputCalcolato = document.getElementById(prefix + '_importo_calcolato');
            if (inputCalcolato) inputCalcolato.setAttribute('name', 'importo');

            ricalcolaTariffa(prefix);
        } else {
            pannelloTariffario.style.display = 'none';
            pannelloManuale.style.display = 'block';
            modalitaInput.value = 'manuale';

            // Riabilita input manuale
            const inputManuale = document.getElementById(prefix + '_importo_manuale');
            if (inputManuale) inputManuale.setAttribute('name', 'importo');
            const inputCalcolato = document.getElementById(prefix + '_importo_calcolato');
            if (inputCalcolato) inputCalcolato.removeAttribute('name');
        }
    }

    // =======================================
    // Ricalcola tariffa lato client
    // =======================================
    function ricalcolaTariffa(prefix) {
        const idCliente = document.getElementById(prefix + '_id_cliente').value;
        const data = tariffaCache[idCliente];
        if (!data || !data.found || !data.tariffa) return;

        const t = data.tariffa;
        const base = parseFloat(t.prezzo_base) || 0;
        let variabile = 0;
        let labelVar = '';
        let descCalcolo = '';

        const km = parseFloat(document.getElementById(prefix + '_km_totali')?.value) || 0;
        const peso = parseFloat(document.getElementById(prefix + '_peso_kg')?.value) || 0;
        const ore = parseFloat(document.getElementById(prefix + '_ore_stimate')?.value) || 0;

        switch (t.tipo_calcolo) {
            case 'fisso':
                descCalcolo = 'Prezzo fisso';
                break;
            case 'km':
                const kmMin = parseFloat(t.km_minimi) || 0;
                const kmFatt = Math.max(km, kmMin);
                const prezzoKm = parseFloat(t.prezzo_per_km) || 0;
                variabile = kmFatt * prezzoKm;
                labelVar = kmFatt + ' km × € ' + prezzoKm.toFixed(3);
                descCalcolo = km < kmMin ? '⚠️ Km minimi applicati: ' + kmMin + ' km' : kmFatt + ' km a € ' + prezzoKm.toFixed(3) + '/km';
                if (km === 0) descCalcolo = '⚠️ Inserisci i Km totali per il calcolo';
                break;
            case 'peso':
                const pesoMin = parseFloat(t.peso_minimo) || 0;
                const pesoFatt = Math.max(peso, pesoMin);
                const prezzoKg = parseFloat(t.prezzo_per_kg) || 0;
                variabile = pesoFatt * prezzoKg;
                labelVar = pesoFatt + ' kg × € ' + prezzoKg.toFixed(3);
                descCalcolo = peso < pesoMin ? '⚠️ Peso minimo applicato: ' + pesoMin + ' kg' : pesoFatt + ' kg a € ' + prezzoKg.toFixed(3) + '/kg';
                if (peso === 0) descCalcolo = '⚠️ Inserisci il Peso per il calcolo';
                break;
            case 'tempo':
                const prezzoOra = parseFloat(t.prezzo_per_ora) || 0;
                variabile = ore * prezzoOra;
                labelVar = ore + ' ore × € ' + prezzoOra.toFixed(2);
                descCalcolo = ore > 0 ? ore + ' ore a € ' + prezzoOra.toFixed(2) + '/ora' : '⚠️ Inserisci le Ore stimate per il calcolo';
                break;
        }

        let subtotale = base + variabile;

        // Maggiorazioni
        let maggiorazioni = 0;
        let descMagg = [];
        if (document.getElementById(prefix + '_urgente')?.checked && t.maggiorazione_urgente > 0) {
            maggiorazioni += subtotale * (parseFloat(t.maggiorazione_urgente) / 100);
            descMagg.push('Urgente +' + t.maggiorazione_urgente + '%');
        }
        if (document.getElementById(prefix + '_festivo')?.checked && t.maggiorazione_festivo > 0) {
            maggiorazioni += subtotale * (parseFloat(t.maggiorazione_festivo) / 100);
            descMagg.push('Festivo +' + t.maggiorazione_festivo + '%');
        }
        if (document.getElementById(prefix + '_notturno')?.checked && t.maggiorazione_notturno > 0) {
            maggiorazioni += subtotale * (parseFloat(t.maggiorazione_notturno) / 100);
            descMagg.push('Notturno +' + t.maggiorazione_notturno + '%');
        }

        // Sconto
        let sconto = 0;
        const scontoFedelta = parseFloat(t['sconto_fedeltà']) || 0;
        if (scontoFedelta > 0) {
            sconto = (subtotale + maggiorazioni) * (scontoFedelta / 100);
        }

        const totale = subtotale + maggiorazioni - sconto;

        // Aggiorna UI
        document.getElementById(prefix + '_val_base').textContent = '€ ' + base.toFixed(2);

        const rowVar = document.getElementById(prefix + '_row_variabile');
        if (variabile > 0 || t.tipo_calcolo !== 'fisso') {
            rowVar.style.display = '';
            document.getElementById(prefix + '_label_variabile').textContent = labelVar || 'Costo variabile';
            document.getElementById(prefix + '_val_variabile').textContent = '€ ' + variabile.toFixed(2);
        } else {
            rowVar.style.display = 'none';
        }

        const rowMagg = document.getElementById(prefix + '_row_maggiorazioni');
        if (maggiorazioni > 0) {
            rowMagg.style.display = '';
            document.getElementById(prefix + '_label_maggiorazioni').textContent = descMagg.join(', ');
            document.getElementById(prefix + '_val_maggiorazioni').textContent = '€ ' + maggiorazioni.toFixed(2);
        } else {
            rowMagg.style.display = 'none';
        }

        const rowSconto = document.getElementById(prefix + '_row_sconto');
        if (sconto > 0) {
            rowSconto.style.display = '';
            document.getElementById(prefix + '_label_sconto').textContent = 'Sconto fedeltà -' + scontoFedelta + '%';
            document.getElementById(prefix + '_val_sconto').textContent = '- € ' + sconto.toFixed(2);
        } else {
            rowSconto.style.display = 'none';
        }

        document.getElementById(prefix + '_val_totale').textContent = '€ ' + totale.toFixed(2);
        document.getElementById(prefix + '_calcolo_descrizione').textContent = descCalcolo;
        document.getElementById(prefix + '_importo_calcolato').value = totale.toFixed(2);
    }

    // =======================================
    // Funzioni esistenti (aggiornate)
    // =======================================
    function cambiaStato(idOrdine, nuovoStato, selectElement) {
        fetch('/azienda/ordine-trasporto/cambia-stato', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ id_ordine: idOrdine, stato: nuovoStato })
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast('Stato aggiornato!', 'success');

                    // Aggiorna data-stato sulla riga per il filtro tab
                    const riga = selectElement.closest('tr');
                    if (riga) {
                        riga.setAttribute('data-stato', nuovoStato);

                        // Aggiorna conteggi e riapplica filtro
                        aggiornaConteggiTab();

                        // Se c'è un filtro attivo e la riga non corrisponde più, nascondila
                        if (statoAttivo !== 'tutti' && nuovoStato !== statoAttivo) {
                            riga.style.display = 'none';
                        }
                    }

                    // Aggiorna anche ordiniData
                    if (ordiniData[idOrdine]) {
                        ordiniData[idOrdine].stato = nuovoStato;
                    }
                } else {
                    alert('Errore');
                    location.reload();
                }
            })
            .catch(() => alert('Errore di connessione'));
    }

    function modificaOrdine(idOrdine) {
        const ordine = ordiniData[idOrdine];
        if (!ordine) { alert('Ordine non trovato'); return; }

        document.getElementById('modifica_id_ordine').value = ordine.id;
        document.getElementById('mod_numero_ordine').value = ordine.numero_ordine;
        document.getElementById('mod_id_cliente').value = ordine.id_cliente;
        document.getElementById('mod_data_ritiro').value = ordine.data_ritiro;
        document.getElementById('mod_ora_ritiro').value = ordine.ora_ritiro || '';
        document.getElementById('mod_data_consegna').value = ordine.data_consegna || '';
        document.getElementById('mod_ora_consegna').value = ordine.ora_consegna || '';
        document.getElementById('mod_indirizzo_ritiro').value = ordine.indirizzo_ritiro;
        document.getElementById('mod_indirizzo_consegna').value = ordine.indirizzo_consegna;
        document.getElementById('mod_descrizione_merce').value = ordine.descrizione_merce;
        document.getElementById('mod_numero_colli').value = ordine.numero_colli || '';
        const tipoUnita = ordine.tipo_unita || 'colli';
        document.getElementById('mod_tipo_colli').checked  = tipoUnita === 'colli';
        document.getElementById('mod_tipo_pedane').checked = tipoUnita === 'pedane';
        document.getElementById('mod_peso_kg').value = ordine.peso_kg || '';
        document.getElementById('mod_km_totali').value = ordine.km_totali || '';
        document.getElementById('mod_ore_stimate').value = ordine.ore_stimate || '';
        caricaTappeModal('mod', ordine.id, ordine.indirizzo_ritiro);
        document.getElementById('mod_stato').value = ordine.stato;
        document.getElementById('mod_note').value = ordine.note || '';
        document.getElementById('mod_pedane_consegnate').value = ordine.pedane_consegnate || 0;
        document.getElementById('mod_pedane_da_ritirare').value = ordine.pedane_da_ritirare || 0;

        // Imposta importo manuale come default
        document.getElementById('mod_importo_manuale').value = ordine.importo || '';

        // Carica tariffa del cliente (questo attiverà il toggle se c'è tariffa)
        onClienteChange(ordine.id_cliente, 'mod');

        // Se l'ordine era con importo manuale, assicurati che rimanga manuale
        setTimeout(() => {
            if (ordine.importo_manuale == 1 || ordine.importo_manuale === undefined) {
                document.getElementById('mod_toggle_tariffario').checked = false;
                toggleModalitaImporto('mod');
            }
        }, 500);

        new bootstrap.Modal(document.getElementById('modalModificaOrdine')).show();
    }

    function eliminaOrdine(idOrdine) {
        const ordine = ordiniData[idOrdine];
        document.getElementById('elimina_id_ordine').value = idOrdine;
        document.getElementById('elimina_numero_ordine').textContent = ordine ? ordine.numero_ordine : '#' + idOrdine;
        new bootstrap.Modal(document.getElementById('modalEliminaOrdine')).show();
    }

    function showToast(message, type) { console.log(type + ': ' + message); }

    // Auto-data oggi
    document.addEventListener('DOMContentLoaded', function() {
        const oggi = new Date().toISOString().split('T')[0];
        const dr = document.querySelector('#modalCreaOrdine input[name="data_ritiro"]');
        if (dr && !dr.value) dr.value = oggi;
    });

    // =======================================
    // Gestione conferma creazione con DDT
    // =======================================

    let modalCrea = null;
    let modalConferma = null;

    function confermaCreazione() {
        const form = document.getElementById('formCreaOrdine');

        // Validazione base del form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // Popola riepilogo
        const clienteSelect = document.getElementById('crea_id_cliente');
        const clienteNome = clienteSelect.options[clienteSelect.selectedIndex]?.text || '-';
        const dataRitiro = form.querySelector('input[name="data_ritiro"]').value;
        const merce = form.querySelector('textarea[name="descrizione_merce"]').value;
        const generaDdt = document.getElementById('crea_genera_ddt').checked;

        document.getElementById('conferma_cliente').textContent = clienteNome;
        document.getElementById('conferma_data').textContent = formatData(dataRitiro);
        document.getElementById('conferma_merce').textContent = merce || '-';
        document.getElementById('conferma_ddt_destinatario').textContent = clienteNome;

        // Mostra/nascondi sezione DDT
        if (generaDdt) {
            document.getElementById('conferma_ddt_section').style.display = 'block';
            document.getElementById('conferma_no_ddt_section').style.display = 'none';
            aggiornaNumeroDdt();
        } else {
            document.getElementById('conferma_ddt_section').style.display = 'none';
            document.getElementById('conferma_no_ddt_section').style.display = 'block';
        }

        // Chiudi modal crea e apri conferma
        modalCrea = bootstrap.Modal.getInstance(document.getElementById('modalCreaOrdine'));
        if (modalCrea) modalCrea.hide();

        setTimeout(() => {
            modalConferma = new bootstrap.Modal(document.getElementById('modalConfermaDDT'));
            modalConferma.show();
        }, 300);
    }

    function inviaForm() {
        const btn = document.getElementById('btnConfermaDefinitiva');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Creazione in corso...';

        if (modalConferma) modalConferma.hide();

        setTimeout(() => {
            document.getElementById('formCreaOrdine').submit();
        }, 200);
    }

    function aggiornaNumeroDdt() {
        fetch('/azienda/prossimo-numero-ddt', {
            headers: { 'Accept': 'application/json' }
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('conferma_numero_ddt').textContent = data.numero_ddt;
                    document.getElementById('badge_prossimo_ddt').textContent = data.numero_ddt;
                }
            })
            .catch(err => console.log('Errore aggiornamento DDT:', err));
    }

    function formatData(data) {
        if (!data) return '-';
        const parti = data.split('-');
        return parti[2] + '/' + parti[1] + '/' + parti[0];
    }

    // Quando si riapre la modal crea (dopo aver annullato la conferma)
    document.getElementById('modalConfermaDDT').addEventListener('hidden.bs.modal', function () {
        const btn = document.getElementById('btnConfermaDefinitiva');
        btn.disabled = false;
        btn.innerHTML = '<i class="ri-check-double-line me-1"></i> Conferma e Crea';

        if (!document.getElementById('formCreaOrdine').submitted) {
            setTimeout(() => {
                if (modalCrea) modalCrea.show();
                else new bootstrap.Modal(document.getElementById('modalCreaOrdine')).show();
            }, 300);
        }
    });

    document.getElementById('formCreaOrdine').addEventListener('submit', function() {
        this.submitted = true;
    });

    // =======================================
    // Calcolo Km automatico con Google Maps
    // =======================================
    // =======================================
    // Calcolo Km con Geocoder + Haversine (lato client)
    // Geocoder fa parte del core Maps JS API — funziona senza API extra
    // Stessa logica del Piano Giornaliero
    // =======================================
    function haversineKm(lat1, lon1, lat2, lon2) {
        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLon/2) * Math.sin(dLon/2);
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    function geocodifica(indirizzo) {
        return new Promise((resolve, reject) => {
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ address: indirizzo }, (results, status) => {
                if (status === 'OK' && results[0]) {
                    const loc = results[0].geometry.location;
                    resolve({ lat: loc.lat(), lng: loc.lng(), formatted: results[0].formatted_address });
                } else {
                    reject('Indirizzo non trovato: ' + indirizzo);
                }
            });
        });
    }

    async function calcolaKmAuto(prefix) {
        // Leggi da prima/ultima tappa
        const container = document.getElementById(prefix + '-tappe-container');
        const items = container ? Array.from(container.querySelectorAll('.tappa-item')) : [];
        let indirizzoRitiro   = items.length > 0 ? (items[0].querySelector('.tappa-da')?.value || '') : '';
        let indirizzoConsegna = items.length > 0 ? (items[items.length-1].querySelector('.tappa-a')?.value || '') : '';

        // fallback ai campi hidden
        if (!indirizzoRitiro)   indirizzoRitiro   = document.getElementById(prefix + '_indirizzo_ritiro')?.value  || '';
        if (!indirizzoConsegna) indirizzoConsegna = document.getElementById(prefix + '_indirizzo_consegna')?.value || '';

        if (!indirizzoRitiro || !indirizzoConsegna) {
            alert('Inserisci prima gli indirizzi nelle tappe (campo "Ritira da" della prima e "Consegna a" dell\'ultima).');
            return;
        }

        const infoEl = document.getElementById(prefix + '_km_info');
        infoEl.innerHTML = '<span class="text-primary"><i class="ri-loader-4-line"></i> Calcolo in corso...</span>';

        if (typeof google === 'undefined' || !google.maps) {
            infoEl.innerHTML = '<span class="text-danger"><i class="ri-error-warning-line"></i> Google Maps non caricato</span>';
            return;
        }

        try {
            // Geocodifica entrambi gli indirizzi
            const [partenza, arrivo] = await Promise.all([
                geocodifica(indirizzoRitiro),
                geocodifica(indirizzoConsegna)
            ]);

            // Haversine × 1.35 = stima distanza stradale
            const kmLineaAria = haversineKm(partenza.lat, partenza.lng, arrivo.lat, arrivo.lng);
            const km = Math.round(kmLineaAria * 1.35);

            // Stima tempo basata sulla distanza:
            // - Brevi (<50 km): 40 km/h (urbano/provinciale)
            // - Medie (50-150 km): 60 km/h (misto)
            // - Lunghe (150-400 km): 80 km/h (prevalenza superstrada/autostrada)
            // - Molto lunghe (>400 km): 90 km/h (prevalenza autostrada)
            let velocitaMedia;
            if (km < 50) velocitaMedia = 40;
            else if (km < 150) velocitaMedia = 60;
            else if (km < 400) velocitaMedia = 80;
            else velocitaMedia = 90;

            const durataMinuti = Math.round((km / velocitaMedia) * 60);
            const ore = (durataMinuti / 60).toFixed(1);

            // Formatta durata
            let durataText;
            if (durataMinuti >= 60) {
                const h = Math.floor(durataMinuti / 60);
                const m = durataMinuti % 60;
                durataText = h + ' or' + (h > 1 ? 'e' : 'a') + (m > 0 ? ' ' + m + ' min' : '');
            } else {
                durataText = durataMinuti + ' min';
            }

            // Compila i campi
            document.getElementById(prefix + '_km_totali').value = km;

            const oreInput = document.getElementById(prefix + '_ore_stimate');
            if (oreInput) {
                oreInput.value = ore;
            }

            // Mostra risultato
            infoEl.innerHTML = '<span class="text-success"><i class="ri-check-line"></i> ~' +
                km + ' km — ~' + durataText + '</span>';

            // Ricalcola tariffa con i nuovi km
            ricalcolaTariffa(prefix);

        } catch (errore) {
            infoEl.innerHTML = '<span class="text-danger"><i class="ri-error-warning-line"></i> ' + errore + '</span>';
        }
    }

    // ====================================================
    // GESTIONE TAPPE (staffetta multi-autista)
    // ====================================================
    const autistiOpzioni = @json($autisti);
    const mezziOpzioni   = @json($mezzi);
    let tappaIdx = 0;

    function _autistiOpts(sel) {
        return '<option value="">-- nessuno --</option>' +
            autistiOpzioni.map(a =>
                `<option value="${a.id}" ${String(a.id) === String(sel) ? 'selected' : ''}>${a.nome} ${a.cognome}</option>`
            ).join('');
    }

    function _mezziOpts(sel) {
        return '<option value="">-- nessuno --</option>' +
            mezziOpzioni.map(m =>
                `<option value="${m.id}" ${String(m.id) === String(sel) ? 'selected' : ''}>${m.targa}${m.marca ? ' - ' + m.marca + (m.modello ? ' ' + m.modello : '') : ''}</option>`
            ).join('');
    }

    function _tappaHtml(prefix, idx, ritiro, consegna, idAutista, idMezzo, note) {
        const esc = v => (v || '').replace(/"/g, '&quot;');
        return `<div class="tappa-item card border mb-2" data-idx="${idx}">
            <div class="card-header py-2 bg-light d-flex justify-content-between align-items-center">
                <span class="fw-semibold small text-primary">
                    <i class="ri-route-line me-1"></i> Tappa <span class="tappa-num"></span>
                </span>
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 remove-tappa-btn"
                        onclick="rimuoviTappa('${prefix}', this)">
                    <i class="ri-close-line"></i>
                </button>
            </div>
            <div class="card-body py-2">
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label small mb-1">Autista</label>
                        <select name="tappe[${idx}][id_autista]" class="form-select form-select-sm">
                            ${_autistiOpts(idAutista)}
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small mb-1">Mezzo</label>
                        <select name="tappe[${idx}][id_mezzo]" class="form-select form-select-sm">
                            ${_mezziOpts(idMezzo)}
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small mb-1"><i class="ri-map-pin-line text-success"></i> Ritira da</label>
                        <input type="text" name="tappe[${idx}][indirizzo_ritiro]"
                               class="form-control form-control-sm tappa-da"
                               value="${esc(ritiro)}" placeholder="Indirizzo ritiro..." autocomplete="off">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small mb-1"><i class="ri-map-pin-line text-danger"></i> Consegna a</label>
                        <input type="text" name="tappe[${idx}][indirizzo_consegna]"
                               class="form-control form-control-sm tappa-a"
                               value="${esc(consegna)}" placeholder="Indirizzo consegna..." autocomplete="off"
                               oninput="sincronizzaDa('${prefix}', this)">
                    </div>
                    <div class="col-12">
                        <label class="form-label small mb-1">Note per l'autista</label>
                        <input type="text" name="tappe[${idx}][note]"
                               class="form-control form-control-sm"
                               value="${esc(note)}" placeholder="Es: Chiamare prima di arrivare">
                    </div>
                </div>
            </div>
        </div>`;
    }

    // Sincronizza campi hidden indirizzo_ritiro/consegna dalla prima/ultima tappa
    function sincronizzaIndirizziOrdine(prefix) {
        const container = document.getElementById(prefix + '-tappe-container');
        if (!container) return;
        const items = Array.from(container.querySelectorAll('.tappa-item'));
        if (!items.length) return;
        const ritiro   = items[0].querySelector('.tappa-da')?.value || '';
        const consegna = items[items.length - 1].querySelector('.tappa-a')?.value || '';
        const hRitiro   = document.getElementById(prefix + '_indirizzo_ritiro');
        const hConsegna = document.getElementById(prefix + '_indirizzo_consegna');
        if (hRitiro)   hRitiro.value   = ritiro;
        if (hConsegna) hConsegna.value = consegna;
    }

    // Google Maps Autocomplete sugli input delle tappe
    function initAutocompleteTappe(container) {
        if (!gmapsReady) return;
        container.querySelectorAll('.tappa-da, .tappa-a').forEach(input => {
            if (input.dataset.acInit) return;
            try {
                const ac = new google.maps.places.Autocomplete(input, {
                    types: ['geocode'],
                    componentRestrictions: { country: 'it' },
                    fields: ['formatted_address']
                });
                ac.addListener('place_changed', function() {
                    const place = ac.getPlace();
                    if (place?.formatted_address) {
                        input.value = place.formatted_address;
                        const prefix = container.id.replace('-tappe-container', '');
                        if (input.classList.contains('tappa-a')) sincronizzaDa(prefix, input);
                        sincronizzaIndirizziOrdine(prefix);
                    }
                });
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        const pac = document.querySelector('.pac-container');
                        if (pac && window.getComputedStyle(pac).display !== 'none') e.preventDefault();
                    }
                });
                input.addEventListener('blur', function() {
                    const prefix = container.id.replace('-tappe-container', '');
                    sincronizzaIndirizziOrdine(prefix);
                });
                input.dataset.acInit = 'true';
            } catch(e) { console.warn('Autocomplete tappa:', e); }
        });
    }

    function aggiungiTappa(prefix, ritiro, consegna, idAutista, idMezzo, note) {
        const container = document.getElementById(prefix + '-tappe-container');
        const idx = tappaIdx++;
        if (ritiro === undefined) {
            const items = container.querySelectorAll('.tappa-item');
            if (items.length === 0) {
                // Prima tappa: usa indirizzo cliente se disponibile
                ritiro = container.dataset.indirizzoCliente || '';
            } else {
                ritiro = items[items.length - 1].querySelector('.tappa-a')?.value || '';
            }
        }
        container.insertAdjacentHTML('beforeend', _tappaHtml(prefix, idx, ritiro, consegna, idAutista, idMezzo, note));
        aggiornaNumeraTappe(prefix);
        initAutocompleteTappe(container);
    }

    function rimuoviTappa(prefix, btn) {
        const container = document.getElementById(prefix + '-tappe-container');
        if (container.querySelectorAll('.tappa-item').length <= 1) {
            alert('Deve esserci almeno una tappa.'); return;
        }
        btn.closest('.tappa-item').remove();
        aggiornaNumeraTappe(prefix);
    }

    function aggiornaNumeraTappe(prefix) {
        const items = document.querySelectorAll(`#${prefix}-tappe-container .tappa-item`);
        const totale = items.length;
        items.forEach((item, i) => {
            const n = item.querySelector('.tappa-num');
            if (n) n.textContent = (i + 1) + (totale > 1 ? '/' + totale : '');
            const rb = item.querySelector('.remove-tappa-btn');
            if (rb) rb.style.display = totale <= 1 ? 'none' : '';
        });
    }

    function sincronizzaDa(prefix, inputA) {
        const container = document.getElementById(prefix + '-tappe-container');
        const items = Array.from(container.querySelectorAll('.tappa-item'));
        const idx = items.findIndex(item => item.querySelector('.tappa-a') === inputA);
        if (idx >= 0 && idx < items.length - 1) {
            const nd = items[idx + 1].querySelector('.tappa-da');
            if (nd) nd.value = inputA.value;
        }
        sincronizzaIndirizziOrdine(prefix);
    }

    async function caricaTappeModal(prefix, idOrdine, indirizzoRitiro) {
        const container = document.getElementById(prefix + '-tappe-container');
        container.innerHTML = '<div class="text-center py-2 text-muted small"><span class="spinner-border spinner-border-sm me-1"></span> Caricamento tappe...</div>';
        try {
            const res  = await fetch('/azienda/ordine/' + idOrdine + '/tappe');
            const data = await res.json();
            container.innerHTML = '';
            if (data.tappe && data.tappe.length > 0) {
                data.tappe.forEach(t => aggiungiTappa(prefix, t.indirizzo_ritiro, t.indirizzo_consegna, t.id_autista, t.id_mezzo, t.note));
            } else {
                aggiungiTappa(prefix, indirizzoRitiro || '', '', data.id_autista, data.id_mezzo, '');
            }
        } catch(e) {
            container.innerHTML = '<div class="alert alert-warning py-2 small">Impossibile caricare le tappe.</div>';
        }
    }

    // Init CREATE modal: prima tappa quando si apre
    document.getElementById('modalCreaOrdine')?.addEventListener('show.bs.modal', function() {
        document.getElementById('crea-tappe-container').innerHTML = '';
        aggiungiTappa('crea');
    });

    // Sync hidden fields prima del submit
    document.getElementById('formCreaOrdine')?.addEventListener('submit', function() {
        sincronizzaIndirizziOrdine('crea');
    });
    document.getElementById('formModificaOrdine')?.addEventListener('submit', function() {
        sincronizzaIndirizziOrdine('mod');
    });

</script>

<!-- ============================================ -->
<!-- ✅ GOOGLE MAPS AUTOCOMPLETE INDIRIZZI       -->
<!-- ============================================ -->
<style>
    .pac-container {
        z-index: 99999 !important;
    }
    .pac-item {
        padding: 8px 12px !important;
        cursor: pointer;
    }
    .pac-item:hover {
        background: #f0f4ff !important;
    }
</style>

<script>
    // ===== Google Maps Autocomplete sugli indirizzi =====
    let gmapsReady = false;
    const autocompleteInstances = {};

    function initGoogleMapsAutocomplete() {
        gmapsReady = true;
        console.log('[Logistia] Google Maps Autocomplete pronto');
        initAutocompleteOnFields();
        // Inizializza autocomplete su tutti i container tappe già visibili
        document.querySelectorAll('[id$="-tappe-container"]').forEach(c => initAutocompleteTappe(c));
    }

    function initAutocompleteOnFields() {
        if (!gmapsReady) return;

        // Solo campi visibili (gli indirizzi sono ora hidden e gestiti nelle tappe)
        const campi = [];

        campi.forEach(id => {
            const input = document.getElementById(id);
            if (input && !input.dataset.acInit) {
                try {
                    const ac = new google.maps.places.Autocomplete(input, {
                        types: ['address'],
                        componentRestrictions: { country: 'it' },
                        fields: ['formatted_address', 'address_components', 'geometry']
                    });

                    ac.addListener('place_changed', function() {
                        const place = ac.getPlace();
                        if (place && place.formatted_address) {
                            input.value = place.formatted_address;
                        }
                    });

                    // Blocca Enter quando il dropdown è aperto
                    input.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter') {
                            const pacContainer = document.querySelector('.pac-container');
                            if (pacContainer && window.getComputedStyle(pacContainer).display !== 'none') {
                                e.preventDefault();
                            }
                        }
                    });

                    autocompleteInstances[id] = ac;
                    input.dataset.acInit = 'true';
                    console.log('[Logistia] Autocomplete attivo su #' + id);
                } catch(e) {
                    console.warn('[Logistia] Errore autocomplete #' + id + ':', e.message);
                }
            }
        });
    }

    // Inizializza quando si aprono le modal
    document.addEventListener('DOMContentLoaded', function() {
        const modalCreaEl = document.getElementById('modalCreaOrdine');
        if (modalCreaEl) {
            modalCreaEl.addEventListener('shown.bs.modal', function() {
                initAutocompleteOnFields();
                initAutocompleteTappe(document.getElementById('crea-tappe-container'));
            });
        }

        const modalModEl = document.getElementById('modalModificaOrdine');
        if (modalModEl) {
            modalModEl.addEventListener('shown.bs.modal', function() {
                initAutocompleteOnFields();
                initAutocompleteTappe(document.getElementById('mod-tappe-container'));
            });
        }
    });
</script>

<!-- ✅ Google Maps JS API con Places library -->
<script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB0Kta9cMMAOEcpcGl0hwXij0I6_gqWeLM&loading=async&libraries=places&callback=initGoogleMapsAutocomplete">
</script>

@include('azienda.common.footer')