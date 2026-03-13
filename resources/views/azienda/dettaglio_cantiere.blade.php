@include('azienda.common.header')


@section('content')

    <div class="container-fluid" style="margin-top: 94px">
        <!-- Immagine di sfondo -->
        <div class="profile-foreground position-relative mx-n4 mt-n4" >
            <div class="profile-wid-bg" >
                <img src="{{ URL::asset('/logo.jpg') }}" alt="Background Cantiere" class="profile-wid-img ">
            </div>
        </div>

        <div class="pt-4 mb-4 mb-lg-3 pb-lg-4 profile-wrapper">
            <div class="row g-4">
                <div class="col-auto">
                    <div class="avatar-lg" >
                        <img src="{{ $cantiere->immagine ? asset('storage/' . $cantiere->immagine) : URL::asset('/logo.jpg') }}"
                             alt="Immagine Cantiere"  class="img-thumbnail rounded-circle" style="height: 120px; width: 120px"/>
                    </div>
                </div>
                <div class="col">
                    <div class="p-2">
                        <h3 class="text-white mb-1">{{ $cantiere->titolo }}</h3>
                        <p class="text-white-75">Cantiere ID: {{ $cantiere->id }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Area con Tabs -->
        <div class="row">
            <div class="col-lg-12">
                <div class="profile-tab-background">
                    <div class="d-flex profile-wrapper">
                        <ul class="nav nav-pills animation-nav profile-nav gap-2 gap-lg-3 flex-grow-1" role="tablist" id="cantiereNavTabs">
                            <li class="nav-item">
                                <a class="nav-link fs-14 active" data-bs-toggle="tab" href="#descrizione-tab" role="tab" id="descrizione-tab-link">
                                    <i class="ri-file-text-line d-inline-block d-md-none"></i> <span class="d-none d-md-inline-block">Descrizione</span>
                                </a>
                            </li>
                            @if($utente->visualizza_costi == 1)

                            <li class="nav-item">
                                <a class="nav-link fs-14" data-bs-toggle="tab" href="#finanziario" role="tab" id="finanziario-tab-link">
                                    <i class="ri-money-dollar-circle-line d-inline-block d-md-none"></i> <span class="d-none d-md-inline-block">Costi</span>
                                </a>
                            </li>
                            @endif
                            <li class="nav-item">
                                <a class="nav-link fs-14" data-bs-toggle="tab" href="#dipendenti" role="tab" id="dipendenti-tab-link">
                                    <i class="ri-group-line d-inline-block d-md-none"></i> <span class="d-none d-md-inline-block">Dipendenti</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fs-14" data-bs-toggle="tab" href="#attività" role="tab" id="attività-tab-link">
                                    <i class="ri-folder-line d-inline-block d-md-none"></i> <span class="d-none d-md-inline-block">Attività</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fs-14" data-bs-toggle="tab" href="#magazzino" role="tab" id="magazzino-tab-link">
                                    <i class="ri-archive-line d-inline-block d-md-none"></i>
                                    <span class="d-none d-md-inline-block">Magazzino</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fs-14" data-bs-toggle="tab" href="#presenze" role="tab" id="presenze-tab-link">
                                    <i class="ri-time-line d-inline-block d-md-none"></i>
                                    <span class="d-none d-md-inline-block">Presenze</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fs-14" data-bs-toggle="tab" href="#allegati" role="tab" id="allegati-tab-link">
                                    <i class="ri-attachment-line d-inline-block d-md-none"></i>
                                    <span class="d-none d-md-inline-block">Allegati</span>
                                </a>
                            </li>
                        </ul>
                        <div class="flex-shrink-0">
                            <a href="{{ url('/azienda/cantieri') }}" class="btn btn-success"><i class="ri-arrow-go-back-line align-bottom"></i> Torna ai Cantieri</a>
                        </div>
                    </div>

                    <div class="tab-content pt-4 text-muted" style="margin-top: 50px" id="cantiereTabsContent">
                        <!-- Tab Descrizione -->
                        <div class="tab-pane active" id="descrizione-tab" role="tabpanel">
                            <h5>Dettagli del Cantiere</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Descrizione</th>
                                    <td>{{ $cantiere->descrizione }}</td>
                                </tr>
                                <tr>
                                    <th>Data Inizio</th>
                                    <td>{{ date('d/m/Y', strtotime($cantiere->data_inizio)) }}</td>
                                </tr>
                                <tr>
                                    <th>Data Fine</th>
                                    <td>{{ date('d/m/Y', strtotime($cantiere->data_fine)) }}</td>
                                </tr>
                                <tr>
                                    <th>Costo Stimato</th>
                                    <td>{{ number_format($cantiere->costo_stimato, 2, '.', '') }} €</td>
                                </tr>
                                <tr>
                                    <th>Valore Stimato</th>
                                    <td>{{ number_format($cantiere->valore_stimato, 2, '.', '') }} €</td>
                                </tr>
                                <tr>
                                    <th>Costo Totale</th>
                                    <td>{{ number_format($cantiere->costo_totale, 2, '.', '') }} €</td>
                                </tr>
                                <tr>
                                    <th>Valore Totale</th>
                                    <td>{{ number_format($cantiere->valore_totale, 2, '.', '') }} €</td>
                                </tr>
                                <tr>
                                    <th>Stato Cantiere</th>
                                    <td>
                                        <select id="statoCantiere" class="form-control" onchange="cambiaStato(this.value)">
                                            <option value="1" {{ $cantiere->stato == 1 ? 'selected' : '' }}>Attivo</option>
                                            <option value="2" {{ $cantiere->stato == 2 ? 'selected' : '' }}>Sospeso</option>
                                            <option value="0" {{ $cantiere->stato == 0 ? 'selected' : '' }}>Chiuso</option>
                                        </select>
                                    </td>
                                </tr>

                                <script>
                                    function cambiaStato(nuovoStato) {
                                        console.log("🔥 Cambio stato chiamato:", nuovoStato);

                                        let cantiereId = {{ $cantiere->id }};
                                        let urlUpdate = "{{ route('cantiere.updateStato') }}";

                                        if (typeof $ === 'undefined') {
                                            console.error("❌ jQuery not available");
                                            alert("Errore: jQuery non caricato");
                                            return;
                                        }

                                        $.ajax({
                                            url: urlUpdate,
                                            type: "POST",
                                            data: {
                                                _token: "{{ csrf_token() }}",
                                                id: cantiereId,
                                                stato: nuovoStato
                                            },
                                            success: function(response) {
                                                console.log("✅ Success:", response);
                                                alert("Stato del cantiere aggiornato con successo!");
                                            },
                                            error: function(xhr) {
                                                console.error("❌ Error:", xhr);
                                                alert("Errore nella richiesta: " + xhr.status + " " + xhr.statusText);
                                            }
                                        });
                                    }
                                </script>
                            </table>
                        </div>
                        <!-- TAB FINANZIARIO - CON GESTIONE PAGAMENTI -->
                        <div class="tab-pane fade" id="finanziario" role="tabpanel">
                            <!-- Sezione Riepilogo Generale -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-info text-white">
                                            <h5 class="mb-0 text-white">Dati Stimati</h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm">
                                                <tr>
                                                    <th>Costo Stimato Base:</th>
                                                    <td class="text-end"><strong>{{ number_format($cantiere->costo_stimato, 2, ',', '.') }} €</strong></td>
                                                </tr>
                                                <tr>
                                                    <th>Valore Stimato:</th>
                                                    <td class="text-end"><strong>{{ number_format($cantiere->valore_stimato, 2, ',', '.') }} €</strong></td>
                                                </tr>
                                                <tr class="table-success">
                                                    <th>Margine Stimato:</th>
                                                    <td class="text-end"><strong>{{ number_format($cantiere->valore_stimato - $cantiere->costo_stimato, 2, ',', '.') }} €</strong></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-warning text-dark">
                                            <h5 class="mb-0 text-white">Dati Reali</h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm">
                                                <tr>
                                                    <th>Costo Totale Reale:</th>
                                                    <td class="text-end"><strong>{{ number_format($cantiere->costo_totale, 2, ',', '.') }} €</strong></td>
                                                </tr>
                                                <tr>
                                                    <th>Valore Totale:</th>
                                                    <td class="text-end"><strong>{{ number_format($cantiere->valore_totale, 2, ',', '.') }} €</strong></td>
                                                </tr>
                                                <tr class="table-warning">
                                                    <th>Margine Reale:</th>
                                                    <td class="text-end"><strong>{{ number_format($cantiere->valore_totale - $cantiere->costo_totale, 2, ',', '.') }} €</strong></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ✅ NUOVA SEZIONE: GESTIONE PAGAMENTI (solo per cantieri contabilizzati) -->

                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0 text-white"><i class="ri-money-euro-circle-line"></i> Gestione Pagamenti Cliente</h5>
                                            @if($utente->solo_lettura != 1)
                                            <button class="btn btn-light btn-sm" onclick="aggiungiPagamento()">
                                                <i class="ri-add-line"></i> Aggiungi Pagamento
                                            </button>
                                            @endif
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <!-- Pagamenti Ricevuti -->
                                                <div class="col-md-6">
                                                    <h6 class="text-success"><i class="ri-check-line"></i> Pagamenti Ricevuti</h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-striped">
                                                            <thead>
                                                            <tr>
                                                                <th>Data</th>
                                                                <th>Importo</th>
                                                                <th>Descrizione</th>
                                                                <th>Azioni</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            @php
                                                                $pagamentiRicevuti = DB::table('cantieri_pagamenti')
                                                                    ->where('id_cantiere', $cantiere->id)
                                                                    ->where('tipo', 'ricevuto')
                                                                    ->orderBy('data_pagamento', 'desc')
                                                                    ->get();
                                                                $totaleRicevuto = $pagamentiRicevuti->sum('importo');
                                                            @endphp

                                                            @foreach($pagamentiRicevuti as $pagamento)
                                                                <tr>
                                                                    <td>{{ date('d/m/Y', strtotime($pagamento->data_pagamento)) }}</td>
                                                                    <td><strong>{{ number_format($pagamento->importo, 2, ',', '.') }} €</strong></td>
                                                                    <td>{{ $pagamento->descrizione }}</td>
                                                                    <td>
                                                                        <button class="btn btn-sm btn-warning" onclick="modificaPagamento({{ $pagamento->id }})">
                                                                            <i class="ri-edit-line"></i>
                                                                        </button>
                                                                        <button class="btn btn-sm btn-danger" onclick="eliminaPagamento({{ $pagamento->id }})">
                                                                            <i class="ri-delete-bin-line"></i>
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            @endforeach

                                                            @if($pagamentiRicevuti->isEmpty())
                                                                <tr>
                                                                    <td colspan="4" class="text-center text-muted">Nessun pagamento ricevuto</td>
                                                                </tr>
                                                            @endif
                                                            </tbody>
                                                            <tfoot class="table-success">
                                                            <tr>
                                                                <th>Totale Ricevuto:</th>
                                                                <th>{{ number_format($totaleRicevuto, 2, ',', '.') }} €</th>
                                                                <th colspan="2"></th>
                                                            </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>
                                                </div>

                                                <!-- Pagamenti Da Ricevere -->
                                                <div class="col-md-6">
                                                    <h6 class="text-warning"><i class="ri-time-line"></i> Da Ricevere</h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-striped">
                                                            <thead>
                                                            <tr>
                                                                <th>Scadenza</th>
                                                                <th>Importo</th>
                                                                <th>Descrizione</th>
                                                                <th>Azioni</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            @php
                                                                $pagamentiDaRicevere = DB::table('cantieri_pagamenti')
                                                                    ->where('id_cantiere', $cantiere->id)
                                                                    ->where('tipo', 'da_ricevere')
                                                                    ->orderBy('data_scadenza', 'asc')
                                                                    ->get();
                                                                $totaleDaRicevere = $pagamentiDaRicevere->sum('importo');
                                                            @endphp

                                                            @foreach($pagamentiDaRicevere as $pagamento)
                                                                <tr class="{{ strtotime($pagamento->data_scadenza) < time() ? 'table-danger' : '' }}">
                                                                    <td>
                                                                        {{ date('d/m/Y', strtotime($pagamento->data_scadenza)) }}
                                                                        @if(strtotime($pagamento->data_scadenza) < time())
                                                                            <span class="badge bg-danger">Scaduto</span>
                                                                        @endif
                                                                    </td>
                                                                    <td><strong>{{ number_format($pagamento->importo, 2, ',', '.') }} €</strong></td>
                                                                    <td>{{ $pagamento->descrizione }}</td>
                                                                    <td>
                                                                        <button class="btn btn-sm btn-success" onclick="segnaComePagato({{ $pagamento->id }})">
                                                                            <i class="ri-check-line"></i> Pagato
                                                                        </button>
                                                                        <button class="btn btn-sm btn-warning" onclick="modificaPagamento({{ $pagamento->id }})">
                                                                            <i class="ri-edit-line"></i>
                                                                        </button>
                                                                        <button class="btn btn-sm btn-danger" onclick="eliminaPagamento({{ $pagamento->id }})">
                                                                            <i class="ri-delete-bin-line"></i>
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            @endforeach

                                                            @if($pagamentiDaRicevere->isEmpty())
                                                                <tr>
                                                                    <td colspan="4" class="text-center text-muted">Nessun pagamento in sospeso</td>
                                                                </tr>
                                                            @endif
                                                            </tbody>
                                                            <tfoot class="table-warning">
                                                            <tr>
                                                                <th>Totale Da Ricevere:</th>
                                                                <th>{{ number_format($totaleDaRicevere, 2, ',', '.') }} €</th>
                                                                <th colspan="2"></th>
                                                            </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Riepilogo Pagamenti -->
                                            <div class="row mt-3">
                                                <div class="col-12">
                                                    <div class="alert alert-info">
                                                        <div class="row text-center">
                                                            <div class="col-md-3">
                                                                <h6>Valore Stimato</h6>
                                                                <span class="badge bg-info fs-6">{{ number_format($cantiere->valore_stimato, 2, ',', '.') }} €</span>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <h6>Già Ricevuto</h6>
                                                                <span class="badge bg-success fs-6">{{ number_format($totaleRicevuto, 2, ',', '.') }} €</span>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <h6>Da Ricevere</h6>
                                                                <span class="badge bg-warning fs-6">{{ number_format($totaleDaRicevere, 2, ',', '.') }} €</span>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <h6>Rimanente</h6>
                                                                @php $rimanente = $cantiere->valore_stimato - $totaleRicevuto - $totaleDaRicevere; @endphp
                                                                <span class="badge {{ $rimanente > 0 ? 'bg-danger' : 'bg-success' }} fs-6">
                                            {{ number_format($rimanente, 2, ',', '.') }} €
                                        </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <!-- ✅ NUOVA SEZIONE: BREAKDOWN DETTAGLIATO DEI COSTI -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="mb-0 text-white">Dettaglio Costi Cantiere</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <!-- Costi Dipendenti -->
                                                <div class="col-md-6">
                                                    <h6><i class="ri-group-line"></i> Costi Dipendenti (Basato su Giorni Lavorati)</h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-striped">
                                                            <thead>
                                                            <tr>
                                                                <th>Dipendente</th>
                                                                <th>Giorni Lavorati</th>
                                                                <th>Ore Totali</th>
                                                                <th>Costo/Giorno</th>
                                                                <th>Totale</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            @if(isset($dettaglioCosti) && count($dettaglioCosti['dipendenti']) > 0)
                                                                @foreach($dettaglioCosti['dipendenti'] as $dip)
                                                                    <tr>
                                                                        <td>{{ $dip->nome }} {{ $dip->cognome }}</td>
                                                                        <td>
                                                                            <span class="badge bg-primary">{{ $dip->giorni_presenza }}</span>
                                                                        </td>
                                                                        <td>
                                                                            <small class="text-muted">{{ number_format($dip->ore_totali, 1) }}h</small>
                                                                        </td>
                                                                        <td>
                                                                            {{ number_format($dip->costo_giornaliero, 2, ',', '.') }} €/giorno
                                                                        </td>
                                                                        <td>
                                                                            <strong>{{ number_format($dip->costo_totale_dipendente, 2, ',', '.') }} €</strong>
                                                                            <br><small class="text-muted">
                                                                                ({{ $dip->giorni_presenza }} × {{ number_format($dip->costo_giornaliero, 0, ',', '.') }}€)
                                                                            </small>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @else
                                                                <tr>
                                                                    <td colspan="5" class="text-center text-muted">
                                                                        <i class="ri-calendar-line"></i> Nessuna presenza registrata
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                            </tbody>
                                                            <tfoot class="table-info">
                                                            <tr>
                                                                <th colspan="4">Totale Costi Dipendenti (Giorni Effettivi):</th>
                                                                <th>{{ number_format($dettaglioCosti['totale_dipendenti'] ?? 0, 2, ',', '.') }} €</th>
                                                            </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>

                                                    <!-- ✅ INDICATORE LIVE AGGIORNATO -->
                                                    <div class="alert alert-success mt-2">
                                                        <small>
                                                            <i class="ri-refresh-line"></i>
                                                            <strong>Calcolo automatico:</strong> Costo = Giorni lavorati × Costo giornaliero dipendente
                                                        </small>
                                                    </div>

                                                    <!-- ✅ CONFRONTO STIMATO VS EFFETTIVO -->
                                                    @if(isset($dettaglioCosti['dipendenti']) && count($dettaglioCosti['dipendenti']) > 0)
                                                        <div class="alert alert-info mt-2">
                                                            <h6><i class="ri-bar-chart-line"></i> Confronto Costi</h6>
                                                            @php
                                                                // Calcola giorni totali cantiere per confronto
                                                                $dataInizio = new DateTime($cantiere->data_inizio);
                                                                $dataFine = new DateTime($cantiere->data_fine);
                                                                $giorniTotaliCantiere = $dataInizio->diff($dataFine)->days + 1;

                                                                // Calcola giorni effettivi lavorati
                                                                $giorniEffettiviTotali = array_sum(array_column($dettaglioCosti['dipendenti'], 'giorni_presenza'));

                                                                // Calcola numero dipendenti
                                                                $numeroDipendenti = count($dettaglioCosti['dipendenti']);
                                                            @endphp
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <strong>Giorni Cantiere:</strong> {{ $giorniTotaliCantiere }}
                                                                </div>
                                                                <div class="col-6">
                                                                    <strong>Giorni Lavorati:</strong> {{ $giorniEffettiviTotali }}
                                                                </div>
                                                            </div>
                                                            <div class="row mt-1">
                                                                <div class="col-6">
                                                                    <strong>Dipendenti:</strong> {{ $numeroDipendenti }}
                                                                </div>
                                                                <div class="col-6">
                                                                    <strong>Media gg/dipendente:</strong>
                                                                    {{ $numeroDipendenti > 0 ? number_format($giorniEffettiviTotali / $numeroDipendenti, 1) : 0 }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>

                                                <!-- Costi Materiali/Magazzino -->
                                                <div class="col-md-6">
                                                    <h6><i class="ri-archive-line"></i> Costi Materiali Scaricati</h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-striped">
                                                            <thead>
                                                            <tr>
                                                                <th>Articolo</th>
                                                                <th>Quantità</th>
                                                                <th>Costo Unit.</th>
                                                                <th>Totale</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            @php
                                                                // Query per recuperare i movimenti di scarico per questo cantiere
                                                                $movimentiScarico = DB::select("
                                                                    SELECT
                                                                        m.*,
                                                                        a.titolo as articolo_nome,
                                                                        a.costo as costo_unitario,
                                                                        (m.qta * a.costo) as costo_totale
                                                                    FROM mgmov m
                                                                    JOIN articoli a ON m.id_articolo = a.id
                                                                    WHERE m.causale = 'Scarico'
                                                                    AND m.id_cantiere = ?
                                                                    ORDER BY m.datamov DESC
                                                                ", [$cantiere->id]);

                                                                $totaleCostiMateriali = 0;
                                                            @endphp

                                                            @foreach($movimentiScarico as $movimento)
                                                                @php
                                                                    $totaleCostiMateriali += $movimento->costo_totale;
                                                                @endphp
                                                                <tr>
                                                                    <td>{{ $movimento->articolo_nome }}</td>
                                                                    <td>{{ $movimento->qta }}</td>
                                                                    <td>{{ number_format($movimento->costo_unitario, 2, ',', '.') }} €</td>
                                                                    <td><strong>{{ number_format($movimento->costo_totale, 2, ',', '.') }} €</strong></td>
                                                                </tr>
                                                            @endforeach

                                                            @if(empty($movimentiScarico))
                                                                <tr>
                                                                    <td colspan="4" class="text-center text-muted">Nessun materiale scaricato</td>
                                                                </tr>
                                                            @endif
                                                            </tbody>
                                                            <tfoot class="table-warning">
                                                            <tr>
                                                                <th colspan="3">Totale Costi Materiali:</th>
                                                                <th>{{ number_format($totaleCostiMateriali, 2, ',', '.') }} €</th>
                                                            </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Totale Riepilogativo -->
                                            <div class="row mt-3">
                                                <div class="col-12">
                                                    <div class="alert alert-info">
                                                        <div class="row text-center">
                                                            <div class="col-md-3">
                                                                <h6>Costo Base Stimato</h6>
                                                                <span class="badge bg-info fs-6">{{ number_format($cantiere->costo_stimato, 2, ',', '.') }} €</span>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <h6>Costi Dipendenti (Effettivi)</h6>
                                                                <span class="badge bg-success fs-6">{{ number_format($dettaglioCosti['totale_dipendenti'] ?? 0, 2, ',', '.') }} €</span>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <h6>Costi Materiali</h6>
                                                                <span class="badge bg-warning fs-6">{{ number_format($dettaglioCosti['totale_materiali'] ?? 0, 2, ',', '.') }} €</span>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <h6>Totale Reale (Live)</h6>
                                                                <span class="badge bg-danger fs-6">{{ number_format($cantiere->costo_totale, 2, ',', '.') }} €</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <script>
                                // Aggiorna i costi quando viene salvata una presenza
                                $('#formPresenza').on('submit', function() {
                                    // Mostra indicatore di caricamento
                                    $('#btnSalvaPresenza').html('<i class="ri-loader-4-line ri-spin"></i> Salvando...');

                                    // Dopo il submit, la pagina si ricaricherà automaticamente con i nuovi costi
                                });

                                // Evidenzia i costi aggiornati
                                $(document).ready(function() {
                                    @if(session('success') && str_contains(session('success'), 'Presenza'))
                                    // Evidenzia la sezione costi per 3 secondi
                                    $('.alert-info').addClass('border border-success').fadeIn();
                                    setTimeout(function() {
                                        $('.alert-info').removeClass('border border-success');
                                    }, 3000);
                                    @endif
                                });
                            </script>
                            <!-- Sezione Confronto (esistente) -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-secondary text-white">
                                            <h5 class="mb-0 text-white">Confronto Stimato vs Reale</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="text-center p-3 border rounded">
                                                        <h6>Avanzamento Costi</h6>
                                                        @php
                                                            $percentuale_costi = $cantiere->costo_stimato > 0 ? ($cantiere->costo_totale / $cantiere->costo_stimato) * 100 : 0;
                                                        @endphp
                                                        <div class="progress mb-2">
                                                            <div class="progress-bar {{ $percentuale_costi > 100 ? 'bg-danger' : 'bg-info' }}"
                                                                 style="width: {{ min($percentuale_costi, 100) }}%"></div>
                                                        </div>
                                                        <span class="badge {{ $percentuale_costi > 100 ? 'bg-danger' : 'bg-info' }}">
                                    {{ number_format($percentuale_costi, 1) }}%
                                </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-center p-3 border rounded">
                                                        <h6>Stato Costi</h6>
                                                        @if($percentuale_costi <= 80)
                                                            <span class="badge bg-success fs-6">Sotto Budget</span>
                                                        @elseif($percentuale_costi <= 100)
                                                            <span class="badge bg-warning fs-6">In Budget</span>
                                                        @else
                                                            <span class="badge bg-danger fs-6">Fuori Budget</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-center p-3 border rounded">
                                                        <h6>Differenza</h6>
                                                        <span class="badge {{ $cantiere->costo_totale > $cantiere->costo_stimato ? 'bg-danger' : 'bg-success' }} fs-6">
                                    {{ number_format($cantiere->costo_totale - $cantiere->costo_stimato, 2, ',', '.') }} €
                                </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ✅ MODAL PER GESTIONE PAGAMENTI -->
                        <div class="modal fade" id="modalPagamento" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="titoloModalPagamento">Aggiungi Pagamento</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST" action="{{ route('cantieri.gestisciPagamento') }}">
                                        @csrf
                                        <input type="hidden" name="id_cantiere" value="{{ $cantiere->id }}">
                                        <input type="hidden" name="id_pagamento" id="id_pagamento">
                                        <input type="hidden" name="azione" id="azione_pagamento" value="aggiungi">

                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Tipo Pagamento</label>
                                                <select name="tipo" id="tipo_pagamento" class="form-control" required>
                                                    <option value="ricevuto">Pagamento Ricevuto</option>
                                                    <option value="da_ricevere">Da Ricevere</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Importo (€)</label>
                                                <input type="number" step="0.01" name="importo" id="importo_pagamento" class="form-control" required>
                                            </div>

                                            <div class="mb-3" id="div_data_pagamento">
                                                <label class="form-label">Data Pagamento</label>
                                                <input type="date" name="data_pagamento" id="data_pagamento" class="form-control">
                                            </div>

                                            <div class="mb-3" id="div_data_scadenza" style="display:none;">
                                                <label class="form-label">Data Scadenza</label>
                                                <input type="date" name="data_scadenza" id="data_scadenza" class="form-control">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Descrizione</label>
                                                <input type="text" name="descrizione" id="descrizione_pagamento" class="form-control" placeholder="Es: Acconto, Saldo finale, Rata 1/3">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Note</label>
                                                <textarea name="note" id="note_pagamento" class="form-control" rows="3"></textarea>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Chiudi</button>
                                            <button type="submit" class="btn btn-primary" id="btnSalvaPagamento">Salva</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Sostituisci la sezione dipendenti nella tua vista con questo -->
                        <div class="tab-pane fade" id="dipendenti" role="tabpanel">
                            <div class="row">
                                <!-- Assegnazione Dipendenti per Giorni Specifici -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Assegna Dipendenti per Giorni Specifici</h5>
                                        </div>
                                        <div class="card-body">
                                            <button type="button" class="btn btn-success" onclick="apriModalAssegnazione()">
                                                <i class="ri-calendar-line"></i> Gestisci Assegnazioni
                                            </button>

                                            <div class="mt-3">
                                                <small class="text-muted">
                                                    Periodo cantiere: {{ date('d/m/Y', strtotime($cantiere->data_inizio)) }} - {{ date('d/m/Y', strtotime($cantiere->data_fine)) }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Lista Dipendenti Assegnati con Giorni -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Dipendenti Assegnati</h5>
                                        </div>
                                        <div class="card-body">
                                            @if(isset($dipendentiAssegnati) && count($dipendentiAssegnati) > 0)
                                                @foreach($dipendentiAssegnati as $dip)
                                                    <div class="card mb-2">
                                                        <div class="card-body p-3">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <h6 class="mb-1">{{ $dip->nome }} {{ $dip->cognome }}</h6>
                                                                    <small class="text-muted">{{ $dip->mansione }}</small>
                                                                    <div class="mt-1">
                                                                        <span class="badge bg-primary">{{ $dip->giorni_assegnati }} giorni</span>
                                                                    </div>
                                                                </div>
                                                                <div>
                                                                    <button class="btn btn-sm btn-outline-primary" onclick="modificaDipendente({{ $dip->id_dipendente }})">
                                                                        <i class="ri-edit-line"></i>
                                                                    </button>
                                                                    <button class="btn btn-sm btn-outline-danger" onclick="rimuoviDipendente({{ $dip->id_dipendente }})">
                                                                        <i class="ri-delete-bin-line"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <p class="text-muted">Nessuna assegnazione presente</p>

                                                <!-- DEBUG: Mostra quanti record ci sono -->
                                                <div class="alert alert-info">
                                                    <strong>DEBUG:</strong>
                                                    Totale record in cantieri_operai_giorni per questo cantiere:
                                                    {{ DB::table('cantieri_operai_giorni')->where('id_cantiere', $cantiere->id)->count() }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal per Gestione Assegnazioni -->
                        <div class="modal fade" id="modalAssegnazione" tabindex="-1">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Gestione Assegnazioni Dipendenti</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- Selezione Dipendente -->
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Seleziona Dipendente</label>
                                                <select id="selectDipendente" class="form-control">
                                                    <option value="">Scegli un dipendente...</option>
                                                    @foreach($dipendenti as $dipendente)
                                                        <option value="{{ $dipendente->id }}"
                                                                data-nome="{{ $dipendente->nome }}"
                                                                data-cognome="{{ $dipendente->cognome }}"
                                                                data-mansione="{{ $dipendente->ruoli_titolo ?? 'N/A' }}">
                                                            {{ $dipendente->nome }} {{ $dipendente->cognome }} ({{ $dipendente->ruoli_titolo ?? 'N/A' }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Azioni Rapide</label>
                                                <div class="btn-group w-100" role="group">
                                                    <button type="button" class="btn btn-outline-success" onclick="selezionaTuttiGiorni()">
                                                        <i class="ri-check-double-line"></i> Tutti i giorni
                                                    </button>
                                                    <button type="button" class="btn btn-outline-warning" onclick="deselezionaTuttiGiorni()">
                                                        <i class="ri-close-line"></i> Deseleziona tutto
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Calendario giorni -->
                                        <div>
                                            <h6>Seleziona i giorni di lavoro:</h6>
                                            <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                                <div id="calendarioGiorni" class="row">
                                                    <!-- Generato via JavaScript -->
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Legenda -->
                                        <div class="mt-3">
                                            <small class="text-muted">
                                                <span class="badge bg-success me-2">●</span> Giorni selezionati
                                                <span class="badge bg-light text-dark me-2">●</span> Giorni disponibili
                                                <span class="badge bg-danger me-2">●</span> Weekend
                                            </small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Chiudi</button>
                                        <button type="button" class="btn btn-primary" onclick="salvaAssegnazioni()">
                                            <i class="ri-save-line"></i> Salva Assegnazioni
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal per Gestione Dipendenti dell'Attività -->
                        <div class="modal fade" id="modalDipendentiAttivita">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Gestione Dipendenti per Attività</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="post" id="formDipendentiAttivita">
                                            @csrf
                                            <input type="hidden" name="attivita_id" id="attivita_id_dipendenti">

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6>Dipendenti Disponibili</h6>
                                                    <div class="list-group" id="dipendentiDisponibili">
                                                        <!-- Populated via JavaScript -->
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6>Dipendenti Assegnati</h6>
                                                    <div class="list-group" id="dipendentiAssegnati">
                                                        <!-- Populated via JavaScript -->
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Chiudi</button>
                                        <button type="button" class="btn btn-primary" onclick="salvaDipendentiAttivita()">Salva Modifiche</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Attività -->
                        <div class="tab-pane fade" id="attività" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5>Attività del Cantiere</h5>
                                @if($utente->solo_lettura != 1)
                                <button class="btn btn-primary" onclick="apriModalAggiunta()">Inserisci Attività</button>
                                @endif
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th>Descrizione</th>
                                        <th>Data Inizio</th>
                                        <th>Data Fine</th>
                                        <th>Dipendenti Assegnati</th>
                                        <th>Note</th>
                                        <th>Azioni</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($attivita as $att)
                                        <tr id="attivita_{{ $att->id }}">
                                            <td>{{ $att->descrizione }}</td>
                                            <td>{{ date('d/m/Y', strtotime($att->data_inizio)) }}</td>
                                            <td>{{ date('d/m/Y', strtotime($att->data_fine)) }}</td>
                                            <td>
                                                @if($utente->solo_lettura != 1)
                                                <button class="btn btn-info btn-sm" onclick="gestisciDipendentiAttivita({{ $att->id }})">
                                                    Gestisci Dipendenti
                                                    <span class="badge bg-light text-dark ms-1">
                                    {{ $att->numero_dipendenti }}

                                </span>
                                                </button>
                                                @endif
                                            </td>
                                            <td>{{ $att->note }}</td>
                                            <td>
                                                @if($utente->solo_lettura != 1)
                                                <button class="btn btn-warning btn-sm" onclick="apriModalModifica({{ $att->id }}, '{{ $att->descrizione }}', '{{ $att->data_inizio }}', '{{ $att->data_fine }}', '{{ $att->note }}')">
                                                    Modifica
                                                </button>
                                                <form method="post" style="display:inline-block;">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{ $att->id }}">
                                                    <button type="submit" class="btn btn-danger btn-sm" name="elimina_attivita" onclick="return confirm('Vuoi eliminare questa attività?')">
                                                        Elimina
                                                    </button>
                                                </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Modal per Inserire/Modificare Attività -->
                        <div class="modal fade" id="modalAttivita">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="titoloModalAttivita">Inserisci Attività</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="post">
                                        @csrf
                                        <input type="hidden" name="id" id="id_attivita">
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label>Descrizione</label>
                                                <input type="text" name="descrizione" id="descrizione_attivita" class="form-control" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Data Inizio <span class="text-muted">(Inizio Cantiere: {{ date('d/m/Y', strtotime($cantiere->data_inizio)) }})</span></label>
                                                <input type="date" name="data_inizio" id="data_inizio_attivita" class="form-control" required>
                                            </div>

                                            <div class="mb-3">
                                                <label>Data Fine <span class="text-muted">(Fine Cantiere: {{ date('d/m/Y', strtotime($cantiere->data_fine)) }})</span></label>
                                                <input type="date" name="data_fine" id="data_fine_attivita" class="form-control" required>
                                            </div>


                                            <div class="mb-3">
                                                <label>Note</label>
                                                <textarea name="note" id="note_attivita" class="form-control"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Chiudi</button>
                                            <button type="submit" class="btn btn-primary" id="btnSalvaAttivita" name="inserisci_attivita">Salva</button>
                                            <button type="submit" class="btn btn-warning" id="btnModificaAttivita" name="modifica_attivita" style="display:none;">Modifica</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>


                        <!-- TAB MAGAZZINO - MIGLIORATO -->
                        <div class="tab-pane fade" id="magazzino" role="tabpanel">
                            <div class="row">
                                <!-- Colonna sinistra - Materiali disponibili -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="mb-0 text-white">Materiali Disponibili</h5>
                                        </div>
                                        <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                                            <div class="mb-3">
                                                <input type="text" id="searchMateriali" class="form-control" placeholder="Cerca materiali...">
                                            </div>
                                            <div id="materialiList">
                                                @foreach($materiali as $articolo)
                                                    @if($articolo->quantita > 0)
                                                        <div class="material-item border rounded p-2 mb-2" data-name="{{ strtolower($articolo->titolo) }}">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <strong>{{ $articolo->titolo }}</strong><br>
                                                                    <small class="text-muted">{{ Str::limit($articolo->descrizione, 30) }}</small><br>
                                                                    <span class="badge bg-success">Disp: {{ $articolo->quantita - $articolo->quantita_impegnata }}</span>
                                                                    <span class="badge bg-info">Costo: {{ number_format($articolo->costo, 2, ',', '.') }}€</span>
                                                                </div>
                                                                <div>
                                                                    <div class="input-group input-group-sm mb-1" style="width: 80px;">
                                                                        <input type="number" id="quantita_{{ $articolo->id }}"
                                                                               class="form-control form-control-sm"
                                                                               min="1" max="{{ $articolo->quantita - $articolo->quantita_impegnata }}"
                                                                               value="1">
                                                                    </div>
                                                                    @if($utente->solo_lettura != 1)
                                                                    <button class="btn btn-primary btn-sm d-block w-100 mb-1"
                                                                            onclick="impegnaMateriale({{ $articolo->id }}, {{ $cantiere->id }})">
                                                                        Impegna
                                                                    </button>
                                                                    <button class="btn btn-danger btn-sm d-block w-100"
                                                                            onclick="scaricaDirectMaterial({{ $articolo->id }}, '{{ $articolo->titolo }}', {{ $cantiere->id }}, '{{ $cantiere->titolo }}')">
                                                                        Scarica
                                                                    </button>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Colonna destra - Materiali impegnati -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-warning text-dark">
                                            <h5 class="mb-0">Materiali Impegnati in questo Cantiere</h5>
                                        </div>
                                        <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                                            <div id="materialiImpegnati">
                                                @foreach($materiali as $articolo)
                                                    @php
                                                        $impegnato = DB::table('impegni_magazzino')
                                                            ->where('id_articolo', $articolo->id)
                                                            ->where('id_cantiere', $cantiere->id)
                                                            ->first();
                                                    @endphp
                                                    @if($impegnato && $impegnato->quantita_impegnata > 0)
                                                        <div class="impegno-item border rounded p-2 mb-2" id="impegno_{{ $articolo->id }}">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <strong>{{ $articolo->titolo }}</strong><br>
                                                                    <span class="badge bg-warning">Impegnato: {{ $impegnato->quantita_impegnata }}</span>
                                                                    <span class="badge bg-info">Costo: {{ number_format($articolo->costo * $impegnato->quantita_impegnata, 2, ',', '.') }}€</span>
                                                                </div>
                                                                <div>
                                                                    <div class="input-group input-group-sm mb-1" style="width: 80px;">
                                                                        <input type="number" id="qta_rimuovi_{{ $articolo->id }}"
                                                                               class="form-control form-control-sm"
                                                                               min="1" max="{{ $impegnato->quantita_impegnata }}"
                                                                               value="{{ $impegnato->quantita_impegnata }}">
                                                                    </div>
                                                                    <button class="btn btn-danger btn-sm d-block w-100 mb-1"
                                                                            onclick="rimuoviMaterialeImpegnato({{ $articolo->id }}, {{ $cantiere->id }})">
                                                                        Rimuovi
                                                                    </button>
                                                                    <button class="btn btn-success btn-sm d-block w-100"
                                                                            onclick="scaricaMaterialeImpegnato({{ $articolo->id }}, '{{ $articolo->titolo }}', {{ $cantiere->id }}, '{{ $cantiere->titolo }}')">
                                                                        Scarica
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Strumenti - rimangono sotto -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-secondary text-white">
                                            <h5 class="mb-0">Strumenti</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                @foreach ($strumenti as $articolo)
                                                    <div class="col-md-4 mb-3">
                                                        <div class="card">
                                                            <div class="card-body p-2">
                                                                <h6 class="card-title">{{ $articolo->titolo }}</h6>
                                                                <p class="card-text small">{{ Str::limit($articolo->descrizione, 50) }}</p>
                                                                <span class="badge bg-info">Disp: {{ $articolo->quantita }}</span>
                                                                <span class="badge bg-warning">Imp: {{ $articolo->quantita_impegnata_cantiere ?? 0 }}</span>
                                                                <div class="mt-2">
                                                                    @if($utente->solo_lettura != 1)
                                                                    <button class="btn btn-primary btn-sm" onclick="impegnaStrumento({{ $articolo->id }}, {{ $cantiere->id }})">
                                                                        Impegna
                                                                    </button>
                                                                    <button class="btn btn-danger btn-sm" onclick="rimuoviStrumento({{ $articolo->id }}, {{ $cantiere->id }})">
                                                                        Rimuovi
                                                                    </button>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Presenze Aggiornato -->
                        <div class="tab-pane fade" id="presenze" role="tabpanel">
                            <!-- Sezione Aggiungi Presenza Manuale -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0 text-white">
                                                <i class="ri-time-line"></i> Gestione Presenze
                                            </h5>
                                            @if($utente->solo_lettura != 1)
                                                <button class="btn btn-light btn-sm" onclick="apriModalPresenza()">
                                                    <i class="ri-add-line"></i> Registra Presenza
                                                </button>
                                            @endif
                                        </div>
                                        <div class="card-body">
                                            <!-- Filtri presenze -->
                                            <div class="row mb-3">
                                                <div class="col-md-3">
                                                    <label class="form-label">Filtra per Dipendente:</label>
                                                    <select id="filtroDipendente" class="form-control">
                                                        <option value="">Tutti i dipendenti</option>
                                                        @foreach($dipendentiAssegnati as $dip)
                                                            <option value="{{ $dip->nome }} {{ $dip->cognome }}">{{ $dip->nome }} {{ $dip->cognome }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Dal:</label>
                                                    <input type="date" id="dataInizio" class="form-control">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Al:</label>
                                                    <input type="date" id="dataFine" class="form-control">
                                                </div>
                                                <div class="col-md-3 d-flex align-items-end">
                                                    <button class="btn btn-primary" onclick="filtraPresenze()">
                                                        <i class="ri-filter-line"></i> Filtra
                                                    </button>
                                                    <button class="btn btn-secondary ms-2" onclick="resetFiltri()">
                                                        <i class="ri-refresh-line"></i> Reset
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Tabella presenze -->
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-hover" id="tabellaPresenze">
                                                    <thead class="table-dark">
                                                    <tr>
                                                        <th>Data</th>
                                                        <th>Dipendente</th>
                                                        <th>Ora Inizio</th>
                                                        <th>Ora Fine</th>
                                                        <th>Ore Totali</th>
                                                        <th>Distanza Check-in</th>
                                                        <th>Distanza Check-out</th>
                                                        <th>Note</th>
                                                        @if($utente->solo_lettura != 1)
                                                            <th>Azioni</th>
                                                        @endif
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach ($presenzePerData as $data => $presenzeInGiornata)
                                                        @foreach ($presenzeInGiornata as $p)
                                                            <tr data-dipendente="{{ $p->nome_dipendente }} {{ $p->cognome_dipendente }}"
                                                                data-data="{{ $p->data }}">
                                                                <td>
                                                                    <strong>{{ \Carbon\Carbon::parse($p->data)->format('d/m/Y') }}</strong>
                                                                    @if($p->tipo_registrazione == 'manuale')
                                                                        <span class="badge bg-info ms-1">Manuale</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    {{ $p->nome_dipendente }} {{ $p->cognome_dipendente }}
                                                                </td>
                                                                <td>
                                                                    <span class="badge bg-primary">{{ substr($p->ora_inizio, 0, 5) }}</span>
                                                                </td>
                                                                <td>
                                                                    @if($p->ora_fine)
                                                                        <span class="badge bg-success">{{ substr($p->ora_fine, 0, 5) }}</span>
                                                                    @else
                                                                        <span class="badge bg-warning">In corso</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($p->ora_fine)
                                                                        @php
                                                                            $inizio = new DateTime($p->ora_inizio);
                                                                            $fine = new DateTime($p->ora_fine);
                                                                            $diff = $fine->diff($inizio);
                                                                            $ore = $diff->h + ($diff->days * 24);
                                                                            $minuti = $diff->i;
                                                                        @endphp
                                                                        <strong>{{ $ore }}h {{ $minuti }}m</strong>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if (!is_null($p->distInizioKm))
                                                                        <span class="badge {{ $p->entroUnKmInizio ? 'bg-success' : 'bg-warning' }}">
                                                        {{ number_format($p->distInizioKm, 2) }} km
                                                    </span>
                                                                    @else
                                                                        <span class="text-muted">N/D</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if (!is_null($p->distFineKm))
                                                                        <span class="badge {{ $p->entroUnKmFine ? 'bg-success' : 'bg-warning' }}">
                                                        {{ number_format($p->distFineKm, 2) }} km
                                                    </span>
                                                                    @else
                                                                        <span class="text-muted">N/D</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($p->note)
                                                                        <i class="ri-sticky-note-line text-info"
                                                                           data-bs-toggle="tooltip"
                                                                           title="{{ $p->note }}"></i>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                                @if($utente->solo_lettura != 1)
                                                                    <td>
                                                                        <div class="btn-group">
                                                                            <button class="btn btn-sm btn-warning"
                                                                                    onclick="modificaPresenza({{ $p->id }}, '{{ $p->data }}', '{{ substr($p->ora_inizio, 0, 5) }}', '{{ $p->ora_fine ? substr($p->ora_fine, 0, 5) : '' }}', '{{ $p->note }}', {{ $p->id_dipendente }})">
                                                                                <i class="ri-edit-line"></i>
                                                                            </button>
                                                                            <button class="btn btn-sm btn-danger"
                                                                                    onclick="eliminaPresenza({{ $p->id }})">
                                                                                <i class="ri-delete-bin-line"></i>
                                                                            </button>
                                                                        </div>
                                                                    </td>
                                                                @endif
                                                            </tr>
                                                        @endforeach
                                                    @endforeach

                                                    @if($presenzePerData->isEmpty())
                                                        <tr>
                                                            <td colspan="{{ $utente->solo_lettura != 1 ? '9' : '8' }}" class="text-center text-muted">
                                                                Nessuna presenza registrata
                                                            </td>
                                                        </tr>
                                                    @endif
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Riepilogo ore -->
                                            <div class="row mt-4">
                                                <div class="col-12">
                                                    <div class="alert alert-info">
                                                        <h6><i class="ri-information-line"></i> Riepilogo Presenze</h6>
                                                        <div class="row">
                                                            @php
                                                                $totaleOre = 0;
                                                                $giorniLavorativi = 0;
                                                                foreach($presenze as $p) {
                                                                    if($p->ora_fine) {
                                                                        $inizio = new DateTime($p->ora_inizio);
                                                                        $fine = new DateTime($p->ora_fine);
                                                                        $diff = $fine->diff($inizio);
                                                                        $ore = $diff->h + ($diff->days * 24) + ($diff->i / 60);
                                                                        $totaleOre += $ore;
                                                                        $giorniLavorativi++;
                                                                    }
                                                                }
                                                                $mediaOreGiorno = $giorniLavorativi > 0 ? $totaleOre / $giorniLavorativi : 0;
                                                            @endphp
                                                            <div class="col-md-3">
                                                                <strong>Totale Ore:</strong> {{ number_format($totaleOre, 1) }}h
                                                            </div>
                                                            <div class="col-md-3">
                                                                <strong>Giorni Lavorati:</strong> {{ $giorniLavorativi }}
                                                            </div>
                                                            <div class="col-md-3">
                                                                <strong>Media Ore/Giorno:</strong> {{ number_format($mediaOreGiorno, 1) }}h
                                                            </div>
                                                            <div class="col-md-3">
                                                                <strong>Dipendenti Coinvolti:</strong> {{ $presenze->unique('id_dipendente')->count() }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal per Aggiungere/Modificare Presenza -->
                        <div class="modal fade" id="modalPresenza" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="titoloModalPresenza">Registra Presenza</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST" id="formPresenza">

                                        <input type="hidden" name="azione_presenza" id="azione_presenza" value="aggiungi_presenza_manuale">
                                        <input type="hidden" name="id_presenza" id="id_presenza">

                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Dipendente <span class="text-danger">*</span></label>
                                                        <select name="id_dipendente" id="id_dipendente" class="form-control" required>
                                                            <option value="">Seleziona dipendente...</option>
                                                            @foreach($dipendentiAssegnati as $dip)
                                                                <option value="{{ $dip->id_dipendente }}">{{ $dip->nome }} {{ $dip->cognome }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Data <span class="text-danger">*</span></label>
                                                        <input type="date" name="data_presenza" id="data_presenza" class="form-control" required>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Ora Inizio <span class="text-danger">*</span></label>
                                                        <input type="time" name="ora_inizio" id="ora_inizio" class="form-control" value="07:00" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Ora Fine <span class="text-danger">*</span></label>
                                                        <input type="time" name="ora_fine" id="ora_fine" class="form-control" value="17:00" required>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Note</label>
                                                <textarea name="note_presenza" id="note_presenza" class="form-control" rows="3"
                                                          placeholder="Eventuali note sulla presenza..."></textarea>
                                            </div>

                                            <!-- Info helper -->
                                            <div class="alert alert-info">
                                                <small>
                                                    <i class="ri-information-line"></i>
                                                    <strong>Orari predefiniti:</strong> 07:00 - 17:00 (modificabili).<br>
                                                    Assicurati che il dipendente sia assegnato al cantiere prima di registrare la presenza.
                                                </small>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                                <i class="ri-close-line"></i> Annulla
                                            </button>
                                            <button type="submit" class="btn btn-success" id="btnSalvaPresenza">
                                                <i class="ri-save-line"></i> Salva Presenza
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- Script JavaScript per gestione presenze -->
                        <script>
                            $(document).ready(function() {
                                // Imposta data odierna come default
                                $('#data_presenza').val(new Date().toISOString().split('T')[0]);

                                // Inizializza tooltips
                                $('[data-bs-toggle="tooltip"]').tooltip();
                            });

                            // Funzione per aprire modal nuova presenza
                            function apriModalPresenza() {
                                // Salva il tab corrente
                                const tabStorageKey = 'cantiere_{{ $cantiere->id }}_active_tab';
                                localStorage.setItem(tabStorageKey, '#presenze');

                                // Reset form
                                $('#titoloModalPresenza').text('Registra Presenza');
                                $('#azione_presenza').val('aggiungi_presenza_manuale');
                                $('#id_presenza').val('');
                                $('#id_dipendente').val('');
                                $('#data_presenza').val(new Date().toISOString().split('T')[0]);
                                $('#ora_inizio').val('07:00');
                                $('#ora_fine').val('17:00');
                                $('#note_presenza').val('');
                                $('#btnSalvaPresenza').html('<i class="ri-save-line"></i> Salva Presenza');

                                $('#modalPresenza').modal('show');
                            }

                            // Funzione per modificare presenza esistente
                            function modificaPresenza(id, data, oraInizio, oraFine, note, idDipendente) {
                                // Salva il tab corrente
                                const tabStorageKey = 'cantiere_{{ $cantiere->id }}_active_tab';
                                localStorage.setItem(tabStorageKey, '#presenze');

                                $('#titoloModalPresenza').text('Modifica Presenza');
                                $('#azione_presenza').val('modifica_presenza');
                                $('#id_presenza').val(id);
                                $('#id_dipendente').val(idDipendente);
                                $('#data_presenza').val(data);
                                $('#ora_inizio').val(oraInizio);
                                $('#ora_fine').val(oraFine || '');
                                $('#note_presenza').val(note || '');
                                $('#btnSalvaPresenza').html('<i class="ri-edit-line"></i> Salva Modifiche');

                                $('#modalPresenza').modal('show');
                            }

                            // Funzione per eliminare presenza
                            function eliminaPresenza(id) {
                                if (confirm('Sei sicuro di voler eliminare questa presenza?')) {
                                    // Salva il tab corrente
                                    const tabStorageKey = 'cantiere_{{ $cantiere->id }}_active_tab';
                                    localStorage.setItem(tabStorageKey, '#presenze');

                                    // Crea form nascosto per eliminazione
                                    const form = $('<form>', {
                                        method: 'POST'
                                    });

                                    form.append($('<input>', {
                                        type: 'hidden',
                                        name: '_token',
                                        value: '{{ csrf_token() }}'
                                    }));

                                    form.append($('<input>', {
                                        type: 'hidden',
                                        name: 'elimina_presenza',
                                        value: '1'
                                    }));

                                    form.append($('<input>', {
                                        type: 'hidden',
                                        name: 'id_presenza',
                                        value: id
                                    }));

                                    $('body').append(form);
                                    form.submit();
                                }
                            }

                            // Funzione per filtrare presenze
                            function filtraPresenze() {
                                const filtroDipendente = $('#filtroDipendente').val().toLowerCase();
                                const dataInizio = new Date($('#dataInizio').val());
                                const dataFine = new Date($('#dataFine').val());

                                $('#tabellaPresenze tbody tr').each(function() {
                                    const row = $(this);
                                    const dipendente = row.data('dipendente').toLowerCase();
                                    const dataPresenza = new Date(row.data('data'));

                                    let mostra = true;

                                    // Filtro dipendente
                                    if (filtroDipendente && !dipendente.includes(filtroDipendente)) {
                                        mostra = false;
                                    }

                                    // Filtro data inizio
                                    if ($('#dataInizio').val() && dataPresenza < dataInizio) {
                                        mostra = false;
                                    }

                                    // Filtro data fine
                                    if ($('#dataFine').val() && dataPresenza > dataFine) {
                                        mostra = false;
                                    }

                                    row.toggle(mostra);
                                });
                            }

                            // Funzione per resettare filtri
                            function resetFiltri() {
                                $('#filtroDipendente').val('');
                                $('#dataInizio').val('');
                                $('#dataFine').val('');
                                $('#tabellaPresenze tbody tr').show();
                            }

                            // Validazione form presenza
                            $('#formPresenza').on('submit', function(e) {
                                const oraInizio = $('#ora_inizio').val();
                                const oraFine = $('#ora_fine').val();

                                if (oraInizio && oraFine && oraInizio >= oraFine) {
                                    e.preventDefault();
                                    alert('L\'ora di fine deve essere successiva all\'ora di inizio');
                                    return false;
                                }
                            });

                            // Auto-calcolo ore lavorate
                            $('#ora_inizio, #ora_fine').on('change', function() {
                                const oraInizio = $('#ora_inizio').val();
                                const oraFine = $('#ora_fine').val();

                                if (oraInizio && oraFine) {
                                    const inizio = new Date('2000-01-01 ' + oraInizio);
                                    const fine = new Date('2000-01-01 ' + oraFine);

                                    if (fine >= inizio) {
                                        const diffMs = fine - inizio;
                                        const diffHrs = Math.floor(diffMs / (1000 * 60 * 60));
                                        const diffMins = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));

                                        // Puoi aggiungere un campo di visualizzazione ore totali se vuoi
                                        console.log(`Ore totali: ${diffHrs}h ${diffMins}m`);
                                    }
                                }
                            });
                        </script>

                        <style>
                            /* Stili aggiuntivi per le presenze */
                            .badge {
                                font-size: 0.8em;
                            }

                            .table th {
                                background-color: #f8f9fa;
                                font-weight: 600;
                            }

                            .table td {
                                vertical-align: middle;
                            }

                            .btn-group .btn {
                                padding: 0.25rem 0.5rem;
                            }

                            #tabellaPresenze tbody tr:hover {
                                background-color: #f8f9fa;
                            }

                            .alert-info {
                                border-left: 4px solid #17a2b8;
                            }

                            /* Badge personalizzati per distanze */
                            .badge.bg-success {
                                background-color: #28a745 !important;
                            }

                            .badge.bg-warning {
                                background-color: #ffc107 !important;
                                color: #212529 !important;
                            }
                        </style>
                        <!-- ✅ NUOVO TAB ALLEGATI CON DEBUG -->
                        <div class="tab-pane fade" id="allegati" role="tabpanel">
                            <div class="row">
                                <!-- COLONNA SINISTRA - CARICAMENTO -->
                                <div class="col-md-4">

                                    @if($utente->solo_lettura == 0)
                                    <div class="card">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="mb-0 text-white">Carica Allegati</h5>
                                        </div>
                                        <div class="card-body">
                                            <!-- FORM UPLOAD FILE -->
                                            <form id="uploadFormAllegati">
                                                <input type="hidden" name="id_cantiere" value="{{ $cantiere->id }}">

                                                <div class="mb-3">
                                                    <label class="form-label">Seleziona File</label>
                                                    <input type="file" name="allegato" id="fileInputAllegato" class="form-control"
                                                           accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" required>
                                                    <small class="text-muted">Max 10MB - Formati: immagini, PDF, documenti Office</small>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Descrizione</label>
                                                    <input type="text" name="descrizione" id="descrizioneAllegato" class="form-control" placeholder="Descrivi il file...">
                                                </div>

                                                <button type="button" onclick="uploadFile()" class="btn btn-primary w-100" id="btnUploadFile">
                                                    <i class="ri-upload-line"></i> Carica File
                                                </button>
                                            </form>

                                    <!-- Messaggio di feedback -->
                                            <div id="uploadMessageAllegato" class="alert mt-3" style="display: none;"></div>

                                            <hr>

                                            <!-- BOTTONE FOTOCAMERA -->
                                            <button type="button" class="btn btn-success w-100" onclick="apriCameraAllegati()">
                                                <i class="ri-camera-line"></i> Scatta Foto
                                            </button>
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                <!-- COLONNA DESTRA - LISTA ALLEGATI -->
                                <div class="col-md-8">
                                    <div class="card">
                                        <div class="card-header bg-info text-white">
                                            <h5 class="mb-0 text-white">Allegati del Cantiere</h5>
                                        </div>
                                        <div class="card-body">
                                            <div id="listaAllegatiCantiere">
                                                <p class="text-muted text-center">Caricamento allegati...</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL FOTOCAMERA ALLEGATI -->
                        <div class="modal fade" id="cameraModalAllegati" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Scatta Foto</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="chiudiCameraAllegati()"></button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <video id="cameraAllegati" width="100%" style="max-width: 600px;"></video>
                                        <canvas id="canvasAllegati" style="display: none;"></canvas>
                                        <img id="photoAllegati" style="max-width: 100%; display: none;">

                                        <div class="mt-3">
                                            <input type="text" id="descrizione_foto_allegati" class="form-control"
                                                   placeholder="Descrizione foto..." style="display: none;">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-warning" onclick="scattaFotoAllegati()" id="btnScattaAllegati">
                                            <i class="ri-camera-line"></i> Scatta
                                        </button>
                                        <button type="button" class="btn btn-success" onclick="salvaFotoAllegati()" style="display: none;" id="btnSalvaFotoAllegati">
                                            <i class="ri-save-line"></i> Salva
                                        </button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="chiudiCameraAllegati()">Chiudi</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- AGGIUNGI QUESTO SCRIPT SUBITO DOPO IL DIV DEL TAB -->
                        <script>
                            // Variabili globali per la camera
                            let currentStreamAllegati = null;

                            // Funzione upload file con debug
                            function uploadFile() {
                                console.log('Inizio upload file...');

                                const formData = new FormData();
                                const fileInput = document.getElementById('fileInputAllegato');
                                const file = fileInput.files[0];

                                if (!file) {
                                    alert('Seleziona un file prima di caricare');
                                    return;
                                }

                                // Prepara i dati
                                formData.append('_token', '{{ csrf_token() }}');
                                formData.append('allegato', file);
                                formData.append('id_cantiere', '{{ $cantiere->id }}');
                                formData.append('descrizione', document.getElementById('descrizioneAllegato').value);

                                // Debug
                                console.log('File da caricare:', file.name, file.size, file.type);

                                // Disabilita bottone
                                const btnUpload = document.getElementById('btnUploadFile');
                                btnUpload.disabled = true;
                                btnUpload.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Caricamento...';

                                // Nascondi messaggi precedenti
                                document.getElementById('uploadMessageAllegato').style.display = 'none';

                                // Invia richiesta
                                fetch('{{ route("cantiere.upload.allegato") }}', {
                                    method: 'POST',
                                    body: formData,
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                })
                                    .then(response => {
                                        console.log('Response status:', response.status);
                                        return response.json();
                                    })
                                    .then(data => {
                                        console.log('Response data:', data);

                                        const messageDiv = document.getElementById('uploadMessageAllegato');

                                        if (data.success) {
                                            messageDiv.className = 'alert alert-success mt-3';
                                            messageDiv.innerHTML = '<i class="ri-check-line"></i> ' + data.message;
                                            messageDiv.style.display = 'block';

                                            // Reset form
                                            document.getElementById('uploadFormAllegati').reset();

                                            // Ricarica lista
                                            caricaAllegatiCantiere();
                                        } else {
                                            messageDiv.className = 'alert alert-danger mt-3';
                                            messageDiv.innerHTML = '<i class="ri-error-warning-line"></i> ' + data.message;
                                            messageDiv.style.display = 'block';
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Errore:', error);
                                        const messageDiv = document.getElementById('uploadMessageAllegato');
                                        messageDiv.className = 'alert alert-danger mt-3';
                                        messageDiv.innerHTML = '<i class="ri-error-warning-line"></i> Errore di connessione: ' + error.message;
                                        messageDiv.style.display = 'block';
                                    })
                                    .finally(() => {
                                        // Riabilita bottone
                                        btnUpload.disabled = false;
                                        btnUpload.innerHTML = '<i class="ri-upload-line"></i> Carica File';
                                    });
                            }

                            // Funzione per caricare la lista allegati
                            let allegatiCaricati = false;

                            // Funzione per caricare la lista allegati
                            function caricaAllegatiCantiere() {
                                console.log('Caricamento lista allegati...');

                                const container = document.getElementById('listaAllegatiCantiere');

                                // Mostra loading
                                container.innerHTML = '<div class="text-center"><i class="ri-loader-4-line ri-spin"></i> Caricamento allegati...</div>';

                                fetch('/azienda/cantiere/{{ $cantiere->id }}/allegati')
                                    .then(response => {
                                        if (!response.ok) {
                                            throw new Error('Errore HTTP: ' + response.status);
                                        }
                                        return response.json();
                                    })
                                    .then(allegati => {
                                        console.log('Allegati ricevuti:', allegati);

                                        let html = '';

                                        if (!allegati || allegati.length === 0) {
                                            html = '<p class="text-muted text-center">Nessun allegato presente</p>';
                                        } else {
                                            html = '<div class="row">';

                                            allegati.forEach(function(allegato) {
                                                html += '<div class="col-md-6 mb-3">';
                                                html += '<div class="card h-100">';
                                                html += '<div class="card-body">';

                                                // Icona in base al tipo
                                                if (allegato.tipo === 'image') {
                                                    html += '<div class="text-center mb-2">';
                                                    html += '<img src="/' + allegato.path + '" class="img-thumbnail" style="max-height: 150px; cursor: pointer;" onclick="visualizzaImmagineAllegato(\'/' + allegato.path + '\', \'' + allegato.nome_originale + '\')">';
                                                    html += '</div>';
                                                } else if (allegato.tipo === 'pdf') {
                                                    html += '<div class="text-center mb-2">';
                                                    html += '<i class="ri-file-pdf-line" style="font-size: 48px; color: #dc3545;"></i>';
                                                    html += '</div>';
                                                } else {
                                                    html += '<div class="text-center mb-2">';
                                                    html += '<i class="ri-file-text-line" style="font-size: 48px; color: #007bff;"></i>';
                                                    html += '</div>';
                                                }

                                                html += '<h6 class="card-title">' + allegato.nome_originale + '</h6>';
                                                html += '<p class="card-text small">' + (allegato.descrizione || 'Nessuna descrizione') + '</p>';
                                                html += '<p class="card-text">';
                                                html += '<small class="text-muted">Dimensione: ' + allegato.dimensione + '</small><br>';
                                                html += '<small class="text-muted">Caricato: ' + formatDateAllegato(allegato.created_at) + '</small>';
                                                html += '</p>';

                                                html += '<div class="btn-group w-100">';
                                                if (allegato.tipo === 'image') {
                                                    html += '<button class="btn btn-sm btn-info" onclick="visualizzaImmagineAllegato(\'/' + allegato.path + '\', \'' + allegato.nome_originale + '\')">';
                                                    html += '<i class="ri-eye-line"></i>';
                                                    html += '</button>';
                                                }
                                                html += '<a href="/' + allegato.path + '" download class="btn btn-sm btn-success">';
                                                html += '<i class="ri-download-line"></i>';
                                                html += '</a>';
                                                html += '<button class="btn btn-sm btn-danger" onclick="eliminaAllegatoCantiere(' + allegato.id + ')">';
                                                html += '<i class="ri-delete-bin-line"></i>';
                                                html += '</button>';
                                                html += '</div>';

                                                html += '</div>';
                                                html += '</div>';
                                                html += '</div>';
                                            });

                                            html += '</div>';
                                        }

                                        container.innerHTML = html;
                                        allegatiCaricati = true;
                                    })
                                    .catch(error => {
                                        console.error('Errore caricamento allegati:', error);
                                        container.innerHTML = '<p class="text-danger text-center">Errore nel caricamento degli allegati<br><small>' + error.message + '</small></p>';
                                    });
                            }

                            // Event listener per il tab allegati
                            document.addEventListener('DOMContentLoaded', function() {
                                // Trova il link del tab allegati
                                const allegatoTab = document.querySelector('a[href="#allegati"]');

                                if (allegatoTab) {
                                    // Aggiungi listener per quando il tab viene mostrato
                                    allegatoTab.addEventListener('shown.bs.tab', function (e) {
                                        console.log('Tab allegati attivato');
                                        // Carica sempre gli allegati quando si clicca sul tab
                                        caricaAllegatiCantiere();
                                    });
                                }

                                // Se il tab allegati è già attivo al caricamento pagina
                                const allegatoTabPane = document.getElementById('allegati');
                                if (allegatoTabPane && allegatoTabPane.classList.contains('active')) {
                                    caricaAllegatiCantiere();
                                }
                            });

                            // Modifica la funzione uploadFile per ricaricare gli allegati dopo l'upload
                            function uploadFile() {
                                console.log('=== INIZIO UPLOAD FILE ===');

                                const fileInput = document.getElementById('fileInputAllegato');
                                const file = fileInput.files[0];

                                if (!file) {
                                    alert('Seleziona un file prima di caricare');
                                    return;
                                }

                                const formData = new FormData();
                                formData.append('_token', '{{ csrf_token() }}');
                                formData.append('allegato', file);
                                formData.append('id_cantiere', '{{ $cantiere->id }}');
                                formData.append('descrizione', document.getElementById('descrizioneAllegato').value || '');

                                const btnUpload = document.getElementById('btnUploadFile');
                                btnUpload.disabled = true;
                                btnUpload.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Caricamento...';

                                document.getElementById('uploadMessageAllegato').style.display = 'none';

                                $.ajax({
                                    url: '/cantiere/upload-allegato',
                                    type: 'POST',
                                    data: formData,
                                    processData: false,
                                    contentType: false,
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    success: function(data) {
                                        console.log('Success:', data);

                                        const messageDiv = document.getElementById('uploadMessageAllegato');

                                        if (data.success) {
                                            messageDiv.className = 'alert alert-success mt-3';
                                            messageDiv.innerHTML = '<i class="ri-check-line"></i> ' + data.message;
                                            messageDiv.style.display = 'block';

                                            // Reset form
                                            document.getElementById('uploadFormAllegati').reset();

                                            // Ricarica lista allegati dopo 500ms
                                            setTimeout(() => {
                                                caricaAllegatiCantiere();
                                                // Nascondi messaggio dopo 3 secondi
                                                setTimeout(() => {
                                                    messageDiv.style.display = 'none';
                                                }, 3000);
                                            }, 500);
                                        } else {
                                            messageDiv.className = 'alert alert-danger mt-3';
                                            messageDiv.innerHTML = '<i class="ri-error-warning-line"></i> ' + data.message;
                                            messageDiv.style.display = 'block';
                                        }
                                    },
                                    error: function(xhr, status, error) {
                                        console.error('Error:', error);
                                        const messageDiv = document.getElementById('uploadMessageAllegato');
                                        messageDiv.className = 'alert alert-danger mt-3';
                                        messageDiv.innerHTML = '<i class="ri-error-warning-line"></i> Errore: ' + error;
                                        messageDiv.style.display = 'block';
                                    },
                                    complete: function() {
                                        btnUpload.disabled = false;
                                        btnUpload.innerHTML = '<i class="ri-upload-line"></i> Carica File';
                                    }
                                });
                            }

                            // Helper per formattare data
                            function formatDateAllegato(dateString) {
                                if (!dateString) return 'N/D';
                                const date = new Date(dateString);
                                return date.toLocaleDateString('it-IT') + ' ' + date.toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' });
                            }

                            // CSS per spinner
                            if (!document.getElementById('spinner-style')) {
                                const style = document.createElement('style');
                                style.id = 'spinner-style';
                                style.textContent = `
        .ri-loader-4-line.ri-spin {
            animation: spin 1s linear infinite;
            font-size: 24px;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    `;
                                document.head.appendChild(style);
                            }

                            // Funzione per eliminare allegato
                            function eliminaAllegatoCantiere(id) {
                                if (!confirm('Sei sicuro di voler eliminare questo allegato?')) {
                                    return;
                                }

                                fetch('{{ route("cantiere.elimina.allegato") }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'X-Requested-With': 'XMLHttpRequest'
                                    },
                                    body: JSON.stringify({ id: id })
                                })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            alert(data.message);
                                            caricaAllegatiCantiere();
                                        } else {
                                            alert('Errore: ' + data.message);
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Errore:', error);
                                        alert('Errore nell\'eliminazione dell\'allegato');
                                    });
                            }

                            // Gestione fotocamera
                            function apriCameraAllegati() {
                                $('#cameraModalAllegati').modal('show');

                                navigator.mediaDevices.getUserMedia({
                                    video: {
                                        width: { ideal: 1280 },
                                        height: { ideal: 720 },
                                        facingMode: 'environment'
                                    }
                                })
                                    .then(function(stream) {
                                        currentStreamAllegati = stream;
                                        const video = document.getElementById('cameraAllegati');
                                        video.srcObject = stream;
                                        video.play();
                                    })
                                    .catch(function(err) {
                                        alert('Errore nell\'accesso alla fotocamera: ' + err.message);
                                        $('#cameraModalAllegati').modal('hide');
                                    });
                            }

                            function scattaFotoAllegati() {
                                const video = document.getElementById('cameraAllegati');
                                const canvas = document.getElementById('canvasAllegati');
                                const photo = document.getElementById('photoAllegati');

                                canvas.width = video.videoWidth;
                                canvas.height = video.videoHeight;
                                canvas.getContext('2d').drawImage(video, 0, 0);

                                const dataURL = canvas.toDataURL('image/jpeg', 0.8);
                                photo.src = dataURL;
                                photo.style.display = 'block';
                                video.style.display = 'none';

                                document.getElementById('descrizione_foto_allegati').style.display = 'block';
                                document.getElementById('btnSalvaFotoAllegati').style.display = 'inline-block';
                                document.getElementById('btnScattaAllegati').style.display = 'none';
                            }

                            function salvaFotoAllegati() {
                                const canvas = document.getElementById('canvasAllegati');
                                const dataURL = canvas.toDataURL('image/jpeg', 0.8);
                                const descrizione = document.getElementById('descrizione_foto_allegati').value || 'Foto scattata in cantiere';

                                $('#btnSalvaFotoAllegati').prop('disabled', true).html('<i class="ri-loader-4-line ri-spin"></i> Salvataggio...');

                                fetch('{{ route("cantiere.salva.foto") }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'X-Requested-With': 'XMLHttpRequest'
                                    },
                                    body: JSON.stringify({
                                        id_cantiere: {{ $cantiere->id }},
                                        foto_data: dataURL,
                                        descrizione: descrizione
                                    })
                                })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            alert(data.message);
                                            $('#cameraModalAllegati').modal('hide');
                                            caricaAllegatiCantiere();
                                        } else {
                                            alert('Errore: ' + data.message);
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Errore:', error);
                                        alert('Errore nel salvataggio della foto');
                                    })
                                    .finally(() => {
                                        $('#btnSalvaFotoAllegati').prop('disabled', false).html('<i class="ri-save-line"></i> Salva');
                                    });
                            }

                            function chiudiCameraAllegati() {
                                if (currentStreamAllegati) {
                                    currentStreamAllegati.getTracks().forEach(track => track.stop());
                                    currentStreamAllegati = null;
                                }

                                const video = document.getElementById('cameraAllegati');
                                const photo = document.getElementById('photoAllegati');

                                video.style.display = 'block';
                                photo.style.display = 'none';
                                document.getElementById('descrizione_foto_allegati').style.display = 'none';
                                document.getElementById('btnSalvaFotoAllegati').style.display = 'none';
                                document.getElementById('btnScattaAllegati').style.display = 'inline-block';
                            }

                            // Chiudi camera quando modal viene chiuso
                            $('#cameraModalAllegati').on('hidden.bs.modal', function() {
                                chiudiCameraAllegati();
                            });

                            // Helper per formattare data
                            function formatDateAllegato(dateString) {
                                const date = new Date(dateString);
                                return date.toLocaleDateString('it-IT') + ' ' + date.toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' });
                            }

                            // Carica allegati quando il tab viene mostrato
                            document.addEventListener('DOMContentLoaded', function() {
                                // Listener per quando il tab allegati viene mostrato
                                const allegatoTab = document.querySelector('a[href="#allegati"]');
                                if (allegatoTab) {
                                    allegatoTab.addEventListener('shown.bs.tab', function() {
                                        console.log('Tab allegati attivato');
                                        caricaAllegatiCantiere();
                                    });
                                }

                                // Se il tab è già attivo al caricamento
                                if (document.querySelector('#allegati.active')) {
                                    caricaAllegatiCantiere();
                                }
                            });

                            // CSS per animazione spinner
                            const style = document.createElement('style');
                            style.textContent = `
    .ri-loader-4-line.ri-spin {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
`;
                            document.head.appendChild(style);
                        </script>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- 4. AGGIUNGI QUESTO CSS -->
    <style>
        /* Stili per allegati */
        #listaAllegati .card {
            transition: transform 0.2s;
        }

        #listaAllegati .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        #listaAllegati .img-thumbnail {
            cursor: pointer;
        }

        .ri-loader-4-line.ri-spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Stili per camera */
        #camera, #photo {
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
    </style>
    <!-- ✅ MODAL PER VISUALIZZAZIONE ALLEGATI -->
    <div class="modal fade" id="modalVisualizzaAllegato" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="titoloAllegato">Visualizza Allegato</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div id="contenutoAllegato">
                        <!-- Contenuto dinamico -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="btnScaricaAllegato">
                        <i class="ri-download-line"></i> Scarica
                    </button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Scarico Articolo -->
    <div class="modal fade" id="modalScarico" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Scarico Materiale</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Confermi lo scarico di <strong id="titolo_articolo"></strong>?</p>
                    <div class="mb-3">
                        <label>Quantità da Scaricare:</label>
                        <input type="number" id="quantita_scarico" class="form-control" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-danger" onclick="scaricaArticolo()">Conferma Scarico</button>
                </div>
            </div>
        </div>
    </div>


    <style>
        .profile-tab-background {
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            padding: 20px;
            border-radius: 10px;

        }


        .profile-tab-background .tab-content {
            background: rgba(255, 255, 255, 1);
            padding: 20px;
            border-radius: 10px;
        }

        table {
            width: 100%;
        }
    </style>


    <script>
        // ✅ VARIABILI GLOBALI PER GESTIONE SCARICO - DICHIARATE ALL'INIZIO
        let currentArticoloId = null;
        let currentNomeCantiere = null;

        let currentIdCantiere = null;

        // ✅ VARIABILI GLOBALI PER GESTIONE ALLEGATI
        let currentPhotoData = null;
        let cameraStream = null;

        $(document).ready(function() {
            $('#dipendenti').select2();
        });





        // ✅ SISTEMA DI PERSISTENZA TAB - Salva e ripristina il tab attivo
        document.addEventListener('DOMContentLoaded', function() {
            // Chiave univoca per questo cantiere
            const tabStorageKey = 'cantiere_{{ $cantiere->id }}_active_tab';

            // Funzione per salvare il tab attivo
            function saveActiveTab(tabId) {
                localStorage.setItem(tabStorageKey, tabId);
            }

            // Funzione per ripristinare il tab attivo
            function restoreActiveTab() {
                const savedTab = localStorage.getItem(tabStorageKey);
                if (savedTab) {
                    // Rimuovi la classe active da tutti i tab
                    document.querySelectorAll('#cantiereNavTabs .nav-link').forEach(tab => {
                        tab.classList.remove('active');
                    });
                    document.querySelectorAll('#cantiereTabsContent .tab-pane').forEach(pane => {
                        pane.classList.remove('active', 'show');
                    });

                    // Attiva il tab salvato
                    const savedTabLink = document.querySelector(`#cantiereNavTabs a[href="${savedTab}"]`);
                    const savedTabPane = document.querySelector(savedTab);

                    if (savedTabLink && savedTabPane) {
                        savedTabLink.classList.add('active');
                        savedTabPane.classList.add('active', 'show');
                    }
                }
            }

            // Aggiungi listener per salvare il tab quando viene cliccato
            document.querySelectorAll('#cantiereNavTabs .nav-link').forEach(tabLink => {
                tabLink.addEventListener('click', function() {
                    const tabTarget = this.getAttribute('href');
                    saveActiveTab(tabTarget);
                });
            });

            // Ripristina il tab attivo al caricamento della pagina
            restoreActiveTab();

            // ✅ Gestione highlight attività (esistente)
            var urlParams = new URLSearchParams(window.location.search);
            var highlightId = urlParams.get('highlight');

            if (highlightId) {
                // Forza l'apertura del tab attività
                localStorage.setItem(tabStorageKey, '#attività');

                // Rimuovi active da tutti
                document.querySelectorAll('#cantiereNavTabs .nav-link').forEach(tab => {
                    tab.classList.remove('active');
                });
                document.querySelectorAll('#cantiereTabsContent .tab-pane').forEach(pane => {
                    pane.classList.remove('active', 'show');
                });

                // Attiva tab attività
                const attivitaLink = document.querySelector('#attività-tab-link');
                const attivitaPane = document.querySelector('#attività');

                if (attivitaLink && attivitaPane) {
                    attivitaLink.classList.add('active');
                    attivitaPane.classList.add('active', 'show');
                }

                setTimeout(() => {
                    var row = document.getElementById('attivita_' + highlightId);
                    if (row) {
                        row.style.backgroundColor = '#ffeeba';
                        row.style.transition = 'background-color 2s ease';
                        setTimeout(() => row.style.backgroundColor = '', 3000);
                    }
                }, 500);
            }
        });

        $(document).ready(function() {
            // ✅ Inizializza Select2 solo se l'elemento è presente sulla pagina
            if ($('.select2').length > 0) {
                $('.select2').select2({
                    placeholder: "Seleziona i dipendenti",
                    allowClear: true
                });
            }
        });

        // ✅ GESTIONE CAMBIO STATO CANTIERE
        document.addEventListener("DOMContentLoaded", function() {
            let selectStato = document.getElementById("statoCantiere");
            if (selectStato) {
                selectStato.addEventListener("change", function() {
                    let nuovoStato = this.value;
                    let cantiereId = {{ $cantiere->id }};


                    let urlUpdate = "{{ route('cantiere.updateStato') }}";

                    $.ajax({
                        url: urlUpdate,
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            id: cantiereId,
                            stato: nuovoStato
                        },
                        success: function(response) {
                            console.log("Success:", response);
                            alert("Stato del cantiere aggiornato con successo!");
                        },
                        error: function(xhr) {
                            alert("Errore nella richiesta: " + xhr.status + " " + xhr.statusText);
                        }
                    });
                });
            }

            // ✅ Inizializza tooltips e popovers solo se esistono
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
        });

        // ✅ GESTIONE PAGAMENTI
        // Gestione cambio tipo pagamento
        document.addEventListener('DOMContentLoaded', function() {
            const tipoPagamentoSelect = document.getElementById('tipo_pagamento');
            if (tipoPagamentoSelect) {
                tipoPagamentoSelect.addEventListener('change', function() {
                    if (this.value === 'ricevuto') {
                        document.getElementById('div_data_pagamento').style.display = 'block';
                        document.getElementById('div_data_scadenza').style.display = 'none';
                        document.getElementById('data_pagamento').required = true;
                        document.getElementById('data_scadenza').required = false;
                    } else {
                        document.getElementById('div_data_pagamento').style.display = 'none';
                        document.getElementById('div_data_scadenza').style.display = 'block';
                        document.getElementById('data_pagamento').required = false;
                        document.getElementById('data_scadenza').required = true;
                    }
                });
            }
        });

        function aggiungiPagamento() {
            // Salva il tab corrente prima di aprire la modal
            const tabStorageKey = 'cantiere_{{ $cantiere->id }}_active_tab';
            localStorage.setItem(tabStorageKey, '#finanziario');

            // Reset form
            document.getElementById('titoloModalPagamento').innerText = 'Aggiungi Pagamento';
            document.getElementById('azione_pagamento').value = 'aggiungi';
            document.getElementById('id_pagamento').value = '';
            document.getElementById('tipo_pagamento').value = 'ricevuto';
            document.getElementById('importo_pagamento').value = '';
            document.getElementById('data_pagamento').value = '';
            document.getElementById('data_scadenza').value = '';
            document.getElementById('descrizione_pagamento').value = '';
            document.getElementById('note_pagamento').value = '';

            // Trigger change event
            document.getElementById('tipo_pagamento').dispatchEvent(new Event('change'));

            new bootstrap.Modal(document.getElementById('modalPagamento')).show();
        }

        function modificaPagamento(id) {
            // Salva il tab corrente
            const tabStorageKey = 'cantiere_{{ $cantiere->id }}_active_tab';
            localStorage.setItem(tabStorageKey, '#finanziario');

            // Qui faresti una chiamata AJAX per recuperare i dati del pagamento
            // Per ora simuliamo
            document.getElementById('titoloModalPagamento').innerText = 'Modifica Pagamento';
            document.getElementById('azione_pagamento').value = 'modifica';
            document.getElementById('id_pagamento').value = id;

            new bootstrap.Modal(document.getElementById('modalPagamento')).show();
        }

        function eliminaPagamento(id) {
            if (confirm('Sei sicuro di voler eliminare questo pagamento?')) {
                // Salva il tab corrente
                const tabStorageKey = 'cantiere_{{ $cantiere->id }}_active_tab';
                localStorage.setItem(tabStorageKey, '#finanziario');

                // Chiamata per eliminazione
                fetch('{{ route("cantieri.gestisciPagamento") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        azione: 'elimina',
                        id_pagamento: id
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Errore nell\'eliminazione');
                        }
                    });
            }
        }

        function segnaComePagato(id) {
            if (confirm('Segnare questo pagamento come ricevuto?')) {
                // Salva il tab corrente
                const tabStorageKey = 'cantiere_{{ $cantiere->id }}_active_tab';
                localStorage.setItem(tabStorageKey, '#finanziario');

                fetch('{{ route("cantieri.gestisciPagamento") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        azione: 'segna_pagato',
                        id_pagamento: id,
                        data_pagamento: new Date().toISOString().split('T')[0]
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Errore nell\'aggiornamento');
                        }
                    });
            }
        }

        // ✅ GESTIONE MAGAZZINO
        // Ricerca materiali
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchMateriali');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const items = document.querySelectorAll('.material-item');

                    items.forEach(item => {
                        const name = item.getAttribute('data-name');
                        if (name.includes(searchTerm)) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            }
        });

        function impegnaMateriale(idArticolo, idCantiere) {
            // Salva il tab corrente
            const tabStorageKey = 'cantiere_{{ $cantiere->id }}_active_tab';
            localStorage.setItem(tabStorageKey, '#magazzino');

            let quantita = document.getElementById('quantita_' + idArticolo).value;

            $.ajax({
                url: "/azienda/magazzino/impegna",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id_articolo: idArticolo,
                    id_cantiere: idCantiere,
                    quantita: quantita
                },
                success: function(response) {
                    if (response.success) {
                        location.reload(); // Ricarica per aggiornare le due colonne
                    } else {
                        alert(response.message);
                    }
                }
            });
        }

        function rimuoviMaterialeImpegnato(articoloId, cantiereId) {
            // Salva il tab corrente
            const tabStorageKey = 'cantiere_{{ $cantiere->id }}_active_tab';
            localStorage.setItem(tabStorageKey, '#magazzino');

            let quantita = document.getElementById('qta_rimuovi_' + articoloId).value;

            $.ajax({
                url: "/azienda/magazzino/rimuovi",
                type: "GET",
                data: {
                    id_articolo: articoloId,
                    id_cantiere: cantiereId,
                    quantita: quantita
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                }
            });
        }

        function scaricaDirectMaterial(id, titolo, cantiereId, cantiereNome) {
            console.log('=== DEBUG SCARICA DIRECT MATERIAL ===');
            console.log('ID:', id);
            console.log('Titolo:', titolo);

            // Salva il tab corrente
            const tabStorageKey = 'cantiere_{{ $cantiere->id }}_active_tab';
            localStorage.setItem(tabStorageKey, '#magazzino');

            let quantita = document.getElementById('quantita_' + id).value;
            console.log('Quantità:', quantita);

            currentArticoloId = id;
            currentIdCantiere = cantiereId;
            currentNomeCantiere = cantiereNome;

            // DEBUG: Controlla se gli elementi esistono
            const titoloElement = document.getElementById("titolo_articolo");
            const quantitaElement = document.getElementById("quantita_scarico");
            const modalElement = document.getElementById('modalScarico');

            console.log('Titolo element:', titoloElement);
            console.log('Quantità element:', quantitaElement);
            console.log('Modal element:', modalElement);

            if (!titoloElement) {
                console.error('ERRORE: elemento titolo_articolo non trovato');
                return;
            }
            if (!quantitaElement) {
                console.error('ERRORE: elemento quantita_scarico non trovato');
                return;
            }
            if (!modalElement) {
                console.error('ERRORE: modal modalScarico non trovata');
                return;
            }

            titoloElement.innerText = titolo;
            quantitaElement.value = quantita;

            console.log('Tentativo di aprire modal...');

            try {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
                console.log('Modal aperta con successo');
            } catch (error) {
                console.error('Errore apertura modal:', error);
                alert('Errore nell\'apertura della modal: ' + error.message);
            }
        }

        function scaricaMaterialeImpegnato(id, titolo, cantiereId, cantiereNome) {
            // Salva il tab corrente
            const tabStorageKey = 'cantiere_{{ $cantiere->id }}_active_tab';
            localStorage.setItem(tabStorageKey, '#magazzino');

            let quantita = document.getElementById('qta_rimuovi_' + id).value;

            currentArticoloId = id;
            currentIdCantiere = cantiereId;
            currentNomeCantiere = cantiereNome;

            document.getElementById("titolo_articolo").innerText = titolo;
            document.getElementById("quantita_scarico").value = quantita;

            new bootstrap.Modal(document.getElementById('modalScarico')).show();
        }

        function impegnaStrumento(articoloId, cantiereId) {
            // Salva il tab corrente
            const tabStorageKey = 'cantiere_{{ $cantiere->id }}_active_tab';
            localStorage.setItem(tabStorageKey, '#magazzino');

            $.ajax({
                url: "/azienda/magazzino/impegna",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id_articolo: articoloId,
                    id_cantiere: cantiereId,
                    quantita: 1
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                }
            });
        }

        function rimuoviStrumento(articoloId, cantiereId) {
            // Salva il tab corrente
            const tabStorageKey = 'cantiere_{{ $cantiere->id }}_active_tab';
            localStorage.setItem(tabStorageKey, '#magazzino');

            $.ajax({
                url: "/azienda/magazzino/rimuovi",
                type: "GET",
                data: {
                    _token: "{{ csrf_token() }}",
                    id_articolo: articoloId,
                    id_cantiere: cantiereId
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                }
            });
        }

        // ✅ GESTIONE ATTIVITÀ
        function apriModalAggiunta() {
            // Salva il tab corrente
            const tabStorageKey = 'cantiere_{{ $cantiere->id }}_active_tab';
            localStorage.setItem(tabStorageKey, '#attività');

            document.getElementById("titoloModalAttivita").innerText = "Inserisci Attività";
            document.getElementById("btnSalvaAttivita").style.display = "inline-block";
            document.getElementById("btnModificaAttivita").style.display = "none";
            document.getElementById("id_attivita").value = "";
            document.getElementById("descrizione_attivita").value = "";
            document.getElementById("data_inizio_attivita").value = "";
            document.getElementById("data_fine_attivita").value = "";
            document.getElementById("note_attivita").value = "";

            let dataInizioCantiere = "{{ date('Y-m-d', strtotime($cantiere->data_inizio)) }}";
            let dataFineCantiere = "{{ date('Y-m-d', strtotime($cantiere->data_fine)) }}";

            document.getElementById("data_inizio_attivita").setAttribute("placeholder", "Data inizio suggerita: " + dataInizioCantiere);
            document.getElementById("data_fine_attivita").setAttribute("placeholder", "Data fine suggerita: " + dataFineCantiere);

            $('#modalAttivita').modal('show');
        }

        function apriModalModifica(id, descrizione, data_inizio, data_fine, note) {
            // Salva il tab corrente
            const tabStorageKey = 'cantiere_{{ $cantiere->id }}_active_tab';
            localStorage.setItem(tabStorageKey, '#attività');

            document.getElementById("titoloModalAttivita").innerText = "Modifica Attività";
            document.getElementById("btnSalvaAttivita").style.display = "none";
            document.getElementById("btnModificaAttivita").style.display = "inline-block";
            document.getElementById("id_attivita").value = id;
            document.getElementById("descrizione_attivita").value = descrizione;
            document.getElementById("data_inizio_attivita").value = data_inizio;
            document.getElementById("data_fine_attivita").value = data_fine;
            document.getElementById("note_attivita").value = note;
            $('#modalAttivita').modal('show');
        }

        function gestisciDipendentiAttivita(attivitaId) {
            // Salva il tab corrente
            const tabStorageKey = 'cantiere_{{ $cantiere->id }}_active_tab';
            localStorage.setItem(tabStorageKey, '#attività');

            fetch(`/get-dipendenti-attivita/${attivitaId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('attivita_id_dipendenti').value = attivitaId;

                    const disponibili = document.getElementById('dipendentiDisponibili');
                    const assegnati = document.getElementById('dipendentiAssegnati');

                    disponibili.innerHTML = data.disponibili.map(d => `
                <div class="list-group-item" onclick="spostaDipendente(this, 'assegnati')"
                     data-dipendente-id="${d.id}">
                    ${d.nome} ${d.cognome}
                </div>
            `).join('');

                    assegnati.innerHTML = data.assegnati.map(d => `
                <div class="list-group-item" onclick="spostaDipendente(this, 'disponibili')"
                     data-dipendente-id="${d.id}">
                    ${d.nome} ${d.cognome}
                </div>
            `).join('');

                    new bootstrap.Modal(document.getElementById('modalDipendentiAttivita')).show();
                })
                .catch(error => alert('Errore nel caricamento dipendenti'));
        }

        function spostaDipendente(element, destinazione) {
            const target = document.getElementById('dipendenti' + destinazione.charAt(0).toUpperCase() + destinazione.slice(1));
            element.remove();
            target.appendChild(element);
        }

        function salvaDipendentiAttivita() {
            // Salva il tab corrente
            const tabStorageKey = 'cantiere_{{ $cantiere->id }}_active_tab';
            localStorage.setItem(tabStorageKey, '#attività');

            const attivitaId = document.getElementById('attivita_id_dipendenti').value;
            const dipendentiAssegnati = Array.from(document.getElementById('dipendentiAssegnati').children)
                .map(el => el.dataset.dipendenteId);

            fetch('/salva-dipendenti-attivita', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    attivita_id: attivitaId,
                    dipendenti: dipendentiAssegnati
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('modalDipendentiAttivita')).hide();
                        const badge = document.querySelector(`#attivita_${attivitaId} .badge`);
                        if (badge) badge.textContent = dipendentiAssegnati.length;
                    }
                })
                .catch(error => alert('Errore nel salvare i dipendenti'));
        }

        // ✅ GESTIONE SCARICO ARTICOLI - Funzioni che usano le variabili globali dichiarate sopra

        function confermaScarico(id, titolo, quantita, cantiereId, cantiereNome) {
            // Salva il tab corrente
            const tabStorageKey = 'cantiere_{{ $cantiere->id }}_active_tab';
            localStorage.setItem(tabStorageKey, '#magazzino');

            currentArticoloId = id;
            currentIdCantiere = cantiereId;
            currentNomeCantiere = cantiereNome;

            document.getElementById("titolo_articolo").innerText = titolo;
            document.getElementById("quantita_scarico").value = quantita;

            new bootstrap.Modal(document.getElementById('modalScarico')).show();
        }

        function scaricaArticolo() {
            let quantita = document.getElementById("quantita_scarico").value;

            $.ajax({
                url: "/azienda/magazzino/scarica",
                type: "GET",
                data: {
                    id_articolo: currentArticoloId,
                    id_cantiere: currentIdCantiere,
                    quantita: quantita,
                    nome_cantiere: currentNomeCantiere
                },
                success: function(response) {
                    if (response.success) {
                        let impegnataElem = document.getElementById('impegnata_' + currentArticoloId);
                        let disponibileElem = document.getElementById('quantita_disponibile_' + currentArticoloId);

                        if (impegnataElem && disponibileElem) {
                            impegnataElem.textContent = response.nuova_quantita_impegnata;
                            disponibileElem.textContent = response.nuova_quantita_articolo;
                        }

                        bootstrap.Modal.getInstance(document.getElementById('modalScarico')).hide();

                        // Salva il tab e ricarica
                        const tabStorageKey = 'cantiere_{{ $cantiere->id }}_active_tab';
                        localStorage.setItem(tabStorageKey, '#magazzino');

                        location.reload();
                    } else {
                        alert("Errore: " + response.message);
                    }
                },
                error: function(xhr) {
                    alert("Errore nello scarico!");
                }
            });
        }

        // ✅ GESTIONE DIPENDENTI - Controllo conflitti
        $(document).ready(function() {
            $('#dipendenti').on('change', function() {
                const dipendentiSelezionati = $(this).val();
                const cantiereId = {{ $cantiere->id }};
                const dataInizio = '{{ $cantiere->data_inizio }}';
                const dataFine = '{{ $cantiere->data_fine }}';

                if (dipendentiSelezionati && dipendentiSelezionati.length > 0) {
                    $.ajax({
                        url: '/azienda/controlla-conflitti-dipendenti',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            dipendenti: dipendentiSelezionati,
                            cantiere_id: cantiereId,
                            data_inizio: dataInizio,
                            data_fine: dataFine
                        },
                        success: function(response) {
                            $('.alert-conflitti').remove();

                            if (response.conflitti && response.conflitti.length > 0) {
                                let alertHtml = '<div class="alert alert-warning alert-conflitti mt-3">';
                                alertHtml += '<h6><i class="ri-warning-line"></i> Attenzione! Conflitti rilevati:</h6>';
                                alertHtml += '<ul>';

                                response.conflitti.forEach(function(conflitto) {
                                    alertHtml += '<li>' + conflitto + '</li>';
                                });

                                alertHtml += '</ul>';
                                alertHtml += '<small>Non sarà possibile assegnare questi dipendenti finché non risolvi i conflitti.</small>';
                                alertHtml += '</div>';

                                $('#dipendenti').closest('.card-body').append(alertHtml);
                                $('button[type="submit"]').prop('disabled', true);
                            } else {
                                $('button[type="submit"]').prop('disabled', false);
                            }
                        },
                        error: function() {
                            console.error('Errore nel controllo conflitti');
                        }
                    });
                } else {
                    $('.alert-conflitti').remove();
                    $('button[type="submit"]').prop('disabled', false);
                }
            });
        });
    </script>
    <script>
        // Variabili globali
        let giorniCantiere = [];
        let assegnazioniCorrente = {};
        let dipendentiData = {};

        // Inizializzazione
        document.addEventListener('DOMContentLoaded', function() {
            generaGiorniCantiere();
            caricaAssegnazioni();

            // Listener per cambio dipendente
            document.getElementById('selectDipendente').addEventListener('change', function() {
                const dipendenteId = this.value;
                if (dipendenteId) {
                    const option = this.options[this.selectedIndex];
                    dipendentiData[dipendenteId] = {
                        nome: option.getAttribute('data-nome'),
                        cognome: option.getAttribute('data-cognome'),
                        mansione: option.getAttribute('data-mansione')
                    };
                    aggiornaCalendario();
                }
            });
        });

        // Genera i giorni del cantiere
        function generaGiorniCantiere() {
            const dataInizio = new Date('{{ $cantiere->data_inizio }}');
            const dataFine = new Date('{{ $cantiere->data_fine }}');

            giorniCantiere = [];

            for (let data = new Date(dataInizio); data <= dataFine; data.setDate(data.getDate() + 1)) {
                giorniCantiere.push(new Date(data));
            }
        }

        // Apre la modal di assegnazione
        function apriModalAssegnazione() {
            $('#modalAssegnazione').modal('show');
            setTimeout(() => {
                generaCalendario();
            }, 300);
        }

        // Genera il calendario visuale
        function generaCalendario() {
            const container = document.getElementById('calendarioGiorni');
            container.innerHTML = '';

            giorniCantiere.forEach(data => {
                const dataString = data.toISOString().split('T')[0];
                const isWeekend = data.getDay() === 0 || data.getDay() === 6;

                const col = document.createElement('div');
                col.className = 'col-md-2 col-sm-3 col-4 mb-2';

                const giornoHtml = `
            <div class="giorno-item ${isWeekend ? 'weekend' : ''}" data-data="${dataString}">
                <div class="form-check">
                    <input class="form-check-input giorno-checkbox" type="checkbox"
                           id="giorno_${dataString}" data-data="${dataString}">
                    <label class="form-check-label w-100" for="giorno_${dataString}">
                        <small class="d-block">${data.toLocaleDateString('it-IT', { weekday: 'short' })}</small>
                        <strong>${data.getDate()}</strong>
                    </label>
                </div>
            </div>
        `;

                col.innerHTML = giornoHtml;
                container.appendChild(col);
            });
        }

        // Aggiorna il calendario in base al dipendente selezionato
        function aggiornaCalendario() {
            const dipendenteId = document.getElementById('selectDipendente').value;

            if (!dipendenteId) {
                document.querySelectorAll('.giorno-checkbox').forEach(cb => cb.checked = false);
                return;
            }

            // Carica le assegnazioni esistenti per questo dipendente
            fetch(`/azienda/cantiere/${{{ $cantiere->id }}}/dipendente/${dipendenteId}/giorni`)
                .then(response => response.json())
                .then(giorni => {
                    document.querySelectorAll('.giorno-checkbox').forEach(cb => {
                        cb.checked = giorni.includes(cb.getAttribute('data-data'));
                    });
                })
                .catch(error => {
                    console.error('Errore nel caricamento giorni:', error);
                });
        }

        // Seleziona tutti i giorni
        function selezionaTuttiGiorni() {
            document.querySelectorAll('.giorno-checkbox').forEach(cb => cb.checked = true);
        }

        // Deseleziona tutti i giorni
        function deselezionaTuttiGiorni() {
            document.querySelectorAll('.giorno-checkbox').forEach(cb => cb.checked = false);
        }

        // Salva le assegnazioni
        function salvaAssegnazioni() {
            const dipendenteId = document.getElementById('selectDipendente').value;

            if (!dipendenteId) {
                alert('Seleziona un dipendente');
                return;
            }

            const giorniSelezionati = [];
            document.querySelectorAll('.giorno-checkbox:checked').forEach(cb => {
                giorniSelezionati.push(cb.getAttribute('data-data'));
            });

            const data = {
                _token: '{{ csrf_token() }}',
                id_cantiere: {{ $cantiere->id }},
                id_dipendente: dipendenteId,
                giorni: giorniSelezionati,
                dipendente_info: dipendentiData[dipendenteId]
            };

            fetch('/azienda/salva-assegnazione-giorni', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert('Assegnazioni salvate con successo!');
                        caricaAssegnazioni();
                        $('#modalAssegnazione').modal('hide');
                    } else {
                        alert('Errore: ' + result.message);
                    }
                })
                .catch(error => {
                    console.error('Errore:', error);
                    alert('Errore nel salvataggio');
                });
        }

        // Carica le assegnazioni esistenti
        function caricaAssegnazioni() {
            fetch(`/azienda/cantiere/${{{ $cantiere->id }}}/assegnazioni`)
                .then(response => response.json())
                .then(assegnazioni => {
                    mostraAssegnazioni(assegnazioni);
                })
                .catch(error => {
                    console.error('Errore nel caricamento assegnazioni:', error);
                    document.getElementById('listaAssegnazioni').innerHTML =
                        '<p class="text-danger">Errore nel caricamento delle assegnazioni</p>';
                });
        }

        // Mostra le assegnazioni nella lista
        function mostraAssegnazioni(assegnazioni) {
            const container = document.getElementById('listaAssegnazioni');

            if (!assegnazioni || assegnazioni.length === 0) {
                container.innerHTML = '<p class="text-muted">Nessuna assegnazione presente</p>';
                return;
            }

            let html = '';

            // Raggruppa per dipendente
            const perDipendente = {};
            assegnazioni.forEach(ass => {
                const key = `${ass.nome} ${ass.cognome}`;
                if (!perDipendente[key]) {
                    perDipendente[key] = {
                        info: ass,
                        giorni: []
                    };
                }
                perDipendente[key].giorni.push(ass.data_lavoro);
            });

            Object.keys(perDipendente).forEach(dipendente => {
                const dati = perDipendente[dipendente];
                html += `
            <div class="card mb-2">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">${dipendente}</h6>
                            <small class="text-muted">${dati.info.mansione}</small>
                            <div class="mt-1">
                                <span class="badge bg-primary">${dati.giorni.length} giorni</span>
                            </div>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-outline-primary" onclick="modificaDipendente(${dati.info.id_dipendente})">
                                <i class="ri-edit-line"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="rimuoviDipendente(${dati.info.id_dipendente})">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
            });

            container.innerHTML = html;
        }

        // Modifica assegnazioni dipendente
        function modificaDipendente(dipendenteId) {
            document.getElementById('selectDipendente').value = dipendenteId;
            document.getElementById('selectDipendente').dispatchEvent(new Event('change'));
            apriModalAssegnazione();
        }

        // Rimuovi dipendente
        function rimuoviDipendente(dipendenteId) {
            if (!confirm('Sei sicuro di voler rimuovere tutte le assegnazioni per questo dipendente?')) {
                return;
            }

            fetch('/azienda/rimuovi-assegnazione-dipendente', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    id_cantiere: {{ $cantiere->id }},
                    id_dipendente: dipendenteId
                })
            })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert('Dipendente rimosso con successo!');
                        caricaAssegnazioni();
                    } else {
                        alert('Errore: ' + result.message);
                    }
                })
                .catch(error => {
                    console.error('Errore:', error);
                    alert('Errore nella rimozione');
                });
        }
    </script>

    <style>
        .giorno-item {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 8px;
            text-align: center;
            transition: all 0.2s;
        }

        .giorno-item:hover {
            background-color: #f8f9fa;
        }

        .giorno-item.weekend {
            background-color: #fff3cd;
            border-color: #ffeaa7;
        }

        .giorno-checkbox:checked + label {
            background-color: #198754;
            color: white;
            border-radius: 4px;
        }

        .form-check-label {
            cursor: pointer;
            margin-bottom: 0;
            padding: 4px;
        }
    </style>
    @include('azienda.common.footer')