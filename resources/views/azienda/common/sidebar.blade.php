<?php $utente = session('utente'); ?>

<div id="scrollbar">
    <div class="container-fluid">

        <div id="two-column-menu">
        </div>
        <ul class="navbar-nav" id="navbar-nav">

            <li class="menu-title"><span data-key="t-menu">Menu</span></li>

            <li class="nav-item">
                <a class="nav-link menu-link" href="/azienda/index">
                    <i class="ri-home-line"></i> <span data-key="t-widgets">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/azienda/ordini-trasporto" class="nav-link">
                    <i class="ri-file-list-3-line"></i> Ordini Trasporto
                </a>
            </li>
            <li class="nav-item">
                <a href="/azienda/centro-operativo" class="nav-link">
                    <i class="ri-map-pin-user-line"></i> Centro Operativo
                </a>
            </li>
            <!-- Tariffari e Costi -->
            <li class="nav-item">
                <a href="/azienda/tariffari" class="nav-link">
                    <i class="ri-price-tag-3-line"></i> Tariffari & Costi
                </a>
            </li>

            <!-- Documenti Trasporto -->
            <li class="nav-item">
                <a href="/azienda/documenti-trasporto" class="nav-link">
                    <i class="ri-file-text-line"></i> Documenti
                </a>
            </li>
            <li class="nav-item">
                <a href="/azienda/pedane" class="nav-link">
                    <i class="ri-stack-line"></i> Pedane
                </a>
            </li>
            <li class="nav-item">
                <a href="/azienda/clienti" class="nav-link">
                    <i class="ri-user-2-line"></i> Clienti
                </a>
            </li>
            <li class="nav-item">
                <a href="/azienda/planning-autisti" class="nav-link">
                    <i class="ri-calendar-check-line"></i> Planning Autisti
                </a>
            </li>


            <?php if(isset($utente->vista_operaio) && (int) $utente->vista_operaio !== 1): ?>

            {{--<li class="nav-item">
                <a class="nav-link menu-link" href="/azienda/cantieri">
                    <i class="ri-building-4-fill"></i> <span data-key="t-widgets">Cantieri</span>
                </a>
            </li>--}}
            @if($utente->gestione_magazzino == 1)
            {{--<li class="nav-item">
                <a class="nav-link menu-link" href="#sidebar_magazzino" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebar_magazzino">
                    <i class=" ri-archive-line"></i> <span data-key="t-dashboards">Magazzino</span>
                </a>
                <div class="collapse menu-dropdown" id="sidebar_magazzino">
                    <ul class="nav nav-sm flex-column">
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="/azienda/materiali">
                                <i class="ri-paint-fill"></i> <span data-key="t-widgets">Materiali</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="/azienda/strumenti">
                                <i class="ri-tools-fill"></i> <span data-key="t-widgets">Strumenti</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="/azienda/movimenti">
                                <i class=" ri-arrow-left-right-line"></i> <span data-key="t-widgets">Movimenti</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>--}}
            @endif
            @if($utente->gestione_mezzi == 1)
            <li class="nav-item">
                <a class="nav-link menu-link" href="#sidebar_mezzi" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebar_mezzi">
                    <i class="ri-truck-fill"></i> <span data-key="t-dashboards">Gestione Mezzi</span>
                </a>
                <div class="collapse menu-dropdown" id="sidebar_mezzi">
                    <ul class="nav nav-sm flex-column">
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="/azienda/mezzi">
                                <i class="ri-truck-fill"></i> <span data-key="t-widgets">Anagrafica Mezzi</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="/azienda/tracking">
                                <i class="ri-gps-line"></i> <span>Tracking Flotta</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="/azienda/flotta">
                                <i class="ri-car-line"></i> <span data-key="t-widgets">Tracking Flotta Live</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endif
            <!-- Aggiungi questa voce nella sidebar dopo "Ruoli" -->

            {{--<li class="nav-item">
                <a class="nav-link menu-link" href="/azienda/responsabili">
                    <i class="ri-user-star-line"></i> <span data-key="t-widgets">responsabili</span>
                </a>
            </li>--}}
            {{--<LI CLASS="NAV-ITEM">
                <A CLASS="NAV-LINK MENU-LINK" HREF="/AZIENDA/DIPENDENTI/VISUALIZZA">
                    <I CLASS="RI-USER-STAR-LINE"></I> <SPAN DATA-KEY="T-WIDGETS">CALENDARIO DIPENDENTI</SPAN>
                </A>
            </LI>--}}


            <li class="nav-item">
                <a class="nav-link menu-link" href="#sidebar_reports" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebar_reports">
                    <i class="ri-file-chart-line"></i> <span data-key="t-dashboards">Report TMS</span>
                </a>
                <div class="collapse menu-dropdown" id="sidebar_reports">
                    <ul class="nav nav-sm flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('reports.tms') }}">
                                <i class="ri-dashboard-3-line"></i> Centro Report
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/azienda/analytics/dashboard" class="nav-link">
                                <i class="ri-file-text-line"></i> Analisi
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        @if($utente->admin_azienda = 1 || ($utente->gestione_mezzi == 1))

            <li class="nav-item">
                <a class="nav-link menu-link" href="/azienda/ruoli">
                    <i class="ri-user-star-line"></i> <span data-key="t-widgets">Ruoli</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link menu-link" href="/azienda/utenti">
                    <i class="ri-user-line"></i> <span data-key="t-widgets">Utenti</span>
                </a>
            </li>
            @endif
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link menu-link" href="/azienda/logout">
                    <i class="ri-logout-box-r-line"></i> <span data-key="t-widgets">Logout</span>
                </a>
            </li>

        </ul>
    </div>
    <!-- Sidebar -->
</div>
