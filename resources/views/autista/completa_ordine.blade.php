@extends('autista.common.layout')

@section('title', 'Completa Ordine')

@section('styles')
    <style>
        .ddt-preview {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 11px;
            max-height: 350px;
            overflow-y: auto;
        }

        .ddt-header {
            text-align: center;
            padding: 10px;
            border-bottom: 2px solid #333;
        }

        .ddt-header h5 {
            margin: 0;
            font-size: 14px;
            font-weight: bold;
        }

        .ddt-box {
            border: 1px solid #333;
            margin: 8px;
            padding: 8px;
        }

        .ddt-box-header {
            background: #e9e9e9;
            margin: -8px -8px 8px -8px;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            border-bottom: 1px solid #333;
        }

        .ddt-box .nome {
            font-weight: bold;
            font-size: 12px;
        }

        .ddt-numero {
            text-align: center;
        }

        .ddt-numero .numero {
            font-size: 22px;
            font-weight: bold;
        }

        .firma-card {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .firma-card .card-header {
            padding: 12px 15px;
        }

        .signature-area {
            background: #f8f9fa;
            border: 2px dashed #ccc;
            border-radius: 8px;
            margin: 10px;
            position: relative;
        }

        .signature-area.signed {
            border-style: solid;
            border-color: #28a745;
            background: #f0fff4;
        }

        .signature-canvas {
            width: 100%;
            height: 120px;
            display: block;
            touch-action: none;
            border-radius: 6px;
        }

        .signature-actions {
            display: flex;
            gap: 10px;
            padding: 10px;
            justify-content: center;
        }

        .firma-img-container {
            padding: 15px;
            text-align: center;
        }

        .firma-img-container img {
            max-height: 80px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
            background: white;
        }

        .btn-completa {
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 10px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-badge.success {
            background: rgba(40, 167, 69, 0.15);
            color: #28a745;
        }

        .status-badge.pending {
            background: rgba(108, 117, 125, 0.15);
            color: #6c757d;
        }
    </style>
@endsection

@section('content')
    <div class="fade-in">

        <!-- Header -->
        <div class="d-flex align-items-center mb-3">
            <a href="/autista/consegne" class="btn btn-outline-secondary btn-sm me-3">
                <i class="ri-arrow-left-line"></i>
            </a>
            <div>
                <h5 class="mb-0">Completa Ordine</h5>
                <small class="text-muted">#{{ $ordine->numero_ordine }}</small>
            </div>
        </div>

        <!-- Anteprima DDT Compatta -->
        <div class="card mb-3">
            <div class="card-header bg-dark text-white py-2">
                <small class="mb-0"><i class="ri-file-text-line me-1"></i> DDT {{ $ddt->numero_documento }}</small>
            </div>
            <div class="card-body p-0">
                <div class="ddt-preview">

                    <div class="ddt-header">
                        <h5>DOCUMENTO DI TRASPORTO</h5>
                        <small class="text-muted">D.D.T. ai sensi D.P.R. 472/96</small>
                    </div>

                    <div class="row g-0 p-2">
                        <div class="col-8 pe-1">
                            <div class="ddt-box">
                                <div class="ddt-box-header">Mittente</div>
                                <div class="nome">{{ $azienda->ragione_sociale ?? $azienda->nome ?? '' }}</div>
                                <small>{{ $ddt->mittente_indirizzo ?? '' }}</small>
                            </div>
                        </div>
                        <div class="col-4 ps-1">
                            <div class="ddt-box ddt-numero">
                                <div class="ddt-box-header">Doc. N.</div>
                                <div class="numero">{{ $ddt->numero_documento }}</div>
                                <small>{{ date('d/m/Y', strtotime($ddt->data_documento)) }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="row g-0 px-2">
                        <div class="col-6 pe-1">
                            <div class="ddt-box">
                                <div class="ddt-box-header">Destinatario</div>
                                <div class="nome">{{ $ddt->destinatario_nome }}</div>
                                <small>{{ $ddt->destinatario_indirizzo }}</small>
                            </div>
                        </div>
                        <div class="col-6 ps-1">
                            <div class="ddt-box">
                                <div class="ddt-box-header">Destinazione</div>
                                <small>{{ $ordine->indirizzo_consegna }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="ddt-box mx-2">
                        <div class="ddt-box-header">Merce</div>
                        <div class="row">
                            <div class="col-6">
                                <small><strong>{{ $ddt->descrizione_merce }}</strong></small>
                            </div>
                            <div class="col-2 text-center">
                                <small class="text-muted">Colli</small><br>
                                <strong>{{ $ddt->numero_colli ?? '-' }}</strong>
                            </div>
                            <div class="col-2 text-center">
                                <small class="text-muted">Peso</small><br>
                                <strong>{{ $ddt->peso_lordo ? $ddt->peso_lordo . ' kg' : '-' }}</strong>
                            </div>
                            <div class="col-2 text-center">
                                <small class="text-muted">Valore</small><br>
                                <strong>{{ $ddt->valore_merce ? '€' . number_format($ddt->valore_merce, 0) : '-' }}</strong>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Firma Vettore -->
        <div class="card firma-card mb-3" id="card-firma-vettore">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <span><i class="ri-steering-2-line me-2"></i>Firma Vettore (Tu)</span>
                @if($ddt->firma_vettore)
                    <span class="status-badge success"><i class="ri-check-line"></i> Firmato</span>
                @else
                    <span class="status-badge pending"><i class="ri-time-line"></i> Da firmare</span>
                @endif
            </div>
            <div class="card-body p-0">
                @if($ddt->firma_vettore)
                    <div class="firma-img-container">
                        <img src="{{ $ddt->firma_vettore }}" alt="Firma Vettore">
                        <br>
                        <small class="text-muted">{{ $ddt->data_firma_vettore ? date('d/m/Y H:i', strtotime($ddt->data_firma_vettore)) : '' }}</small>
                        <br>
                        <button class="btn btn-outline-danger btn-sm mt-2" onclick="rimuoviFirma('vettore')">
                            <i class="ri-delete-bin-line"></i> Rimuovi
                        </button>
                    </div>
                @else
                    <div class="signature-area" id="area-vettore">
                        <canvas id="canvas-vettore" class="signature-canvas"></canvas>
                    </div>
                    <div class="signature-actions">
                        <button class="btn btn-outline-secondary btn-sm" onclick="pulisciFirma('vettore')">
                            <i class="ri-eraser-line"></i> Pulisci
                        </button>
                        <button class="btn btn-primary btn-sm" onclick="salvaFirma('vettore', this)">
                            <i class="ri-check-line"></i> Conferma Firma
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Firma Destinatario -->
        <div class="card firma-card mb-3" id="card-firma-destinatario">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <span><i class="ri-user-line me-2"></i>Firma Destinatario</span>
                @if($ddt->firma_destinatario)
                    <span class="status-badge success"><i class="ri-check-line"></i> Firmato</span>
                @else
                    <span class="status-badge pending"><i class="ri-time-line"></i> Da firmare</span>
                @endif
            </div>
            <div class="card-body p-0">
                @if($ddt->firma_destinatario)
                    <div class="firma-img-container">
                        <img src="{{ $ddt->firma_destinatario }}" alt="Firma Destinatario">
                        <br>
                        <small class="text-muted">{{ $ddt->data_firma_destinatario ? date('d/m/Y H:i', strtotime($ddt->data_firma_destinatario)) : '' }}</small>
                        <br>
                        <button class="btn btn-outline-danger btn-sm mt-2" onclick="rimuoviFirma('destinatario')">
                            <i class="ri-delete-bin-line"></i> Rimuovi
                        </button>
                    </div>
                @else
                    <div class="signature-area" id="area-destinatario">
                        <canvas id="canvas-destinatario" class="signature-canvas"></canvas>
                    </div>
                    <div class="signature-actions">
                        <button class="btn btn-outline-secondary btn-sm" onclick="pulisciFirma('destinatario')">
                            <i class="ri-eraser-line"></i> Pulisci
                        </button>
                        <button class="btn btn-success btn-sm" onclick="salvaFirma('destinatario', this)">
                            <i class="ri-check-line"></i> Conferma Firma
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Pulsante Completa -->
        <div class="mb-5 pb-3">
            @if($ddt->firma_vettore && $ddt->firma_destinatario)
                <button class="btn btn-success btn-completa w-100" onclick="completaOrdine(this)">
                    <i class="ri-checkbox-circle-line me-2"></i>
                    COMPLETA ORDINE
                </button>
            @else
                <button class="btn btn-secondary btn-completa w-100" disabled>
                    <i class="ri-lock-line me-2"></i>
                    Raccogli le firme per completare
                </button>
                <div class="text-center mt-2">
                    <small class="text-muted">
                        @if(!$ddt->firma_vettore && !$ddt->firma_destinatario)
                            <i class="ri-error-warning-line"></i> Mancano entrambe le firme
                        @elseif(!$ddt->firma_vettore)
                            <i class="ri-error-warning-line"></i> Manca la tua firma (vettore)
                        @else
                            <i class="ri-error-warning-line"></i> Manca la firma del destinatario
                        @endif
                    </small>
                </div>
            @endif
        </div>

    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <script>
        const idDdt = {{ $ddt->id }};
        const idOrdine = {{ $ordine->id }};
        let signaturePads = {};

        document.addEventListener('DOMContentLoaded', function() {
            // Inizializza canvas se esistono
            initCanvas('vettore');
            initCanvas('destinatario');
        });

        function initCanvas(tipo) {
            const canvas = document.getElementById('canvas-' + tipo);
            if (!canvas) return;

            const container = canvas.parentElement;
            const ratio = Math.max(window.devicePixelRatio || 1, 1);

            canvas.width = container.offsetWidth * ratio;
            canvas.height = 120 * ratio;
            canvas.style.width = container.offsetWidth + 'px';
            canvas.style.height = '120px';

            const ctx = canvas.getContext('2d');
            ctx.scale(ratio, ratio);

            signaturePads[tipo] = new SignaturePad(canvas, {
                backgroundColor: 'rgb(248, 249, 250)',
                penColor: 'rgb(0, 0, 0)',
                minWidth: 1,
                maxWidth: 2.5
            });
        }

        function pulisciFirma(tipo) {
            if (signaturePads[tipo]) {
                signaturePads[tipo].clear();
            }
        }

        function salvaFirma(tipo, btn) {
            const pad = signaturePads[tipo];

            if (!pad || pad.isEmpty()) {
                alert('Inserisci la firma prima di confermare');
                return;
            }

            const firmaBase64 = pad.toDataURL('image/png');
            const originalText = btn.innerHTML;

            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            btn.disabled = true;

            fetch('/autista/ddt/salva-firma', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify({
                    id_ddt: idDdt,
                    tipo_firma: tipo,
                    firma: firmaBase64
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Errore: ' + (data.message || 'Riprova'));
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Errore:', error);
                    alert('Errore di connessione');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        }

        function rimuoviFirma(tipo) {
            if (!confirm('Rimuovere questa firma?')) return;

            fetch('/autista/ddt/rimuovi-firma', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify({
                    id_ddt: idDdt,
                    tipo_firma: tipo
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Errore: ' + (data.message || 'Riprova'));
                    }
                })
                .catch(error => {
                    console.error('Errore:', error);
                    alert('Errore di connessione');
                });
        }

        function completaOrdine(btn) {
            if (!confirm('Confermi di completare questo ordine?')) return;

            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Completamento...';
            btn.disabled = true;

            fetch('/autista/ordine/completa', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify({
                    id_ordine: idOrdine
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Redirect alla pagina riepilogo
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            window.location.href = '/autista/ordine/' + idOrdine + '/completato';
                        }
                    } else {
                        alert('Errore: ' + (data.message || 'Riprova'));
                        btn.innerHTML = '<i class="ri-checkbox-circle-line me-2"></i> COMPLETA ORDINE';
                        btn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Errore:', error);
                    alert('Errore di connessione');
                    btn.innerHTML = '<i class="ri-checkbox-circle-line me-2"></i> COMPLETA ORDINE';
                    btn.disabled = false;
                });
        }
    </script>
@endsection