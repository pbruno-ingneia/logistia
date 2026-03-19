@include('azienda.common.header')

<div class="page-content">
    <div class="container-fluid">

        <!-- Titolo pagina -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0"><i class="ri-calendar-check-line"></i> Planning Autisti</h4>
                </div>
            </div>
        </div>

        <!-- Navigazione settimana -->
        <div class="row mb-3">
            <div class="col-12 d-flex align-items-center gap-3">
                <a href="/azienda/planning-autisti?settimana={{ $offset - 1 }}" class="btn btn-outline-secondary btn-sm">
                    <i class="ri-arrow-left-s-line"></i> Settimana precedente
                </a>
                <h5 class="mb-0 text-center flex-grow-1">
                    {{ $lunedi->locale('it')->isoFormat('D MMMM') }} – {{ $domenica->locale('it')->isoFormat('D MMMM YYYY') }}
                </h5>
                <a href="/azienda/planning-autisti?settimana={{ $offset + 1 }}" class="btn btn-outline-secondary btn-sm">
                    Settimana successiva <i class="ri-arrow-right-s-line"></i>
                </a>
                @if($offset !== 0)
                    <a href="/azienda/planning-autisti" class="btn btn-outline-primary btn-sm">Settimana corrente</a>
                @endif
            </div>
        </div>

        <!-- Panel Regole Lavoro -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center"
                         style="cursor:pointer" data-bs-toggle="collapse" data-bs-target="#panelRegole">
                        <span><i class="ri-settings-3-line me-1"></i> <strong>Regole Lavoro</strong></span>
                        <i class="ri-arrow-down-s-line"></i>
                    </div>
                    <div class="collapse" id="panelRegole">
                        <div class="card-body">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-2">
                                    <label class="form-label small">Max giorni consecutivi</label>
                                    <input type="number" class="form-control form-control-sm" id="r_max_giorni"
                                           value="{{ $regole->max_giorni_consecutivi }}" min="1" max="14">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Ore max/giorno</label>
                                    <input type="number" class="form-control form-control-sm" id="r_ore_max_giornaliere"
                                           value="{{ $regole->ore_max_giornaliere }}" min="1" max="24" step="0.5">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Riposo minimo (h)</label>
                                    <input type="number" class="form-control form-control-sm" id="r_ore_riposo_minime"
                                           value="{{ $regole->ore_riposo_minime }}" min="1" max="24" step="0.5">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Ore max settimana</label>
                                    <input type="number" class="form-control form-control-sm" id="r_ore_max_settimanali"
                                           value="{{ $regole->ore_max_settimanali }}" min="1" max="80" step="0.5">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Giorni riposo obbligatori</label>
                                    <input type="number" class="form-control form-control-sm" id="r_giorni_riposo"
                                           value="{{ $regole->giorni_riposo_obbligatori }}" min="1" max="7">
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-primary btn-sm w-100" onclick="salvaRegole()">
                                        <i class="ri-save-line"></i> Salva Regole
                                    </button>
                                </div>
                            </div>

                            <!-- Selezione ruoli da visualizzare -->
                            <div class="mt-3 pt-3 border-top">
                                <label class="form-label small fw-semibold">
                                    <i class="ri-user-settings-line me-1"></i>
                                    Ruoli da visualizzare nel planning
                                    <span class="text-muted fw-normal">(nessuna selezione = tutti gli utenti)</span>
                                </label>
                                <div class="d-flex flex-wrap gap-3" id="checkboxRuoli">
                                    @forelse($tuttiRuoli as $ruolo)
                                        <div class="form-check">
                                            <input class="form-check-input ruolo-check" type="checkbox"
                                                   id="ruolo_{{ $ruolo->id }}" value="{{ $ruolo->id }}"
                                                   {{ in_array($ruolo->id, $ruoliSelezionati) ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="ruolo_{{ $ruolo->id }}">
                                                {{ $ruolo->titolo }}
                                            </label>
                                        </div>
                                    @empty
                                        <span class="text-muted small">Nessun ruolo configurato per questa azienda.
                                            <a href="/azienda/ruoli">Gestisci ruoli →</a>
                                        </span>
                                    @endforelse
                                </div>
                            </div>

                            <div id="msgRegole" class="mt-2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legenda -->
        <div class="row mb-2">
            <div class="col-12 d-flex gap-2 flex-wrap">
                <span class="badge bg-success px-3 py-2">Lavoro</span>
                <span class="badge bg-secondary px-3 py-2">Riposo</span>
                <span class="badge bg-info px-3 py-2">Ferie</span>
                <span class="badge bg-warning text-dark px-3 py-2">Malattia</span>
                <span class="badge bg-danger px-3 py-2"><i class="ri-lock-line"></i> Bloccato</span>
                <span class="badge bg-light text-dark border px-3 py-2">Non impostato</span>
            </div>
        </div>

        <!-- Griglia planning -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0" id="tabellaPlanning">
                                <thead class="table-dark">
                                <tr>
                                    <th style="min-width:140px">Autista</th>
                                    @foreach($giorni as $giorno)
                                        <th class="text-center" style="min-width:90px">
                                            <div>{{ $giorno->locale('it')->isoFormat('ddd') }}</div>
                                            <div class="fw-bold">{{ $giorno->format('d/m') }}</div>
                                        </th>
                                    @endforeach
                                    <th class="text-center">Ore sett.</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($autisti as $autista)
                                    <tr>
                                        <td class="fw-semibold">
                                            <a href="/azienda/planning-autisti/storico/{{ $autista->id }}"
                                               target="_blank"
                                               class="text-dark text-decoration-none d-flex align-items-center gap-1"
                                               title="Storico ordini">
                                                {{ $autista->nome }} {{ $autista->cognome }}
                                                <i class="ri-external-link-line text-muted" style="font-size:0.75rem"></i>
                                            </a>
                                        </td>
                                        @foreach($giorni as $giorno)
                                            @php
                                                $dataStr = $giorno->toDateString();
                                                $cella = $celle[$autista->id][$dataStr] ?? ['tipo'=>null,'bloccato'=>false,'warning'=>false,'consecutivi'=>0,'ordini'=>0,'row'=>null];
                                                $tipo = $cella['tipo'];
                                                $bloccato = $cella['bloccato'];
                                                $warning = $cella['warning'];
                                                $ordini = $cella['ordini'];
                                                $row = $cella['row'];

                                                if ($bloccato) {
                                                    $bgClass = 'bg-danger text-white';
                                                    $icona = '<i class="ri-lock-line"></i>';
                                                    $label = 'BLOCCO';
                                                } elseif ($tipo === 'lavoro') {
                                                    $bgClass = 'bg-success text-white';
                                                    $icona = $warning ? '⚠️' : '<i class="ri-check-line"></i>';
                                                    $label = $warning ? $cella['consecutivi'].'°gg' : 'Lavoro';
                                                } elseif ($tipo === 'riposo') {
                                                    $bgClass = 'bg-secondary text-white';
                                                    $icona = '<i class="ri-rest-time-line"></i>';
                                                    $label = 'Riposo';
                                                } elseif ($tipo === 'ferie') {
                                                    $bgClass = 'bg-info text-white';
                                                    $icona = '<i class="ri-umbrella-line"></i>';
                                                    $label = 'Ferie';
                                                } elseif ($tipo === 'malattia') {
                                                    $bgClass = 'bg-warning text-dark';
                                                    $icona = '<i class="ri-hospital-line"></i>';
                                                    $label = 'Malattia';
                                                } else {
                                                    $bgClass = '';
                                                    $icona = '';
                                                    $label = '';
                                                }
                                            @endphp
                                            <td class="text-center p-1 {{ $bgClass }}"
                                                @if(!$bloccato)
                                                    style="cursor:pointer"
                                                    onclick="apriModal({{ $autista->id }}, '{{ $autista->nome }} {{ $autista->cognome }}', '{{ $dataStr }}', '{{ $tipo ?? '' }}', '{{ $row?->ora_inizio ?? '' }}', '{{ $row?->ora_fine ?? '' }}', '{{ addslashes($row?->note ?? '') }}')"
                                                @else
                                                    title="Blocco riposo obbligatorio dopo {{ $cella['consecutivi'] }} giorni consecutivi"
                                                @endif
                                            >
                                                <div>{!! $icona !!}</div>
                                                @if($label)<div class="small" style="font-size:0.7rem">{{ $label }}</div>@endif
                                                @if($ordini > 0)
                                                    <span class="badge {{ $bloccato ? 'bg-light text-dark' : 'bg-dark bg-opacity-25' }} mt-1" style="font-size:0.65rem">
                                                        {{ $ordini }} ord.
                                                    </span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="text-center align-middle">
                                            @php
                                                $ore = $oreSettimanali[$autista->id] ?? 0;
                                                $oreClass = $ore > $regole->ore_max_settimanali ? 'text-danger fw-bold' : 'text-muted';
                                            @endphp
                                            <span class="{{ $oreClass }}">{{ $ore }}h</span>
                                            @if($ore > $regole->ore_max_settimanali)
                                                <div class="small text-danger">⚠️ max {{ $regole->ore_max_settimanali }}h</div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                @if(count($autisti) === 0)
                                    <tr>
                                        <td colspan="{{ count($giorni) + 2 }}" class="text-center text-muted py-4">
                                            Nessun autista trovato.
                                        </td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Mini-Modal impostazione giorno -->
<div class="modal fade" id="modalGiorno" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalGiornoTitolo">Imposta giorno</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modalIdAutista">
                <input type="hidden" id="modalData">

                <div class="mb-3">
                    <label class="form-label">Tipo</label>
                    <select class="form-select" id="modalTipo">
                        <option value="">-- Non impostato --</option>
                        <option value="lavoro">Lavoro</option>
                        <option value="riposo">Riposo</option>
                        <option value="ferie">Ferie</option>
                        <option value="malattia">Malattia</option>
                    </select>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label">Ora inizio</label>
                        <input type="time" class="form-control" id="modalOraInizio">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Ora fine</label>
                        <input type="time" class="form-control" id="modalOraFine">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Note</label>
                    <input type="text" class="form-control" id="modalNote" placeholder="Opzionale">
                </div>
                <div id="modalWarning" class="alert alert-warning d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                <button type="button" class="btn btn-primary" onclick="salvaGiorno()">
                    <i class="ri-save-line"></i> Salva
                </button>
            </div>
        </div>
    </div>
</div>

@include('azienda.common.footer')

<script>
const csrfToken = '{{ csrf_token() }}';

function apriModal(idAutista, nomeAutista, data, tipo, oraInizio, oraFine, note) {
    document.getElementById('modalIdAutista').value = idAutista;
    document.getElementById('modalData').value = data;

    // Formatta data per titolo
    const d = new Date(data + 'T00:00:00');
    const opzioni = { weekday: 'long', day: '2-digit', month: '2-digit', year: 'numeric' };
    document.getElementById('modalGiornoTitolo').textContent = nomeAutista + ' — ' + d.toLocaleDateString('it-IT', opzioni);

    document.getElementById('modalTipo').value = tipo || '';
    document.getElementById('modalOraInizio').value = oraInizio ? oraInizio.substring(0, 5) : '';
    document.getElementById('modalOraFine').value = oraFine ? oraFine.substring(0, 5) : '';
    document.getElementById('modalNote').value = note || '';
    document.getElementById('modalWarning').classList.add('d-none');
    document.getElementById('modalWarning').textContent = '';

    const modal = new bootstrap.Modal(document.getElementById('modalGiorno'));
    modal.show();
}

function salvaGiorno() {
    const idAutista = document.getElementById('modalIdAutista').value;
    const data = document.getElementById('modalData').value;
    const tipo = document.getElementById('modalTipo').value;
    const oraInizio = document.getElementById('modalOraInizio').value;
    const oraFine = document.getElementById('modalOraFine').value;
    const note = document.getElementById('modalNote').value;

    fetch('/azienda/planning-autisti/salva-giorno', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ id_autista: idAutista, data, tipo, ora_inizio: oraInizio, ora_fine: oraFine, note })
    })
    .then(r => r.json())
    .then(resp => {
        if (resp.success) {
            if (resp.warning) {
                // Mostra warning ma considera salvato
                const warningEl = document.getElementById('modalWarning');
                warningEl.textContent = '⚠️ ' + resp.warning;
                warningEl.classList.remove('d-none');
                setTimeout(() => { bootstrap.Modal.getInstance(document.getElementById('modalGiorno')).hide(); location.reload(); }, 2000);
            } else {
                bootstrap.Modal.getInstance(document.getElementById('modalGiorno')).hide();
                location.reload();
            }
        } else if (resp.bloccato) {
            const warningEl = document.getElementById('modalWarning');
            warningEl.textContent = '🔒 ' + resp.message;
            warningEl.classList.remove('d-none');
            warningEl.classList.remove('alert-warning');
            warningEl.classList.add('alert-danger');
        } else {
            alert('Errore: ' + (resp.message || 'Errore sconosciuto'));
        }
    })
    .catch(err => {
        alert('Errore di comunicazione con il server.');
        console.error(err);
    });
}

function salvaRegole() {
    const ruoliSelezionati = Array.from(document.querySelectorAll('.ruolo-check:checked'))
        .map(cb => parseInt(cb.value));

    const dati = {
        max_giorni_consecutivi: document.getElementById('r_max_giorni').value,
        ore_max_giornaliere: document.getElementById('r_ore_max_giornaliere').value,
        ore_riposo_minime: document.getElementById('r_ore_riposo_minime').value,
        ore_max_settimanali: document.getElementById('r_ore_max_settimanali').value,
        giorni_riposo_obbligatori: document.getElementById('r_giorni_riposo').value,
        ruoli_ids: ruoliSelezionati,
    };

    fetch('/azienda/planning-autisti/salva-regole', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify(dati)
    })
    .then(r => r.json())
    .then(resp => {
        const msgEl = document.getElementById('msgRegole');
        if (resp.success) {
            msgEl.innerHTML = '<div class="alert alert-success py-2">Regole salvate. La pagina si aggiornerà...</div>';
            setTimeout(() => location.reload(), 1200);
        } else {
            msgEl.innerHTML = '<div class="alert alert-danger py-2">Errore nel salvataggio.</div>';
        }
    })
    .catch(() => {
        document.getElementById('msgRegole').innerHTML = '<div class="alert alert-danger py-2">Errore di comunicazione.</div>';
    });
}
</script>
