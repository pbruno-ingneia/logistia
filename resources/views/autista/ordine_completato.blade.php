@extends('autista.common.layout')

@section('title', 'Ordine Completato')

@section('styles')
    <style>
        .success-header {
            background: linear-gradient(135deg, #27ae60 0%, #1e8449 100%);
            color: white;
            text-align: center;
            padding: 30px 20px;
            border-radius: 0 0 30px 30px;
            margin: -20px -20px 20px -20px;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 40px;
        }

        .ddt-container {
            background: white;
            border: 1px solid #ddd;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .ddt-header {
            background: #2c3e50;
            color: white;
            padding: 15px;
            text-align: center;
        }

        .ddt-body {
            padding: 15px;
            font-size: 11px;
        }

        .ddt-box {
            border: 1px solid #333;
            margin-bottom: 10px;
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

        .ddt-numero-box {
            text-align: center;
        }

        .ddt-numero-box .numero {
            font-size: clamp(14px, 4vw, 24px);
            font-weight: bold;
            word-break: break-all;
            line-height: 1.2;
        }

        .firme-section {
            display: flex;
            gap: 15px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }

        .firma-box {
            flex: 1;
            text-align: center;
        }

        .firma-box img {
            max-height: 60px;
            max-width: 100%;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
            background: #fafafa;
        }

        .firma-box .label {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
            font-weight: bold;
        }

        .firma-box .data {
            font-size: 9px;
            color: #999;
        }

        .share-section {
            margin-top: 20px;
        }

        .share-title {
            text-align: center;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .share-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-share {
            flex: 1;
            padding: 15px;
            border-radius: 12px;
            border: none;
            font-weight: 600;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            color: white;
        }

        .btn-whatsapp {
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
        }

        .btn-email {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        }

        .btn-download {
            background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
        }

        .btn-share:active {
            transform: scale(0.98);
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-action {
            flex: 1;
            padding: 12px;
            border-radius: 10px;
            font-weight: 500;
        }

        .completato-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(255,255,255,0.2);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            margin-top: 10px;
        }
    </style>
@endsection

@section('content')
    <div class="fade-in">

        <!-- Success Header -->
        <div class="success-header">
            <div class="success-icon">
                <i class="ri-checkbox-circle-fill"></i>
            </div>
            <h4 class="mb-1">Ordine Completato!</h4>
            <p class="mb-0 opacity-75">Consegna effettuata con successo</p>
            <div class="completato-badge">
                <i class="ri-time-line"></i>
                {{ date('d/m/Y H:i') }}
            </div>
        </div>

        <!-- DDT Firmato -->
        <div class="ddt-container mb-4">
            <div class="ddt-header">
                <h6 class="mb-0">
                    <i class="ri-file-text-line me-2"></i>
                    DDT N. {{ $ddt->numero_documento }}
                </h6>
            </div>
            <div class="ddt-body">

                <div class="row g-2 mb-2">
                    <div class="col-8">
                        <div class="ddt-box">
                            <div class="ddt-box-header">Mittente</div>
                            <div class="nome">{{ $azienda->ragione_sociale ?? $azienda->nome ?? '' }}</div>
                            <small>{{ $ddt->mittente_indirizzo ?? '' }}</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="ddt-box ddt-numero-box">
                            <div class="ddt-box-header">Doc. N.</div>
                            <div class="numero">{{ $ddt->numero_documento }}</div>
                            <small>{{ date('d/m/Y', strtotime($ddt->data_documento)) }}</small>
                        </div>
                    </div>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <div class="ddt-box">
                            <div class="ddt-box-header">Destinatario</div>
                            <div class="nome">{{ $ddt->destinatario_nome }}</div>
                            <small>{{ $ddt->destinatario_indirizzo }}</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="ddt-box">
                            <div class="ddt-box-header">Destinazione</div>
                            <small>{{ $ordine->indirizzo_consegna }}</small>
                        </div>
                    </div>
                </div>

                <div class="ddt-box">
                    <div class="ddt-box-header">Merce Trasportata</div>
                    <div class="row">
                        <div class="col-6">
                            <strong>{{ $ddt->descrizione_merce }}</strong>
                        </div>
                        <div class="col-2 text-center">
                            <small class="text-muted d-block">Colli</small>
                            <strong>{{ $ddt->numero_colli ?? '-' }}</strong>
                        </div>
                        <div class="col-2 text-center">
                            <small class="text-muted d-block">Peso</small>
                            <strong>{{ $ddt->peso_lordo ? $ddt->peso_lordo . ' kg' : '-' }}</strong>
                        </div>
                        <div class="col-2 text-center">
                            <small class="text-muted d-block">Valore</small>
                            <strong>{{ $ddt->valore_merce ? '€' . number_format($ddt->valore_merce, 0) : '-' }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Firme -->
                <div class="firme-section">
                    <div class="firma-box">
                        @if($ddt->firma_vettore)
                            <img src="{{ $ddt->firma_vettore }}" alt="Firma Vettore">
                        @else
                            <div class="text-muted">-</div>
                        @endif
                        <div class="label">Firma Vettore</div>
                        @if($ddt->data_firma_vettore)
                            <div class="data">{{ date('d/m/Y H:i', strtotime($ddt->data_firma_vettore)) }}</div>
                        @endif
                    </div>
                    <div class="firma-box">
                        @if($ddt->firma_destinatario)
                            <img src="{{ $ddt->firma_destinatario }}" alt="Firma Destinatario">
                        @else
                            <div class="text-muted">-</div>
                        @endif
                        <div class="label">Firma Destinatario</div>
                        @if($ddt->data_firma_destinatario)
                            <div class="data">{{ date('d/m/Y H:i', strtotime($ddt->data_firma_destinatario)) }}</div>
                        @endif
                    </div>
                </div>

            </div>
        </div>

        <!-- Condivisione -->
        <div class="share-section">
            <div class="share-title">
                <i class="ri-share-line me-1"></i>
                Invia DDT al cliente
            </div>

            <!-- Prima scarica il PDF -->
            <div class="mb-3">
                <a href="/autista/ddt/{{ $ddt->id }}/pdf" target="_blank" class="btn btn-outline-dark w-100 btn-action">
                    <i class="ri-download-line me-2"></i>
                    Scarica PDF Firmato
                </a>
            </div>

            <div class="share-buttons">
                <button type="button" class="btn-share btn-whatsapp" onclick="condividiWhatsApp()">
                    <i class="ri-whatsapp-line"></i>
                    WhatsApp
                </button>
                <button type="button" class="btn-share btn-email" onclick="inviaEmail()">
                    <i class="ri-mail-line"></i>
                    Email
                </button>
            </div>

            <div class="action-buttons">
                <a href="/autista/piano-giornaliero" class="btn btn-primary btn-action w-100">
                    <i class="ri-arrow-left-line me-1"></i>
                    Torna alle consegne
                </a>
            </div>
        </div>

        <!-- Modal Invio WhatsApp -->
        <div class="modal fade" id="whatsappModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 16px;">
                    <div class="modal-header border-0" style="background: #25D366; color: white; border-radius: 16px 16px 0 0;">
                        <h6 class="modal-title"><i class="ri-whatsapp-line me-2"></i>Invia via WhatsApp</h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Numero telefono cliente</label>
                            <input type="tel" class="form-control" id="whatsappNumero"
                                   value="{{ $telefono_cliente }}"
                                   placeholder="Es: 393331234567 (con prefisso)">
                            <small class="text-muted">Inserisci il numero con prefisso internazionale (es: 39 per Italia)</small>
                        </div>
                        <div class="alert alert-info small">
                            <i class="ri-information-line me-1"></i>
                            <strong>Nota:</strong> WhatsApp non permette di allegare file automaticamente.
                            Il cliente riceverà il messaggio con il link per scaricare il PDF.
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annulla</button>
                        <button type="button" class="btn btn-success" onclick="inviaWhatsApp()">
                            <i class="ri-send-plane-line me-1"></i>
                            Invia
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Invio Email -->
        <div class="modal fade" id="emailModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 16px;">
                    <div class="modal-header border-0" style="background: #3498db; color: white; border-radius: 16px 16px 0 0;">
                        <h6 class="modal-title"><i class="ri-mail-line me-2"></i>Invia via Email</h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email cliente</label>
                            <input type="email" class="form-control" id="emailCliente"
                                   value="{{ $email_cliente }}"
                                   placeholder="esempio@email.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Messaggio</label>
                            <textarea class="form-control" id="emailMessaggio" rows="4">{{ $messaggio_email }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annulla</button>
                        <button type="button" class="btn btn-primary" onclick="confermaInvioEmail(this, '{{ $email_cliente }}')">
                            <i class="ri-send-plane-line me-1"></i>
                            Invia con allegato PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        const idDdt = {{ $ddt->id }};
        const idOrdine = {{ $ordine->id }};
        const numeroDdt = '{{ $ddt->numero_documento }}';

        // Link PUBBLICO al PDF (non richiede login!)
        const linkPdfPubblico = '{{ $link_pdf_pubblico }}';

        // Link privato per l'autista
        const linkPdfPrivato = window.location.origin + '/autista/ddt/' + idDdt + '/pdf';

        function condividiWhatsApp() {
            new bootstrap.Modal(document.getElementById('whatsappModal')).show();
        }

        function inviaWhatsApp() {
            let numero = document.getElementById('whatsappNumero').value.trim();

            if (!numero) {
                alert('Inserisci il numero di telefono');
                return;
            }

            // Rimuovi spazi e caratteri non numerici
            numero = numero.replace(/[^0-9]/g, '');

            // Se inizia con 0, sostituisci con 39 (Italia)
            if (numero.startsWith('0')) {
                numero = '39' + numero.substring(1);
            }

            // Se non ha prefisso internazionale, aggiungi 39
            if (!numero.startsWith('39') && numero.length === 10) {
                numero = '39' + numero;
            }

            // Usa il LINK PUBBLICO per il cliente!
            const messaggio = `Gentile Cliente,

La informiamo che la consegna è stata completata.

📄 *DDT N. ${numeroDdt}*
📅 Data: {{ date('d/m/Y', strtotime($ddt->data_documento)) }}
            📦 Merce: {{ $ddt->descrizione_merce }}

            📎 *Scarica il DDT firmato:*
${linkPdfPubblico}

Grazie per aver scelto {{ $azienda->ragione_sociale ?? $azienda->nome ?? 'i nostri servizi' }}!`;

            const url = `https://wa.me/${numero}?text=${encodeURIComponent(messaggio)}`;
            window.open(url, '_blank');

            bootstrap.Modal.getInstance(document.getElementById('whatsappModal')).hide();
        }

        function inviaEmail() {
            new bootstrap.Modal(document.getElementById('emailModal')).show();
        }

        async function confermaInvioEmail(btn, emailDefault) {
            const email = document.getElementById('emailCliente').value.trim();
            const messaggio = document.getElementById('emailMessaggio').value.trim();

            if (!email) {
                alert('Inserisci l\'email del cliente');
                return;
            }

            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Preparazione...';
            btn.disabled = true;

            const oggetto = 'DDT ' + numeroDdt + ' - Documento di Trasporto';

            try {
                const response = await fetch('/autista/ddt/' + idDdt + '/pdf');
                if (!response.ok) throw new Error('Errore generazione PDF');

                const blob = await response.blob();
                const file = new File([blob], 'DDT_' + numeroDdt.replace(/\//g, '-') + '.pdf', { type: 'application/pdf' });

                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    await navigator.share({
                        files: [file],
                        title: oggetto,
                        text: messaggio
                    });
                    bootstrap.Modal.getInstance(document.getElementById('emailModal')).hide();
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    return;
                }

                // Fallback: mailto con email precompilata
                window.location.href = 'mailto:' + encodeURIComponent(email)
                    + '?subject=' + encodeURIComponent(oggetto)
                    + '&body=' + encodeURIComponent(messaggio + '\n\n📎 Scarica il DDT firmato: ' + linkPdfPubblico);

                bootstrap.Modal.getInstance(document.getElementById('emailModal')).hide();

            } catch (e) {
                if (e.name !== 'AbortError') {
                    console.error('Errore:', e);
                    window.location.href = 'mailto:' + encodeURIComponent(email)
                        + '?subject=' + encodeURIComponent(oggetto)
                        + '&body=' + encodeURIComponent(messaggio + '\n\n📎 Scarica il DDT firmato: ' + linkPdfPubblico);
                    bootstrap.Modal.getInstance(document.getElementById('emailModal')).hide();
                }
            }

            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    </script>
@endsection