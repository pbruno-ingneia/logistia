@include('azienda.common.header')

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary">
                        <h4 class="card-title mb-0 text-white">Planning Settimanale - Dipendenti nei Cantieri</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <form method="GET" class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Data inizio</label>
                                    <input type="date" name="data_inizio" class="form-control" value="{{ request('data_inizio', date('Y-m-d', strtotime('monday this week'))) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Data fine</label>
                                    <input type="date" name="data_fine" class="form-control" value="{{ request('data_fine', date('Y-m-d', strtotime('friday this week'))) }}">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary">Visualizza</button>
                                </div>
                            </form>
                        </div>

                        <!-- Tabella Planning Cantieri -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-planning">
                                <thead>
                                <tr>
                                    @foreach($giorni as $giorno)
                                        <th class="text-center planning-header">
                                            <div class="day-name">{{ strtoupper($giorno['nome']) }}</div>
                                            <div class="day-number">{{ $giorno['numero'] }}</div>
                                        </th>
                                    @endforeach
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($cantieriPerGiorno as $cantiereId => $cantiereDati)
                                    <tr class="cantiere-row">
                                        @foreach($giorni as $giorno)
                                            <td class="planning-cell" style="background-color: {{ $cantiereDati['colore'] }};">
                                                @if(isset($cantiereDati['giorni'][$giorno['data']]))
                                                    <div class="cantiere-box">
                                                        <!-- Intestazione cantiere -->
                                                        <div class="cantiere-header">
                                                            <div class="cliente-line">CL: {{ $cantiereDati['cliente'] }}</div>
                                                            <div class="pos-line">POS: {{ $cantiereDati['posizione'] }}</div>
                                                            <div class="lav-line">LAV: {{ $cantiereDati['lavorazione'] }}</div>
                                                            <div class="resp-line">RESP: {{ $cantiereDati['responsabile'] }}</div>
                                                        </div>

                                                        <!-- Lista dipendenti -->
                                                        <div class="dipendenti-section">
                                                            @foreach($cantiereDati['giorni'][$giorno['data']]['dipendenti'] as $dipendente)
                                                                <div class="dipendente-name">{{ $dipendente }}</div>
                                                            @endforeach
                                                        </div>

                                                        <!-- Stato se presente -->
                                                        @if(isset($cantiereDati['giorni'][$giorno['data']]['stato']) && $cantiereDati['giorni'][$giorno['data']]['stato'])
                                                            <div class="stato-badge">
                                                                {{ $cantiereDati['giorni'][$giorno['data']]['stato'] }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach

                                <!-- Righe speciali come "DA TAGLIARE", "IN CORSO", etc. -->
                                @if(isset($specialRows))
                                    @foreach($specialRows as $rowId => $rowData)
                                        <tr class="cantiere-row special-row">
                                            @foreach($giorni as $giorno)
                                                <td class="planning-cell" style="background-color: {{ $rowData['colore'] }};">
                                                    @if(isset($rowData['giorni'][$giorno['data']]))
                                                        <div class="cantiere-box">
                                                            <div class="cantiere-header">
                                                                @foreach($rowData['giorni'][$giorno['data']]['titoli'] as $titolo)
                                                                    <div class="special-title">{{ $titolo }}</div>
                                                                @endforeach
                                                            </div>

                                                            @if(isset($rowData['giorni'][$giorno['data']]['dipendenti']))
                                                                <div class="dipendenti-section">
                                                                    @foreach($rowData['giorni'][$giorno['data']]['dipendenti'] as $dipendente)
                                                                        <div class="dipendente-name">{{ $dipendente }}</div>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>

                        <!-- ✅ NUOVA SEZIONE: Dipendenti Liberi -->
                        <div class="mt-5">
                            <h5 class="mb-3 text-primary">Dipendenti Liberi per Giorno</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-dipendenti-liberi">
                                    <thead>
                                    <tr>
                                        @foreach($giorni as $giorno)
                                            <th class="text-center dipendenti-liberi-header">
                                                <div class="day-name">{{ strtoupper($giorno['nome']) }}</div>
                                                <div class="day-number">{{ $giorno['numero'] }}</div>
                                            </th>
                                        @endforeach
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        @foreach($giorni as $giorno)
                                            <td class="dipendenti-liberi-cell">
                                                @if(isset($dipendentiLiberi[$giorno['data']]) && count($dipendentiLiberi[$giorno['data']]) > 0)
                                                    <div class="dipendenti-liberi-box">
                                                        <div class="liberi-count">
                                                            <strong>{{ count($dipendentiLiberi[$giorno['data']]) }} Liberi</strong>
                                                        </div>
                                                        <div class="liberi-list">
                                                            @foreach($dipendentiLiberi[$giorno['data']] as $dipendente)
                                                                <div class="dipendente-libero">{{ $dipendente }}</div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="dipendenti-liberi-box">
                                                        <div class="no-liberi">
                                                            <em>Tutti impegnati</em>
                                                        </div>
                                                    </div>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .table-planning {
        border-collapse: collapse;
        width: 100%;
        font-size: 11px;
        font-family: Arial, sans-serif;
    }

    .table-planning th,
    .table-planning td {
        border: 2px solid #000;
        vertical-align: top;
        padding: 0;
    }

    .planning-header {
        background-color: #f8f9fa;
        font-weight: bold;
        text-align: center;
        padding: 8px 4px;
        min-width: 180px;
    }

    .day-name {
        font-size: 12px;
        font-weight: bold;
        margin-bottom: 2px;
    }

    .day-number {
        font-size: 14px;
        font-weight: bold;
    }

    .planning-cell {
        width: 180px;
        min-height: 120px;
        padding: 0;
    }

    .cantiere-box {
        padding: 4px;
        height: 100%;
        min-height: 120px;
    }

    .cantiere-header {
        margin-bottom: 6px;
        border-bottom: 1px solid rgba(0,0,0,0.2);
        padding-bottom: 4px;
    }

    .cliente-line,
    .pos-line,
    .lav-line,
    .resp-line {
        font-size: 10px;
        font-weight: bold;
        line-height: 1.2;
        margin-bottom: 1px;
        word-wrap: break-word;
    }

    .dipendenti-section {
        margin-top: 4px;
    }

    .dipendente-name {
        font-size: 10px;
        font-weight: bold;
        line-height: 1.3;
        margin-bottom: 1px;
        word-wrap: break-word;
    }

    .stato-badge {
        position: absolute;
        top: 2px;
        right: 2px;
        background-color: #ff9800;
        color: white;
        font-size: 8px;
        font-weight: bold;
        padding: 1px 3px;
        border-radius: 2px;
    }

    .special-title {
        font-size: 11px;
        font-weight: bold;
        text-align: center;
        margin-bottom: 2px;
    }

    .cantiere-row {
        position: relative;
    }

    /* ✅ NUOVI STILI PER DIPENDENTI LIBERI */
    .table-dipendenti-liberi {
        border-collapse: collapse;
        width: 100%;
        font-size: 11px;
        font-family: Arial, sans-serif;
        margin-top: 20px;
    }

    .table-dipendenti-liberi th,
    .table-dipendenti-liberi td {
        border: 2px solid #28a745;
        vertical-align: top;
        padding: 0;
    }

    .dipendenti-liberi-header {
        background-color: #d4edda;
        font-weight: bold;
        text-align: center;
        padding: 8px 4px;
        min-width: 180px;
        color: #155724;
    }

    .dipendenti-liberi-cell {
        width: 180px;
        min-height: 80px;
        padding: 0;
        background-color: #f8fff9;
    }

    .dipendenti-liberi-box {
        padding: 8px;
        height: 100%;
        min-height: 80px;
    }

    .liberi-count {
        font-size: 11px;
        font-weight: bold;
        color: #28a745;
        margin-bottom: 6px;
        text-align: center;
        border-bottom: 1px solid #28a745;
        padding-bottom: 4px;
    }

    .liberi-list {
        margin-top: 4px;
    }

    .dipendente-libero {
        font-size: 10px;
        font-weight: bold;
        line-height: 1.3;
        margin-bottom: 2px;
        color: #155724;
        word-wrap: break-word;
    }

    .no-liberi {
        text-align: center;
        color: #6c757d;
        font-style: italic;
        margin-top: 20px;
    }

    /* Colori specifici per i diversi tipi di cantiere */
    .cantiere-row:nth-child(odd) .planning-cell {
        border-left: 3px solid #333;
    }

    /* Stile per righe vuote */
    .empty-row .planning-cell {
        background-color: #fff !important;
        min-height: 40px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .table-planning,
        .table-dipendenti-liberi {
            font-size: 9px;
        }

        .planning-cell,
        .dipendenti-liberi-cell {
            min-width: 120px;
        }

        .cliente-line,
        .pos-line,
        .lav-line,
        .resp-line,
        .dipendente-name,
        .dipendente-libero {
            font-size: 8px;
        }
    }
</style>

@include('azienda.common.footer')