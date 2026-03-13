@include('azienda.common.header')

<div class="auth-page-wrapper auth-bg-cover py-5 d-flex justify-content-center align-items-center min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card overflow-hidden">
                    <div class="row g-0">
                        <div class="col-lg-6">
                            <div class="p-lg-5 p-4">
                                <div>
                                    <h5 class="text-primary">Bentornato!</h5>
                                    <p class="text-muted">Registra la tua presenza per oggi.</p>
                                </div>
                                @if(empty($presenza_oggi))
                                    @if(!empty($attivita_corrente))
                                        <div class="mt-4">
                                            <form method="post" id="formInizio">
                                                @csrf
                                                <input type="hidden" name="id_cantiere" value="{{ $attivita_corrente[0]->id_cantiere }}">
                                                <input type="hidden" name="id_attivita" value="{{ $attivita_corrente[0]->id_attivita }}">
                                                <input type="hidden" name="lat_inizio" id="lat_inizio">
                                                <input type="hidden" name="long_inizio" id="long_inizio">
                                                <input type="hidden" name="inizio_lavoro" value="1">
                                                <input type="button" value="Conferma Inizio Lavoro" onclick="getLocation('formInizio', 'inizio')" class="btn btn-success w-100">
                                            </form>
                                        </div>
                                    @else
                                        <div class="alert alert-info mt-4">
                                            Non hai attività pianificate per oggi.
                                        </div>
                                    @endif
                                @elseif(empty($presenza_oggi[0]->ora_fine))
                                    <div class="mt-4">
                                        <form method="post" id="formFine">
                                            @csrf
                                            <input type="hidden" name="lat_fine" id="lat_fine">
                                            <input type="hidden" name="long_fine" id="long_fine">
                                            <input type="hidden" name="fine_lavoro" value="1">
                                            <input type="button" value="Conferma Fine Lavoro" onclick="getLocation('formFine', 'fine')" class="btn btn-primary w-100">
                                        </form>
                                    </div>
                                @else
                                    <div class="alert alert-success mt-4">
                                        Presenza registrata per oggi!<br>
                                        Dalle {{ substr($presenza_oggi[0]->ora_inizio, 0, 5) }}
                                        alle {{ substr($presenza_oggi[0]->ora_fine, 0, 5) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="p-lg-5 p-4 bg-primary h-100 d-flex align-items-center justify-content-center">
                                @if(!empty($attivita_corrente))
                                    <div class="text-white text-center">
                                        <h4 class="mb-4" style="color: #f8f9fa !important;">{{ $attivita_corrente[0]->cantiere_titolo }}</h4>
                                        @if($attivita_corrente[0]->immagine)
                                            <img src="{{ $attivita_corrente[0]->immagine }}" alt="Cantiere" class="img-fluid rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                                        @endif
                                        <div class="mt-4">
                                            <h5 class="text-white-50 mb-3">Attività Corrente:</h5>
                                            <p>{{ $attivita_corrente[0]->attivita_descrizione }}</p>
                                            <p class="mt-3">
                                                {{ date('d/m/Y', strtotime($attivita_corrente[0]->attivita_inizio)) }} -
                                                {{ date('d/m/Y', strtotime($attivita_corrente[0]->attivita_fine)) }}
                                            </p>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-white text-center">
                                        <h4>Nessuna Attività</h4>
                                        <p class="mt-3">Non hai attività programmate per oggi</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function getLocation(formId, tipo) {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    // Aggiungiamo i campi nascosti al form con ID specifici
                    if(tipo === 'inizio') {
                        document.getElementById('lat_inizio').value = position.coords.latitude;
                        document.getElementById('long_inizio').value = position.coords.longitude;
                    } else {
                        document.getElementById('lat_fine').value = position.coords.latitude;
                        document.getElementById('long_fine').value = position.coords.longitude;
                    }
                    // Inviamo il form
                    document.getElementById(formId).submit();
                },
                function(error) {
                    alert('Errore nella geolocalizzazione: ' + error.message);
                }
            );
        } else {
            alert("Il tuo browser non supporta la geolocalizzazione");
        }
        return false;
    }
</script>
@include('azienda.common.footer')