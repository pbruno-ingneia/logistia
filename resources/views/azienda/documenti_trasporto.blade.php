@include('azienda.common.header')

<div class="page-content">
    <div class="container-fluid">
        <!-- Titolo pagina -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">📋 Documenti di Trasporto</h4>
                    <div class="page-title-right">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCreaDocumento">
                            <i class="ri-add-line"></i> Nuovo Documento
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiche rapide -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center bg-primary text-white">
                    <div class="card-body">
                        <h3>{{ $documenti->where('tipo_documento', 'ddt')->count() }}</h3>
                        <p class="mb-0">DDT Creati</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center bg-info text-white">
                    <div class="card-body">
                        <h3>{{ $documenti->where('tipo_documento', 'cmr')->count() }}</h3>
                        <p class="mb-0">CMR Creati</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center bg-success text-white">
                    <div class="card-body">
                        <h3>{{ $documenti->where('data_consegna', '!=', null)->count() }}</h3>
                        <p class="mb-0">Consegnati</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center bg-warning text-white">
                    <div class="card-body">
                        <h3>{{ $documenti->where('created_at', '>=', date('Y-m-d'))->count() }}</h3>
                        <p class="mb-0">Oggi</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtri -->
        @if($idOrdine)
            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        Stai visualizzando i documenti per l'ordine specifico.
                        <a href="/azienda/documenti-trasporto" class="btn btn-outline-info btn-sm ms-2">
                            <i class="ri-eye-line"></i> Vedi Tutti
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- Tabella documenti -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title text-white mb-0">
                            <i class="ri-file-text-line me-2"></i>Elenco Documenti
                        </h5>
                    </div>
                    <div class="card-body">
                        @if(count($documenti) > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered datatable w-100">
                                    <thead class="table-light">
                                    <tr>
                                        <th>Tipo</th>
                                        <th>N. Documento</th>
                                        <th>Data</th>
                                        <th>Ordine</th>
                                        <th>Cliente</th>
                                        <th>Mittente → Destinatario</th>
                                        <th>Merce</th>
                                        <th>Stato</th>
                                        <th class="no-sort">Azioni</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($documenti as $documento)
                                        <tr id="riga-doc-{{ $documento->id }}">
                                            <td>
                                                <span class="badge
                                                    @if($documento->tipo_documento == 'ddt') bg-primary
                                                    @elseif($documento->tipo_documento == 'cmr') bg-info
                                                    @elseif($documento->tipo_documento == 'fattura') bg-success
                                                    @elseif($documento->tipo_documento == 'bolla') bg-warning
                                                    @else bg-secondary @endif">
                                                    {{ strtoupper($documento->tipo_documento) }}
                                                </span>
                                            </td>
                                            <td><strong>{{ $documento->numero_documento }}</strong></td>
                                            <td>{{ date('d/m/Y', strtotime($documento->data_documento)) }}</td>
                                            <td>
                                                @if($documento->numero_ordine)
                                                    <a href="/azienda/ordine-trasporto/{{ $documento->id_ordine }}" class="text-decoration-none">
                                                        {{ $documento->numero_ordine }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>{{ $documento->cliente_nome ?? 'Non specificato' }}</td>
                                            <td>
                                                <small>
                                                    <strong>Da:</strong> {{ Str::limit($documento->mittente_nome, 20) ?? 'Non specificato' }}<br>
                                                    <strong>A:</strong> {{ Str::limit($documento->destinatario_indirizzo, 20) ?? 'Non specificato' }}

                                                </small>
                                            </td>
                                            <td>
                                                <small>
                                                    {{ Str::limit($documento->descrizione_merce, 30) ?? 'Non specificata' }}
                                                    @if($documento->peso_lordo)
                                                        <br><strong>{{ $documento->peso_lordo }}kg</strong>
                                                    @endif
                                                    @if($documento->numero_colli)
                                                        <br>{{ $documento->numero_colli }} colli
                                                    @endif
                                                </small>
                                            </td>
                                            <td>
                                                @if($documento->data_consegna)
                                                    <span class="badge bg-success">✅ Consegnato</span><br>
                                                    <small>{{ date('d/m/Y H:i', strtotime($documento->data_consegna)) }}</small>
                                                    @if($documento->consegnato_a)
                                                        <br><small>a: {{ $documento->consegnato_a }}</small>
                                                    @endif
                                                @else
                                                    <span class="badge bg-warning">📋 In Transito</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-info btn-sm" onclick="visualizzaDocumento({{ $documento->id }})" title="Visualizza">
                                                        <i class="ri-eye-line"></i>
                                                    </button>
                                                    @if(!$documento->data_consegna)
                                                        <button class="btn btn-success btn-sm" id="btn-consegna-{{ $documento->id }}" onclick="segnaConsegnato({{ $documento->id }})" title="Segna Consegnato">
                                                            <i class="ri-check-line"></i>
                                                        </button>
                                                    @endif
                                                    <button class="btn btn-primary btn-sm" onclick="stampaPDF({{ $documento->id_ordine ?? 'null' }})" title="Stampa PDF">
                                                        <i class="ri-printer-line"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="ri-file-text-line text-muted mb-3" style="font-size: 48px;"></i>
                                <h5 class="text-muted">Nessun documento creato</h5>
                                <p class="text-muted">Crea il primo documento di trasporto per iniziare a gestire DDT, CMR e altri documenti.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreaDocumento">
                                    <i class="ri-add-line"></i> Crea Primo Documento
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Crea Documento -->
<div class="modal fade" id="modalCreaDocumento" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="post">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title text-white">
                        <i class="ri-file-add-line me-2"></i>Nuovo Documento di Trasporto
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Ordine di Trasporto *</label>
                            <select name="id_ordine" class="form-select" required onchange="caricaDatiOrdine(this.value)">
                                <option value="">Seleziona ordine...</option>
                                @foreach($ordini as $ordine)
                                    <option value="{{ $ordine->id }}" {{ $idOrdine && $idOrdine == $ordine->id ? 'selected' : '' }}>
                                        {{ $ordine->numero_ordine }} - {{ $ordine->cliente_nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tipo Documento *</label>
                            <select name="tipo_documento" class="form-select" required>
                                <option value="ddt">DDT - Documento di Trasporto</option>
                                <option value="cmr">CMR - Trasporto Internazionale</option>
                                <option value="bolla">Bolla di Accompagnamento</option>
                                <option value="ricevuta">Ricevuta di Consegna</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Data Documento *</label>
                            <input type="date" name="data_documento" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    <!-- Mittente e Destinatario -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-muted">📍 Mittente e Destinatario</h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nome Mittente *</label>
                            <input type="text" name="mittente_nome" id="mittente_nome" class="form-control" required value="{{ $azienda->ragione_sociale ?? '' }}">

                            <label class="form-label mt-2">Indirizzo Mittente</label>
                            <textarea name="mittente_indirizzo" id="mittente_indirizzo" class="form-control" rows="2">{{ isset($azienda) ? implode(', ', array_filter([$azienda->indirizzo ?? '', $azienda->cap ?? '', $azienda->comune ?? '', $azienda->provincia ? '('.$azienda->provincia.')' : ''])) : '' }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nome Destinatario *</label>
                            <input type="text" name="destinatario_nome" id="destinatario_nome" class="form-control" required>

                            <label class="form-label mt-2">Indirizzo Destinatario</label>
                            <textarea name="destinatario_indirizzo" id="destinatario_indirizzo" class="form-control" rows="2"></textarea>
                        </div>
                    </div>

                    <!-- Dettagli Merce -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-muted">📦 Dettagli Merce</h6>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Descrizione Merce *</label>
                            <textarea name="descrizione_merce" id="descrizione_merce" class="form-control" rows="2" required></textarea>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-3">
                            <label class="form-label">Peso Lordo (kg)</label>
                            <input type="number" step="0.01" name="peso_lordo" id="peso_lordo" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Peso Netto (kg)</label>
                            <input type="number" step="0.01" name="peso_netto" id="peso_netto" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Numero Colli</label>
                            <input type="number" name="numero_colli" id="numero_colli" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Valore Merce €</label>
                            <input type="number" step="0.01" name="valore_merce" id="valore_merce" class="form-control">
                        </div>
                    </div>

                    <!-- Note -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <label class="form-label">Note Aggiuntive</label>
                            <textarea name="note" class="form-control" rows="2" placeholder="Istruzioni speciali, modalità di consegna, etc."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <input type="hidden" name="crea_documento" value="1">
                    <button type="submit" class="btn btn-success">
                        <i class="ri-save-line"></i> Crea Documento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Visualizza Documento -->
<div class="modal fade" id="modalVisualizzaDocumento" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white pb-2" id="titoloModalDocumento">
                    <i class="ri-file-text-line me-2"></i>Dettagli Documento
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contenutoDocumento">
                <!-- Contenuto caricato dinamicamente con dati REALI -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                <button type="button" class="btn btn-primary" id="btnStampaPDFModal" onclick="stampaPDFCorrente()">
                    <i class="ri-printer-line"></i> Stampa PDF
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Segna Consegnato -->
<div class="modal fade" id="modalSegnaConsegnato" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white">
                    <i class="ri-check-double-line me-2"></i>Segna come Consegnato
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <p>Stai per segnare il documento <strong id="consegna_numero_doc"></strong> come consegnato.</p>
                </div>
                <div class="mb-3">
                    <label class="form-label">Data e Ora Consegna *</label>
                    <input type="datetime-local" class="form-control" id="consegna_data" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Consegnato a (nome persona) *</label>
                    <input type="text" class="form-control" id="consegna_persona" placeholder="Nome della persona che ha ricevuto" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Note consegna</label>
                    <textarea class="form-control" id="consegna_note" rows="2" placeholder="Eventuali note sulla consegna..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                <button type="button" class="btn btn-success" id="btnConfermaConsegna" onclick="confermaConsegna()">
                    <i class="ri-check-line me-1"></i> Conferma Consegna
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // ==========================================
    // DATI REALI dal database passati via JSON
    // ==========================================
    const documentiData = @json($documenti);
    const ordiniData = @json($ordini);

    let documentoCorrente = null;
    let ordineCorrente = null;
    let idDocumentoConsegna = null;

    // ==========================================
    // CARICA DATI ORDINE nella form Crea Documento
    // ==========================================
    function caricaDatiOrdine(idOrdine) {
        if (!idOrdine) {
            // Reset campi
            document.getElementById('destinatario_nome').value = '';
            document.getElementById('destinatario_indirizzo').value = '';
            document.getElementById('descrizione_merce').value = '';
            document.getElementById('peso_lordo').value = '';
            document.getElementById('numero_colli').value = '';
            document.getElementById('valore_merce').value = '';
            return;
        }

        // Carica dati ordine completi via AJAX
        fetch('/azienda/get-dati-ordine/' + idOrdine, {
            headers: { 'Accept': 'application/json' }
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const o = data.ordine;
                    document.getElementById('destinatario_nome').value = o.cliente_nome || '';
                    document.getElementById('destinatario_indirizzo').value = o.indirizzo_consegna || '';
                    document.getElementById('descrizione_merce').value = o.descrizione_merce || '';
                    document.getElementById('peso_lordo').value = o.peso_kg || '';
                    document.getElementById('numero_colli').value = o.numero_colli || '';
                    document.getElementById('valore_merce').value = o.importo || '';
                }
            })
            .catch(err => {
                console.log('Errore caricamento ordine:', err);
                // Fallback: usa dati base dal select
                const ordine = ordiniData.find(o => o.id == idOrdine);
                if (ordine) {
                    document.getElementById('destinatario_nome').value = ordine.cliente_nome || '';
                }
            });
    }

    // ==========================================
    // VISUALIZZA DOCUMENTO con dati REALI
    // ==========================================
    function visualizzaDocumento(idDocumento) {
        // Trova il documento nei dati reali
        const doc = documentiData.find(d => d.id == idDocumento);
        if (!doc) {
            alert('Documento non trovato');
            return;
        }

        documentoCorrente = idDocumento;
        ordineCorrente = doc.id_ordine;

        // Tipo documento in formato leggibile
        const tipiDoc = {
            'ddt': 'DOCUMENTO DI TRASPORTO (DDT)',
            'cmr': 'LETTERA DI VETTURA INTERNAZIONALE (CMR)',
            'bolla': 'BOLLA DI ACCOMPAGNAMENTO',
            'ricevuta': 'RICEVUTA DI CONSEGNA'
        };
        const tipoLabel = tipiDoc[doc.tipo_documento] || doc.tipo_documento.toUpperCase();

        // Formatta data
        const dataDoc = doc.data_documento ? new Date(doc.data_documento).toLocaleDateString('it-IT') : '-';

        // Stato consegna
        let statoHtml = '';
        if (doc.data_consegna) {
            const dataConsegna = new Date(doc.data_consegna).toLocaleDateString('it-IT', {
                day: '2-digit', month: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit'
            });
            statoHtml = `
                <div class="alert alert-success py-2 mb-0">
                    <i class="ri-check-double-line me-1"></i>
                    <strong>Consegnato</strong> il ${dataConsegna}
                    ${doc.consegnato_a ? ' a <strong>' + escapeHtml(doc.consegnato_a) + '</strong>' : ''}
                </div>`;
        } else {
            statoHtml = `<span class="badge bg-warning fs-6">📋 In Transito</span>`;
        }

        // Formatta valuta
        const formatValuta = (val) => {
            if (!val || val == 0) return '-';
            return parseFloat(val).toLocaleString('it-IT', { style: 'currency', currency: 'EUR' });
        };

        // Costruisci contenuto con DATI REALI
        const contenuto = `
            <div class="row">
                <div class="col-12 text-center mb-3">
                    <h4 class="mb-1">${tipoLabel}</h4>
                    <p class="text-muted mb-0">
                        N. <strong>${escapeHtml(doc.numero_documento)}</strong> del ${dataDoc}
                        ${doc.numero_ordine ? ' — Ordine: <a href="/azienda/ordine-trasporto/' + doc.id_ordine + '">' + escapeHtml(doc.numero_ordine) + '</a>' : ''}
                    </p>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary"><i class="ri-map-pin-line me-1"></i>MITTENTE</h6>
                    <div class="border rounded p-2 bg-light">
                        <strong>${escapeHtml(doc.mittente_nome || 'Non specificato')}</strong>
                        ${doc.mittente_indirizzo ? '<br><small class="text-muted">' + escapeHtml(doc.mittente_indirizzo) + '</small>' : ''}
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="text-success"><i class="ri-map-pin-line me-1"></i>DESTINATARIO</h6>
                    <div class="border rounded p-2 bg-light">
                        <strong>${escapeHtml(doc.destinatario_nome || 'Non specificato')}</strong>
                        ${doc.destinatario_indirizzo ? '<br><small class="text-muted">' + escapeHtml(doc.destinatario_indirizzo) + '</small>' : ''}
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <h6><i class="ri-box-3-line me-1"></i>DESCRIZIONE MERCE</h6>
                    <div class="border rounded p-2">
                        ${escapeHtml(doc.descrizione_merce || 'Non specificata')}
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-3 text-center">
                    <div class="border rounded p-2">
                        <small class="text-muted d-block">Peso Lordo</small>
                        <strong>${doc.peso_lordo ? doc.peso_lordo + ' kg' : '-'}</strong>
                    </div>
                </div>
                <div class="col-md-3 text-center">
                    <div class="border rounded p-2">
                        <small class="text-muted d-block">Peso Netto</small>
                        <strong>${doc.peso_netto ? doc.peso_netto + ' kg' : '-'}</strong>
                    </div>
                </div>
                <div class="col-md-3 text-center">
                    <div class="border rounded p-2">
                        <small class="text-muted d-block">N. Colli</small>
                        <strong>${doc.numero_colli || '-'}</strong>
                    </div>
                </div>
                <div class="col-md-3 text-center">
                    <div class="border rounded p-2">
                        <small class="text-muted d-block">Valore Merce</small>
                        <strong>${formatValuta(doc.valore_merce)}</strong>
                    </div>
                </div>
            </div>
            ${doc.note ? `
            <div class="row mt-3">
                <div class="col-12">
                    <h6><i class="ri-sticky-note-line me-1"></i>NOTE</h6>
                    <div class="border rounded p-2 bg-light">
                        <small>${escapeHtml(doc.note)}</small>
                    </div>
                </div>
            </div>` : ''}
            <div class="row mt-3">
                <div class="col-12">
                    <h6><i class="ri-truck-line me-1"></i>STATO CONSEGNA</h6>
                    ${statoHtml}
                </div>
            </div>
            ${doc.cliente_nome ? `
            <div class="row mt-3">
                <div class="col-12">
                    <small class="text-muted">Cliente: <strong>${escapeHtml(doc.cliente_nome)}</strong></small>
                </div>
            </div>` : ''}
        `;

        // Aggiorna titolo modal
        document.getElementById('titoloModalDocumento').innerHTML =
            `<i class="ri-file-text-line me-2"></i>${escapeHtml(doc.numero_documento)} - Dettagli`;

        // Mostra/nascondi bottone PDF in base all'ordine
        const btnPdf = document.getElementById('btnStampaPDFModal');
        if (doc.id_ordine) {
            btnPdf.style.display = 'inline-block';
        } else {
            btnPdf.style.display = 'none';
        }

        document.getElementById('contenutoDocumento').innerHTML = contenuto;
        new bootstrap.Modal(document.getElementById('modalVisualizzaDocumento')).show();
    }

    // ==========================================
    // SEGNA CONSEGNATO - Modal + AJAX reale
    // ==========================================
    function segnaConsegnato(idDocumento) {
        const doc = documentiData.find(d => d.id == idDocumento);
        if (!doc) return;

        idDocumentoConsegna = idDocumento;

        // Popola modal
        document.getElementById('consegna_numero_doc').textContent = doc.numero_documento;

        // Precompila data/ora corrente
        const now = new Date();
        const offset = now.getTimezoneOffset() * 60000;
        const localISO = new Date(now - offset).toISOString().slice(0, 16);
        document.getElementById('consegna_data').value = localISO;
        document.getElementById('consegna_persona').value = '';
        document.getElementById('consegna_note').value = '';

        // Apri modal
        new bootstrap.Modal(document.getElementById('modalSegnaConsegnato')).show();
    }

    function confermaConsegna() {
        const dataConsegna = document.getElementById('consegna_data').value;
        const consegnatoA = document.getElementById('consegna_persona').value.trim();

        if (!dataConsegna || !consegnatoA) {
            alert('Compila data e nome della persona');
            return;
        }

        const btn = document.getElementById('btnConfermaConsegna');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Salvataggio...';

        // Invia AJAX al server
        fetch('/azienda/documenti-trasporto/segna-consegnato', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    || document.querySelector('input[name="_token"]')?.value
            },
            body: JSON.stringify({
                id_documento: idDocumentoConsegna,
                data_consegna: dataConsegna,
                consegnato_a: consegnatoA,
                note_consegna: document.getElementById('consegna_note').value
            })
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Chiudi modal
                    bootstrap.Modal.getInstance(document.getElementById('modalSegnaConsegnato')).hide();

                    // Aggiorna la riga in tabella senza ricaricare
                    const riga = document.getElementById('riga-doc-' + idDocumentoConsegna);
                    if (riga) {
                        // Aggiorna colonna stato
                        const cellStato = riga.cells[7];
                        const dataFormatted = new Date(dataConsegna).toLocaleDateString('it-IT', {
                            day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
                        });
                        cellStato.innerHTML = `
                        <span class="badge bg-success">✅ Consegnato</span><br>
                        <small>${dataFormatted}</small><br>
                        <small>a: ${escapeHtml(consegnatoA)}</small>
                    `;

                        // Rimuovi bottone "segna consegnato"
                        const btnConsegna = document.getElementById('btn-consegna-' + idDocumentoConsegna);
                        if (btnConsegna) btnConsegna.remove();

                        // Aggiorna anche l'oggetto nei dati JS
                        const docIdx = documentiData.findIndex(d => d.id == idDocumentoConsegna);
                        if (docIdx >= 0) {
                            documentiData[docIdx].data_consegna = dataConsegna;
                            documentiData[docIdx].consegnato_a = consegnatoA;
                        }
                    }

                    // Feedback
                    showToast('Documento segnato come consegnato!', 'success');
                } else {
                    alert('Errore: ' + (data.message || 'Errore sconosciuto'));
                }
            })
            .catch(err => {
                console.error('Errore:', err);
                alert('Errore di connessione. Riprova.');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="ri-check-line me-1"></i> Conferma Consegna';
            });
    }

    // ==========================================
    // STAMPA PDF
    // ==========================================
    function stampaPDF(idOrdine) {
        if (!idOrdine || idOrdine === 'null' || idOrdine === null) {
            alert('Nessun ordine associato a questo documento. Impossibile generare il PDF.');
            return;
        }
        window.open('/azienda/ddt/' + idOrdine + '/pdf', '_blank');
    }

    function stampaPDFCorrente() {
        stampaPDF(ordineCorrente);
    }

    // ==========================================
    // UTILITY
    // ==========================================
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showToast(messaggio, tipo) {
        // Se hai un sistema toast (es. Toastify), usalo qui
        // Altrimenti fallback semplice
        if (typeof Toastify !== 'undefined') {
            Toastify({
                text: messaggio,
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor: tipo === 'success' ? "#28a745" : "#dc3545"
            }).showToast();
        } else {
            alert(messaggio);
        }
    }

    // Pre-seleziona ordine se passato via URL
    @if($idOrdine)
    document.addEventListener('DOMContentLoaded', function() {
        caricaDatiOrdine('{{ $idOrdine }}');
    });
    @endif
</script>

@include('azienda.common.footer')