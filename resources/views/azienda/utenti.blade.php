@include('azienda.common.header')

<div class="page-content">
    <div class="container-fluid">
        <h2>Gestione Utenti</h2>

        <!-- Pulsante per aggiungere un nuovo utente -->
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addUserModal">Aggiungi Utente</button>

        <table class="table table-bordered datatable w-100">
            <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Cognome</th>
                <th>Email</th>
                <th>Ruoli</th>
                {{--<th>Vista Operaio</th>
                <th>Responsabile</th>--}}
                <th>Costo Giornaliero (€)</th>
                <th width="150px">Permessi</th>
                <th>Azioni</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($utenti as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->nome }}</td>
                    <td>{{ $user->cognome }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ implode(', ', $user->ruoli) }}</td>
                    {{--<td class="text-center">
                        <div class="form-check form-switch">
                            <input class="form-check-input vista-operaio-checkbox" type="checkbox"
                                   data-id="{{ $user->id }}"
                                    {{ $user->vista_operaio == 1 ? 'checked' : '' }}>
                        </div>
                    </td>
                    <td class="text-center">
                        <div class="form-check form-switch">
                            <input class="form-check-input is-responsabile-checkbox" type="checkbox"
                                   data-id="{{ $user->id }}"
                                    {{ $user->is_responsabile == 1 ? 'checked' : '' }}>
                        </div>
                    </td>--}}
                    <td>{{ number_format($user->costo_giornaliero ?? 0, 2, ',', '.') }}</td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="gestisciPermessi({{ $user->id }}, '{{ $user->nome }} {{ $user->cognome }}')">
                            <i class="ri-shield-user-line"></i> Permessi
                        </button>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary"
                                onclick="openEditModalUser({{ $user->id }}, '{{ $user->nome }}', '{{ $user->cognome }}', '{{ $user->email }}', '{{ $user->costo_giornaliero }}', '{{ implode(',', $user->ruoli) }}')">
                            <i class="ri-edit-line"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal" onclick="setUtenteIdToDelete({{ $user->id }})">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <!-- Modal per aggiungere un nuovo utente -->
        <div class="modal fade" id="addUserModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST">
                        @csrf
                        <input type="hidden" name="crea_utente" value="1">
                        <div class="modal-header">
                            <h5 class="modal-title">Aggiungi Nuovo Utente</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label>Nome</label>
                                <input type="text" name="nome" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Cognome</label>
                                <input type="text" name="cognome" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Costo Giornaliero (€)</label>
                                <input type="number" step="0.01" name="costo_giornaliero" class="form-control" placeholder="Es: 80.00">
                            </div>
                            <div class="mb-3">
                                <label>Ruoli</label>
                                <select class="form-control select2-modal" name="id_ruolo[]" multiple="multiple">
                                    @foreach ($ruoli as $ruolo)
                                        <option value="{{ $ruolo->id }}">{{ $ruolo->titolo }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Chiudi</button>
                            <input type="submit" class="btn btn-primary" value="Crea Utente">
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal per modificare un utente -->
        <div class="modal fade" id="editUserModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST">
                        @csrf
                        <input type="hidden" name="modifica_utente" value="1">
                        <input type="hidden" name="id_utente" id="edit_id_utente">
                        <div class="modal-header">
                            <h5 class="modal-title">Modifica Utente</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label>Nome</label>
                                <input type="text" name="nome" id="edit_nome" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Cognome</label>
                                <input type="text" name="cognome" id="edit_cognome" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" name="email" id="edit_email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Costo Giornaliero (€)</label>
                                <input type="number" step="0.01" name="costo_giornaliero" id="edit_costo_giornaliero" class="form-control" placeholder="Es: 80.00">
                            </div>
                            <div class="mb-3">
                                <label>Ruoli</label>
                                <select class="form-control select2-modal" name="id_ruolo[]" id="edit_id_ruolo" multiple="multiple">
                                    @foreach ($ruoli as $ruolo)
                                        <option value="{{ $ruolo->id }}">{{ $ruolo->titolo }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Chiudi</button>
                            <input type="submit" class="btn btn-primary" value="Salva Modifiche">
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal per eliminare un utente -->
        <div class="modal fade" id="deleteUserModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST">
                        @csrf
                        <input type="hidden" name="elimina_utente" value="1">
                        <input type="hidden" name="id_utente" id="delete_id_utente">
                        <div class="modal-header">
                            <h5 class="modal-title">Conferma Eliminazione</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>Sei sicuro di voler eliminare questo utente?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annulla</button>
                            <input type="submit" class="btn btn-danger" value="Elimina">
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Gestione Permessi -->
        <div class="modal fade" id="modalPermessi" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST" action="{{ url('/azienda/utente/aggiorna-permessi') }}">
                        @csrf
                        <input type="hidden" name="id_utente" id="permessi_id_utente">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title">
                                <i class="ri-shield-user-line me-2"></i>
                                Gestione Permessi - <span id="nome_utente_permessi"></span>
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <!-- Colonna Sinistra -->
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">
                                        <i class="ri-lock-line me-1"></i>
                                        Livello di Accesso
                                    </h6>

                                    <div class="card border-danger mb-3">
                                        <div class="card-body p-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="solo_lettura" id="solo_lettura">
                                                <label class="form-check-label fw-bold text-danger" for="solo_lettura">
                                                    <i class="ri-eye-line me-1"></i>
                                                    Solo Lettura
                                                </label>
                                            </div>
                                            <small class="text-muted">L'utente può solo visualizzare, non modificare</small>
                                        </div>
                                    </div>

                                    <div class="card border-warning mb-3">
                                        <div class="card-body p-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="visualizza_costi" id="visualizza_costi">
                                                <label class="form-check-label fw-bold text-warning" for="visualizza_costi">
                                                    <i class="ri-money-euro-circle-line me-1"></i>
                                                    Visualizza Costi
                                                </label>
                                            </div>
                                            <small class="text-muted">Può vedere prezzi, costi e informazioni finanziarie</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Colonna Destra -->
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">
                                        <i class="ri-settings-3-line me-1"></i>
                                        Aree di Gestione
                                    </h6>

                                    <div class="card border-primary mb-3">
                                        <div class="card-body p-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="gestione_cantieri" id="gestione_cantieri">
                                                <label class="form-check-label fw-bold text-primary" for="gestione_cantieri">
                                                    <i class="ri-building-4-line me-1"></i>
                                                    Gestione Cantieri
                                                </label>
                                            </div>
                                            <small class="text-muted">Può creare, modificare ed eliminare cantieri</small>
                                        </div>
                                    </div>

                                    <div class="card border-success mb-3">
                                        <div class="card-body p-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="gestione_mezzi" id="gestione_mezzi">
                                                <label class="form-check-label fw-bold text-success" for="gestione_mezzi">
                                                    <i class="ri-truck-line me-1"></i>
                                                    Gestione Mezzi
                                                </label>
                                            </div>
                                            <small class="text-muted">Può gestire i mezzi aziendali</small>
                                        </div>
                                    </div>

                                    <div class="card border-info mb-3">
                                        <div class="card-body p-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="gestione_magazzino" id="gestione_magazzino">
                                                <label class="form-check-label fw-bold text-info" for="gestione_magazzino">
                                                    <i class="ri-archive-line me-1"></i>
                                                    Gestione Magazzino
                                                </label>
                                            </div>
                                            <small class="text-muted">Può gestire materiali e strumenti</small>
                                        </div>
                                    </div>

                                    <div class="card border-secondary mb-3">
                                        <div class="card-body p-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="gestione_utenti" id="gestione_utenti">
                                                <label class="form-check-label fw-bold text-secondary" for="gestione_utenti">
                                                    <i class="ri-user-settings-line me-1"></i>
                                                    Gestione Utenti
                                                </label>
                                            </div>
                                            <small class="text-muted">Può creare e modificare altri utenti</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-warning mt-3">
                                <i class="ri-information-line me-2"></i>
                                <strong>Nota:</strong> Se "Solo Lettura" è attivo, tutti gli altri permessi saranno ignorati.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annulla</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line me-1"></i>
                                Salva Permessi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openEditModalUser(id, nome, cognome, email, costo_giornaliero, ruoli) {
        $('#editUserModal').modal('show');
        $('#edit_id_utente').val(id);
        $('#edit_nome').val(nome);
        $('#edit_cognome').val(cognome);
        $('#edit_email').val(email);
        $('#edit_costo_giornaliero').val(costo_giornaliero);

        let ruoliArray = ruoli ? ruoli.split(',') : [];
        $('#edit_id_ruolo').val(ruoliArray).trigger('change');
    }

    function setUtenteIdToDelete(id) {
        $('#delete_id_utente').val(id);
    }

    function gestisciPermessi(userId, nomeCompleto) {
        // Carica i permessi attuali dell'utente
        fetch(`/azienda/utente/get-permessi/${userId}`)
            .then(response => response.json())
            .then(data => {
                // Popola il modal con i dati
                document.getElementById('permessi_id_utente').value = userId;
                document.getElementById('nome_utente_permessi').textContent = nomeCompleto;

                // Imposta i checkbox
                document.getElementById('solo_lettura').checked = data.solo_lettura == 1;
                document.getElementById('gestione_cantieri').checked = data.gestione_cantieri == 1;
                document.getElementById('gestione_mezzi').checked = data.gestione_mezzi == 1;
                document.getElementById('gestione_magazzino').checked = data.gestione_magazzino == 1;
                document.getElementById('gestione_utenti').checked = data.gestione_utenti == 1;
                document.getElementById('visualizza_costi').checked = data.visualizza_costi == 1;

                // Mostra il modal
                new bootstrap.Modal(document.getElementById('modalPermessi')).show();
            })
            .catch(error => {
                console.error('Errore:', error);
                alert('Errore nel caricamento dei permessi');
            });
    }

    document.addEventListener("DOMContentLoaded", function () {
        // Imposta il token CSRF per AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        // Gestione Vista Operaio
        document.querySelectorAll('.vista-operaio-checkbox').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                let userId = this.getAttribute('data-id');
                let isChecked = this.checked ? 1 : 0;

                fetch("{{ url('/azienda/utente/update-vista-operaio') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ id: userId, vista_operaio: isChecked })
                })
                    .then(response => response.json())
                    .then(data => {
                        console.log(data.message);
                    })
                    .catch(error => {
                        console.error("Errore AJAX:", error);
                        this.checked = !this.checked; // Ripristina stato precedente
                        alert("Errore durante l'aggiornamento.");
                    });
            });
        });

        // Gestione Responsabile
        document.querySelectorAll('.is-responsabile-checkbox').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                let userId = this.getAttribute('data-id');
                let isChecked = this.checked ? 1 : 0;

                fetch("{{ url('/azienda/utente/update-responsabile') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ id: userId, is_responsabile: isChecked })
                })
                    .then(response => response.json())
                    .then(data => {
                        console.log(data.message);
                    })
                    .catch(error => {
                        console.error("Errore AJAX:", error);
                        this.checked = !this.checked; // Ripristina stato precedente
                        alert("Errore durante l'aggiornamento.");
                    });
            });
        });

        // Inizializza Select2
        if ($('.select2-modal').length > 0) {
            $('.select2-modal').select2({
                dropdownParent: $('.modal')
            });

            $('.modal').on('shown.bs.modal', function () {
                $(this).find('.select2-modal').select2({
                    dropdownParent: $(this)
                });
            });
        }

        // Gestione Solo Lettura - disabilita altri permessi se attivo
        document.getElementById('solo_lettura').addEventListener('change', function() {
            const otherCheckboxes = ['gestione_cantieri', 'gestione_mezzi', 'gestione_magazzino', 'gestione_utenti'];

            if (this.checked) {
                otherCheckboxes.forEach(id => {
                    document.getElementById(id).checked = false;
                    document.getElementById(id).disabled = true;
                });
            } else {
                otherCheckboxes.forEach(id => {
                    document.getElementById(id).disabled = false;
                });
            }
        });
    });
</script>

<style>
    .form-switch .form-check-input {
        width: 2em;
        height: 1.2em;
    }

    .card {
        transition: all 0.2s;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
</style>

@include('azienda.common.footer')