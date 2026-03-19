@include('azienda.common.header')

<div class="page-content">
    <div class="container-fluid">

        <!-- Titolo -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-0">
                            <i class="ri-user-line me-1"></i>
                            Storico ordini — {{ $autista->nome }} {{ $autista->cognome }}
                        </h4>
                        @if($autista->telefono)
                            <small class="text-muted"><i class="ri-phone-line me-1"></i>{{ $autista->telefono }}</small>
                        @endif
                    </div>
                    <div>
                        <a href="/azienda/planning-autisti" class="btn btn-outline-secondary btn-sm">
                            <i class="ri-arrow-left-line"></i> Torna al Planning
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtri periodo -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body py-2">
                        <form method="GET" class="d-flex flex-wrap align-items-end gap-3">
                            <div>
                                <label class="form-label small mb-1">Dal</label>
                                <input type="date" name="data_da" class="form-control form-control-sm" value="{{ $dataDa }}">
                            </div>
                            <div>
                                <label class="form-label small mb-1">Al</label>
                                <input type="date" name="data_a" class="form-control form-control-sm" value="{{ $dataA }}">
                            </div>
                            <div>
                                <label class="form-label small mb-1">Stato</label>
                                <select name="stato" class="form-select form-select-sm">
                                    <option value="tutti" {{ $stato === 'tutti' ? 'selected' : '' }}>Tutti</option>
                                    <option value="pianificato"  {{ $stato === 'pianificato'  ? 'selected' : '' }}>📋 Pianificato</option>
                                    <option value="assegnato"    {{ $stato === 'assegnato'    ? 'selected' : '' }}>👤 Assegnato</option>
                                    <option value="in_corso"     {{ $stato === 'in_corso'     ? 'selected' : '' }}>🚛 In Corso</option>
                                    <option value="completato"   {{ $stato === 'completato'   ? 'selected' : '' }}>✅ Completato</option>
                                    <option value="annullato"    {{ $stato === 'annullato'    ? 'selected' : '' }}>❌ Annullato</option>
                                </select>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="ri-search-line"></i> Filtra
                                </button>
                                <a href="?data_da={{ now()->startOfYear()->toDateString() }}&data_a={{ now()->toDateString() }}&stato=tutti"
                                   class="btn btn-outline-secondary btn-sm ms-1">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="row mb-3 g-3">
            <div class="col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1 small">Ordini totali</p>
                                <h4 class="mb-0">{{ $stats->totale_ordini ?? 0 }}</h4>
                            </div>
                            <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                <i class="ri-file-list-3-line text-primary fs-4"></i>
                            </div>
                        </div>
                        @if(($stats->totale_ordini ?? 0) > 0)
                            <small class="text-success">
                                ✅ {{ $stats->completati ?? 0 }} completati
                                @if(($stats->annullati ?? 0) > 0)
                                    &nbsp;❌ {{ $stats->annullati }} annullati
                                @endif
                            </small>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1 small">Importo totale</p>
                                <h4 class="mb-0">€ {{ number_format($stats->totale_importo ?? 0, 2, ',', '.') }}</h4>
                            </div>
                            <div class="avatar-sm bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                <i class="ri-money-euro-circle-line text-success fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1 small">Km percorsi</p>
                                <h4 class="mb-0">{{ number_format($stats->totale_km ?? 0, 0, ',', '.') }} km</h4>
                            </div>
                            <div class="avatar-sm bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                <i class="ri-road-map-line text-info fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1 small">Giorni lavorati (planning)</p>
                                <h4 class="mb-0">{{ $giorniLavorati }}</h4>
                            </div>
                            <div class="avatar-sm bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                <i class="ri-calendar-check-line text-warning fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabella ordini -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="ri-list-check me-1"></i> Ordini nel periodo</h6>
                        <span class="badge bg-secondary">{{ count($ordini) }} ordini</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered mb-0 align-middle">
                                <thead class="table-light">
                                <tr>
                                    <th>N. Ordine</th>
                                    <th>Data ritiro</th>
                                    <th>Cliente</th>
                                    <th>Da → A</th>
                                    <th class="text-center">Colli / Peso</th>
                                    <th class="text-center">Km</th>
                                    <th>Mezzo</th>
                                    <th class="text-center">Stato</th>
                                    <th class="text-end">Importo</th>
                                    <th class="text-center">Azioni</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($ordini as $ordine)
                                    <tr>
                                        <td>
                                            <strong class="text-primary">{{ $ordine->numero_ordine }}</strong>
                                        </td>
                                        <td>
                                            <strong>{{ \Carbon\Carbon::parse($ordine->data_ritiro)->format('d/m/Y') }}</strong>
                                            @if($ordine->data_consegna && $ordine->data_consegna !== $ordine->data_ritiro)
                                                <br><small class="text-muted">→ {{ \Carbon\Carbon::parse($ordine->data_consegna)->format('d/m/Y') }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $ordine->cliente_nome ?? '-' }}</td>
                                        <td>
                                            <small>
                                                <span class="text-success">📍</span> {{ \Illuminate\Support\Str::limit($ordine->indirizzo_ritiro, 28) }}<br>
                                                <span class="text-danger">📍</span> {{ \Illuminate\Support\Str::limit($ordine->indirizzo_consegna, 28) }}
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            @if($ordine->numero_colli)
                                                <span class="badge bg-secondary">{{ $ordine->numero_colli }} {{ $ordine->tipo_unita ?? 'colli' }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                            @if($ordine->peso_kg)
                                                <br><small class="text-muted">{{ number_format($ordine->peso_kg, 0) }} kg</small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            {{ $ordine->km_totali ? number_format($ordine->km_totali, 0) . ' km' : '-' }}
                                        </td>
                                        <td>
                                            @if($ordine->targa)
                                                <span class="badge bg-dark">{{ $ordine->targa }}</span>
                                                @if($ordine->mezzo_nome)
                                                    <br><small class="text-muted">{{ $ordine->mezzo_nome }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $badgeStato = match($ordine->stato) {
                                                    'pianificato' => 'bg-info',
                                                    'assegnato'   => 'bg-primary',
                                                    'in_corso'    => 'bg-warning text-dark',
                                                    'completato'  => 'bg-success',
                                                    'annullato'   => 'bg-danger',
                                                    default       => 'bg-secondary',
                                                };
                                                $labelStato = match($ordine->stato) {
                                                    'pianificato' => '📋 Pianificato',
                                                    'assegnato'   => '👤 Assegnato',
                                                    'in_corso'    => '🚛 In Corso',
                                                    'completato'  => '✅ Completato',
                                                    'annullato'   => '❌ Annullato',
                                                    default       => $ordine->stato,
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeStato }}">{{ $labelStato }}</span>
                                        </td>
                                        <td class="text-end">
                                            @if($ordine->importo > 0)
                                                <strong class="text-success">€ {{ number_format($ordine->importo, 2, ',', '.') }}</strong>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="/azienda/ordine-trasporto/{{ $ordine->id }}"
                                               class="btn btn-info btn-sm" title="Dettaglio ordine" target="_blank">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">
                                            <i class="ri-inbox-line fs-2 d-block mb-2"></i>
                                            Nessun ordine nel periodo selezionato.
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                                @if(count($ordini) > 0)
                                    <tfoot class="table-light fw-semibold">
                                        <tr>
                                            <td colspan="5" class="text-end">Totali:</td>
                                            <td class="text-center">
                                                {{ $stats->totale_km ? number_format($stats->totale_km, 0) . ' km' : '-' }}
                                            </td>
                                            <td colspan="2"></td>
                                            <td class="text-end text-success">
                                                € {{ number_format($stats->totale_importo ?? 0, 2, ',', '.') }}
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@include('azienda.common.footer')
