@include('azienda.common.header')

<div class="page-content">
    <div class="container-fluid">
        <!-- Titolo della pagina -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 text-light" style="color: #f8f9fa !important;">Gestione Ruoli</h4>
                </div>
            </div>
        </div>

        <!-- Bottone per aggiungere un nuovo ruolo -->
        <div class="row mb-3">
            <div class="col">
                <button class="btn btn-success" onclick="apriModalAggiunta()">
                    <i class="ri-add-line"></i> Aggiungi Ruolo
                </button>
            </div>
        </div>

        <!-- Tabella con lista ruoli -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead class="table-light">
                            <tr>
                                <th>Titolo</th>
                                <th style="width: 150px;">Azioni</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($ruoli as $ruolo)
                                <tr>
                                    <td>{{ $ruolo->titolo }}</td>
                                    <td>
                                        <button class="btn btn-warning btn-sm"
                                                onclick="apriModalModifica({{ $ruolo->id }}, '{{ $ruolo->titolo }}')">
                                            <i class="ri-edit-line"></i>
                                        </button>

                                        <form method="post" style="display:inline-block;">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $ruolo->id }}">
                                            <button type="submit" class="btn btn-danger btn-sm" name="elimina"
                                                    onclick="return confirm('Vuoi eliminare questo ruolo?')">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @if($ruoli->isEmpty())
                            <p class="text-muted text-center mt-3">Nessun ruolo presente.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal per aggiungere/modificare ruoli -->
<div class="modal fade" id="modalRuolo">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="titoloModalRuolo"  style="color: #f8f9fa !important;">Aggiungi Ruolo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                @csrf
                <input type="hidden" name="id" id="id_ruolo">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Titolo del Ruolo</label>
                        <input type="text" name="titolo" id="titolo_ruolo" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Chiudi</button>
                    <button type="submit" class="btn btn-success" id="btnSalvaRuolo" name="aggiungi">Salva</button>
                    <button type="submit" class="btn btn-warning" id="btnModificaRuolo" name="modifica" style="display:none;">Modifica</button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
    function apriModalAggiunta() {
        document.getElementById("titoloModalRuolo").innerText = "Aggiungi Ruolo";
        document.getElementById("btnSalvaRuolo").style.display = "inline-block";
        document.getElementById("btnModificaRuolo").style.display = "none";
        document.getElementById("id_ruolo").value = "";
        document.getElementById("titolo_ruolo").value = "";
        $('#modalRuolo').modal('show');
    }

    function apriModalModifica(id, titolo) {
        document.getElementById("titoloModalRuolo").innerText = "Modifica Ruolo";
        document.getElementById("btnSalvaRuolo").style.display = "none";
        document.getElementById("btnModificaRuolo").style.display = "inline-block";
        document.getElementById("id_ruolo").value = id;
        document.getElementById("titolo_ruolo").value = titolo;
        $('#modalRuolo').modal('show');
    }
</script>

@include('azienda.common.footer')
