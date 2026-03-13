@extends('autista.common.layout')

@section('title', 'Profilo')

@section('styles')
    <style>
        .profile-header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-color) 100%);
            color: white;
            padding: 30px 20px;
            border-radius: var(--radius-lg);
            text-align: center;
            margin-bottom: 25px;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 2.5rem;
            font-weight: 700;
            border: 4px solid rgba(255,255,255,0.3);
        }

        .profile-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .profile-role {
            opacity: 0.8;
            font-size: 0.95rem;
        }

        .info-section {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .info-section .section-header {
            padding: 15px 20px;
            background: #f8f9fa;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #eee;
        }

        .info-section .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-section .info-row:last-child {
            border-bottom: none;
        }

        .info-section .info-label {
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-section .info-value {
            font-weight: 500;
            text-align: right;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            background: white;
            border-radius: var(--radius-md);
            margin-bottom: 10px;
            box-shadow: var(--shadow-sm);
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
        }

        .menu-item:hover {
            transform: translateX(5px);
        }

        .menu-item .icon {
            width: 45px;
            height: 45px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin-right: 15px;
        }

        .menu-item .icon.blue { background: rgba(52, 152, 219, 0.1); color: var(--primary-color); }
        .menu-item .icon.green { background: rgba(39, 174, 96, 0.1); color: var(--success-color); }
        .menu-item .icon.orange { background: rgba(243, 156, 18, 0.1); color: var(--warning-color); }
        .menu-item .icon.red { background: rgba(231, 76, 60, 0.1); color: var(--danger-color); }

        .menu-item .content {
            flex: 1;
        }

        .menu-item .title {
            font-weight: 500;
        }

        .menu-item .subtitle {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .menu-item .arrow {
            color: var(--text-muted);
        }

        .logout-btn {
            width: 100%;
            padding: 15px;
            margin-top: 20px;
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
            border: none;
            border-radius: var(--radius-md);
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .version-info {
            text-align: center;
            padding: 20px;
            color: var(--text-muted);
            font-size: 0.85rem;
        }
    </style>
@endsection

@section('content')
    <div class="fade-in">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar">
                {{ strtoupper(substr($utente->nome ?? 'U', 0, 1)) }}{{ strtoupper(substr($utente->cognome ?? '', 0, 1)) }}
            </div>
            <div class="profile-name">{{ $utente->nome ?? '' }} {{ $utente->cognome ?? '' }}</div>
            <div class="profile-role">
                <i class="ri-truck-line me-1"></i>
                Autista
            </div>
        </div>

        <!-- Info Personali -->
        <div class="info-section">
            <div class="section-header">
                <i class="ri-user-3-line text-primary"></i>
                Informazioni personali
            </div>
            <div class="info-row">
            <span class="info-label">
                <i class="ri-mail-line"></i>
                Email
            </span>
                <span class="info-value">{{ $utente->email ?? 'N/D' }}</span>
            </div>
            <div class="info-row">
            <span class="info-label">
                <i class="ri-phone-line"></i>
                Telefono
            </span>
                <span class="info-value">{{ $utente->telefono ?? 'N/D' }}</span>
            </div>
        </div>

        <!-- Info Mezzo -->
        @if($dispositivo)
            <div class="info-section">
                <div class="section-header">
                    <i class="ri-truck-line text-primary"></i>
                    Mezzo assegnato
                </div>
                <div class="info-row">
            <span class="info-label">
                <i class="ri-car-line"></i>
                Veicolo
            </span>
                    <span class="info-value">{{ $dispositivo->nome_mezzo ?? 'N/D' }}</span>
                </div>
                <div class="info-row">
            <span class="info-label">
                <i class="ri-hashtag"></i>
                Targa
            </span>
                    <span class="info-value">{{ $dispositivo->targa ?? 'N/D' }}</span>
                </div>
                <div class="info-row">
            <span class="info-label">
                <i class="ri-route-line"></i>
                Km attuali
            </span>
                    <span class="info-value text-primary">{{ number_format($dispositivo->km_attuali ?? 0, 0, ',', '.') }} km</span>
                </div>
            </div>
        @endif

        <!-- Menu
        <div class="section-title mt-4">
            <i class="ri-settings-3-line text-primary"></i>
            Impostazioni
        </div>

        <a href="/autista/notifiche" class="menu-item">
            <div class="icon blue">
                <i class="ri-notification-3-line"></i>
            </div>
            <div class="content">
                <div class="title">Notifiche</div>
                <div class="subtitle">Gestisci le notifiche push</div>
            </div>
            <i class="ri-arrow-right-s-line arrow"></i>
        </a>

        <a href="/autista/privacy" class="menu-item">
            <div class="icon green">
                <i class="ri-shield-check-line"></i>
            </div>
            <div class="content">
                <div class="title">Privacy</div>
                <div class="subtitle">Informativa e consensi</div>
            </div>
            <i class="ri-arrow-right-s-line arrow"></i>
        </a>

        <a href="/autista/supporto" class="menu-item">
            <div class="icon orange">
                <i class="ri-question-line"></i>
            </div>
            <div class="content">
                <div class="title">Supporto</div>
                <div class="subtitle">Assistenza e FAQ</div>
            </div>
            <i class="ri-arrow-right-s-line arrow"></i>
        </a>

-->
        <!-- Logout -->
        <button class="logout-btn" onclick="logout()">
            <i class="ri-logout-box-r-line"></i>
            Esci
        </button>

        <!-- Version -->
        <div class="version-info">
            <div>Logistia - Area Autista</div>
            <div>Versione 1.0.0</div>
        </div>
    </div>

    <!-- Modal Conferma Logout -->
    <div class="modal fade" id="logoutModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: var(--radius-lg);">
                <div class="modal-body text-center py-4">
                    <i class="ri-logout-box-r-line text-danger mb-3" style="font-size: 3rem;"></i>
                    <h5>Vuoi uscire?</h5>
                    <p class="text-muted">Dovrai effettuare nuovamente l'accesso</p>
                </div>
                <div class="modal-footer border-0 justify-content-center gap-3">
                    <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Annulla</button>
                    <a href="/azienda/logout" class="btn btn-danger">
                        <i class="ri-logout-box-r-line me-1"></i>
                        Esci
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function logout() {
            new bootstrap.Modal(document.getElementById('logoutModal')).show();
        }
    </script>
@endsection