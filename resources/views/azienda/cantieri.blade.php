@include('azienda.common.header')

<div class="page-content">
    <div class="container-fluid">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Cantieri</h4>
        </div>

        <!-- Pulsante Aggiungi Cantiere -->
        <button class="btn btn-info mb-3" onclick="aggiungi();">
            <i class="ri-add-fill me-1 align-bottom"></i> Aggiungi Cantiere
        </button>

        <!-- ✅ TABS AGGIORNATI per separare cantieri -->
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#attivi">Cantieri Attivi</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#sospesi">Cantieri Sospesi</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#chiusi">Cantieri Chiusi</a>
            </li>
            <!-- ✅ NUOVA TAB -->
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#non_contabilizzati">Non Contabilizzati</a>
            </li>
        </ul>

        <div class="tab-content mt-3">
            @foreach(['attivi' => 1, 'sospesi' => 2, 'chiusi' => 0] as $tab => $stato)
                <div id="{{ $tab }}" class="tab-pane fade show {{ $loop->first ? 'active' : '' }}">
                    <table class="table table-bordered table-hover datatable w-100">
                        <thead>
                        <tr>
                            <th>Titolo</th>
                            <th>Descrizione</th>
                            <th>Periodo</th>
                            <th>Stime</th>
                            <th>Valore Effettivo</th>
                            <th>Responsabile</th>
                            <th>Tipo</th> <!-- ✅ NUOVA COLONNA -->
                            <th>Azioni</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($cantieri as $c)
                            @if($c->stato == $stato && ($c->contabilizzato ?? 1) == 1)
                                <tr>
                                    <td>{{ $c->titolo }}</td>
                                    <td>{{ $c->descrizione }}</td>
                                    <td>
                                        Inizio: {{ date('d/m/Y', strtotime($c->data_inizio)) }}<br>
                                        Fine: {{ date('d/m/Y', strtotime($c->data_fine)) }}
                                    </td>
                                    <td>
                                        Costo: {{ number_format($c->costo_stimato, 2, ',', '.') }} €<br>
                                        Valore: {{ number_format($c->valore_stimato, 2, ',', '.') }} €
                                    </td>
                                    <td>
                                        Costo: {{ number_format($c->costo_totale, 2, ',', '.') }} €<br>
                                        Valore: {{ number_format($c->valore_totale, 2, ',', '.') }} €
                                    </td>
                                    <td>
                                        @if(isset($responsabiliCantieri[$c->id]) && count($responsabiliCantieri[$c->id]) > 0)
                                            <small class="badge bg-primary">
                                                {{ $responsabiliCantieri[$c->id][0]->nome }} {{ $responsabiliCantieri[$c->id][0]->cognome }} ({{ $responsabiliCantieri[$c->id][0]->percentuale }}%)
                                            </small>
                                        @else
                                            <small class="text-muted">Nessun responsabile</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-success">Contabilizzato</span>
                                    </td>
                                    <td class="d-flex">
                                        <a href="{{ url('/azienda/cantiere/'.$c->id) }}" class="btn btn-sm btn-success">Dettagli</a>
                                        @if($utente->solo_lettura != 1)
                                        <a onclick="modifica({{ $c->id }})" class="btn btn-sm btn-primary mx-1">Modifica</a>
                                        <form method="post" onsubmit="return confirm('Vuoi eliminare questo cantiere?')">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $c->id }}">
                                            <input name="elimina" type="submit" class="btn btn-sm btn-danger" value="Elimina">
                                            @endif
                                        </form>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach

            <!-- ✅ NUOVA TAB PER CANTIERI NON CONTABILIZZATI -->
            <div id="non_contabilizzati" class="tab-pane fade">
                <table class="table table-bordered table-hover datatable w-100">
                    <thead>
                    <tr>
                        <th>Titolo</th>
                        <th>Descrizione</th>
                        <th>Periodo</th>
                        <th>Stime</th>
                        <th>Valore Effettivo</th>
                        <th>Responsabile</th>
                        <th>Stato</th>
                        <th>Azioni</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($cantieri as $c)
                        @if(($c->contabilizzato ?? 1) == 0)
                            <tr>
                                <td>{{ $c->titolo }}</td>
                                <td>{{ $c->descrizione }}</td>
                                <td>
                                    Inizio: {{ date('d/m/Y', strtotime($c->data_inizio)) }}<br>
                                    Fine: {{ date('d/m/Y', strtotime($c->data_fine)) }}
                                </td>
                                <td>
                                    Costo: {{ number_format($c->costo_stimato, 2, ',', '.') }} €<br>
                                    Valore: {{ number_format($c->valore_stimato, 2, ',', '.') }} €
                                </td>
                                <td>
                                    Costo: {{ number_format($c->costo_totale, 2, ',', '.') }} €<br>
                                    Valore: {{ number_format($c->valore_totale, 2, ',', '.') }} €
                                </td>
                                <td>
                                    @if(isset($responsabiliCantieri[$c->id]) && count($responsabiliCantieri[$c->id]) > 0)
                                        <small class="badge bg-primary">
                                            {{ $responsabiliCantieri[$c->id][0]->nome }} {{ $responsabiliCantieri[$c->id][0]->cognome }} ({{ $responsabiliCantieri[$c->id][0]->percentuale }}%)
                                        </small>
                                    @else
                                        <small class="text-muted">Nessun responsabile</small>
                                    @endif
                                </td>
                                <td>
                                    @if($c->stato == 1)
                                        <span class="badge bg-success">Attivo</span>
                                    @elseif($c->stato == 2)
                                        <span class="badge bg-warning">Sospeso</span>
                                    @else
                                        <span class="badge bg-secondary">Chiuso</span>
                                    @endif
                                    <br><span class="badge bg-info">Non Contabilizzato</span>
                                </td>
                                <td class="d-flex">
                                    <a href="{{ url('/azienda/cantiere/'.$c->id) }}" class="btn btn-sm btn-success">Dettagli</a>
                                    <a onclick="modifica({{ $c->id }})" class="btn btn-sm btn-primary mx-1">Modifica</a>
                                    <form method="post" onsubmit="return confirm('Vuoi eliminare questo cantiere?')">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $c->id }}">
                                        <input name="elimina" type="submit" class="btn btn-sm btn-danger" value="Elimina">
                                    </form>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modale per Aggiunta Cantiere -->
<div class="modal fade" id="modal_aggiungi" >
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header bg-soft-info p-3">
                <h5 class="modal-title" id="exampleModalLabel">Aggiungi Cantiere</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
            </div>
            <form class="tablelist-form" autocomplete="on" enctype="multipart/form-data" method="post">
                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-lg-12">
                            <div class="text-center">
                                <div class="position-relative d-inline-block">
                                    <div class="avatar-lg p-1">
                                        <div class="avatar-title bg-light rounded-circle">
                                            <img src="/placehold_immagine.png" id="customer-img" class="avatar-md rounded-circle object-cover" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Indirizzo<b style="color:red">*</b></label>
                            <input type="text" name="indirizzo" id="autocomplete" class="form-control" placeholder="Inserisci indirizzo" required>
                        </div>
                        <input type="hidden" name="latitudine" id="latitudine">
                        <input type="hidden" name="longitudine" id="longitudine">

                        <div class="col-md-12">
                            <label class="form-label">Titolo<b style="color:red">*</b></label>
                            <input type="text" name="titolo" class="form-control" placeholder="Titolo" required/>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Descrizione<b style="color:red">*</b></label>
                            <textarea name="descrizione" class="form-control" style="height:150px"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Data Inizio<b style="color:red">*</b></label>
                            <input type="date" name="data_inizio" class="form-control" placeholder="Data Inizio" required/>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Data Fine<b style="color:red">*</b></label>
                            <input type="date" name="data_fine" class="form-control date-picker" placeholder="Data Fine" required/>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Costo Stimato<b style="color:red">*</b></label>
                            <input type="number" name="costo_stimato" class="form-control" required/>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Valore Stimato<b style="color:red">*</b></label>
                            <input type="number" name="valore_stimato" class="form-control" required/>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Colore Cantiere</label>
                            <input type="color" name="colore" class="form-control" value="#007bff">
                        </div>

                        <!-- ✅ NUOVO CAMPO PER CONTABILIZZAZIONE -->
                        <div class="col-md-6">
                            <label class="form-label">Tipo Cantiere</label>
                            <select name="contabilizzato" class="form-control" required>
                                <option value="1">Contabilizzato</option>
                                <option value="0">Non Contabilizzato</option>
                            </select>
                        </div>

                        <!-- ✅ CAMPO RESPONSABILE -->
                        <div class="col-md-8">
                            <label class="form-label">Responsabile</label>
                            <select name="responsabile" class="form-control">
                                <option value="">Seleziona Responsabile</option>
                                @foreach($responsabili as $resp)
                                    <option value="{{ $resp->id }}">{{ $resp->nome }} {{ $resp->cognome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Percentuale (%)</label>
                            <input type="number" name="percentuale" class="form-control" min="0" max="100" step="0.01" placeholder="0.00">
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Chiudi</button>
                        <input type="submit" class="btn btn-success" id="add-btn" name="aggiungi" value="Aggiungi" >
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php foreach($cantieri as $c){ ?>

<div class="modal fade" id="modal_modifica_<?php echo $c->id ?>" >
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header bg-soft-info p-3">
                <h5 class="modal-title" id="exampleModalLabel">Modifica Cantiere <?php echo $c->titolo ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
            </div>
            <form class="tablelist-form" autocomplete="off" enctype="multipart/form-data" method="post">
                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-lg-12">
                            <div class="text-center">
                                <div class="position-relative d-inline-block">
                                    <div class="avatar-lg p-1">
                                        <div class="avatar-title bg-light rounded-circle">
                                            <img src="/placehold_immagine.png" id="customer-img" class="avatar-md rounded-circle object-cover" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Indirizzo<b style="color:red">*</b></label>
                            <input
                                    type="text"
                                    name="indirizzo"
                                    id="autocomplete_{{ $c->id }}"
                                    class="form-control"
                                    placeholder="Inserisci indirizzo"
                                    value="{{ $c->indirizzo ?? '' }}"
                            >
                        </div>

                        <!-- hidden per lat/long -->
                        <input type="hidden" name="latitudine"  id="latitudine_{{ $c->id }}"  value="{{ $c->latitudine ?? '' }}">
                        <input type="hidden" name="longitudine" id="longitudine_{{ $c->id }}" value="{{ $c->longitudine ?? '' }}">

                        <div class="col-md-12">
                            <input class="form-control" type="file" name="immagine" accept="image/png, image/gif, image/jpeg">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Titolo<b style="color:red">*</b></label>
                            <input type="text" name="titolo" value="<?php echo $c->titolo ?>" class="form-control" placeholder="Titolo" required/>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Descrizione<b style="color:red">*</b></label>
                            <textarea name="descrizione" class="form-control" style="height:150px"><?php echo $c->descrizione ?></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Data Inizio<b style="color:red">*</b></label>
                            <input type="date" name="data_inizio" class="form-control" value="<?php echo $c->data_inizio ?>" placeholder="Data Inizio" required/>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Data Fine<b style="color:red">*</b></label>
                            <input type="date" name="data_fine" class="form-control" value="<?php echo $c->data_fine ?>" placeholder="Data Fine" required/>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Costo Stimato<b style="color:red">*</b></label>
                            <input type="number" name="costo_stimato" value="<?php echo $c->costo_stimato ?>" class="form-control" required/>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Valore Stimato<b style="color:red">*</b></label>
                            <input type="number" name="valore_stimato" value="<?php echo $c->valore_stimato ?>" class="form-control" required/>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Colore Cantiere</label>
                            <input type="color" name="colore" class="form-control" value="<?php echo $c->colore ?? '#007bff' ?>">
                        </div>

                        <!-- ✅ NUOVO CAMPO PER CONTABILIZZAZIONE NELLE MODAL DI MODIFICA -->
                        <div class="col-md-6">
                            <label class="form-label">Tipo Cantiere</label>
                            <select name="contabilizzato" class="form-control" required>
                                <option value="1" <?php echo (($c->contabilizzato ?? 1) == 1) ? 'selected' : '' ?>>Contabilizzato</option>
                                <option value="0" <?php echo (($c->contabilizzato ?? 1) == 0) ? 'selected' : '' ?>>Non Contabilizzato</option>
                            </select>
                        </div>

                        <!-- ✅ CAMPO RESPONSABILE NELLA MODIFICA -->
                        @php
                            $responsabileCantiere = isset($responsabiliCantieri[$c->id]) && count($responsabiliCantieri[$c->id]) > 0 ? $responsabiliCantieri[$c->id][0] : null;
                        @endphp

                        <div class="col-md-8">
                            <label class="form-label">Responsabile</label>
                            <select name="responsabile" class="form-control">
                                <option value="">Seleziona Responsabile</option>
                                @foreach($responsabili as $resp)
                                    <option value="{{ $resp->id }}"
                                            {{ $responsabileCantiere && $responsabileCantiere->id_responsabile == $resp->id ? 'selected' : '' }}>
                                        {{ $resp->nome }} {{ $resp->cognome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Percentuale (%)</label>
                            <input type="number" name="percentuale" class="form-control" min="0" max="100" step="0.01"
                                   value="{{ $responsabileCantiere ? $responsabileCantiere->percentuale : '' }}">
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Chiudi</button>
                        <input type="hidden" name="id" value="<?php echo $c->id ?>">
                        <input type="submit" class="btn btn-success" id="add-btn" name="modifica" value="Modifica" >
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php } ?>

@include('azienda.common.footer')

<!-- ✅ JAVASCRIPT COMPLETO CON GOOGLE MAPS -->
<script>
    // Manteniamo le tue funzioni originali per le modal
    function aggiungi() {
        console.log("Apertura modale aggiungi...");
        $('#modal_aggiungi').modal('show');
    }

    function modifica(id) {
        console.log("Apertura modale modifica ID:", id);
        $('#modal_modifica_' + id).modal('show');
    }

    // Funzione che verrà chiamata quando Google Maps è caricato
    function initMap() {
        console.log('Google Maps API caricata');

        // Funzione per inizializzare l'autocomplete su un input specifico
        function initGoogleAutocomplete(inputId) {
            const input = document.getElementById(inputId);
            if (!input) {
                console.log('Input non trovato:', inputId);
                return;
            }

            const autocomplete = new google.maps.places.Autocomplete(input, {
                types: ['address'],
                componentRestrictions: { country: 'IT' }
            });

            autocomplete.addListener('place_changed', function() {
                const place = autocomplete.getPlace();
                if (!place.geometry) return;

                // Trova gli input nascosti nel form genitore
                const form = input.closest('form');
                const latInput = form.querySelector('[name="latitudine"]');
                const lngInput = form.querySelector('[name="longitudine"]');

                if (latInput && lngInput) {
                    latInput.value = place.geometry.location.lat();
                    lngInput.value = place.geometry.location.lng();
                    console.log('Coordinate salvate:', latInput.value, lngInput.value);
                }
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                }
            });
        }

        // Aggiungi listener per la modal di aggiunta
        const modalAggiungi = document.getElementById('modal_aggiungi');
        if (modalAggiungi) {
            modalAggiungi.addEventListener('shown.bs.modal', function() {
                console.log('Modal aggiungi aperta, inizializzo autocomplete');
                setTimeout(() => {
                    initGoogleAutocomplete('autocomplete');
                }, 100);
            });
        }

        // Per le modal di modifica
        const modaliModifica = document.querySelectorAll('[id^="modal_modifica_"]');
        modaliModifica.forEach(modal => {
            modal.addEventListener('shown.bs.modal', function() {
                const modalId = this.id;
                const cantiereId = modalId.split('_')[2];
                const inputId = 'autocomplete_' + cantiereId;
                console.log('Modal modifica aperta per cantiere:', cantiereId);
                setTimeout(() => {
                    initGoogleAutocomplete(inputId);
                }, 100);
            });
        });
    }

    // Caricamento asincrono di Google Maps seguendo le best practice
    window.initMap = initMap;

    // Assicurati che il DOM sia caricato prima di inizializzare
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM caricato, Google Maps dovrebbe essere disponibile');
    });
</script>

<style>
    .pac-container {
        z-index: 999999 !important;
    }
</style>

<!-- Carica Google Maps alla fine del body -->
<script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB0Kta9cMMAOEcpcGl0hwXij0I6_gqWeLM&loading=async&libraries=places&callback=initMap">
</script>