@include('azienda.common.header')

<div class="page-content">
    <div class="container-fluid">

        <!-- Header -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">📱 Gestione Dispositivi Tracking</h4>
                    <div class="page-title-right">
                        <a href="/azienda/tracking" class="btn btn-secondary">
                            <i class="ri-arrow-left-line"></i> Torna alla Mappa
                        </a>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNuovoDispositivo">
                            <i class="ri-add-line"></i> Nuovo Dispositivo
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Istruzioni -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-info">
                    <h5><i class="ri-information-line"></i> Come funziona</h5>
                    <ol class="mb-0">
                        <li><strong>Crea un dispositivo</strong> e associalo a un mezzo/furgone</li>
                        <li><strong>Copia il token</strong> generato</li>
                        <li><strong>Sul tablet del furgone</strong>, apri Logistia e inserisci il token nelle impostazioni</li>
                        <li><strong>Al primo avvio</strong>, l'autista inserirà i km dal contachilometri</li>
                        <li><strong>Da quel momento</strong>, il tracking è completamente automatico!</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Lista Dispositivi -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Dispositivi Registrati</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                <tr>
                                    <th>Nome</th>
                                    <th>Mezzo Associato</th>
                                    <th>Token</th>
                                    <th>Stato</th>
                                    <th>Ultimo Contatto</th>
                                    <th>Azioni</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($dispositivi as $dispositivo)
                                    <tr class="{{ !$dispositivo->is_active ? 'table-secondary' : '' }}">
                                        <td>
                                            <strong>{{ $dispositivo->nome }}</strong>
                                            @if(!$dispositivo->is_active)
                                                <span class="badge bg-secondary">Disattivato</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($dispositivo->nome_mezzo)
                                                <span class="badge bg-primary">{{ $dispositivo->nome_mezzo }}</span>
                                                <br>
                                                <small class="text-muted">{{ $dispositivo->targa }}</small>
                                            @else
                                                <span class="text-warning">
                                                        <i class="ri-alert-line"></i> Non associato
                                                    </span>
                                                <br>
                                                <button class="btn btn-sm btn-outline-primary mt-1"
                                                        onclick="mostraAssociaMezzo({{ $dispositivo->id }})">
                                                    Associa Mezzo
                                                </button>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm" style="max-width: 280px;">
                                                <input type="text" class="form-control font-monospace" style="font-size: 11px;"
                                                       value="{{ $dispositivo->device_token }}"
                                                       id="token-{{ $dispositivo->id }}" readonly>
                                                <button class="btn btn-outline-secondary"
                                                        onclick="copiaToken({{ $dispositivo->id }})"
                                                        title="Copia">
                                                    <i class="ri-file-copy-line"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td>
                                            @if($dispositivo->configurato)
                                                <span class="badge bg-success">
                                                        <i class="ri-check-line"></i> Configurato
                                                    </span>
                                            @else
                                                <span class="badge bg-warning">
                                                        <i class="ri-time-line"></i> In attesa setup
                                                    </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($dispositivo->ultimo_heartbeat)
                                                @php
                                                    $diff = now()->diffInMinutes($dispositivo->ultimo_heartbeat);
                                                @endphp
                                                @if($diff < 5)
                                                    <span class="text-success">
                                                            <i class="ri-wifi-line"></i> Online
                                                        </span>
                                                @elseif($diff < 60)
                                                    <span class="text-warning">
                                                            {{ $diff }} min fa
                                                        </span>
                                                @else
                                                    <span class="text-danger">
                                                            {{ \Carbon\Carbon::parse($dispositivo->ultimo_heartbeat)->diffForHumans() }}
                                                        </span>
                                                @endif
                                            @else
                                                <span class="text-muted">Mai connesso</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($dispositivo->is_active)
                                                <button class="btn btn-sm btn-danger"
                                                        onclick="disattivaDispositivo({{ $dispositivo->id }})">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <i class="ri-device-line fs-1 text-muted mb-2 d-block"></i>
                                            <p class="text-muted">Nessun dispositivo registrato</p>
                                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuovoDispositivo">
                                                <i class="ri-add-line"></i> Crea il primo dispositivo
                                            </button>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal Nuovo Dispositivo -->
<div class="modal fade" id="modalNuovoDispositivo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuovo Dispositivo Tracking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nome Dispositivo *</label>
                    <input type="text" class="form-control" id="nomeDispositivo"
                           placeholder="Es: Tablet Furgone Milano">
                </div>
                <div class="mb-3">
                    <label class="form-label">Associa a Mezzo *</label>
                    <select class="form-select" id="mezzoDispositivo" required>
                        <option value="">-- Seleziona un mezzo --</option>
                        @foreach($mezziLiberi as $mezzo)
                            <option value="{{ $mezzo->id }}">
                                {{ $mezzo->nome }} - {{ $mezzo->targa }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Associa a Utente (autista) *</label>
                    <select class="form-select" id="utenteDispositivo" required>
                        <option value="">-- Seleziona un utente --</option>
                        @foreach($utentiAzienda as $u)
                            <option value="{{ $u->id }}">
                                {{ $u->nome }} {{ $u->cognome }} - {{ $u->email }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Quando questo utente fa login, il tracking parte automaticamente</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                <button type="button" class="btn btn-success" onclick="creaDispositivo()">
                    <i class="ri-add-line"></i> Crea Dispositivo
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Token Generato -->
<div class="modal fade" id="modalTokenGenerato" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="ri-check-line"></i> Dispositivo Creato!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p class="mb-3">Copia questo token e inseriscilo sul tablet:</p>
                <div class="bg-light p-3 rounded mb-3">
                    <code id="tokenGenerato" class="fs-6 user-select-all" style="word-break: break-all;"></code>
                </div>
                <button class="btn btn-primary" onclick="copiaTokenGenerato()">
                    <i class="ri-file-copy-line"></i> Copia Token
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Associa Mezzo -->
<div class="modal fade" id="modalAssociaMezzo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Associa Mezzo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="dispositivoIdAssociazione">
                <div class="mb-3">
                    <label class="form-label">Seleziona Mezzo</label>
                    <select class="form-select" id="mezzoAssociazione">
                        @foreach($mezziLiberi as $mezzo)
                            <option value="{{ $mezzo->id }}">
                                {{ $mezzo->nome }} - {{ $mezzo->targa }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                <button type="button" class="btn btn-primary" onclick="associaMezzo()">
                    Associa
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function creaDispositivo() {
        const nome = document.getElementById('nomeDispositivo').value;
        const mezzo = document.getElementById('mezzoDispositivo').value;
        const utente = document.getElementById('utenteDispositivo').value;

        if (!nome || !mezzo || !utente) {
            alert('Compila tutti i campi');
            return;
        }

        fetch('/azienda/tracking/dispositivi/crea', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                nome: nome,
                id_mezzo: mezzo,
                id_utente: utente
            })
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalNuovoDispositivo')).hide();
                    alert('Dispositivo creato! Quando l\'utente farà login, il tracking partirà automaticamente.');
                    location.reload();
                } else {
                    alert('Errore: ' + (data.error || 'Errore sconosciuto'));
                }
            });
    }

    function copiaToken(id) {
        const input = document.getElementById('token-' + id);
        navigator.clipboard.writeText(input.value);

        const btn = input.nextElementSibling;
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="ri-check-line"></i>';
        setTimeout(() => btn.innerHTML = originalHtml, 2000);
    }

    function copiaTokenGenerato() {
        const token = document.getElementById('tokenGenerato').textContent;
        navigator.clipboard.writeText(token);
        alert('Token copiato negli appunti!');
    }

    function mostraAssociaMezzo(id) {
        document.getElementById('dispositivoIdAssociazione').value = id;
        new bootstrap.Modal(document.getElementById('modalAssociaMezzo')).show();
    }

    function associaMezzo() {
        const dispositivoId = document.getElementById('dispositivoIdAssociazione').value;
        const mezzoId = document.getElementById('mezzoAssociazione').value;

        fetch(`/azienda/tracking/dispositivi/${dispositivoId}/associa-mezzo`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ id_mezzo: mezzoId })
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Errore: ' + (data.error || 'Errore sconosciuto'));
                }
            });
    }

    function disattivaDispositivo(id) {
        if (!confirm('Vuoi disattivare questo dispositivo?')) {
            return;
        }

        fetch(`/azienda/tracking/dispositivi/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
    }
</script>

@include('azienda.common.footer')
