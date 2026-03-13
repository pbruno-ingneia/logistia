@include('azienda.common.header')

<div class="page-content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0" style="color: #f8f9fa !important;">Anagrafica Mezzi</h5>
                <div>
                    @if($utente->solo_lettura != 1)
                        <button class="btn btn-success me-2 d-none" onclick="sincronizzaMezzi()">
                            <i class="ri-refresh-line"></i> Sincronizza da Flotta
                        </button>
                        <button class="btn btn-warning me-2" onclick="aggiornaKmMezzi()">
                            <i class="ri-dashboard-line"></i> Aggiorna Km
                        </button>
                        <button class="btn btn-light" onclick="apriModalAggiunta()">
                            <i class="ri-add-line"></i> Aggiungi Mezzo
                        </button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                    <tr>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Targa</th>
                        <th>Anno</th>
                        <th>Km Attuali</th>
                        <th>Stato</th>
                        <th>Origine</th>
                        <th>Azioni</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($mezzi as $mezzo)
                        <tr>
                            <td>
                                {{ $mezzo->nome }}
                                @if($mezzo->flotta_in_cloud == 1)
                                    <i class="ri-wifi-line text-success ms-1" title="Connesso a Flotta in Cloud"></i>
                                @endif
                            </td>
                            <td>{{ $mezzo->tipo }}</td>
                            <td>{{ $mezzo->targa }}</td>
                            <td>{{ $mezzo->anno_immatricolazione }}</td>
                            <td>
                                <strong>{{ number_format($mezzo->km_attuali ?? 0) }} km</strong>
                                @if($mezzo->flotta_in_cloud == 1)
                                    <br><small class="text-muted">Auto-aggiornato</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $mezzo->stato == 'Disponibile' ? 'success' : ($mezzo->stato == 'In uso' ? 'primary' : 'warning') }}">
                                    {{ $mezzo->stato }}
                                </span>
                            </td>
                            <td>
                                @if($mezzo->flotta_in_cloud == 1)
                                    <span class="badge bg-info">
                                        <i class="ri-cloud-line"></i> Flotta Cloud
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="ri-user-line"></i> Manuale
                                    </span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ url('/azienda/mezzo/'.$mezzo->id) }}" class="btn btn-info btn-sm">
                                    <i class="ri-eye-line"></i>
                                </a>
                                @if($utente->solo_lettura != 1)
                                    <button class="btn btn-warning btn-sm"
                                            onclick="apriModalModifica({{ $mezzo->id }}, '{{ $mezzo->nome }}', '{{ $mezzo->tipo }}', '{{ $mezzo->targa }}', '{{ $mezzo->anno_immatricolazione }}', '{{ $mezzo->stato }}')">
                                        Modifica
                                    </button>
                                    <form method="post" style="display:inline-block;">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $mezzo->id }}">
                                        <button type="submit" class="btn btn-danger btn-sm" name="elimina" value="1" onclick="return confirm('Eliminare questo mezzo?')">
                                            <i class="ri-delete-bin-line"></i>
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
    </div>
</div>

<!-- Modal per aggiungere/modificare un mezzo -->
<div class="modal fade" id="modalMezzo">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="titoloModalMezzo">Aggiungi Mezzo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ url('/azienda/mezzi') }}" method="POST">
                <input type="hidden" name="id" id="id_mezzo">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nome</label>
                        <input type="text" name="nome" id="nome_mezzo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Tipo</label>
                        <input type="text" name="tipo" id="tipo_mezzo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Targa</label>
                        <input type="text" name="targa" id="targa_mezzo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Anno Immatricolazione</label>
                        <input type="number" name="anno_immatricolazione" id="anno_mezzo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Stato</label>
                        <select name="stato" id="stato_mezzo" class="form-control">
                            <option value="Disponibile">Disponibile</option>
                            <option value="In uso">In uso</option>
                            <option value="Manutenzione">Manutenzione</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Chiudi</button>
                    <button type="submit" class="btn btn-primary" id="btnSalvaMezzo" name="aggiungi" value="1">Salva</button>
                    <button type="submit" class="btn btn-warning" id="btnModificaMezzo" name="modifica" value="1" style="display:none;">Modifica</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function apriModalAggiunta() {
        document.getElementById("titoloModalMezzo").innerText = "Aggiungi Mezzo";
        document.getElementById("btnSalvaMezzo").style.display = "inline-block";
        document.getElementById("btnModificaMezzo").style.display = "none";
        document.getElementById("id_mezzo").value = "";
        document.getElementById("nome_mezzo").value = "";
        document.getElementById("tipo_mezzo").value = "";
        document.getElementById("targa_mezzo").value = "";
        document.getElementById("anno_mezzo").value = "";
        document.getElementById("stato_mezzo").value = "Disponibile";

        var modal = new bootstrap.Modal(document.getElementById('modalMezzo'));
        modal.show();
    }

    function apriModalModifica(id, nome, tipo, targa, anno, stato) {
        document.getElementById("titoloModalMezzo").innerText = "Modifica Mezzo";
        document.getElementById("btnSalvaMezzo").style.display = "none";
        document.getElementById("btnModificaMezzo").style.display = "inline-block";
        document.getElementById("id_mezzo").value = id;
        document.getElementById("nome_mezzo").value = nome;
        document.getElementById("tipo_mezzo").value = tipo;
        document.getElementById("targa_mezzo").value = targa;
        document.getElementById("anno_mezzo").value = anno;
        document.getElementById("stato_mezzo").value = stato;

        var modal = new bootstrap.Modal(document.getElementById('modalMezzo'));
        modal.show();
    }

    function sincronizzaMezzi() {
        if (!confirm('Vuoi sincronizzare i mezzi da Flotta in Cloud?')) {
            return;
        }

        const btn = event.target;
        btn.disabled = true;
        btn.innerHTML = '<i class="ri-loader-2-line"></i> Sincronizzando...';

        fetch('/azienda/sincronizza-mezzi')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`✅ ${data.message}`);
                    location.reload();
                } else {
                    alert(`❌ Errore: ${data.message}`);
                }
            })
            .catch(error => {
                alert(`❌ Errore: ${error.message}`);
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="ri-refresh-line"></i> Sincronizza da Flotta';
            });
    }

    function aggiornaKmMezzi() {
        if (!confirm('Vuoi aggiornare i km di tutti i mezzi?')) {
            return;
        }

        const btn = event.target;
        btn.disabled = true;
        btn.innerHTML = '<i class="ri-loader-2-line"></i> Aggiornando...';

        fetch('/azienda/aggiorna-km')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let messaggio = `✅ ${data.message}`;
                    if (data.dettagli && data.dettagli.length > 0) {
                        messaggio += '\n\nDettagli:';
                        data.dettagli.forEach(item => {
                            messaggio += `\n• ${item.nome}: ${item.km_precedenti.toLocaleString()} → ${item.km_attuali.toLocaleString()} km`;
                        });
                    }
                    alert(messaggio);
                    location.reload();
                } else {
                    alert(`❌ Errore: ${data.message}`);
                }
            })
            .catch(error => {
                alert(`❌ Errore: ${error.message}`);
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="ri-dashboard-line"></i> Aggiorna Km';
            });
    }
</script>

@include('azienda.common.footer')