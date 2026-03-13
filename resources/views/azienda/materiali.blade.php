@include('azienda.common.header')

<div class="page-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestione Materiale</h2>
            <div>
                <button class="btn btn-primary" onclick="aggiungiArticolo(1)">
                    <i class="ri-add-line"></i> Aggiungi Materiale
                </button>
                <button class="btn btn-info" onclick="gestisciSoglieRiordino()">
                    <i class="ri-notification-3-line"></i> Gestisci Soglie
                </button>
            </div>
        </div>

        <!-- Alert per articoli sotto soglia -->
        @if(isset($articoli_sotto_soglia) && count($articoli_sotto_soglia) > 0)
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="ri-alarm-warning-line me-2"></i>
                <strong>Attenzione!</strong> {{ count($articoli_sotto_soglia) }} articoli sono sotto la soglia di riordino.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <table class="table table-bordered datatable w-100">
            <thead>
            <tr>
                <th>Titolo</th>
                <th>Descrizione</th>
                <th>Quantità</th>
                <th>Costo Unitario</th>
                <th>Unità di Misura</th>
                <th>Quantità Impegnata</th>
                <th>Soglia Riordino</th>
                <th>Impegnato In</th>
                <th>Azioni</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($articoli as $articolo)
                @php
                    $quantita_disponibile = $articolo->quantita - $articolo->quantita_impegnata;
                    $sotto_soglia = $quantita_disponibile <= ($articolo->soglia_riordino ?? 0) && $articolo->soglia_riordino > 0;
                @endphp
                <tr class="{{ $sotto_soglia ? 'table-danger' : '' }}"
                    @if($sotto_soglia)
                        title="ATTENZIONE: Quantità sotto soglia di riordino!"
                    data-bs-toggle="tooltip"
                        @endif>
                    <td>
                        @if($sotto_soglia)
                            <i class="ri-alarm-warning-fill text-danger me-1"></i>
                        @endif
                        {{ $articolo->titolo }}
                    </td>
                    <td>{{ $articolo->descrizione }}</td>
                    <td>
                        <span class="{{ $sotto_soglia ? 'fw-bold text-danger' : '' }}">
                            {{ $articolo->quantita }}
                        </span>
                    </td>
                    <td>{{ number_format($articolo->costo, 2, ',', '.') }} €</td>
                    <td>{{ $articolo->unita_misura }}</td>
                    <td>{{ $articolo->quantita_impegnata }}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="me-2 {{ $sotto_soglia ? 'text-danger fw-bold' : '' }}">
                                {{ $articolo->soglia_riordino ?? 0 }}
                            </span>
                            <button class="btn btn-sm btn-outline-secondary"
                                    onclick="modificaSoglia({{ $articolo->id }}, {{ $articolo->soglia_riordino ?? 0 }}, '{{ $articolo->titolo }}')"
                                    title="Modifica soglia">
                                <i class="ri-edit-line"></i>
                            </button>
                        </div>
                    </td>
                    <td>
                        @php
                            $impegniArticolo = array_filter($impegni, function($impegno) use ($articolo) {
                                return $impegno->id_articolo == $articolo->id;
                            });
                        @endphp
                        @if(count($impegniArticolo) > 0)
                            <ul class="mb-0">
                                @foreach($impegniArticolo as $impegno)
                                    <li>{{ $impegno->nome_cantiere }} ({{ $impegno->quantita_impegnata }})</li>
                                @endforeach
                            </ul>
                        @else
                            <em class="text-muted">Nessun impegno</em>
                        @endif
                    </td>
                    <td>
                        @if($utente->solo_lettura != 1)
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-success" onclick="gestisciMagazzino({{ $articolo->id }}, 'carico')" title="Carico">
                                <i class="ri-add-line"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="gestisciMagazzino({{ $articolo->id }}, 'scarico')" title="Scarico">
                                <i class="ri-subtract-line"></i>
                            </button>
                            <button class="btn btn-sm btn-warning"
                                    onclick="modificaArticolo({{ $articolo->id }}, '{{ $articolo->titolo }}', '{{ $articolo->descrizione }}', {{ $articolo->quantita }}, '{{ $articolo->unita_misura }}', {{ $articolo->quantita_impegnata }}, {{ $articolo->costo }}, {{ $articolo->soglia_riordino ?? 0 }}, 1)"
                                    title="Modifica">
                                <i class="ri-edit-line"></i>
                            </button>
                        </div>
                        @endif
                        <form method="POST" action="{{ route('magazzino.gestisci') }}" style="display:inline-block;" class="mt-1">
                            @csrf
                            <input type="hidden" name="id" value="{{ $articolo->id }}">
                            <input type="hidden" name="tipologia" value="1">
                            <button type="submit" name="elimina" class="btn btn-sm btn-danger" value="1"
                                    onclick="return confirm('Vuoi eliminare questo articolo?')" title="Elimina">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal per aggiungere/modificare articolo -->
<div class="modal fade" id="modalAggiungi" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('magazzino.gestisci') }}">
                @csrf
                <input type="hidden" name="id" id="id_articolo">
                <input type="hidden" name="tipologia" id="tipologia_articolo">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Aggiungi Articolo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Titolo</label>
                        <input type="text" name="titolo" id="titolo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Descrizione</label>
                        <textarea name="descrizione" id="descrizione" class="form-control" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Quantità</label>
                        <input type="number" name="quantita" id="quantita" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Costo Unitario (€)</label>
                        <input type="number" step="0.01" name="costo" id="costo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Unità di Misura</label>
                        <input type="text" name="unita_misura" id="unita_misura" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Quantità Impegnata</label>
                        <input type="number" name="quantita_impegnata" id="quantita_impegnata" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Soglia Riordino <small class="text-muted">(Notifica quando la quantità scende sotto questo valore)</small></label>
                        <input type="number" name="soglia_riordino" id="soglia_riordino" class="form-control" min="0" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Chiudi</button>
                    <button type="submit" name="aggiungi" class="btn btn-primary" value="1" id="btnSalva">Salva</button>
                    <button type="submit" name="modifica" class="btn btn-warning" value="1" id="btnModifica" style="display:none;">Modifica</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal per modificare solo la soglia -->
<div class="modal fade" id="modalSoglia" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form method="POST" action="{{ route('magazzino.aggiorna-soglia') }}">
                @csrf
                <input type="hidden" name="id_articolo" id="id_articolo_soglia">
                <div class="modal-header">
                    <h6 class="modal-title">Modifica Soglia Riordino</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong id="nome_articolo_soglia"></strong></p>
                    <div class="mb-3">
                        <label>Soglia Riordino</label>
                        <input type="number" name="soglia_riordino" id="nuova_soglia" class="form-control" min="0" required>
                        <small class="text-muted">Riceverai una notifica quando la quantità scende sotto questo valore</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary">Salva Soglia</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal per Carico/Scarico -->
<div class="modal fade" id="modalMagazzino" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('magazzino.movimento') }}">
                @csrf
                <input type="hidden" name="id_articolo" id="id_articolo_mov">
                <input type="hidden" name="causale" id="causale_mov">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalMagazzinoTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label>Quantità</label>
                        <input type="number" name="quantita" id="quantita_mov" class="form-control" required min="1">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Chiudi</button>
                    <button type="submit" class="btn btn-primary">Conferma</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Gestisci Soglie Massive -->
<div class="modal fade" id="modalGestisciSoglie" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Gestione Soglie Riordino</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Imposta rapidamente le soglie per tutti i materiali</p>
                <form method="POST" action="{{ route('magazzino.aggiorna-soglie-massive') }}">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                            <tr>
                                <th>Materiale</th>
                                <th>Quantità Attuale</th>
                                <th>Soglia Riordino</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($articoli as $articolo)
                                <tr>
                                    <td>{{ $articolo->titolo }}</td>
                                    <td>{{ $articolo->quantita }}</td>
                                    <td>
                                        <input type="number" name="soglie[{{ $articolo->id }}]"
                                               value="{{ $articolo->soglia_riordino ?? 0 }}"
                                               class="form-control form-control-sm" min="0">
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annulla</button>
                        <button type="submit" class="btn btn-primary">Salva Tutte le Soglie</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    function gestisciMagazzino(id, tipo) {
        $('#id_articolo_mov').val(id);
        $('#causale_mov').val(tipo);

        if (tipo === 'carico') {
            $('#modalMagazzinoTitle').text('Carico Magazzino');
        } else {
            $('#modalMagazzinoTitle').text('Scarico Magazzino');
        }

        $('#modalMagazzino').modal('show');
    }

    function modificaArticolo(id, titolo, descrizione, quantita, unita_misura, quantita_impegnata, costo, soglia_riordino, tipologia) {
        $('#id_articolo').val(id);
        $('#titolo').val(titolo);
        $('#descrizione').val(descrizione);
        $('#quantita').val(quantita);
        $('#unita_misura').val(unita_misura);
        $('#quantita_impegnata').val(quantita_impegnata);
        $('#costo').val(costo);
        $('#soglia_riordino').val(soglia_riordino);
        $('#tipologia_articolo').val(tipologia);

        $('#modalTitle').text("Modifica Articolo");
        $('#btnSalva').hide();
        $('#btnModifica').show();
        $('#modalAggiungi').modal('show');
    }

    function aggiungiArticolo(tipologia) {
        $('#id_articolo').val('');
        $('#titolo').val('');
        $('#descrizione').val('');
        $('#quantita').val('');
        $('#unita_misura').val('');
        $('#quantita_impegnata').val('');
        $('#costo').val('');
        $('#soglia_riordino').val('0');
        $('#tipologia_articolo').val(tipologia);

        $('#modalTitle').text("Aggiungi Articolo");
        $('#btnModifica').hide();
        $('#btnSalva').show();
        $('#modalAggiungi').modal('show');
    }

    function modificaSoglia(id, soglia_attuale, nome) {
        $('#id_articolo_soglia').val(id);
        $('#nuova_soglia').val(soglia_attuale);
        $('#nome_articolo_soglia').text(nome);
        $('#modalSoglia').modal('show');
    }

    function gestisciSoglieRiordino() {
        $('#modalGestisciSoglie').modal('show');
    }
</script>

@include('azienda.common.footer')