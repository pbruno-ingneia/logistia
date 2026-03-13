@include('azienda.common.header')

<div class="page-content">
    <div class="container-fluid">
        <!-- Titolo pagina -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">💰 Gestione Tariffari</h4>
                    <div class="page-title-right">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCreaTariffa">
                            <i class="ri-add-line"></i> Nuovo Tariffario
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calcolatore Costi -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title text-white mb-0">
                            <i class="ri-calculator-line me-2"></i>Calcolatore Costi Trasporto
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Indirizzo Partenza</label>
                                <input type="text" id="indirizzo_partenza" class="form-control" placeholder="Via, città...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Indirizzo Arrivo</label>
                                <input type="text" id="indirizzo_arrivo" class="form-control" placeholder="Via, città...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Cliente</label>
                                <select id="cliente_calcolo" class="form-select">
                                    <option value="">Tariffa Standard</option>
                                    @foreach($clienti as $cliente)
                                        <option value="{{ $cliente->id }}">{{ $cliente->ragione_sociale }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label><br>
                                <button class="btn btn-primary w-100" onclick="calcolaCosto()">
                                    <i class="ri-calculator-line"></i> Calcola
                                </button>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-2">
                                <label class="form-label">Peso (kg)</label>
                                <input type="number" id="peso_calcolo" class="form-control" value="0" step="0.1">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Tipo Mezzo</label>
                                <select id="tipo_mezzo_calcolo" class="form-select">
                                    <option value="furgoncino">Furgoncino</option>
                                    <option value="furgone" selected>Furgone</option>
                                    <option value="camion">Camion</option>
                                    <option value="bilico">Bilico</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="urgente_calcolo">
                                    <label class="form-check-label" for="urgente_calcolo">Urgente</label>
                                </div>
                            </div>
                        </div>

                        <!-- Risultato calcolo -->
                        <div id="risultato_calcolo" class="mt-4" style="display: none;">
                            <div class="alert alert-success">
                                <div class="row">
                                    <div class="col-md-3">
                                        <h6>📏 Distanza</h6>
                                        <span id="distanza_risultato" class="h5"></span>
                                    </div>
                                    <div class="col-md-3">
                                        <h6>⏱️ Tempo</h6>
                                        <span id="tempo_risultato" class="h5"></span>
                                    </div>
                                    <div class="col-md-3">
                                        <h6>💰 Costo Totale</h6>
                                        <span id="costo_risultato" class="h5 text-success"></span>
                                    </div>
                                    <div class="col-md-3">
                                        <button class="btn btn-outline-primary btn-sm" onclick="mostraDettaglioCosto()">
                                            <i class="ri-eye-line"></i> Dettaglio
                                        </button>
                                    </div>
                                </div>

                                <!-- Dettaglio costi -->
                                <div id="dettaglio_costi" class="mt-3" style="display: none;">
                                    <h6>Dettaglio Calcolo:</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small>Costo Base: €<span id="costo_base"></span></small><br>
                                            <small>Chilometri: €<span id="costo_km"></span></small><br>
                                            <small>Peso: €<span id="costo_peso"></span></small>
                                        </div>
                                        <div class="col-md-6">
                                            <small>Tempo: €<span id="costo_tempo"></span></small><br>
                                            <small>Maggiorazioni: €<span id="costo_maggiorazioni"></span></small><br>
                                            <small>Sconti: €<span id="costo_sconti"></span></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabella tariffari -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title text-white mb-0">
                            <i class="ri-price-tag-3-line me-2"></i>Tariffari Configurati
                        </h5>
                    </div>
                    <div class="card-body">
                        @if(count($tariffari) > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered datatable w-100">
                                    <thead class="table-light">
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Nome Tariffa</th>
                                        <th>Tipo Calcolo</th>
                                        <th>Prezzi</th>
                                        <th>Validità</th>
                                        <th>Stato</th>
                                        <th class="no-sort">Azioni</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($tariffari as $tariffa)
                                        <tr>
                                            <td><strong>{{ $tariffa->cliente_nome ?? 'Cliente rimosso' }}</strong></td>
                                            <td>{{ $tariffa->nome_tariffa }}</td>
                                            <td>
                                                <span class="badge
                                                    @if($tariffa->tipo_calcolo == 'fisso') bg-secondary
                                                    @elseif($tariffa->tipo_calcolo == 'km') bg-primary
                                                    @elseif($tariffa->tipo_calcolo == 'peso') bg-warning
                                                    @elseif($tariffa->tipo_calcolo == 'tempo') bg-info
                                                    @else bg-dark @endif">
                                                    @if($tariffa->tipo_calcolo == 'fisso') 💸 Fisso
                                                    @elseif($tariffa->tipo_calcolo == 'km') 📏 Per Km
                                                    @elseif($tariffa->tipo_calcolo == 'peso') ⚖️ Per Peso
                                                    @elseif($tariffa->tipo_calcolo == 'tempo') ⏱️ Per Tempo
                                                    @else 📊 Volume @endif
                                                </span>
                                            </td>
                                            <td>
                                                <small>
                                                    Base: €{{ number_format($tariffa->prezzo_base, 2) }}<br>
                                                    @if($tariffa->prezzo_per_km)
                                                        Km: €{{ number_format($tariffa->prezzo_per_km, 3) }}<br>
                                                    @endif
                                                    @if($tariffa->prezzo_per_kg)
                                                        Kg: €{{ number_format($tariffa->prezzo_per_kg, 3) }}<br>
                                                    @endif
                                                    @if($tariffa->prezzo_per_ora)
                                                        Ora: €{{ number_format($tariffa->prezzo_per_ora, 2) }}
                                                    @endif
                                                </small>
                                            </td>
                                            <td>
                                                <small>
                                                    Dal: {{ date('d/m/Y', strtotime($tariffa->valido_dal)) }}<br>
                                                    @if($tariffa->valido_fino)
                                                        Al: {{ date('d/m/Y', strtotime($tariffa->valido_fino)) }}
                                                    @else
                                                        Al: <em>Indefinito</em>
                                                    @endif
                                                </small>
                                            </td>
                                            <td>
                                                @if($tariffa->attivo && (!$tariffa->valido_fino || $tariffa->valido_fino >= date('Y-m-d')))
                                                    <span class="badge bg-success">✅ Attivo</span>
                                                @else
                                                    <span class="badge bg-secondary">❌ Inattivo</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-danger btn-sm" onclick="eliminaTariffa({{ $tariffa->id }})" title="Elimina">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="ri-price-tag-3-line text-muted mb-3" style="font-size: 48px;"></i>
                                <h5 class="text-muted">Nessun tariffario configurato</h5>
                                <p class="text-muted">Crea il primo tariffario per iniziare a calcolare automaticamente i costi dei trasporti.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreaTariffa">
                                    <i class="ri-add-line"></i> Crea Primo Tariffario
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Crea Tariffa -->
<div class="modal fade" id="modalCreaTariffa" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="post">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title text-white">
                        <i class="ri-price-tag-3-line me-2"></i>Nuovo Tariffario
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Cliente *</label>
                            <select name="id_cliente" class="form-select" required>
                                <option value="">Seleziona cliente...</option>
                                @foreach($clienti as $cliente)
                                    <option value="{{ $cliente->id }}">{{ $cliente->ragione_sociale }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nome Tariffa *</label>
                            <input type="text" name="nome_tariffa" class="form-control" required placeholder="Es. Tariffa Standard 2024">
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label class="form-label">Tipo Calcolo *</label>
                            <select name="tipo_calcolo" class="form-select" required onchange="mostraCampiTariffa(this.value)">
                                <option value="km">Per Chilometri</option>
                                <option value="peso">Per Peso</option>
                                <option value="tempo">Per Tempo</option>
                                <option value="fisso">Prezzo Fisso</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Prezzo Base €</label>
                            <input type="number" step="0.01" name="prezzo_base" class="form-control" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Valido Dal *</label>
                            <input type="date" name="valido_dal" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    <!-- Campi dinamici per tipo calcolo -->
                    <div id="campi_km" class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label">Prezzo per Km €</label>
                            <input type="number" step="0.001" name="prezzo_per_km" class="form-control" placeholder="0.850">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Km Minimi</label>
                            <input type="number" name="km_minimi" class="form-control" value="0">
                        </div>
                    </div>

                    <div id="campi_peso" class="row mt-3" style="display: none;">
                        <div class="col-md-6">
                            <label class="form-label">Prezzo per Kg €</label>
                            <input type="number" step="0.001" name="prezzo_per_kg" class="form-control" placeholder="0.050">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Peso Minimo (kg)</label>
                            <input type="number" step="0.1" name="peso_minimo" class="form-control" value="0">
                        </div>
                    </div>

                    <div id="campi_tempo" class="row mt-3" style="display: none;">
                        <div class="col-md-6">
                            <label class="form-label">Prezzo per Ora €</label>
                            <input type="number" step="0.01" name="prezzo_per_ora" class="form-control" placeholder="25.00">
                        </div>
                    </div>

                    <!-- Maggiorazioni e sconti -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-muted">Maggiorazioni e Sconti (%)</h6>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Urgente %</label>
                            <input type="number" step="0.1" name="maggiorazione_urgente" class="form-control" value="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Festivo %</label>
                            <input type="number" step="0.1" name="maggiorazione_festivo" class="form-control" value="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Notturno %</label>
                            <input type="number" step="0.1" name="maggiorazione_notturno" class="form-control" value="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sconto Fedeltà %</label>
                            <input type="number" step="0.1" name="sconto_fedeltà" class="form-control" value="0">
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label">Valido Fino</label>
                            <input type="date" name="valido_fino" class="form-control">
                            <small class="text-muted">Lascia vuoto per validità indefinita</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <input type="hidden" name="crea_tariffa" value="1">
                    <button type="submit" class="btn btn-success">
                        <i class="ri-save-line"></i> Crea Tariffario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB0Kta9cMMAOEcpcGl0hwXij0I6_gqWeLM&loading=async&libraries=places&callback=initMap"></script>
<script>
    let autocompletePartenza, autocompleteArrivo;

    function initMap() {
        // Inizializza autocomplete Google Places
        const inputPartenza = document.getElementById('indirizzo_partenza');
        const inputArrivo = document.getElementById('indirizzo_arrivo');

        if (inputPartenza && inputArrivo) {
            autocompletePartenza = new google.maps.places.Autocomplete(inputPartenza);
            autocompleteArrivo = new google.maps.places.Autocomplete(inputArrivo);

            // Limita i risultati all'Italia
            autocompletePartenza.setComponentRestrictions({country: 'it'});
            autocompleteArrivo.setComponentRestrictions({country: 'it'});
        }
    }

    function calcolaCosto() {
        const partenza = document.getElementById('indirizzo_partenza').value;
        const arrivo = document.getElementById('indirizzo_arrivo').value;
        const cliente = document.getElementById('cliente_calcolo').value;
        const peso = document.getElementById('peso_calcolo').value;
        const tipoMezzo = document.getElementById('tipo_mezzo_calcolo').value;
        const urgente = document.getElementById('urgente_calcolo').checked;

        if (!partenza || !arrivo) {
            alert('Inserisci entrambi gli indirizzi');
            return;
        }

        // Mostra loading
        const btnCalcola = event.target;
        const testoOriginale = btnCalcola.innerHTML;
        btnCalcola.innerHTML = '<i class="ri-loader-4-line"></i> Calcolo...';
        btnCalcola.disabled = true;

        fetch('/azienda/calcola-costo-trasporto', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                indirizzo_partenza: partenza,
                indirizzo_arrivo: arrivo,
                id_cliente: cliente,
                peso: peso,
                tipo_mezzo: tipoMezzo,
                urgente: urgente
            })
        })
            .then(response => response.json())
            .then(data => {
                btnCalcola.innerHTML = testoOriginale;
                btnCalcola.disabled = false;

                if (data.success) {
                    document.getElementById('distanza_risultato').textContent = data.distanza_km + ' km';
                    document.getElementById('tempo_risultato').textContent = data.tempo_formattato;
                    document.getElementById('costo_risultato').textContent = '€ ' + data.costo_totale.toFixed(2);

                    // Popola dettaglio
                    const dettaglio = data.costo_dettaglio;
                    document.getElementById('costo_base').textContent = dettaglio.base.toFixed(2);
                    document.getElementById('costo_km').textContent = dettaglio.chilometri.toFixed(2);
                    document.getElementById('costo_peso').textContent = dettaglio.peso.toFixed(2);
                    document.getElementById('costo_tempo').textContent = dettaglio.tempo.toFixed(2);
                    document.getElementById('costo_maggiorazioni').textContent = dettaglio.maggiorazioni.toFixed(2);
                    document.getElementById('costo_sconti').textContent = dettaglio.sconti.toFixed(2);

                    document.getElementById('risultato_calcolo').style.display = 'block';

                    if (data.ha_tariffa_personalizzata) {
                        document.querySelector('#risultato_calcolo .alert').classList.remove('alert-success');
                        document.querySelector('#risultato_calcolo .alert').classList.add('alert-info');
                        document.querySelector('#risultato_calcolo .alert').innerHTML += '<br><small><i class="ri-vip-crown-line"></i> Calcolato con tariffa personalizzata cliente</small>';
                    }
                } else {
                    alert('Errore: ' + data.error);
                }
            })
            .catch(error => {
                btnCalcola.innerHTML = testoOriginale;
                btnCalcola.disabled = false;
                alert('Errore nella richiesta');
            });
    }

    function mostraDettaglioCosto() {
        const dettaglio = document.getElementById('dettaglio_costi');
        dettaglio.style.display = dettaglio.style.display === 'none' ? 'block' : 'none';
    }

    function mostraCampiTariffa(tipo) {
        document.getElementById('campi_km').style.display = tipo === 'km' ? 'flex' : 'none';
        document.getElementById('campi_peso').style.display = tipo === 'peso' ? 'flex' : 'none';
        document.getElementById('campi_tempo').style.display = tipo === 'tempo' ? 'flex' : 'none';
    }

    function eliminaTariffa(idTariffa) {
        if (confirm('Sei sicuro di voler eliminare questo tariffario?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
            @csrf
            <input type="hidden" name="id_tariffa" value="${idTariffa}">
            <input type="hidden" name="elimina_tariffa" value="1">
        `;
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>

@include('azienda.common.footer')