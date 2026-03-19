@include('azienda.common.header')

<div class="page-content">
    <div class="container-fluid">

        <!-- Titolo pagina -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0"><i class="ri-stack-line me-2"></i>Gestione Pedane</h4>
                    <div class="page-title-right">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRettifica">
                            <i class="ri-add-line me-1"></i> Rettifica Manuale
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="ri-check-line me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="ri-error-warning-line me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Card Riepilogo -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Pedane Consegnate</p>
                                <h3 class="mb-0 text-primary">{{ $totConsegnate }}</h3>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-3">
                                    <i class="ri-arrow-right-up-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Pedane Rientrate</p>
                                <h3 class="mb-0 text-success">{{ $totRitirate }}</h3>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-success-subtle text-success rounded-circle fs-3">
                                    <i class="ri-arrow-left-down-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Rettifiche</p>
                                <h3 class="mb-0 text-warning">{{ $totRettifiche >= 0 ? '+' : '' }}{{ $totRettifiche }}</h3>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-warning-subtle text-warning rounded-circle fs-3">
                                    <i class="ri-equalizer-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card {{ $saldoTotale > 0 ? 'border-danger border-2' : 'border-success border-2' }}">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Saldo Totale (fuori)</p>
                                <h3 class="mb-0 {{ $saldoTotale > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $saldoTotale }}
                                </h3>
                                <small class="text-muted">
                                    {{ $saldoTotale > 0 ? 'Pedane ancora presso i clienti' : 'Tutte le pedane sono rientrate' }}
                                </small>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title {{ $saldoTotale > 0 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }} rounded-circle fs-3">
                                    <i class="ri-stack-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">

            <!-- Saldo per Cliente -->
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="ri-user-2-line me-2"></i>Saldo per Cliente</h5>
                    </div>
                    <div class="card-body p-0">
                        @if($saldoPerCliente->isEmpty())
                            <div class="text-center py-4 text-muted">
                                <i class="ri-stack-line fs-1"></i>
                                <p class="mt-2">Nessun movimento pedane registrato</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Cliente</th>
                                            <th class="text-center">Consegnate</th>
                                            <th class="text-center">Rientrate</th>
                                            <th class="text-center">Saldo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($saldoPerCliente as $riga)
                                            <tr>
                                                <td class="fw-semibold">{{ $riga->ragione_sociale }}</td>
                                                <td class="text-center text-primary">{{ $riga->tot_consegnate }}</td>
                                                <td class="text-center text-success">{{ $riga->tot_ritirate }}</td>
                                                <td class="text-center">
                                                    @if($riga->saldo > 0)
                                                        <span class="badge bg-danger fs-6">{{ $riga->saldo }}</span>
                                                    @elseif($riga->saldo < 0)
                                                        <span class="badge bg-warning text-dark fs-6">{{ $riga->saldo }}</span>
                                                    @else
                                                        <span class="badge bg-success fs-6">0</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Storico Movimenti -->
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0"><i class="ri-history-line me-2"></i>Storico Movimenti</h5>
                        <div class="d-flex gap-2">
                            <select id="filtroTipo" class="form-select form-select-sm" style="width:auto" onchange="filtraMovimenti()">
                                <option value="">Tutti</option>
                                <option value="consegnata">Consegnate</option>
                                <option value="ritirata">Ritirate</option>
                                <option value="rettifica">Rettifiche</option>
                            </select>
                            <select id="filtroCliente" class="form-select form-select-sm" style="width:auto" onchange="filtraMovimenti()">
                                <option value="">Tutti i clienti</option>
                                @foreach($clienti as $cliente)
                                    <option value="{{ $cliente->id }}">{{ $cliente->ragione_sociale }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if($movimenti->isEmpty())
                            <div class="text-center py-4 text-muted">
                                <i class="ri-file-list-3-line fs-1"></i>
                                <p class="mt-2">Nessun movimento registrato</p>
                            </div>
                        @else
                            <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
                                <table class="table table-sm table-hover mb-0" id="tabellaMovimenti">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>Data</th>
                                            <th>Cliente</th>
                                            <th class="text-center">Tipo</th>
                                            <th class="text-center">Qtà</th>
                                            <th>Ordine / Note</th>
                                            <th>Autista</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($movimenti as $mov)
                                            <tr data-tipo="{{ $mov->tipo }}" data-cliente="{{ $mov->id_cliente }}">
                                                <td class="text-nowrap">
                                                    <small>{{ date('d/m/Y', strtotime($mov->data)) }}</small>
                                                </td>
                                                <td class="fw-semibold">{{ $mov->cliente_nome }}</td>
                                                <td class="text-center">
                                                    @if($mov->tipo === 'consegnata')
                                                        <span class="badge bg-primary">
                                                            <i class="ri-arrow-right-up-line"></i> Consegnata
                                                        </span>
                                                    @elseif($mov->tipo === 'ritirata')
                                                        <span class="badge bg-success">
                                                            <i class="ri-arrow-left-down-line"></i> Ritirata
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="ri-equalizer-line"></i> Rettifica
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-center fw-bold">
                                                    @if($mov->tipo === 'consegnata')
                                                        <span class="text-primary">+{{ $mov->quantita }}</span>
                                                    @elseif($mov->tipo === 'ritirata')
                                                        <span class="text-success">-{{ $mov->quantita }}</span>
                                                    @else
                                                        <span class="text-warning">{{ $mov->quantita >= 0 ? '+' : '' }}{{ $mov->quantita }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($mov->numero_ordine)
                                                        <a href="/azienda/ordine-trasporto/{{ $mov->id_ordine }}" class="text-decoration-none">
                                                            <small><i class="ri-file-list-3-line me-1"></i>{{ $mov->numero_ordine }}</small>
                                                        </a>
                                                    @elseif($mov->note)
                                                        <small class="text-muted">{{ $mov->note }}</small>
                                                    @else
                                                        <small class="text-muted">—</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ $mov->autista_nome ?: '—' }}</small>
                                                </td>
                                                <td class="text-end">
                                                    @if($mov->tipo === 'rettifica')
                                                        <form method="post" class="d-inline"
                                                              onsubmit="return confirm('Eliminare questo movimento?')">
                                                            @csrf
                                                            <input type="hidden" name="elimina_movimento" value="1">
                                                            <input type="hidden" name="id_movimento" value="{{ $mov->id }}">
                                                            <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-1">
                                                                <i class="ri-delete-bin-line"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div><!-- end row -->
    </div><!-- end container -->
</div><!-- end page-content -->

<!-- Modal Rettifica Manuale -->
<div class="modal fade" id="modalRettifica" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                @csrf
                <input type="hidden" name="salva_rettifica" value="1">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="ri-equalizer-line me-2"></i>Rettifica Manuale Pedane</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Cliente *</label>
                        <select name="id_cliente" class="form-select" required>
                            <option value="">Seleziona cliente...</option>
                            @foreach($clienti as $cliente)
                                <option value="{{ $cliente->id }}">{{ $cliente->ragione_sociale }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantità *</label>
                        <input type="number" name="quantita" class="form-control" required placeholder="Es. +5 o -3">
                        <small class="text-muted">Usa valori positivi per aggiungere pedane al saldo, negativi per ridurlo</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Data *</label>
                        <input type="date" name="data" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="Motivo della rettifica..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="ri-save-line me-1"></i> Salva Rettifica
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function filtraMovimenti() {
        const tipo     = document.getElementById('filtroTipo').value;
        const cliente  = document.getElementById('filtroCliente').value;
        document.querySelectorAll('#tabellaMovimenti tbody tr').forEach(tr => {
            const matchTipo    = !tipo    || tr.dataset.tipo    === tipo;
            const matchCliente = !cliente || tr.dataset.cliente === cliente;
            tr.style.display = (matchTipo && matchCliente) ? '' : 'none';
        });
    }
</script>

@include('azienda.common.footer')