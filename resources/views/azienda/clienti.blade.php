@include('azienda.common.header')

<div class="page-content">
    <div class="container-fluid">
        <!-- Titolo pagina -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">👥 Gestione Clienti</h4>
                    <div class="page-title-right">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCreaCliente">
                            <i class="ri-add-line"></i> Nuovo Cliente
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiche rapide -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-primary">{{ count($clienti) }}</h3>
                        <p class="text-muted mb-0">Clienti Totali</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-success">{{ $clienti->filter(function($c) { return $c->email; })->count() }}</h3>
                        <p class="text-muted mb-0">Con Email</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-info">{{ $clienti->filter(function($c) { return $c->telefono; })->count() }}</h3>
                        <p class="text-muted mb-0">Con Telefono</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-warning">{{ $clienti->filter(function($c) { return $c->partita_iva; })->count() }}</h3>
                        <p class="text-muted mb-0">Con P.IVA</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabella clienti -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title text-white mb-0">
                            <i class="ri-user-2-line me-2"></i>Elenco Clienti
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered datatable w-100">
                                <thead class="table-light">
                                <tr>
                                    <th>Ragione Sociale</th>
                                    <th>Indirizzo</th>
                                    <th>Contatti</th>
                                    <th>P.IVA / C.F.</th>
                                    <th>Data Creazione</th>
                                    <th class="no-sort">Azioni</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($clienti as $cliente)
                                    <tr>
                                        <td>
                                            <strong class="text-primary">{{ $cliente->ragione_sociale }}</strong>
                                        </td>
                                        <td>
                                            @if($cliente->indirizzo)
                                                <small class="text-muted">{{ Str::limit($cliente->indirizzo, 50) }}</small>
                                            @else
                                                <span class="text-muted">Non specificato</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($cliente->telefono)
                                                <div class="mb-1">
                                                    <i class="ri-phone-line text-success"></i>
                                                    <a href="tel:{{ $cliente->telefono }}" class="text-decoration-none">{{ $cliente->telefono }}</a>
                                                </div>
                                            @endif
                                            @if($cliente->email)
                                                <div>
                                                    <i class="ri-mail-line text-info"></i>
                                                    <a href="mailto:{{ $cliente->email }}" class="text-decoration-none">{{ $cliente->email }}</a>
                                                </div>
                                            @endif
                                            @if(!$cliente->telefono && !$cliente->email)
                                                <span class="text-muted">Non specificati</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($cliente->partita_iva)
                                                <div><strong>P.IVA:</strong> {{ $cliente->partita_iva }}</div>
                                            @endif
                                            @if($cliente->codice_fiscale)
                                                <div><strong>C.F.:</strong> {{ $cliente->codice_fiscale }}</div>
                                            @endif
                                            @if(!$cliente->partita_iva && !$cliente->codice_fiscale)
                                                <span class="text-muted">Non specificati</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ date('d/m/Y', strtotime($cliente->created_at)) }}<br>
                                                {{ date('H:i', strtotime($cliente->created_at)) }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-info btn-sm" onclick="visualizzaCliente({{ $cliente->id }})" title="Visualizza">
                                                    <i class="ri-eye-line"></i>
                                                </button>
                                                <button class="btn btn-warning btn-sm" onclick="modificaCliente({{ $cliente->id }})" title="Modifica">
                                                    <i class="ri-edit-line"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm" onclick="eliminaCliente({{ $cliente->id }})" title="Elimina">
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

<!-- Modal Crea Cliente -->
<div class="modal fade" id="modalCreaCliente" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title text-white">
                        <i class="ri-user-add-line me-2"></i>Nuovo Cliente
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-label">Ragione Sociale *</label>
                            <input type="text" name="ragione_sociale" class="form-control" required placeholder="Es. Acme Transport S.r.l.">
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label class="form-label">Indirizzo Completo</label>
                            <textarea name="indirizzo" class="form-control" rows="3" placeholder="Via, numero civico, CAP, città, provincia"></textarea>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label">Telefono</label>
                            <input type="text" name="telefono" class="form-control" placeholder="Es. +39 123 456 7890">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="info@cliente.it">
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label">Partita IVA</label>
                            <input type="text" name="partita_iva" class="form-control" placeholder="IT12345678901">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Codice Fiscale</label>
                            <input type="text" name="codice_fiscale" class="form-control" placeholder="ABCDEF12G34H567I">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <input type="hidden" name="crea_cliente" value="1">
                    <button type="submit" class="btn btn-success">
                        <i class="ri-save-line"></i> Crea Cliente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Visualizza Cliente -->
<div class="modal fade" id="modalVisualizzaCliente" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white">
                    <i class="ri-user-line me-2"></i>Dettagli Cliente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <h4 id="visualizza_ragione_sociale" class="text-primary"></h4>
                        <hr>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">📍 Indirizzo</h6>
                        <p id="visualizza_indirizzo" class="mb-3"></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">📞 Contatti</h6>
                        <p id="visualizza_contatti" class="mb-3"></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">🏢 Dati Fiscali</h6>
                        <p id="visualizza_fiscali" class="mb-3"></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">📅 Creazione</h6>
                        <p id="visualizza_creazione" class="mb-3"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                <button type="button" class="btn btn-warning" onclick="modificaClienteDaVisualizza()">
                    <i class="ri-edit-line"></i> Modifica
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Modifica Cliente -->
<div class="modal fade" id="modalModificaCliente" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post">
                @csrf
                <input type="hidden" id="modifica_id_cliente" name="id_cliente">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title text-white">
                        <i class="ri-edit-line me-2"></i>Modifica Cliente
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-label">Ragione Sociale *</label>
                            <input type="text" id="modifica_ragione_sociale" name="ragione_sociale" class="form-control" required>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label class="form-label">Indirizzo Completo</label>
                            <textarea id="modifica_indirizzo" name="indirizzo" class="form-control" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label">Telefono</label>
                            <input type="text" id="modifica_telefono" name="telefono" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" id="modifica_email" name="email" class="form-control">
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label">Partita IVA</label>
                            <input type="text" id="modifica_partita_iva" name="partita_iva" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Codice Fiscale</label>
                            <input type="text" id="modifica_codice_fiscale" name="codice_fiscale" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <input type="hidden" name="modifica_cliente" value="1">
                    <button type="submit" class="btn btn-warning">
                        <i class="ri-save-line"></i> Salva Modifiche
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Elimina Cliente -->
<div class="modal fade" id="modalEliminaCliente" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                @csrf
                <input type="hidden" id="elimina_id_cliente" name="id_cliente">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white">
                        <i class="ri-delete-bin-line me-2"></i>Conferma Eliminazione
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <i class="ri-delete-bin-line text-danger mb-3" style="font-size: 48px;"></i>
                        <h5>Sei sicuro di voler eliminare questo cliente?</h5>
                        <p class="text-muted">Il cliente <strong id="elimina_nome_cliente"></strong> verrà eliminato definitivamente.</p>
                        <div class="alert alert-danger">
                            <strong>Attenzione!</strong> Questa operazione eliminerà anche tutti gli ordini di trasporto associati a questo cliente.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <input type="hidden" name="elimina_cliente" value="1">
                    <button type="submit" class="btn btn-danger">
                        <i class="ri-delete-bin-line"></i> Elimina Definitivamente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Dati clienti per JavaScript
    const clientiData = @json($clienti);

    function visualizzaCliente(idCliente) {
        const cliente = clientiData.find(c => c.id == idCliente);

        if (cliente) {
            document.getElementById('visualizza_ragione_sociale').textContent = cliente.ragione_sociale;
            document.getElementById('visualizza_indirizzo').textContent = cliente.indirizzo || 'Non specificato';

            // Contatti
            let contatti = [];
            if (cliente.telefono) contatti.push('📞 ' + cliente.telefono);
            if (cliente.email) contatti.push('✉️ ' + cliente.email);
            document.getElementById('visualizza_contatti').innerHTML = contatti.length > 0 ? contatti.join('<br>') : 'Non specificati';

            // Dati fiscali
            let fiscali = [];
            if (cliente.partita_iva) fiscali.push('P.IVA: ' + cliente.partita_iva);
            if (cliente.codice_fiscale) fiscali.push('C.F.: ' + cliente.codice_fiscale);
            document.getElementById('visualizza_fiscali').innerHTML = fiscali.length > 0 ? fiscali.join('<br>') : 'Non specificati';

            // Data creazione
            const dataCreazione = new Date(cliente.created_at);
            document.getElementById('visualizza_creazione').textContent = dataCreazione.toLocaleDateString('it-IT') + ' alle ' + dataCreazione.toLocaleTimeString('it-IT');

            // Memorizza l'ID per eventuali azioni
            window.clienteCorrente = cliente.id;

            new bootstrap.Modal(document.getElementById('modalVisualizzaCliente')).show();
        }
    }

    function modificaClienteDaVisualizza() {
        // Chiudi il modal visualizza
        bootstrap.Modal.getInstance(document.getElementById('modalVisualizzaCliente')).hide();
        // Apri il modal modifica
        setTimeout(() => {
            modificaCliente(window.clienteCorrente);
        }, 500);
    }

    function modificaCliente(idCliente) {
        const cliente = clientiData.find(c => c.id == idCliente);

        if (cliente) {
            document.getElementById('modifica_id_cliente').value = cliente.id;
            document.getElementById('modifica_ragione_sociale').value = cliente.ragione_sociale;
            document.getElementById('modifica_indirizzo').value = cliente.indirizzo || '';
            document.getElementById('modifica_telefono').value = cliente.telefono || '';
            document.getElementById('modifica_email').value = cliente.email || '';
            document.getElementById('modifica_partita_iva').value = cliente.partita_iva || '';
            document.getElementById('modifica_codice_fiscale').value = cliente.codice_fiscale || '';

            new bootstrap.Modal(document.getElementById('modalModificaCliente')).show();
        }
    }

    function eliminaCliente(idCliente) {
        const cliente = clientiData.find(c => c.id == idCliente);

        if (cliente) {
            document.getElementById('elimina_id_cliente').value = cliente.id;
            document.getElementById('elimina_nome_cliente').textContent = cliente.ragione_sociale;

            new bootstrap.Modal(document.getElementById('modalEliminaCliente')).show();
        }
    }
</script>

@include('azienda.common.footer')