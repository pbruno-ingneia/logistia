<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Logistia - Notifiche</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        body { background: #f5f5f5; min-height: 100vh; padding-bottom: 80px; }
        .header-autista {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white; padding: 15px 20px; position: sticky; top: 0; z-index: 100;
        }
        .notifica-card {
            border: none; border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            margin-bottom: 10px; transition: all 0.3s;
            border-left: 4px solid transparent;
        }
        .notifica-card.non-letta {
            background: #fff;
            border-left-color: #3498db;
        }
        .notifica-card.letta {
            background: #fafafa;
            opacity: 0.7;
        }
        .notifica-icon {
            width: 40px; height: 40px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; flex-shrink: 0;
        }
        .tipo-nuovo_ordine { background: #e3f2fd; }
        .tipo-cambio_stato { background: #fff3e0; }
        .tipo-modifica_ordine { background: #e8f5e9; }
        .tipo-messaggio { background: #f3e5f5; }
        .tipo-urgente { background: #ffebee; }
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; right: 0;
            background: white; border-top: 1px solid #eee;
            display: flex; z-index: 100; padding: 8px 0;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        }
        .bottom-nav a {
            flex: 1; text-align: center; text-decoration: none;
            color: #999; font-size: 0.7rem; padding: 5px 0;
        }
        .bottom-nav a.active { color: #3498db; }
        .bottom-nav a i { font-size: 1.4rem; display: block; }
    </style>
</head>
<body>

<!-- Header -->
<div class="header-autista">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">🔔 Notifiche</h5>
            <small class="opacity-75">
                {{ $notifiche->where('letta', 0)->count() }} non lette
            </small>
        </div>
        @if($notifiche->where('letta', 0)->count() > 0)
            <button class="btn btn-outline-light btn-sm" onclick="segnaTutteLette()">
                <i class="ri-check-double-line"></i> Leggi tutte
            </button>
        @endif
    </div>
</div>

<div class="container-fluid py-3">

    @forelse($notifiche as $notifica)
        <div class="notifica-card card {{ $notifica->letta ? 'letta' : 'non-letta' }}"
             id="notifica-{{ $notifica->id }}"
             onclick="segnaLetta({{ $notifica->id }}, '{{ $notifica->id_ordine }}')">
            <div class="card-body py-3 d-flex align-items-start gap-3">
                <!-- Icona tipo -->
                <div class="notifica-icon tipo-{{ $notifica->tipo }}">
                    @switch($notifica->tipo)
                        @case('nuovo_ordine') 📦 @break
                        @case('cambio_stato') 🔄 @break
                        @case('modifica_ordine') ✏️ @break
                        @case('messaggio') 💬 @break
                        @case('urgente') 🔴 @break
                    @endswitch
                </div>

                <!-- Contenuto -->
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start">
                        <strong style="font-size: 0.9rem;">{{ $notifica->titolo }}</strong>
                        @if(!$notifica->letta)
                            <span class="badge bg-primary" style="font-size: 0.6rem;">NUOVA</span>
                        @endif
                    </div>

                    @if($notifica->messaggio)
                        <p class="text-muted mb-1" style="font-size: 0.85rem;">{{ $notifica->messaggio }}</p>
                    @endif

                    <div class="d-flex gap-3" style="font-size: 0.75rem;">
                        <span class="text-muted">
                            <i class="ri-time-line"></i>
                            {{ \Carbon\Carbon::parse($notifica->created_at)->locale('it')->diffForHumans() }}
                        </span>
                        @if($notifica->numero_ordine)
                            <span class="text-primary">
                                <i class="ri-file-text-line"></i> {{ $notifica->numero_ordine }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-5">
            <i class="ri-notification-off-line" style="font-size: 3rem; color: #ddd;"></i>
            <h6 class="text-muted mt-2">Nessuna notifica</h6>
            <p class="text-muted small">Le notifiche sui tuoi ordini appariranno qui.</p>
        </div>
    @endforelse

</div>

<!-- Bottom Navigation -->
<div class="bottom-nav">
    <a href="/autista/dashboard">
        <i class="ri-dashboard-line"></i> Dashboard
    </a>
    <a href="/autista/consegne">
        <i class="ri-truck-line"></i> Consegne
    </a>
    <a href="/autista/storico">
        <i class="ri-history-line"></i> Storico
    </a>
    <a href="/autista/notifiche" class="active">
        <i class="ri-notification-3-line"></i> Notifiche
    </a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const csrfToken = '{{ csrf_token() }}';

    function segnaLetta(idNotifica, idOrdine) {
        const card = document.getElementById('notifica-' + idNotifica);
        if (card.classList.contains('letta')) {
            // Già letta, naviga all'ordine se presente
            if (idOrdine) {
                window.location.href = '/autista/consegne';
            }
            return;
        }

        fetch('/autista/notifiche/' + idNotifica + '/letta', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    card.classList.remove('non-letta');
                    card.classList.add('letta');
                    card.querySelector('.badge')?.remove();

                    if (idOrdine) {
                        window.location.href = '/autista/consegne';
                    }
                }
            });
    }

    function segnaTutteLette() {
        fetch('/autista/notifiche/segna-tutte-lette', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.querySelectorAll('.non-letta').forEach(card => {
                        card.classList.remove('non-letta');
                        card.classList.add('letta');
                        card.querySelector('.badge')?.remove();
                    });
                }
            });
    }
</script>
</body>
</html>