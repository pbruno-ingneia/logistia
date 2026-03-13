@include('azienda.common.header')

<div class="page-content">
    <div class="container-fluid">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Responsabili</h4>
            <div class="page-title-right">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="ri-download-line me-1"></i> Esporta Report
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ url('/azienda/responsabili/report/pdf') }}">
                                <i class="ri-file-pdf-line me-2 text-danger"></i>Report PDF Completo
                            </a></li>
                        <li><a class="dropdown-item" href="{{ url('/azienda/responsabili/report/excel') }}">
                                <i class="ri-file-excel-line me-2 text-success"></i>Report Excel Completo
                            </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ url('/azienda/responsabili/report/pdf/attivi') }}">
                                <i class="ri-file-pdf-line me-2 text-danger"></i>PDF Solo Cantieri Attivi
                            </a></li>
                        <li><a class="dropdown-item" href="{{ url('/azienda/responsabili/report/excel/attivi') }}">
                                <i class="ri-file-excel-line me-2 text-success"></i>Excel Solo Cantieri Attivi
                            </a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            @foreach($responsabili as $responsabile)
                <div class="col-xl-4 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-3">
                                        <div class="avatar-title bg-primary rounded-circle">
                                            {{ strtoupper(substr($responsabile->nome, 0, 1)) }}{{ strtoupper(substr($responsabile->cognome, 0, 1)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-1">{{ $responsabile->nome }} {{ $responsabile->cognome }}</h5>
                                        <p class="text-muted mb-0">{{ $responsabile->email }}</p>
                                    </div>
                                </div>
                                <!-- Dropdown per report individuali -->
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="ri-more-2-line"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ url('/azienda/responsabili/report/pdf/singolo/'.$responsabile->id) }}">
                                                <i class="ri-file-pdf-line me-2 text-danger"></i>PDF Individuale
                                            </a></li>
                                        <li><a class="dropdown-item" href="{{ url('/azienda/responsabili/report/excel/singolo/'.$responsabile->id) }}">
                                                <i class="ri-file-excel-line me-2 text-success"></i>Excel Individuale
                                            </a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="text-muted mb-0">Cantieri Assegnati</h6>
                                <div class="d-flex gap-2">
                                    @php
                                        $cantieri_attivi = collect($responsabile->cantieri)->where('stato', 1)->count();
                                        $cantieri_sospesi = collect($responsabile->cantieri)->where('stato', 2)->count();
                                        $cantieri_chiusi = collect($responsabile->cantieri)->where('stato', 0)->count();
                                    @endphp
                                    <span class="badge bg-success">{{ $cantieri_attivi }} Attivi</span>
                                    @if($cantieri_sospesi > 0)
                                        <span class="badge bg-warning">{{ $cantieri_sospesi }} Sospesi</span>
                                    @endif
                                    @if($cantieri_chiusi > 0)
                                        <span class="badge bg-secondary">{{ $cantieri_chiusi }} Chiusi</span>
                                    @endif
                                </div>
                            </div>

                            @if(count($responsabile->cantieri) > 0)
                                <div class="list-group list-group-flush">
                                    @foreach(collect($responsabile->cantieri)->take(3) as $cantiere)
                                        <a href="{{ url('/azienda/cantiere/'.$cantiere->id_cantiere) }}"
                                           class="list-group-item list-group-item-action border-0 px-0">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">{{ $cantiere->titolo }}</h6>
                                                    <p class="mb-1 text-muted small">{{ Str::limit($cantiere->descrizione, 60) }}</p>
                                                    <small class="text-muted">
                                                        {{ date('d/m/Y', strtotime($cantiere->data_inizio)) }} -
                                                        {{ date('d/m/Y', strtotime($cantiere->data_fine)) }}
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-primary">{{ $cantiere->percentuale }}%</span>
                                                    <br>
                                                    @if($cantiere->stato == 1)
                                                        <span class="badge bg-success mt-1">Attivo</span>
                                                    @elseif($cantiere->stato == 2)
                                                        <span class="badge bg-warning mt-1">Sospeso</span>
                                                    @else
                                                        <span class="badge bg-secondary mt-1">Chiuso</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                    @if(count($responsabile->cantieri) > 3)
                                        <div class="text-center pt-2">
                                            <small class="text-muted">e altri {{ count($responsabile->cantieri) - 3 }} cantieri...</small>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="ri-building-line text-muted fs-1"></i>
                                    <p class="text-muted mt-2">Nessun cantiere assegnato</p>
                                </div>
                            @endif
                        </div>
                        <div class="card-footer bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    Totale Percentuale:
                                    <strong>{{ array_sum(array_column($responsabile->cantieri, 'percentuale')) }}%</strong>
                                </small>
                                <small class="text-muted">
                                    Valore Totale:
                                    <strong>€ {{ number_format(array_sum(array_column($responsabile->cantieri, 'valore_stimato')), 2, ',', '.') }}</strong>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if(count($responsabili) == 0)
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="ri-user-star-line text-muted fs-1"></i>
                            <h4 class="mt-3">Nessun Responsabile</h4>
                            <p class="text-muted">Non ci sono responsabili configurati al momento.</p>
                            <a href="{{ url('/azienda/utenti') }}" class="btn btn-primary">
                                <i class="ri-add-line me-1"></i> Crea Responsabile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Statistiche Generali -->
        @if(count($responsabili) > 0)
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ri-bar-chart-line me-2"></i>
                                Statistiche Generali
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                @php
                                    $totale_cantieri = 0;
                                    $totale_attivi = 0;
                                    $totale_sospesi = 0;
                                    $totale_chiusi = 0;
                                    $totale_valore = 0;

                                    foreach($responsabili as $resp) {
                                        $totale_cantieri += count($resp->cantieri);
                                        foreach($resp->cantieri as $cantiere) {
                                            if($cantiere->stato == 1) $totale_attivi++;
                                            elseif($cantiere->stato == 2) $totale_sospesi++;
                                            else $totale_chiusi++;
                                            $totale_valore += $cantiere->valore_stimato ?? 0;
                                        }
                                    }
                                @endphp

                                <div class="col-md-2">
                                    <div class="border rounded p-3">
                                        <h4 class="text-primary">{{ count($responsabili) }}</h4>
                                        <p class="text-muted mb-0">Responsabili</p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="border rounded p-3">
                                        <h4 class="text-info">{{ $totale_cantieri }}</h4>
                                        <p class="text-muted mb-0">Cantieri Totali</p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="border rounded p-3">
                                        <h4 class="text-success">{{ $totale_attivi }}</h4>
                                        <p class="text-muted mb-0">Cantieri Attivi</p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="border rounded p-3">
                                        <h4 class="text-warning">{{ $totale_sospesi }}</h4>
                                        <p class="text-muted mb-0">Cantieri Sospesi</p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="border rounded p-3">
                                        <h4 class="text-secondary">{{ $totale_chiusi }}</h4>
                                        <p class="text-muted mb-0">Cantieri Chiusi</p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="border rounded p-3">
                                        <h4 class="text-primary">€ {{ number_format($totale_valore, 0, ',', '.') }}</h4>
                                        <p class="text-muted mb-0">Valore Totale</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@include('azienda.common.footer')