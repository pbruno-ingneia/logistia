@extends('autista.common.layout')

@section('title', 'Storico Ordini')

@section('styles')
    <style>
        .card-ordine {
            border: none;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: 12px;
            background: white;
        }
        .stat-box {
            border: none;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            text-align: center;
            padding: 15px 10px;
            background: white;
        }
        .filtro-pill {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            text-decoration: none;
            margin: 2px;
            border: 1px solid #dee2e6;
            color: var(--text-muted);
            background: white;
            transition: all 0.3s ease;
        }
        .filtro-pill.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
    </style>
@endsection

@section('content')

    <!-- Titolo pagina -->
    <div class="section-title mb-3">
        <i class="ri-history-line"></i> Storico Ordini
    </div>

    <!-- Statistiche riepilogo -->
    <div class="row mb-3 g-2">
        <div class="col-4">
            <div class="stat-box">
                <div class="text-muted" style="font-size:0.7rem;">TOTALE</div>
                <div class="fs-4 fw-bold" style="color: var(--primary-color);">{{ $stats['totale_ordini'] }}</div>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-box">
                <div class="text-muted" style="font-size:0.7rem;">COMPLETATI</div>
                <div class="fs-4 fw-bold" style="color: var(--success-color);">{{ $stats['completati'] }}</div>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-box">
                <div class="text-muted" style="font-size:0.7rem;">KM TOTALI</div>
                <div class="fs-4 fw-bold text-info">{{ number_format($stats['km_totali'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <!-- Filtri periodo -->
    <div class="mb-3">
        <div class="d-flex flex-wrap gap-1">
            <a href="/autista/storico?periodo=oggi" class="filtro-pill {{ $periodo == 'oggi' ? 'active' : '' }}">Oggi</a>
            <a href="/autista/storico?periodo=settimana" class="filtro-pill {{ $periodo == 'settimana' ? 'active' : '' }}">Ultima settimana</a>
            <a href="/autista/storico?periodo=mese" class="filtro-pill {{ $periodo == 'mese' ? 'active' : '' }}">Questo mese</a>
            <a href="/autista/storico?periodo=mese_scorso" class="filtro-pill {{ $periodo == 'mese_scorso' ? 'active' : '' }}">Mese scorso</a>
        </div>
    </div>

    <!-- Filtri stato -->
    <div class="mb-3">
        <div class="d-flex flex-wrap gap-1">
            <a href="/autista/storico?periodo={{ $periodo }}&stato=tutti"
               class="filtro-pill {{ $stato == 'tutti' ? 'active' : '' }}">Tutti</a>
            <a href="/autista/storico?periodo={{ $periodo }}&stato=completato"
               class="filtro-pill {{ $stato == 'completato' ? 'active' : '' }}">✅ Completati</a>
            <a href="/autista/storico?periodo={{ $periodo }}&stato=in_corso"
               class="filtro-pill {{ $stato == 'in_corso' ? 'active' : '' }}">🚛 In Corso</a>
            <a href="/autista/storico?periodo={{ $periodo }}&stato=annullato"
               class="filtro-pill {{ $stato == 'annullato' ? 'active' : '' }}">❌ Annullati</a>
        </div>
    </div>

    <!-- Lista ordini -->
    @forelse($ordini as $ordine)
        <div class="card card-ordine fade-in">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <strong style="color: var(--primary-color); font-size: 0.9rem;">{{ $ordine->numero_ordine }}</strong>
                        <br>
                        <small class="text-muted">
                            {{ $ordine->data_consegna ? date('d/m/Y', strtotime($ordine->data_consegna)) : date('d/m/Y', strtotime($ordine->data_ritiro)) }}
                            @if($ordine->data_completamento)
                                — <span style="color: var(--success-color);">{{ date('H:i', strtotime($ordine->data_completamento)) }}</span>
                            @endif
                        </small>
                    </div>
                    <div>
                        @switch($ordine->stato)
                            @case('completato')
                                <span class="badge-status completed">✅ Completato</span>
                                @break
                            @case('in_corso')
                                <span class="badge-status active">🚛 In Corso</span>
                                @break
                            @case('annullato')
                                <span class="badge bg-danger" style="border-radius:20px; padding:6px 12px; font-size:0.75rem;">❌ Annullato</span>
                                @break
                            @default
                                <span class="badge-status pending">{{ ucfirst($ordine->stato) }}</span>
                        @endswitch
                    </div>
                </div>

                @if($ordine->cliente)
                    <div class="mb-1" style="font-size: 0.85rem;">
                        <i class="ri-building-line text-muted"></i> {{ $ordine->cliente }}
                    </div>
                @endif

                <div style="font-size: 0.8rem;" class="text-muted">
                    <span style="color: var(--success-color);">📍</span> {{ Str::limit($ordine->indirizzo_ritiro, 35) }}
                    →
                    <span style="color: var(--danger-color);">📍</span> {{ Str::limit($ordine->indirizzo_consegna, 35) }}
                </div>

                <!-- Info aggiuntive -->
                <div class="d-flex gap-3 mt-2" style="font-size: 0.75rem;">
                    @if($ordine->descrizione_merce)
                        <span class="text-muted"><i class="ri-box-3-line"></i> {{ Str::limit($ordine->descrizione_merce, 20) }}</span>
                    @endif
                    @if($ordine->peso_kg)
                        <span class="text-muted"><i class="ri-scales-line"></i> {{ number_format($ordine->peso_kg, 0) }} kg</span>
                    @endif
                    @if($ordine->km_percorsi)
                        <span class="text-info"><i class="ri-road-map-line"></i> {{ number_format($ordine->km_percorsi, 1) }} km</span>
                    @endif
                </div>

                @if($ordine->note_autista)
                    <div class="mt-2 p-2 rounded" style="background: var(--bg-light); font-size: 0.8rem;">
                        <i class="ri-sticky-note-line"></i> {{ $ordine->note_autista }}
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="empty-state">
            <i class="ri-inbox-line"></i>
            <h6 class="text-muted mt-2">Nessun ordine trovato</h6>
            <p class="text-muted small">Prova a cambiare il periodo o i filtri.</p>
        </div>
    @endforelse

@endsection