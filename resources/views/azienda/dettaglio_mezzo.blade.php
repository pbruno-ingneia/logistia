@include('azienda.common.header')

<div class="page-content">
    <div class="container-fluid">
        <!-- Header del mezzo -->
        <div class="card">
            <div class="card-header d-flex justify-content-between bg-primary text-white">
                <h5 class="mb-0" style="color: #f8f9fa !important;">{{ $mezzo->nome }} - {{ $mezzo->targa }}</h5>
                <div class="flex-shrink-0">
                    <a href="{{ url('/azienda/mezzi') }}" class="btn btn-success">
                        <i class="ri-arrow-go-back-line align-bottom"></i> Torna ai Mezzi
                    </a>
                </div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="card mt-3">
            <div class="card-body">
                <ul class="nav nav-tabs nav-justified" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#info-generali" role="tab">
                            <i class="ri-file-info-line"></i> Info Generali
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#carburante" role="tab">
                            <i class="ri-gas-station-line"></i> Carburante
                            @if(isset($stats_carburante) && $stats_carburante['num_rifornimenti'] > 0)
                                <span class="badge bg-info ms-1">{{ $stats_carburante['num_rifornimenti'] }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#gomme" role="tab">
                            <i class="ri-roadster-line"></i> Gomme
                            @if(isset($alert_gomme) && $alert_gomme > 0)
                                <span class="badge bg-danger ms-1">{{ $alert_gomme }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tagliandi" role="tab">
                            <i class="ri-calendar-check-line"></i> Tagliandi
                            @if(isset($tagliando_scaduto) && $tagliando_scaduto)
                                <span class="badge bg-danger ms-1">!</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#manutenzioni" role="tab">
                            <i class="ri-tools-line"></i> Manutenzioni
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#impostazioni" role="tab">
                            <i class="ri-settings-3-line"></i> Impostazioni
                        </a>
                    </li>
                </ul>

                <div class="tab-content mt-4">

                    <!-- ======================== -->
                    <!-- TAB INFO GENERALI -->
                    <!-- ======================== -->
                    <div class="tab-pane active" id="info-generali" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr><th width="30%">Nome</th><td>{{ $mezzo->nome }}</td></tr>
                                    <tr><th>Tipo</th><td>{{ $mezzo->tipo }}</td></tr>
                                    <tr><th>Targa</th><td>{{ $mezzo->targa }}</td></tr>
                                    <tr><th>Anno</th><td>{{ $mezzo->anno_immatricolazione }}</td></tr>
                                    <tr>
                                        <th>Stato</th>
                                        <td>
                                            <form action="{{ url('/azienda/mezzo/'.$mezzo->id.'/modifica-stato') }}" method="POST" id="formCambioStato">
                                                @csrf
                                                <select name="stato" class="form-select" onchange="this.form.submit()">
                                                    <option value="Disponibile" {{ $mezzo->stato == 'Disponibile' ? 'selected' : '' }}>Disponibile</option>
                                                    <option value="In uso" {{ $mezzo->stato == 'In uso' ? 'selected' : '' }}>In uso</option>
                                                    <option value="Manutenzione" {{ $mezzo->stato == 'Manutenzione' ? 'selected' : '' }}>Manutenzione</option>
                                                </select>
                                            </form>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6>Km Attuali</h6>
                                        <h3 class="text-primary">{{ number_format($mezzo->km_attuali ?? 0) }} km</h3>
                                        <button class="btn btn-sm btn-primary" onclick="aggiornaKm()">Aggiorna Km</button>
                                    </div>
                                </div>

                                {{-- Mini riepilogo carburante --}}
                                @if(isset($stats_carburante) && $stats_carburante['num_rifornimenti'] > 0)
                                    <div class="card bg-light mt-3">
                                        <div class="card-body">
                                            <h6 class="text-center"><i class="ri-gas-station-line text-warning"></i> Riepilogo Carburante</h6>
                                            <div class="row text-center">
                                                <div class="col-4">
                                                    <small class="text-muted d-block">Consumo</small>
                                                    <strong class="text-primary">
                                                        {{ $stats_carburante['consumo_medio'] > 0 ? $stats_carburante['consumo_medio'].' km/l' : 'N/D' }}
                                                    </strong>
                                                </div>
                                                <div class="col-4">
                                                    <small class="text-muted d-block">Costo/Km</small>
                                                    <strong class="text-danger">
                                                        {{ $stats_carburante['costo_per_km'] > 0 ? '€ '.number_format($stats_carburante['costo_per_km'], 3, ',', '.') : 'N/D' }}
                                                    </strong>
                                                </div>
                                                <div class="col-4">
                                                    <small class="text-muted d-block">Spesa Mese</small>
                                                    <strong class="text-success">€ {{ number_format($stats_carburante['spesa_ultimo_mese'], 2, ',', '.') }}</strong>
                                                </div>
                                            </div>
                                            <div class="text-center mt-2">
                                                <a href="#carburante" class="btn btn-outline-warning btn-sm"
                                                   onclick="document.querySelector('a[href=\'#carburante\']').click()">
                                                    Dettagli Carburante <i class="ri-arrow-right-line"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if(isset($prossime_scadenze) && count($prossime_scadenze) > 0)
                            <div class="alert alert-warning mt-3">
                                <h6><i class="ri-alarm-warning-line"></i> Prossime Scadenze:</h6>
                                <ul class="mb-0">
                                    @foreach($prossime_scadenze as $scadenza)
                                        <li>{{ $scadenza }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <!-- ======================== -->
                    <!-- TAB CARBURANTE - NUOVO -->
                    <!-- ======================== -->
                    <div class="tab-pane" id="carburante" role="tabpanel">

                        {{-- Cards Statistiche --}}
                        <div class="row mb-4">
                            <div class="col-md-2">
                                <div class="card text-center border-primary">
                                    <div class="card-body py-3">
                                        <i class="ri-gas-station-line text-primary" style="font-size: 24px;"></i>
                                        <h4 class="mb-0 mt-2 text-primary">{{ $stats_carburante['num_rifornimenti'] ?? 0 }}</h4>
                                        <small class="text-muted">Rifornimenti</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card text-center border-danger">
                                    <div class="card-body py-3">
                                        <i class="ri-money-euro-circle-line text-danger" style="font-size: 24px;"></i>
                                        <h4 class="mb-0 mt-2 text-danger">€ {{ number_format($stats_carburante['totale_speso'] ?? 0, 0, ',', '.') }}</h4>
                                        <small class="text-muted">Totale Speso</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card text-center border-info">
                                    <div class="card-body py-3">
                                        <i class="ri-drop-line text-info" style="font-size: 24px;"></i>
                                        <h4 class="mb-0 mt-2 text-info">{{ number_format($stats_carburante['totale_litri'] ?? 0, 0, ',', '.') }} L</h4>
                                        <small class="text-muted">Totale Litri</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card text-center border-success">
                                    <div class="card-body py-3">
                                        <i class="ri-speed-line text-success" style="font-size: 24px;"></i>
                                        <h4 class="mb-0 mt-2 text-success">
                                            {{ ($stats_carburante['consumo_medio'] ?? 0) > 0 ? $stats_carburante['consumo_medio'] : '--' }}
                                        </h4>
                                        <small class="text-muted">km/l Medio</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card text-center border-warning">
                                    <div class="card-body py-3">
                                        <i class="ri-money-euro-box-line text-warning" style="font-size: 24px;"></i>
                                        <h4 class="mb-0 mt-2 text-warning">
                                            {{ ($stats_carburante['costo_per_km'] ?? 0) > 0 ? '€ '.number_format($stats_carburante['costo_per_km'], 2, ',', '.') : '--' }}
                                        </h4>
                                        <small class="text-muted">Costo/Km</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card text-center border-secondary">
                                    <div class="card-body py-3">
                                        <i class="ri-line-chart-line text-secondary" style="font-size: 24px;"></i>
                                        <h4 class="mb-0 mt-2 text-secondary">€ {{ number_format($stats_carburante['previsione_mensile'] ?? 0, 0, ',', '.') }}</h4>
                                        <small class="text-muted">Prev./Mese</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            {{-- Colonna Sinistra: Grafico + Analisi + Storico --}}
                            <div class="col-lg-8">

                                {{-- Grafico --}}
                                @if(isset($grafico_carburante) && count($grafico_carburante) > 0)
                                    <div class="card mb-4">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0"><i class="ri-line-chart-line me-2"></i>Andamento Ultimi 12 Mesi</h6>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <input type="radio" class="btn-check" name="graficoTipo" id="grafico_costi" value="costi" checked>
                                                <label class="btn btn-outline-primary" for="grafico_costi">Costi €</label>
                                                <input type="radio" class="btn-check" name="graficoTipo" id="grafico_litri" value="litri">
                                                <label class="btn btn-outline-info" for="grafico_litri">Litri</label>
                                                <input type="radio" class="btn-check" name="graficoTipo" id="grafico_consumo" value="consumo">
                                                <label class="btn btn-outline-success" for="grafico_consumo">km/l</label>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <canvas id="chartCarburante" height="250"></canvas>
                                        </div>
                                    </div>
                                @endif

                                {{-- Analisi Predittiva --}}
                                @if(isset($stats_carburante) && $stats_carburante['num_rifornimenti'] >= 3)
                                    <div class="card mb-4">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="ri-brain-line me-2 text-primary"></i>Analisi e Previsioni</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4 text-center">
                                                    <div class="border rounded p-3">
                                                        <small class="text-muted d-block">Previsione Mensile</small>
                                                        <h3 class="text-primary mb-0">€ {{ number_format($stats_carburante['previsione_mensile'], 2, ',', '.') }}</h3>
                                                        <small class="text-muted">Media ultimi 3 mesi</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 text-center">
                                                    <div class="border rounded p-3">
                                                        <small class="text-muted d-block">Previsione Annuale</small>
                                                        <h3 class="text-danger mb-0">€ {{ number_format($stats_carburante['previsione_mensile'] * 12, 2, ',', '.') }}</h3>
                                                        <small class="text-muted">Stima su 12 mesi</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 text-center">
                                                    <div class="border rounded p-3">
                                                        <small class="text-muted d-block">Costo per 100 km</small>
                                                        <h3 class="text-warning mb-0">
                                                            {{ $stats_carburante['costo_per_km'] > 0 ? '€ '.number_format($stats_carburante['costo_per_km'] * 100, 2, ',', '.') : 'N/D' }}
                                                        </h3>
                                                        <small class="text-muted">Basato su dati reali</small>
                                                    </div>
                                                </div>
                                            </div>

                                            @if($stats_carburante['consumo_medio'] > 0)
                                                <div class="mt-3 p-3 bg-light rounded">
                                                    <h6><i class="ri-lightbulb-line text-warning"></i> Insight</h6>
                                                    <p class="mb-1">
                                                        Con un consumo medio di <strong>{{ $stats_carburante['consumo_medio'] }} km/l</strong>
                                                        e un prezzo medio di <strong>€ {{ number_format($stats_carburante['prezzo_medio_litro'], 3, ',', '.') }}/l</strong>,
                                                        ogni 1000 km costano circa
                                                        <strong>€ {{ number_format((1000 / $stats_carburante['consumo_medio']) * $stats_carburante['prezzo_medio_litro'], 2, ',', '.') }}</strong>.
                                                    </p>
                                                    @php $consumoL100 = $stats_carburante['consumo_medio'] > 0 ? round(100 / $stats_carburante['consumo_medio'], 2) : 0; @endphp
                                                    <p class="mb-0 text-muted">
                                                        Consumo: <strong>{{ $consumoL100 }} l/100km</strong> |
                                                        Prezzo medio: <strong>€ {{ number_format($stats_carburante['prezzo_medio_litro'], 3, ',', '.') }}/l</strong>
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- Tabella Storico Rifornimenti --}}
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0"><i class="ri-history-line me-2"></i>Storico Rifornimenti</h6>
                                        <button class="btn btn-primary btn-sm" onclick="apriModalRifornimento()">
                                            <i class="ri-add-line"></i> Nuovo Rifornimento
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        @if(isset($rifornimenti) && count($rifornimenti) > 0)
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-hover">
                                                    <thead class="table-light">
                                                    <tr>
                                                        <th>Data</th>
                                                        <th>Km</th>
                                                        <th>Litri</th>
                                                        <th>Importo</th>
                                                        <th>€/L</th>
                                                        <th>km/l</th>
                                                        <th>Stazione</th>
                                                        <th>Scontr.</th>
                                                        <th width="60">Azioni</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($rifornimenti as $rif)
                                                        <tr>
                                                            <td>{{ date('d/m/Y', strtotime($rif->data_rifornimento)) }}</td>
                                                            <td>{{ number_format($rif->km_rifornimento) }}</td>
                                                            <td><strong>{{ number_format($rif->litri, 2, ',', '.') }} L</strong></td>
                                                            <td class="text-end"><strong>€ {{ number_format($rif->importo_totale, 2, ',', '.') }}</strong></td>
                                                            <td>€ {{ $rif->prezzo_litro ? number_format($rif->prezzo_litro, 3, ',', '.') : '-' }}</td>
                                                            <td>
                                                                @if($rif->consumo_calcolato)
                                                                    <span class="badge bg-{{ $rif->consumo_calcolato > 8 ? 'success' : ($rif->consumo_calcolato > 5 ? 'warning' : 'danger') }}">
                                                                        {{ number_format($rif->consumo_calcolato, 1, ',', '.') }}
                                                                    </span>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                            <td><small>{{ $rif->stazione_servizio ?? '-' }}</small></td>
                                                            <td class="text-center">
                                                                @if($rif->foto_scontrino)
                                                                    <a href="{{ asset($rif->foto_scontrino) }}" target="_blank" class="btn btn-outline-info btn-sm" title="Vedi scontrino">
                                                                        <i class="ri-image-line"></i>
                                                                    </a>
                                                                @else
                                                                    <button class="btn btn-outline-secondary btn-sm" onclick="uploadScontrinoEsistente({{ $rif->id }})" title="Carica scontrino">
                                                                        <i class="ri-camera-line"></i>
                                                                    </button>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <form method="POST" action="{{ url('/azienda/mezzo/'.$mezzo->id.'/rifornimento/elimina') }}"
                                                                      style="display:inline;" onsubmit="return confirm('Eliminare?')">
                                                                    @csrf
                                                                    <input type="hidden" name="id_rifornimento" value="{{ $rif->id }}">
                                                                    <button type="submit" class="btn btn-danger btn-sm"><i class="ri-delete-bin-line"></i></button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="text-center py-5">
                                                <i class="ri-gas-station-line text-muted" style="font-size: 48px;"></i>
                                                <h5 class="text-muted mt-3">Nessun rifornimento registrato</h5>
                                                <p class="text-muted">Inizia a registrare i rifornimenti per statistiche sui consumi.</p>
                                                <button class="btn btn-primary" onclick="apriModalRifornimento()">
                                                    <i class="ri-add-line"></i> Registra Primo Rifornimento
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Colonna Destra: Form rapido + Riepilogo --}}
                            <div class="col-lg-4">

                                {{-- Form Registrazione Rapida --}}
                                <div class="card mb-4">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0"><i class="ri-add-circle-line me-2"></i>Registra Rifornimento</h6>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="{{ url('/azienda/mezzo/'.$mezzo->id.'/rifornimento') }}" enctype="multipart/form-data">
                                            @csrf
                                            <div class="row">
                                                <div class="col-6 mb-3">
                                                    <label class="form-label">Data <span class="text-danger">*</span></label>
                                                    <input type="date" name="data_rifornimento" class="form-control" value="{{ date('Y-m-d') }}" required>
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label class="form-label">Km <span class="text-danger">*</span></label>
                                                    <input type="number" name="km_rifornimento" class="form-control" value="{{ $mezzo->km_attuali ?? 0 }}" required>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-6 mb-3">
                                                    <label class="form-label">Litri <span class="text-danger">*</span></label>
                                                    <input type="number" step="0.01" name="litri" class="form-control" id="input_litri" placeholder="0.00" required oninput="calcolaPrezzo()">
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label class="form-label">Importo € <span class="text-danger">*</span></label>
                                                    <input type="number" step="0.01" name="importo_totale" class="form-control" id="input_importo" placeholder="0.00" required oninput="calcolaPrezzo()">
                                                </div>
                                            </div>
                                            <div class="mb-2 text-center" id="prezzoCalcolato" style="display:none;">
                                                <small class="text-muted">Prezzo: <strong id="prezzoLitroCalcolato">-</strong> €/L</small>
                                            </div>
                                            <div class="row">
                                                <div class="col-6 mb-3">
                                                    <label class="form-label">Carburante</label>
                                                    <select name="tipo_carburante" class="form-select">
                                                        <option value="diesel">Diesel</option>
                                                        <option value="benzina">Benzina</option>
                                                        <option value="gpl">GPL</option>
                                                        <option value="metano">Metano</option>
                                                        <option value="elettrico">Elettrico</option>
                                                    </select>
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label class="form-label">Pieno?</label>
                                                    <select name="pieno" class="form-select">
                                                        <option value="1">Sì, completo</option>
                                                        <option value="0">No, parziale</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Stazione</label>
                                                <input type="text" name="stazione_servizio" class="form-control" placeholder="Es: Eni Via Roma">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label"><i class="ri-camera-line"></i> Foto Scontrino</label>
                                                <input type="file" name="foto_scontrino" class="form-control" accept="image/*,application/pdf">
                                                <small class="text-muted">JPG, PNG o PDF</small>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Note</label>
                                                <textarea name="note" class="form-control" rows="2" placeholder="Opzionale..."></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-warning w-100">
                                                <i class="ri-gas-station-line"></i> Registra Rifornimento
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                {{-- Info calcolo --}}
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ri-information-line me-2"></i>Come funziona</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-2"><small>Il consumo (km/l) si calcola automaticamente tra due <strong>pieni completi</strong>:</small></p>
                                        <p class="mb-2"><small><strong>Formula:</strong> (Km attuale − Km pieno precedente) ÷ Litri</small></p>
                                        <p class="mb-0"><small class="text-muted">Per risultati accurati seleziona sempre "Pieno completo".</small></p>
                                    </div>
                                </div>

                                {{-- Riepilogo ultimo mese --}}
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ri-calendar-line me-2"></i>Ultimo Mese</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">Spesa:</span>
                                            <strong>€ {{ number_format($stats_carburante['spesa_ultimo_mese'] ?? 0, 2, ',', '.') }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">Litri:</span>
                                            <strong>{{ number_format($stats_carburante['litri_ultimo_mese'] ?? 0, 1, ',', '.') }} L</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">Prezzo medio/L:</span>
                                            <strong>€ {{ number_format($stats_carburante['prezzo_medio_litro'] ?? 0, 3, ',', '.') }}</strong>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Previsione prossimo mese:</span>
                                            <strong class="text-primary">€ {{ number_format($stats_carburante['previsione_mensile'] ?? 0, 2, ',', '.') }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- FINE TAB CARBURANTE -->

                    <!-- ======================== -->
                    <!-- TAB GOMME -->
                    <!-- ======================== -->
                    <div class="tab-pane" id="gomme" role="tabpanel">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Stato Gomme</h6>
                                        <small class="text-muted">Km attuali: {{ number_format($mezzo->km_attuali ?? 0) }} km</small>
                                    </div>
                                    <div class="card-body text-center">
                                        <div class="vehicle-container" style="position: relative; width: 300px; height: 150px; margin: 0 auto;">
                                            <div style="position: absolute; top: 25px; left: 50px; width: 200px; height: 100px; background: #e9ecef; border-radius: 10px; border: 2px solid #6c757d;"></div>
                                            @foreach(['anteriore_sx' => ['10px', '20px', 'AS'], 'anteriore_dx' => ['10px', '260px', 'AD'], 'posteriore_sx' => ['100px', '20px', 'PS'], 'posteriore_dx' => ['100px', '260px', 'PD']] as $pos => $config)
                                                <div class="gomma" data-posizione="{{ $pos }}"
                                                     style="position: absolute; top: {{ $config[0] }}; left: {{ $config[1] }}; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; border: 3px solid {{ $stati_gomme[$pos]['colore'] ?? '#28a745' }};"
                                                     title="{{ $config[2] }} - {{ $stati_gomme[$pos]['messaggio'] ?? 'Buona' }} ({{ number_format($stati_gomme[$pos]['km_percorsi_dalla_sostituzione'] ?? 0) }} km)">
                                                    <div style="background: {{ $stati_gomme[$pos]['colore'] ?? '#28a745' }}; width: 100%; height: 100%; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: bold;">{{ $config[2] }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="mt-4">
                                            <p class="text-muted">Clicca su una gomma per registrare la sostituzione</p>
                                            <div class="d-flex justify-content-center gap-3">
                                                <span><i class="ri-circle-fill text-success"></i> Buona</span>
                                                <span><i class="ri-circle-fill text-warning"></i> Da controllare</span>
                                                <span><i class="ri-circle-fill text-danger"></i> Da cambiare</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header"><h6 class="mb-0">Storico Sostituzioni</h6></div>
                                    <div class="card-body">
                                        @if(isset($sostituzioni_gomme) && count($sostituzioni_gomme) > 0)
                                            <div class="timeline">
                                                @foreach($sostituzioni_gomme as $sostituzione)
                                                    <div class="timeline-item">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div>
                                                                <small class="text-muted">{{ date('d/m/Y', strtotime($sostituzione->data_sostituzione)) }}</small>
                                                                <div><strong>{{ ucfirst(str_replace(['_', 'sx', 'dx'], [' ', 'Sinistra', 'Destra'], $sostituzione->posizione)) }}</strong></div>
                                                                <div class="text-muted">{{ number_format($sostituzione->km_sostituzione) }} km</div>
                                                                @if($sostituzione->marca_modello)
                                                                    <div class="text-info"><small><i class="ri-tire-line"></i> {{ $sostituzione->marca_modello }}</small></div>
                                                                @endif
                                                                @if($sostituzione->fornitore)
                                                                    <div class="text-muted"><small><i class="ri-store-line"></i> {{ $sostituzione->fornitore }}</small></div>
                                                                @endif
                                                            </div>
                                                            <div class="text-end">
                                                                @if($sostituzione->costo && $sostituzione->costo > 0)
                                                                    <span class="badge bg-success">{{ number_format($sostituzione->costo, 2, ',', '.') }} €</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-muted">Nessuna sostituzione registrata</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ======================== -->
                    <!-- TAB TAGLIANDI -->
                    <!-- ======================== -->
                    <div class="tab-pane" id="tagliandi" role="tabpanel">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Stato Tagliando</h6>
                                        <button class="btn btn-primary btn-sm" onclick="registraTagliando()">
                                            <i class="ri-calendar-check-line"></i> Registra Tagliando
                                        </button>
                                    </div>
                                    <div class="card-body text-center">
                                        @if(isset($ultimo_tagliando) && $ultimo_tagliando)
                                            <div class="alert alert-{{ $stato_tagliando['classe'] }}">
                                                <h5><i class="ri-calendar-line"></i> Ultimo Tagliando</h5>
                                                <p class="mb-2"><strong>Data:</strong> {{ date('d/m/Y', strtotime($ultimo_tagliando->data_operazione)) }}</p>
                                                <p class="mb-2"><strong>Km:</strong> {{ number_format($ultimo_tagliando->km_operazione ?? 0) }} km</p>
                                                <p class="mb-0"><strong>Stato:</strong> {{ $stato_tagliando['messaggio'] }}</p>
                                                @if($stato_tagliando['km_prossimo'] > 0)
                                                    <small>Prossimo tagliando a: {{ number_format($stato_tagliando['km_prossimo']) }} km</small>
                                                @endif
                                            </div>
                                        @else
                                            <div class="alert alert-warning">
                                                <h5><i class="ri-alert-line"></i> Nessun Tagliando Registrato</h5>
                                                <p>Non è stato ancora effettuato nessun tagliando per questo veicolo.</p>
                                            </div>
                                        @endif
                                        @if(isset($stato_tagliando))
                                            <div class="progress mt-3" style="height: 25px;">
                                                <div class="progress-bar bg-{{ $stato_tagliando['classe'] }}" style="width: {{ min(100, $stato_tagliando['percentuale']) }}%">
                                                    {{ $stato_tagliando['km_percorsi'] }} / {{ $stato_tagliando['km_intervallo'] }} km
                                                </div>
                                            </div>
                                            <small class="text-muted">
                                                @if($stato_tagliando['km_rimanenti'] > 0)
                                                    {{ number_format($stato_tagliando['km_rimanenti']) }} km al prossimo tagliando
                                                @else
                                                    Tagliando scaduto da {{ number_format(abs($stato_tagliando['km_rimanenti'])) }} km
                                                @endif
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header"><h6 class="mb-0">Storico Tagliandi</h6></div>
                                    <div class="card-body">
                                        @if(isset($storico_tagliandi) && count($storico_tagliandi) > 0)
                                            <div class="timeline">
                                                @foreach($storico_tagliandi as $tagliando)
                                                    <div class="timeline-item">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div>
                                                                <small class="text-muted">{{ date('d/m/Y', strtotime($tagliando->data_operazione)) }}</small>
                                                                <div class="text-muted">{{ number_format($tagliando->km_operazione ?? 0) }} km</div>
                                                                @if(strpos($tagliando->descrizione, ' - ') !== false)
                                                                    @php $parti = explode(' - ', $tagliando->descrizione); $officina = $parti[1] ?? ''; @endphp
                                                                    @if($officina && $officina !== 'Non specificata')
                                                                        <div class="text-muted"><small><i class="ri-store-line"></i> {{ $officina }}</small></div>
                                                                    @endif
                                                                @endif
                                                            </div>
                                                            <div class="text-end">
                                                                @if($tagliando->importo && $tagliando->importo > 0)
                                                                    <span class="badge bg-primary">{{ number_format($tagliando->importo, 2, ',', '.') }} €</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-muted">Nessun tagliando registrato</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ======================== -->
                    <!-- TAB MANUTENZIONI -->
                    <!-- ======================== -->
                    <div class="tab-pane" id="manutenzioni" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6>Storico Manutenzioni e Spese</h6>
                            <button class="btn btn-primary" onclick="apriModalManutenzione()">
                                <i class="ri-add-line"></i> Aggiungi Spesa
                            </button>
                        </div>
                        <div class="mb-3">
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check" name="filtroManutenzioni" id="tutti" value="tutti" checked>
                                <label class="btn btn-outline-primary btn-sm" for="tutti">Tutti</label>
                                <input type="radio" class="btn-check" name="filtroManutenzioni" id="gomme_filter" value="Sostituzione Gomma">
                                <label class="btn btn-outline-success btn-sm" for="gomme_filter">Gomme</label>
                                <input type="radio" class="btn-check" name="filtroManutenzioni" id="tagliandi_filter" value="Tagliando">
                                <label class="btn btn-outline-warning btn-sm" for="tagliandi_filter">Tagliandi</label>
                                <input type="radio" class="btn-check" name="filtroManutenzioni" id="altro" value="altro">
                                <label class="btn btn-outline-info btn-sm" for="altro">Altro</label>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="tabellaManutenzioni">
                                <thead class="table-light">
                                <tr><th>Data</th><th>Tipo</th><th>Descrizione</th><th>Importo</th><th>Km</th><th width="120px">Azioni</th></tr>
                                </thead>
                                <tbody>
                                @if(isset($manutenzioni))
                                    @foreach($manutenzioni as $manutenzione)
                                        <tr data-tipo="{{ $manutenzione->tipo }}">
                                            <td>{{ date('d/m/Y', strtotime($manutenzione->data_operazione)) }}</td>
                                            <td>
                                                @if($manutenzione->tipo === 'Sostituzione Gomma')
                                                    <span class="badge bg-success"><i class="ri-tire-line"></i> Gomme</span>
                                                @elseif($manutenzione->tipo === 'Tagliando')
                                                    <span class="badge bg-warning"><i class="ri-calendar-check-line"></i> Tagliando</span>
                                                @else
                                                    <span class="badge bg-primary"><i class="ri-tools-line"></i> {{ $manutenzione->tipo }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $manutenzione->descrizione }}</td>
                                            <td class="text-end"><strong>{{ number_format($manutenzione->importo, 2, ',', '.') }} €</strong></td>
                                            <td>{{ isset($manutenzione->km_operazione) ? number_format($manutenzione->km_operazione).' km' : '-' }}</td>
                                            <td>
                                                @if(!in_array($manutenzione->tipo, ['Sostituzione Gomma', 'Tagliando']))
                                                    <button class="btn btn-warning btn-sm" onclick="modificaManutenzione({{ $manutenzione->id }}, '{{ $manutenzione->tipo }}', '{{ $manutenzione->descrizione }}', '{{ $manutenzione->importo }}', '{{ $manutenzione->data_operazione }}')">
                                                        <i class="ri-edit-2-line"></i>
                                                    </button>
                                                    <form method="POST" style="display:inline-block;" onsubmit="return confirm('Eliminare questa manutenzione?')">
                                                        @csrf
                                                        <input type="hidden" name="id_manutenzione" value="{{ $manutenzione->id }}">
                                                        <input type="hidden" name="elimina" value="1">
                                                        <button type="submit" class="btn btn-danger btn-sm"><i class="ri-delete-bin-line"></i></button>
                                                    </form>
                                                @else
                                                    <small class="text-muted">Auto-generata</small>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- ======================== -->
                    <!-- TAB IMPOSTAZIONI -->
                    <!-- ======================== -->
                    <div class="tab-pane" id="impostazioni" role="tabpanel">
                        <form method="POST" action="{{ url('/azienda/mezzo/'.$mezzo->id.'/impostazioni') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header"><h6 class="mb-0"><i class="ri-tire-line"></i> Impostazioni Gomme</h6></div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Km "Da controllare" (Arancione)</label>
                                                <input type="number" name="km_warning" class="form-control" value="{{ $mezzo->km_warning ?? 30000 }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Km "Da cambiare" (Rosso)</label>
                                                <input type="number" name="km_danger" class="form-control" value="{{ $mezzo->km_danger ?? 50000 }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header"><h6 class="mb-0"><i class="ri-calendar-check-line"></i> Impostazioni Tagliandi</h6></div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Intervallo Tagliando (Km)</label>
                                                <input type="number" name="km_tagliando" class="form-control" value="{{ $mezzo->km_tagliando ?? 15000 }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Preavviso Tagliando (Km)</label>
                                                <input type="number" name="km_preavviso_tagliando" class="form-control" value="{{ $mezzo->km_preavviso_tagliando ?? 1000 }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header"><h6 class="mb-0"><i class="ri-mail-line"></i> Notifiche Email</h6></div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Email notifiche gomme</label>
                                                        <input type="email" name="email_alert_gomme" class="form-control" value="{{ $mezzo->email_alert_gomme ?? '' }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Email notifiche tagliandi</label>
                                                        <input type="email" name="email_alert_tagliandi" class="form-control" value="{{ $mezzo->email_alert_tagliandi ?? '' }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="notifiche_attive" value="1" {{ ($mezzo->notifiche_attive ?? 1) ? 'checked' : '' }}>
                                                <label class="form-check-label">Attiva notifiche email automatiche</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-3">
                                <button type="submit" class="btn btn-success btn-lg"><i class="ri-save-line"></i> Salva Tutte le Impostazioni</button>
                            </div>
                        </form>
                    </div>

                </div>{{-- fine tab-content --}}
            </div>
        </div>
    </div>
</div>

<!-- ============================== -->
<!-- MODALS -->
<!-- ============================== -->

<!-- Modal Aggiorna Km -->
<div class="modal fade" id="modalAggiornaKm">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Aggiorna Chilometraggio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ url('/azienda/mezzo/'.$mezzo->id.'/aggiorna-km') }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info"><strong>Km attuali:</strong> {{ number_format($mezzo->km_attuali ?? 0) }} km</div>
                    <div class="mb-3">
                        <label class="form-label">Nuovi Km <span class="text-danger">*</span></label>
                        <input type="number" name="km_attuali" class="form-control" value="{{ $mezzo->km_attuali ?? 0 }}" min="{{ $mezzo->km_attuali ?? 0 }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary"><i class="ri-refresh-line"></i> Aggiorna Km</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Manutenzione -->
<div class="modal fade" id="modalManutenzione">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Aggiungi Manutenzione o Spesa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo <span class="text-danger">*</span></label>
                            <select name="tipo" class="form-control" required>
                                <option value="">Seleziona</option>
                                <option value="Riparazione">Riparazione</option>
                                <option value="Sostituzione">Sostituzione</option>
                                <option value="Controllo">Controllo</option>
                                <option value="Lavaggio">Lavaggio</option>
                                <option value="Carburante">Carburante</option>
                                <option value="Assicurazione">Assicurazione</option>
                                <option value="Bollo">Bollo</option>
                                <option value="Altro">Altro</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Data <span class="text-danger">*</span></label>
                            <input type="date" name="data_operazione" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrizione <span class="text-danger">*</span></label>
                        <textarea name="descrizione" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Importo (€)</label>
                            <input type="number" step="0.01" name="importo" class="form-control" placeholder="0.00">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Km operazione</label>
                            <input type="number" name="km_operazione" class="form-control" value="{{ $mezzo->km_attuali ?? 0 }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Chiudi</button>
                    <button type="submit" name="aggiungi_manutenzione" value="1" class="btn btn-primary"><i class="ri-add-line"></i> Salva</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Modifica Manutenzione -->
<div class="modal fade" id="modalModificaManutenzione">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifica Manutenzione</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                @csrf
                <input type="hidden" name="id_manutenzione" id="edit_id_manutenzione">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo</label>
                            <select name="tipo" id="edit_tipo_manutenzione" class="form-control" required>
                                <option value="Riparazione">Riparazione</option>
                                <option value="Sostituzione">Sostituzione</option>
                                <option value="Controllo">Controllo</option>
                                <option value="Lavaggio">Lavaggio</option>
                                <option value="Carburante">Carburante</option>
                                <option value="Assicurazione">Assicurazione</option>
                                <option value="Bollo">Bollo</option>
                                <option value="Altro">Altro</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Data</label>
                            <input type="date" name="data_operazione" id="edit_data_manutenzione" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrizione</label>
                        <textarea name="descrizione" id="edit_descrizione_manutenzione" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Importo (€)</label>
                            <input type="number" step="0.01" name="importo" id="edit_importo_manutenzione" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Km</label>
                            <input type="number" name="km_operazione" id="edit_km_manutenzione" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Chiudi</button>
                    <button type="submit" class="btn btn-warning"><i class="ri-edit-line"></i> Salva Modifiche</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Sostituzione Gomma -->
<div class="modal fade" id="modalSostituzioneGomma">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sostituzione Gomma</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ url('/azienda/mezzo/'.$mezzo->id.'/sostituisci-gomma') }}">
                @csrf
                <input type="hidden" name="posizione" id="posizione_gomma">
                <div class="modal-body">
                    <div class="alert alert-info"><strong>Posizione:</strong> <span id="posizione_text"></span></div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Data <span class="text-danger">*</span></label>
                            <input type="date" name="data_sostituzione" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Km <span class="text-danger">*</span></label>
                            <input type="number" name="km_sostituzione" class="form-control" value="{{ $mezzo->km_attuali ?? 0 }}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Costo (€)</label>
                            <input type="number" step="0.01" name="costo" class="form-control" placeholder="0.00">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fornitore</label>
                            <input type="text" name="fornitore" class="form-control" placeholder="Nome gommista">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Marca/Modello</label>
                        <input type="text" name="marca_modello" class="form-control" placeholder="es. Michelin Energy Saver">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea name="note" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-success">Registra Sostituzione</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tagliando -->
<div class="modal fade" id="modalTagliando">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registra Tagliando</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ url('/azienda/mezzo/'.$mezzo->id.'/registra-tagliando') }}">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Data <span class="text-danger">*</span></label>
                            <input type="date" name="data_tagliando" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Km <span class="text-danger">*</span></label>
                            <input type="number" name="km_tagliando" class="form-control" value="{{ $mezzo->km_attuali ?? 0 }}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Costo (€)</label>
                            <input type="number" step="0.01" name="costo" class="form-control" placeholder="0.00">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Officina</label>
                            <input type="text" name="officina" class="form-control" placeholder="Nome officina">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo tagliando</label>
                        <select name="tipo_tagliando" class="form-control">
                            <option value="Ordinario">Ordinario</option>
                            <option value="Straordinario">Straordinario</option>
                            <option value="Controllo">Controllo Generale</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea name="note" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-warning"><i class="ri-calendar-check-line"></i> Registra</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Upload Scontrino -->
<div class="modal fade" id="modalUploadScontrino">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ri-camera-line"></i> Carica Scontrino</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ url('/azienda/mezzo/'.$mezzo->id.'/rifornimento/upload-scontrino') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id_rifornimento" id="upload_id_rifornimento">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Foto Scontrino</label>
                        <input type="file" name="foto_scontrino" class="form-control" accept="image/*,application/pdf" required>
                        <small class="text-muted">JPG, PNG o PDF</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-info"><i class="ri-upload-line"></i> Carica</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================== -->
<!-- STYLES -->
<!-- ============================== -->
<style>
    .timeline { position: relative; }
    .timeline-item { padding: 10px 0; border-left: 2px solid #e9ecef; padding-left: 15px; margin-bottom: 10px; position: relative; }
    .timeline-item::before { content: ''; position: absolute; left: -5px; top: 15px; width: 8px; height: 8px; border-radius: 50%; background: #0d6efd; }
    .gomma:hover { transform: scale(1.1); transition: transform 0.2s; }
</style>

<!-- ============================== -->
<!-- SCRIPTS -->
<!-- ============================== -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // ========== Grafico Carburante ==========
    @if(isset($grafico_carburante) && count($grafico_carburante) > 0)
    const datiGrafico = @json($grafico_carburante);
    const mesi = datiGrafico.map(d => {
        const [anno, mese] = d.mese.split('-');
        const nomi = ['Gen','Feb','Mar','Apr','Mag','Giu','Lug','Ago','Set','Ott','Nov','Dic'];
        return nomi[parseInt(mese)-1] + ' ' + anno.slice(2);
    });
    const costiData = datiGrafico.map(d => parseFloat(d.totale_speso));
    const litriData = datiGrafico.map(d => parseFloat(d.totale_litri));
    const consumoData = datiGrafico.map(d => d.consumo_medio ? parseFloat(d.consumo_medio) : null);

    let chartCarburante = null;

    function creaGrafico(tipo) {
        if (chartCarburante) chartCarburante.destroy();
        let data, label, color, unit;
        switch(tipo) {
            case 'litri': data = litriData; label = 'Litri'; color = '#0dcaf0'; unit = ' L'; break;
            case 'consumo': data = consumoData; label = 'km/l'; color = '#198754'; unit = ' km/l'; break;
            default: data = costiData; label = 'Spesa (€)'; color = '#dc3545'; unit = ' €';
        }
        chartCarburante = new Chart(document.getElementById('chartCarburante').getContext('2d'), {
            type: 'bar',
            data: {
                labels: mesi,
                datasets: [{
                    label: label,
                    data: data,
                    backgroundColor: color + '40',
                    borderColor: color,
                    borderWidth: 2,
                    borderRadius: 5,
                }, {
                    label: 'Trend',
                    data: data,
                    type: 'line',
                    borderColor: color,
                    borderWidth: 2,
                    fill: false,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: color,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    tooltip: { callbacks: { label: ctx => ctx.dataset.label + ': ' + (ctx.raw ? ctx.raw.toFixed(2) : '0') + unit } },
                    legend: { display: false }
                },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Inizializza grafico solo quando il tab carburante è visibile
        const carburanteTab = document.querySelector('a[href="#carburante"]');
        let graficoInizializzato = false;

        carburanteTab.addEventListener('shown.bs.tab', function() {
            if (!graficoInizializzato) {
                creaGrafico('costi');
                graficoInizializzato = true;
            }
        });

        document.querySelectorAll('input[name="graficoTipo"]').forEach(radio => {
            radio.addEventListener('change', function() { creaGrafico(this.value); });
        });
    });
    @endif

    // ========== Calcolo prezzo in tempo reale ==========
    function calcolaPrezzo() {
        const litri = parseFloat(document.getElementById('input_litri').value) || 0;
        const importo = parseFloat(document.getElementById('input_importo').value) || 0;
        const div = document.getElementById('prezzoCalcolato');
        const span = document.getElementById('prezzoLitroCalcolato');
        if (litri > 0 && importo > 0) {
            span.textContent = (importo / litri).toFixed(3);
            div.style.display = 'block';
        } else {
            div.style.display = 'none';
        }
    }

    // ========== Upload scontrino esistente ==========
    function uploadScontrinoEsistente(id) {
        document.getElementById('upload_id_rifornimento').value = id;
        new bootstrap.Modal(document.getElementById('modalUploadScontrino')).show();
    }

    // ========== Apertura tab da hash URL ==========
    document.addEventListener('DOMContentLoaded', function() {
        const hash = window.location.hash;
        if (hash) {
            const tab = document.querySelector('a[href="' + hash + '"]');
            if (tab) {
                const bsTab = new bootstrap.Tab(tab);
                bsTab.show();
            }
        }

        // Click gomme
        document.querySelectorAll('.gomma').forEach(function(gomma) {
            gomma.addEventListener('click', function() {
                let posizione = this.getAttribute('data-posizione');
                let posizioneText = posizione.replace('_', ' ').replace('sx', 'Sinistra').replace('dx', 'Destra');
                posizioneText = posizioneText.charAt(0).toUpperCase() + posizioneText.slice(1);
                document.getElementById('posizione_gomma').value = posizione;
                document.getElementById('posizione_text').textContent = posizioneText;
                new bootstrap.Modal(document.getElementById('modalSostituzioneGomma')).show();
            });
        });

        // Filtro manutenzioni
        document.querySelectorAll('input[name="filtroManutenzioni"]').forEach(filtro => {
            filtro.addEventListener('change', function() {
                const valore = this.value;
                document.querySelectorAll('#tabellaManutenzioni tbody tr').forEach(riga => {
                    const tipo = riga.getAttribute('data-tipo');
                    if (valore === 'tutti') riga.style.display = '';
                    else if (valore === 'altro') riga.style.display = !['Sostituzione Gomma', 'Tagliando'].includes(tipo) ? '' : 'none';
                    else riga.style.display = tipo === valore ? '' : 'none';
                });
            });
        });
    });

    // ========== Funzioni Modal ==========
    function aggiornaKm() { new bootstrap.Modal(document.getElementById('modalAggiornaKm')).show(); }
    function registraTagliando() { new bootstrap.Modal(document.getElementById('modalTagliando')).show(); }
    function apriModalManutenzione() { new bootstrap.Modal(document.getElementById('modalManutenzione')).show(); }
    function apriModalRifornimento() {
        // Scrolla al form nella sidebar oppure apri un modal
        document.querySelector('a[href="#carburante"]').click();
        setTimeout(() => {
            document.querySelector('#carburante .col-lg-4').scrollIntoView({ behavior: 'smooth' });
        }, 300);
    }

    function modificaManutenzione(id, tipo, descrizione, importo, data, km) {
        document.getElementById('edit_id_manutenzione').value = id;
        document.getElementById('edit_tipo_manutenzione').value = tipo;
        document.getElementById('edit_descrizione_manutenzione').value = descrizione;
        document.getElementById('edit_importo_manutenzione').value = importo;
        document.getElementById('edit_data_manutenzione').value = data;
        document.getElementById('edit_km_manutenzione').value = km || '';
        new bootstrap.Modal(document.getElementById('modalModificaManutenzione')).show();
    }
</script>

@include('azienda.common.footer')