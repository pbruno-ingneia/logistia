@include('azienda.common.header')

<div class="page-content">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="/azienda/ordini-trasporto" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-1"></i> Torna agli Ordini
            </a>
            <h2 class="mb-0">
                <i class="fas fa-shipping-fast me-2 text-primary"></i>
                Ordine #{{ $ordine->numero_ordine }}
            </h2>
        </div>
        <div>
            @php
                switch($ordine->stato) {
                    case 'pianificato':
                        $badgeClass = 'bg-secondary';
                        break;
                    case 'assegnato':
                        $badgeClass = 'bg-info';
                        break;
                    case 'in_corso':
                        $badgeClass = 'bg-warning text-dark';
                        break;
                    case 'completato':
                        $badgeClass = 'bg-success';
                        break;
                    case 'annullato':
                        $badgeClass = 'bg-danger';
                        break;
                    default:
                        $badgeClass = 'bg-secondary';
                }
                $statoLabel = ucfirst(str_replace('_', ' ', $ordine->stato));
            @endphp
            <span class="badge {{ $badgeClass }} fs-6 px-3 py-2">{{ $statoLabel }}</span>
        </div>
    </div>

    <div class="row">
        {{-- COLONNA SINISTRA - Dettagli Ordine --}}
        <div class="col-lg-6 mb-4">

            {{-- Card Riepilogo --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0 text-white"><i class="fas fa-info-circle me-2"></i>Riepilogo Ordine</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <small class="text-muted d-block">Data Ritiro</small>
                            <strong>{{ $ordine->data_ritiro ? date('d/m/Y', strtotime($ordine->data_ritiro)) : '-' }}</strong>
                            @if($ordine->ora_ritiro)
                                <span class="text-muted">{{ $ordine->ora_ritiro }}</span>
                            @endif
                        </div>
                        <div class="col-6 mb-3">
                            <small class="text-muted d-block">Data Consegna</small>
                            <strong>{{ $ordine->data_consegna ? date('d/m/Y', strtotime($ordine->data_consegna)) : '-' }}</strong>
                            @if($ordine->ora_consegna)
                                <span class="text-muted">{{ $ordine->ora_consegna }}</span>
                            @endif
                        </div>
                        <div class="col-4 mb-3">
                            <small class="text-muted d-block">Importo</small>
                            <strong class="text-success fs-5">€ {{ number_format($ordine->importo ?? 0, 2, ',', '.') }}</strong>
                        </div>
                        <div class="col-4 mb-3">
                            <small class="text-muted d-block">N° Colli</small>
                            <strong>{{ $ordine->numero_colli ?? '-' }}</strong>
                        </div>
                        <div class="col-4 mb-3">
                            <small class="text-muted d-block">Peso</small>
                            <strong>{{ $ordine->peso_kg ? number_format($ordine->peso_kg, 2, ',', '.') . ' Kg' : '-' }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card Cliente --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-user-tie me-2 text-primary"></i>Cliente</h5>
                </div>
                <div class="card-body">
                    <h5 class="mb-2">{{ $ordine->cliente_nome ?? 'N/D' }}</h5>
                    @if(isset($ordine->cliente_indirizzo) && $ordine->cliente_indirizzo)
                        <p class="mb-1"><i class="fas fa-map-marker-alt text-muted me-2"></i>{{ $ordine->cliente_indirizzo }}</p>
                    @endif
                    @if(isset($ordine->cliente_telefono) && $ordine->cliente_telefono)
                        <p class="mb-1"><i class="fas fa-phone text-muted me-2"></i>{{ $ordine->cliente_telefono }}</p>
                    @endif
                    @if(isset($ordine->cliente_email) && $ordine->cliente_email)
                        <p class="mb-0"><i class="fas fa-envelope text-muted me-2"></i>{{ $ordine->cliente_email }}</p>
                    @endif
                </div>
            </div>

            {{-- Card Indirizzi --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-route me-2 text-primary"></i>Percorso</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <div class="bg-success rounded-circle p-2 me-3">
                            <i class="fas fa-arrow-up text-white"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Ritiro da</small>
                            <strong>{{ $ordine->indirizzo_ritiro }}</strong>
                        </div>
                    </div>
                    <div class="border-start ms-3 ps-4 py-2 text-muted">
                        <i class="fas fa-ellipsis-v"></i>
                    </div>
                    <div class="d-flex align-items-start">
                        <div class="bg-danger rounded-circle p-2 me-3">
                            <i class="fas fa-arrow-down text-white"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Consegna a</small>
                            <strong>{{ $ordine->indirizzo_consegna }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card Mezzo e Autista --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-truck me-2 text-primary"></i>Mezzo & Autista</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted d-block">Mezzo</small>
                            @if($ordine->targa)
                                <span class="badge bg-dark fs-6">{{ $ordine->targa }}</span>
                                <div class="mt-1">{{ $ordine->mezzo_marca ?? '' }} {{ $ordine->mezzo_modello ?? '' }}</div>
                            @else
                                <span class="text-muted">Non assegnato</span>
                            @endif
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Autista</small>
                            @if($ordine->autista_nome)
                                <strong>{{ $ordine->autista_nome }} {{ $ordine->autista_cognome }}</strong>
                                @if(isset($ordine->autista_telefono) && $ordine->autista_telefono)
                                    <div class="mt-1">
                                        <a href="tel:{{ $ordine->autista_telefono }}" class="text-decoration-none">
                                            <i class="fas fa-phone me-1"></i>{{ $ordine->autista_telefono }}
                                        </a>
                                    </div>
                                @endif
                            @else
                                <span class="text-muted">Non assegnato</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card Merce --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-boxes me-2 text-primary"></i>Descrizione Merce</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $ordine->descrizione_merce ?: 'Nessuna descrizione' }}</p>
                </div>
            </div>

            @if($ordine->note)
                {{-- Card Note --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-sticky-note me-2 text-primary"></i>Note</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $ordine->note }}</p>
                    </div>
                </div>
            @endif

            {{-- Card Foto Consegna --}}
            @php
                $fotoConsegna = DB::table('foto_consegna')->where('id_ordine', $ordine->id)->orderBy('created_at', 'desc')->get();
            @endphp
            @if($fotoConsegna->count() > 0)
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-camera me-2 text-primary"></i>Foto Consegna ({{ $fotoConsegna->count() }})</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            @foreach($fotoConsegna as $foto)
                                <div class="col-4 col-md-3">
                                    <a href="/{{ $foto->percorso_file }}" target="_blank">
                                        <img src="/{{ $foto->percorso_file }}" class="img-fluid rounded"
                                             style="width:100%; height:120px; object-fit:cover; border:2px solid #eee; cursor:pointer;">
                                    </a>
                                    <div class="text-center mt-1">
                            <span class="badge bg-{{ $foto->tipo == 'danno' ? 'danger' : ($foto->tipo == 'merce' ? 'primary' : 'secondary') }}" style="font-size:0.7rem;">
                                {{ ucfirst($foto->tipo) }}
                            </span>
                                        <div style="font-size:0.65rem;" class="text-muted">{{ date('d/m H:i', strtotime($foto->created_at)) }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

        </div>

        {{-- COLONNA DESTRA - DDT --}}
        <div class="col-lg-6 mb-4">

            @if($ddt)
                {{-- Card DDT --}}
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>DDT Associato</h5>
                        <div>
                            <a href="/azienda/ddt/{{ $ordine->id }}/pdf" target="_blank" class="btn btn-light btn-sm">
                                <i class="fas fa-file-pdf me-1"></i> Scarica PDF
                            </a>
                            <button onclick="window.print()" class="btn btn-outline-light btn-sm ms-1">
                                <i class="fas fa-print me-1"></i> Stampa
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">

                        {{-- Anteprima DDT --}}
                        <div class="ddt-preview p-4" style="background: #f8f9fa; min-height: 600px;">

                            {{-- Header DDT --}}
                            <div class="text-center border-bottom border-dark pb-2 mb-3">
                                <h4 class="mb-0 fw-bold">DOCUMENTO DI TRASPORTO</h4>
                                <small class="text-muted">(D.D.T. - Art. 1 D.P.R. 472 del 14/08/96)</small>
                            </div>

                            <div class="row mb-3">
                                {{-- Mittente --}}
                                <div class="col-8">
                                    <div class="border border-dark p-2 h-100">
                                        <div class="bg-light border-bottom border-dark p-1 mb-2 fw-bold" style="margin: -8px -8px 8px -8px;">
                                            MITTENTE
                                        </div>
                                        <strong>{{ $ddt->mittente_nome ?: ($azienda->ragione_sociale ?? $azienda->nome ?? '') }}</strong><br>
                                        {{ $ddt->mittente_indirizzo ?: ($azienda->indirizzo ?? '') }}
                                        @if(isset($azienda->partita_iva) && $azienda->partita_iva)
                                            <br>P.IVA: {{ $azienda->partita_iva }}
                                        @endif
                                    </div>
                                </div>
                                {{-- Numero DDT --}}
                                <div class="col-4">
                                    <div class="border border-dark p-2 h-100 text-center">
                                        <div class="bg-light border-bottom border-dark p-1 mb-2 fw-bold" style="margin: -8px -8px 8px -8px;">
                                            DOCUMENTO N.
                                        </div>
                                        <h3 class="mb-0 text-primary fw-bold">{{ $ddt->numero_documento }}</h3>
                                        <small>del {{ date('d/m/Y', strtotime($ddt->data_documento)) }}</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                {{-- Destinatario --}}
                                <div class="col-6">
                                    <div class="border border-dark p-2 h-100">
                                        <div class="bg-light border-bottom border-dark p-1 mb-2 fw-bold" style="margin: -8px -8px 8px -8px;">
                                            DESTINATARIO
                                        </div>
                                        <strong>{{ $ddt->destinatario_nome }}</strong><br>
                                        {{ $ddt->destinatario_indirizzo }}
                                        @if(isset($ordine->cliente_piva) && $ordine->cliente_piva)
                                            <br>P.IVA: {{ $ordine->cliente_piva }}
                                        @endif
                                    </div>
                                </div>
                                {{-- Luogo Destinazione --}}
                                <div class="col-6">
                                    <div class="border border-dark p-2 h-100">
                                        <div class="bg-light border-bottom border-dark p-1 mb-2 fw-bold" style="margin: -8px -8px 8px -8px;">
                                            LUOGO DESTINAZIONE
                                        </div>
                                        {{ $ddt->destinatario_indirizzo ?: $ordine->indirizzo_consegna }}
                                    </div>
                                </div>
                            </div>

                            {{-- Dati Trasporto --}}
                            <div class="border border-dark p-2 mb-3">
                                <div class="bg-light border-bottom border-dark p-1 mb-2 fw-bold" style="margin: -8px -8px 8px -8px;">
                                    DATI TRASPORTO
                                </div>
                                <div class="row small">
                                    <div class="col-3"><strong>Causale:</strong> Vendita</div>
                                    <div class="col-3"><strong>Mezzo:</strong> {{ $ordine->mezzo_marca ?? '' }} {{ $ordine->mezzo_modello ?? '' }}</div>
                                    <div class="col-3"><strong>Targa:</strong> {{ $ordine->targa ?? 'N/D' }}</div>
                                    <div class="col-3"><strong>Autista:</strong> {{ $ordine->autista_nome ?? '' }} {{ $ordine->autista_cognome ?? '' }}</div>
                                </div>
                            </div>

                            {{-- Tabella Merce --}}
                            <div class="border border-dark mb-3">
                                <div class="bg-light border-bottom border-dark p-1 fw-bold">
                                    DESCRIZIONE MERCE
                                </div>
                                <table class="table table-bordered mb-0" style="font-size: 12px;">
                                    <thead class="table-light">
                                    <tr>
                                        <th style="width: 50%;">Descrizione</th>
                                        <th class="text-center" style="width: 15%;">Colli</th>
                                        <th class="text-center" style="width: 17%;">Peso (Kg)</th>
                                        <th class="text-end" style="width: 18%;">Valore (€)</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr style="min-height: 80px;">
                                        <td>{{ $ddt->descrizione_merce }}</td>
                                        <td class="text-center">{{ $ddt->numero_colli ?? '-' }}</td>
                                        <td class="text-center">{{ $ddt->peso_lordo ? number_format($ddt->peso_lordo, 2, ',', '.') : '-' }}</td>
                                        <td class="text-end">{{ $ddt->valore_merce ? '€ ' . number_format($ddt->valore_merce, 2, ',', '.') : '-' }}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>

                            @if($ddt->note)
                                {{-- Note --}}
                                <div class="border border-dark p-2 mb-3">
                                    <div class="bg-light border-bottom border-dark p-1 mb-2 fw-bold" style="margin: -8px -8px 8px -8px;">
                                        NOTE
                                    </div>
                                    {{ $ddt->note }}
                                </div>
                            @endif

                            {{-- Firme (solo Vettore e Destinatario) --}}
                            <div class="row mt-4">
                                <div class="col-6 text-center">
                                    @if(isset($ddt->firma_vettore) && $ddt->firma_vettore)
                                        <img src="{{ $ddt->firma_vettore }}" alt="Firma Vettore" class="img-fluid" style="max-height: 60px; border-bottom: 1px solid #333;">
                                        <div class="small text-muted">{{ isset($ddt->data_firma_vettore) && $ddt->data_firma_vettore ? date('d/m/Y H:i', strtotime($ddt->data_firma_vettore)) : '' }}</div>
                                    @else
                                        <div class="border-top border-dark pt-2 mt-5"></div>
                                    @endif
                                    <div class="small fw-bold">Firma Vettore</div>
                                </div>
                                <div class="col-6 text-center">
                                    @if(isset($ddt->firma_destinatario) && $ddt->firma_destinatario)
                                        <img src="{{ $ddt->firma_destinatario }}" alt="Firma Destinatario" class="img-fluid" style="max-height: 60px; border-bottom: 1px solid #333;">
                                        <div class="small text-muted">{{ isset($ddt->data_firma_destinatario) && $ddt->data_firma_destinatario ? date('d/m/Y H:i', strtotime($ddt->data_firma_destinatario)) : '' }}</div>
                                    @else
                                        <div class="border-top border-dark pt-2 mt-5"></div>
                                    @endif
                                    <div class="small fw-bold">Firma Destinatario</div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @else
                {{-- Nessun DDT --}}
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>DDT Non Trovato</h5>
                    </div>
                    <div class="card-body text-center py-5">
                        <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                        <p class="text-muted">Nessun DDT associato a questo ordine.</p>
                        <button class="btn btn-primary" onclick="creaDDT({{ $ordine->id }})">
                            <i class="fas fa-plus me-1"></i> Genera DDT
                        </button>
                    </div>
                </div>
            @endif

        </div>
    </div>

</div>

<style>
    @media print {
        .btn, .card-header, nav, .sidebar, header, footer {
            display: none !important;
        }
        .ddt-preview {
            background: white !important;
            border: none !important;
        }
        .col-lg-6:first-child {
            display: none !important;
        }
        .col-lg-6 {
            width: 100% !important;
            max-width: 100% !important;
        }
    }
</style>

<script>
    function creaDDT(idOrdine) {
        if(confirm('Vuoi generare il DDT per questo ordine?')) {
            // Chiamata AJAX per creare DDT
            fetch('/azienda/genera-ddt/' + idOrdine, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        location.reload();
                    } else {
                        alert('Errore: ' + data.message);
                    }
                });
        }
    }
</script>
@include('azienda.common.footer')