@extends('autista.common.layout')

@section('title', 'Segnala Guasto')

@section('content')
    <div class="fade-in">

        <!-- Header -->
        <div class="mb-4">
            <h4 class="mb-1">
                <i class="ri-alert-line text-danger"></i>
                Segnala un Guasto
            </h4>
            <p class="text-muted mb-0">Compila e invia la segnalazione via email</p>
        </div>

        <!-- Alert -->
        <div class="alert alert-info d-none" id="alertInfo">
            <i class="ri-information-line me-2"></i>
            <span id="msgInfo"></span>
        </div>

        <form id="formGuasto">

            <!-- Mezzo Attuale -->
            @if($mezzo)
                <div class="card-custom mb-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon blue me-3">
                                <i class="ri-truck-line"></i>
                            </div>
                            <div>
                                <div class="fw-500">{{ $mezzo->nome ?? 'Mezzo' }}</div>
                                <small class="text-muted">{{ $mezzo->targa ?? '' }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Tipo Guasto -->
            <div class="card-custom mb-3">
                <div class="card-header">
                    <i class="ri-tools-line text-warning"></i>
                    Tipo di Guasto *
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="tipo_guasto" id="tipo_meccanico" value="🔧 Meccanico" required>
                            <label class="btn btn-outline-secondary w-100 py-3" for="tipo_meccanico">
                                <i class="ri-settings-3-line d-block mb-1" style="font-size: 1.5rem;"></i>
                                Meccanico
                            </label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="tipo_guasto" id="tipo_elettrico" value="⚡ Elettrico">
                            <label class="btn btn-outline-secondary w-100 py-3" for="tipo_elettrico">
                                <i class="ri-flashlight-line d-block mb-1" style="font-size: 1.5rem;"></i>
                                Elettrico
                            </label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="tipo_guasto" id="tipo_pneumatico" value="🔘 Pneumatico">
                            <label class="btn btn-outline-secondary w-100 py-3" for="tipo_pneumatico">
                                <i class="ri-circle-line d-block mb-1" style="font-size: 1.5rem;"></i>
                                Pneumatico
                            </label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="tipo_guasto" id="tipo_carrozzeria" value="🚗 Carrozzeria">
                            <label class="btn btn-outline-secondary w-100 py-3" for="tipo_carrozzeria">
                                <i class="ri-car-line d-block mb-1" style="font-size: 1.5rem;"></i>
                                Carrozzeria
                            </label>
                        </div>
                        <div class="col-12">
                            <input type="radio" class="btn-check" name="tipo_guasto" id="tipo_altro" value="❓ Altro">
                            <label class="btn btn-outline-secondary w-100 py-2" for="tipo_altro">
                                <i class="ri-question-line me-2"></i> Altro
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Urgenza -->
            <div class="card-custom mb-3">
                <div class="card-header">
                    <i class="ri-alarm-warning-line text-danger"></i>
                    Urgenza
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-3">
                            <input type="radio" class="btn-check" name="urgenza" id="urg_bassa" value="🟢 BASSA">
                            <label class="btn btn-outline-success w-100" for="urg_bassa">Bassa</label>
                        </div>
                        <div class="col-3">
                            <input type="radio" class="btn-check" name="urgenza" id="urg_media" value="🟡 MEDIA" checked>
                            <label class="btn btn-outline-warning w-100" for="urg_media">Media</label>
                        </div>
                        <div class="col-3">
                            <input type="radio" class="btn-check" name="urgenza" id="urg_alta" value="🟠 ALTA">
                            <label class="btn btn-outline-orange w-100" for="urg_alta">Alta</label>
                        </div>
                        <div class="col-3">
                            <input type="radio" class="btn-check" name="urgenza" id="urg_critica" value="🔴 CRITICA">
                            <label class="btn btn-outline-danger w-100" for="urg_critica">Critica</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Descrizione -->
            <div class="card-custom mb-3">
                <div class="card-header">
                    <i class="ri-file-text-line text-primary"></i>
                    Descrizione *
                </div>
                <div class="card-body">
                <textarea class="form-control" name="descrizione" id="descrizione" rows="3"
                          placeholder="Descrivi il problema..." required></textarea>
                </div>
            </div>

            <!-- Posizione GPS -->
            <div class="card-custom mb-3">
                <div class="card-header d-flex justify-content-between">
                    <span><i class="ri-map-pin-line text-danger"></i> Posizione</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="rileva()">
                        <i class="ri-gps-line"></i> Rileva
                    </button>
                </div>
                <div class="card-body">
                    <div id="infoPos" class="text-muted small">
                        Clicca "Rileva" per la posizione
                    </div>
                    <input type="hidden" name="latitudine" id="lat">
                    <input type="hidden" name="longitudine" id="lng">
                    <input type="hidden" name="indirizzo" id="indirizzo">
                </div>
            </div>

            <!-- Pulsante Invio Email -->
            <button type="button" class="btn btn-danger btn-lg w-100 mb-3" onclick="apriEmail()">
                <i class="ri-mail-send-line me-2"></i>
                Invia Segnalazione via Email
            </button>

            <p class="text-center text-muted small mb-4">
                Si aprirà la tua app email con i dati già compilati
            </p>

        </form>
    </div>

    <!-- Dati dal server -->
    <script>
        // Dati precaricati dal server
        const datiSegnalazione = {
            emailDestinatario: "{{ $emailDestinatario ?? '' }}",
            nomeMezzo: "{{ $mezzo->nome ?? 'N/D' }}",
            targaMezzo: "{{ $mezzo->targa ?? 'N/D' }}",
            nomeAutista: "{{ $utente->nome ?? '' }} {{ $utente->cognome ?? '' }}",
            azienda: "{{ $azienda->ragione_sociale ?? '' }}"
        };
    </script>
@endsection

@section('styles')
    <style>
        .btn-check:checked + .btn-outline-secondary {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        .btn-check:checked + .btn-outline-success { background: #27ae60; border-color: #27ae60; color: white; }
        .btn-check:checked + .btn-outline-warning { background: #f39c12; border-color: #f39c12; color: white; }
        .btn-check:checked + .btn-outline-orange { background: #e67e22; border-color: #e67e22; color: white; }
        .btn-outline-orange { border-color: #e67e22; color: #e67e22; }
        .btn-check:checked + .btn-outline-danger { background: #e74c3c; border-color: #e74c3c; color: white; }
    </style>
@endsection

@section('scripts')
    <script>
        // Rileva posizione GPS
        function rileva() {
            const info = document.getElementById('infoPos');
            if (!navigator.geolocation) {
                info.innerHTML = '<span class="text-danger">GPS non supportato</span>';
                return;
            }
            info.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Rilevamento...';

            navigator.geolocation.getCurrentPosition(
                async (pos) => {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    document.getElementById('lat').value = lat;
                    document.getElementById('lng').value = lng;

                    // Reverse geocoding
                    try {
                        const res = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`);
                        const data = await res.json();
                        const addr = data.display_name || `${lat}, ${lng}`;
                        document.getElementById('indirizzo').value = addr;
                        info.innerHTML = `<span class="text-success"><i class="ri-checkbox-circle-line"></i> Rilevato</span><br><small class="text-muted">${addr.substring(0, 100)}...</small>`;
                    } catch(e) {
                        document.getElementById('indirizzo').value = `${lat}, ${lng}`;
                        info.innerHTML = `<span class="text-success"><i class="ri-checkbox-circle-line"></i> ${lat.toFixed(5)}, ${lng.toFixed(5)}</span>`;
                    }
                },
                (err) => {
                    info.innerHTML = '<span class="text-danger">Posizione non disponibile</span>';
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        }

        // Apri app email con dati precompilati
        function apriEmail() {
            // Validazione
            const tipoGuasto = document.querySelector('input[name="tipo_guasto"]:checked');
            const descrizione = document.getElementById('descrizione').value.trim();
            const urgenza = document.querySelector('input[name="urgenza"]:checked');

            if (!tipoGuasto) {
                alert('Seleziona il tipo di guasto');
                return;
            }
            if (!descrizione) {
                alert('Inserisci una descrizione del problema');
                return;
            }

            // Dati posizione
            const lat = document.getElementById('lat').value;
            const lng = document.getElementById('lng').value;
            const indirizzo = document.getElementById('indirizzo').value;

            // Link Google Maps
            let linkMappa = '';
            if (lat && lng) {
                linkMappa = `https://www.google.com/maps?q=${lat},${lng}`;
            }

            // Data e ora
            const now = new Date();
            const dataOra = now.toLocaleString('it-IT');

            // Costruisci oggetto email
            const oggetto = `🚨 [${urgenza.value}] Guasto ${datiSegnalazione.targaMezzo} - ${tipoGuasto.value}`;

            // Costruisci corpo email
            let corpo = `SEGNALAZIONE GUASTO
====================

📅 Data/Ora: ${dataOra}

🚛 MEZZO
Veicolo: ${datiSegnalazione.nomeMezzo}
Targa: ${datiSegnalazione.targaMezzo}

👤 AUTISTA
${datiSegnalazione.nomeAutista}

⚠️ DETTAGLI GUASTO
Tipo: ${tipoGuasto.value}
Urgenza: ${urgenza.value}

📝 DESCRIZIONE
${descrizione}
`;

            // Aggiungi posizione se disponibile
            if (indirizzo) {
                corpo += `
📍 POSIZIONE
${indirizzo}
`;
            }

            if (linkMappa) {
                corpo += `
🗺️ APRI MAPPA
${linkMappa}
`;
            }

            corpo += `
---
Inviato da Logistia`;

            // Codifica per URL
            const oggettoEncoded = encodeURIComponent(oggetto);
            const corpoEncoded = encodeURIComponent(corpo);

            // Email destinatario
            let email = datiSegnalazione.emailDestinatario;
            if (!email) {
                email = prompt('Inserisci email destinatario:', '');
                if (!email) return;
            }

            // Apri client email
            const mailtoLink = `mailto:${email}?subject=${oggettoEncoded}&body=${corpoEncoded}`;

            // Apri il link
            window.location.href = mailtoLink;

            // Mostra messaggio
            document.getElementById('msgInfo').textContent = 'App email aperta. Controlla e invia!';
            document.getElementById('alertInfo').classList.remove('d-none');
        }

        // Rileva posizione automaticamente al caricamento
        document.addEventListener('DOMContentLoaded', () => {
            rileva();
        });
    </script>
@endsection