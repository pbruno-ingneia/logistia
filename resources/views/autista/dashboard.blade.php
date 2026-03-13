@extends('autista.common.layout')

@section('title', 'Dashboard')

@section('content')
    <div class="fade-in">
        <!-- Saluto -->
        <div class="mb-4">
            <h4 class="mb-1">Ciao, {{ $utente->nome ?? 'Autista' }}! 👋</h4>
            <p class="text-muted mb-0">{{ date('l d F Y') }}</p>
        </div>

        <!-- Stat Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6">
                <div class="stat-card primary">
                    <i class="ri-route-line stat-icon"></i>
                    <div class="stat-value" id="kmOggi">{{ number_format($kmOggi, 1) }}</div>
                    <div class="stat-label">Km oggi</div>
                </div>
            </div>
            <div class="col-6">
                <div class="stat-card success">
                    <i class="ri-checkbox-circle-line stat-icon"></i>
                    <div class="stat-value" id="consegneOggi">{{ $consegneOggi ?? 0 }}</div>
                    <div class="stat-label">Consegne oggi</div>
                </div>
            </div>
        </div>

        <!-- Info Mezzo -->
        @if($dispositivo)
            <div class="card-custom mb-4">
                <div class="card-header">
                    <i class="ri-car-line text-primary"></i>
                    Il tuo mezzo
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon blue me-3" style="width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="ri-truck-line" style="font-size: 2rem;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1">{{ $dispositivo->nome_mezzo ?? 'Mezzo' }}</h5>
                            <div class="text-muted">
                                <i class="ri-hashtag"></i> {{ $dispositivo->targa ?? 'N/D' }}
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-primary" style="font-size: 1.3rem;">
                                {{ number_format($dispositivo->km_attuali ?? 0, 0, ',', '.') }}
                            </div>
                            <small class="text-muted">km totali</small>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="card-custom mb-4">
                <div class="card-body">
                    <div class="empty-state">
                        <i class="ri-car-line"></i>
                        <p>Nessun mezzo associato</p>
                        <small class="text-muted">Contatta l'amministratore per associare un mezzo</small>
                    </div>
                </div>
            </div>
        @endif

        <!-- Azioni Rapide -->
        <div class="section-title">
            <i class="ri-flashlight-line text-warning"></i>
            Azioni rapide
        </div>

        <a href="/autista/piano-giornaliero" class="list-item">
            <div class="icon blue">
                <i class="ri-route-line"></i>
            </div>
            <div class="content">
                <div class="title">Piano Giornaliero</div>
                <div class="subtitle">Percorso ottimizzato e piano di carico</div>
            </div>
            <i class="ri-arrow-right-s-line arrow"></i>
        </a>

        <a href="/autista/tracking" class="list-item">
            <div class="icon green">
                <i class="ri-gps-line"></i>
            </div>
            <div class="content">
                <div class="title">Avvia Tracking GPS</div>
                <div class="subtitle">Inizia a registrare il tuo percorso</div>
            </div>
            <i class="ri-arrow-right-s-line arrow"></i>
        </a>

        <a href="/autista/consegne" class="list-item d-none">
            <div class="icon orange">
                <i class="ri-file-list-3-line"></i>
            </div>
            <div class="content">
                <div class="title">Le mie consegne</div>
                <div class="subtitle">Visualizza le consegne di oggi</div>
            </div>
            <i class="ri-arrow-right-s-line arrow"></i>
        </a>



        <a href="/autista/storico" class="list-item">
            <div class="icon red">
                <i class="ri-history-line"></i>
            </div>
            <div class="content">
                <div class="title">Storico km</div>
                <div class="subtitle">Visualizza i km percorsi negli ultimi giorni</div>
            </div>
            <i class="ri-arrow-right-s-line arrow"></i>
        </a>
        <!-- Pulsante Segnala Guasto -->
        <a href="/autista/segnala-guasto" class="list-item" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
            <div class="icon" style="background: rgba(255,255,255,0.2); color: white;">
                <i class="ri-alarm-warning-line"></i>
            </div>
            <div class="content">
                <div class="title" style="color: white;">Segnala Guasto</div>
                <div class="subtitle" style="color: rgba(255,255,255,0.7);">Comunica un problema al mezzo</div>
            </div>
            <i class="ri-arrow-right-s-line arrow" style="color: white;"></i>
        </a>

        <!-- Km ultimi 7 giorni -->
        @if(count($kmSettimana) > 0)
            <div class="section-title mt-4">
                <i class="ri-bar-chart-box-line text-primary"></i>
                Ultimi 7 giorni
            </div>

            <div class="card-custom">
                <div class="card-body p-0">
                    @foreach($kmSettimana as $giorno)
                        <div class="d-flex justify-content-between align-items-center p-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div>
                                <div class="fw-500">{{ \Carbon\Carbon::parse($giorno->data)->translatedFormat('l') }}</div>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($giorno->data)->format('d/m/Y') }}</small>
                            </div>
                            <div class="fw-bold text-primary">
                                {{ number_format($giorno->km_percorsi, 1) }} km
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection

@section('scripts')
    <script>
        // Aggiorna i dati ogni 30 secondi
        setInterval(async () => {
            try {
                const response = await fetch('/autista/api/stats');
                const data = await response.json();

                if (data.success) {
                    document.getElementById('kmOggi').textContent = data.kmOggi.toFixed(1);
                    document.getElementById('consegneOggi').textContent = data.consegneOggi;
                }
            } catch (error) {
                console.error('Errore aggiornamento stats:', error);
            }
        }, 30000);
    </script>
@endsection