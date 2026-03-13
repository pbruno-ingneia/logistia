@include('azienda.common.header')

<div class="page-content">
    <div class="container-fluid">
        <h2>Gestione Strumenti</h2>

        <button class="btn btn-primary mb-3" onclick="aggiungiArticolo(2)">Aggiungi Strumento</button>

        <table class="table table-bordered datatable w-100">
            <thead>
            <tr>
                <th>Titolo</th>
                <th>Descrizione</th>
                <th>Quantità</th>
                <th>Unità di Misura</th>
                <th>Quantità Impegnata</th>
                <th>Impegnato In</th>
                <th>Azioni</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($articoli as $articolo)
                <tr>
                    <td>{{ $articolo->titolo }}</td>
                    <td>{{ $articolo->descrizione }}</td>
                    <td>{{ $articolo->quantita }}</td>
                    <td>{{ $articolo->unita_misura }}</td>
                    <td>{{ $articolo->quantita_impegnata }}</td>
                    <td>
                        @php
                            $impegniArticolo = array_filter($impegni, function($impegno) use ($articolo) {
                                return $impegno->id_articolo == $articolo->id;
                            });
                        @endphp

                        @if(count($impegniArticolo) > 0)
                            <button
                                    class="btn btn-secondary btn-sm"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#collapseArticolo{{ $articolo->id }}"
                                    aria-expanded="false"
                                    aria-controls="collapseArticolo{{ $articolo->id }}">
                                Mostra Cantieri ({{ count($impegniArticolo) }})
                            </button>
                            <div class="collapse mt-2" id="collapseArticolo{{ $articolo->id }}">
                                <ul class="list-group">
                                    @foreach($impegniArticolo as $impegno)
                                        <li class="list-group-item">
                                            {{ $impegno->nome_cantiere }} ({{ $impegno->quantita_impegnata }})
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <em>Nessun impegno</em>
                        @endif
                    </td>



                    <td>
                        <button class="btn btn-sm btn-success" onclick="gestisciMagazzino({{ $articolo->id }}, 'carico')">+</button>
                        <button class="btn btn-sm btn-danger" onclick="gestisciMagazzino({{ $articolo->id }}, 'scarico')">-</button>

                        <button class="btn btn-sm btn-warning" onclick="modificaArticolo({{ $articolo->id }}, '{{ $articolo->titolo }}', '{{ $articolo->descrizione }}', {{ $articolo->quantita }}, '{{ $articolo->unita_misura }}', {{ $articolo->quantita_impegnata }}, 1)">Modifica</button>

                        <form method="POST" action="{{ route('magazzino.gestisci') }}" style="display:inline-block;">
                            @csrf
                            <input type="hidden" name="id" value="{{ $articolo->id }}">
                            <input type="hidden" name="tipologia" value="1">
                            <button type="submit" name="elimina" class="btn btn-sm btn-danger" value="1" onclick="return confirm('Vuoi eliminare questo articolo?')">Elimina</button>
                        </form>
                    </td>

                </tr>
            @endforeach
            </tbody>
        </table>
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

<!-- Modal per aggiungere/modificare articolo -->
<!-- Modal per aggiungere/modificare articolo -->
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
                        <label>Unità di Misura</label>
                        <input type="text" name="unita_misura" id="unita_misura" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Quantità Impegnata</label>
                        <input type="number" name="quantita_impegnata" id="quantita_impegnata" class="form-control" required>
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
<script>

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


function modificaArticolo(id, titolo, descrizione, quantita, unita_misura, quantita_impegnata, tipologia) {
        $('#id_articolo').val(id);
        $('#titolo').val(titolo);
        $('#descrizione').val(descrizione);
        $('#quantita').val(quantita);
        $('#unita_misura').val(unita_misura);
        $('#quantita_impegnata').val(quantita_impegnata);
        $('#tipologia_articolo').val(tipologia);

        // Cambia il titolo della modal
        $('#modalTitle').text("Modifica Articolo");

        // Nasconde il bottone "Aggiungi" e mostra "Modifica"
        $('#btnSalva').hide();
        $('#btnModifica').show();

        // Mostra la modal
        $('#modalAggiungi').modal('show');
    }

    function aggiungiArticolo(tipologia) {
        // Pulisce i campi della modal
        $('#id_articolo').val('');
        $('#titolo').val('');
        $('#descrizione').val('');
        $('#quantita').val('');
        $('#unita_misura').val('');
        $('#quantita_impegnata').val('');
        $('#tipologia_articolo').val(tipologia);

        // Cambia il titolo della modal
        $('#modalTitle').text("Aggiungi Articolo");

        // Nasconde il bottone "Modifica" e mostra "Aggiungi"
        $('#btnModifica').hide();
        $('#btnSalva').show();

        // Mostra la modal
        $('#modalAggiungi').modal('show');
    }
</script>

@include('azienda.common.footer')
