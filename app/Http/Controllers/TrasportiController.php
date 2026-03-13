<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Http\Controllers\AutistaController;


class TrasportiController extends Controller
{
    public function is_loggato()
    {
        if (!session()->has('utente')) {
            return Redirect::to('admin/login')->send();
        }
    }

    /**
     * Visualizza lista ordini di trasporto
     */
    public function ordiniTrasporto(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        // Gestione POST per creazione/modifica/eliminazione
        if ($request->isMethod('post')) {
            $dati = $request->all();

            if (isset($dati['crea_ordine'])) {
                $this->creaOrdine($dati, $utente);

            } elseif (isset($dati['modifica_ordine'])) {
                $this->modificaOrdine($dati, $utente);
            } elseif (isset($dati['elimina_ordine'])) {
                $this->eliminaOrdine($dati, $utente);
            }

            return redirect('/azienda/ordini-trasporto')->with('success', 'Operazione completata!');
        }

        // Filtri per stato
        $filtroStato = $request->get('stato', 'tutti');

        $query = DB::table('ordini_trasporto as ot')
            ->leftJoin('clienti as c', 'ot.id_cliente', '=', 'c.id')
            ->leftJoin('mezzi as m', 'ot.id_mezzo', '=', 'm.id')
            ->leftJoin('utenti as u', 'ot.id_autista', '=', 'u.id')
            ->leftJoin('documenti_trasporto as dt', function($join) {
                $join->on('ot.id', '=', 'dt.id_ordine')
                    ->where('dt.tipo_documento', '=', 'ddt');
            })
            ->where('ot.id_azienda', $utente->id_azienda)
            ->select(
                'ot.*',
                'c.ragione_sociale as cliente_nome',
                'm.targa',
                'm.tipo as mezzo_marca',
                'm.modello as mezzo_modello',
                'm.nome as mezzo_nome',
                'u.nome as autista_nome',
                'u.cognome as autista_cognome',
                'dt.numero_documento as numero_ddt'  // <-- NUOVO
            );

        if ($filtroStato !== 'tutti') {
            $query->where('ot.stato', $filtroStato);
        }

        $ordini = $query->orderBy('ot.data_ritiro', 'desc')->get();
        // Ottieni clienti, mezzi e autisti per i form
        $clienti = DB::table('clienti')->where('id_azienda', $utente->id_azienda)->get();
        $mezzi = DB::table('mezzi')->where('id_azienda', $utente->id_azienda)->where('stato', 1)->get();
        $autisti = DB::table('utenti')->where('id_azienda', $utente->id_azienda)->get();

        // Calcola prossimo numero DDT per anteprima nella modal
        $prossimoDdt = $this->generaNumeroDocumento('ddt', $utente->id_azienda);

        // Dati azienda per compilazione automatica DDT
        $azienda = DB::table('aziende')->where('id', $utente->id_azienda)->first();

        return view('azienda.ordini_trasporto', compact('ordini','azienda', 'prossimoDdt', 'clienti', 'mezzi', 'autisti', 'utente', 'filtroStato'));
    }





    /**
     * Elimina ordine + DDT collegato
     */
    private function eliminaOrdine($dati, $utente)
    {
        // 1. Elimina prima i documenti collegati
        DB::table('documenti_trasporto')
            ->where('id_ordine', $dati['id_ordine'])
            ->delete();

        // 2. Elimina l'ordine
        DB::table('ordini_trasporto')
            ->where('id', $dati['id_ordine'])
            ->where('id_azienda', $utente->id_azienda)
            ->delete();
    }

    /**
     * Cambia stato ordine (AJAX)
     */
    public function cambiaStatoOrdine(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        $idOrdine = $request->input('id_ordine');
        $nuovoStato = $request->input('stato');

        $updated = DB::table('ordini_trasporto')
            ->where('id', $idOrdine)
            ->where('id_azienda', $utente->id_azienda)
            ->update([
                'stato' => $nuovoStato,
                'updated_at' => now()
            ]);

        if ($updated) {
            // ============================================
            // NOTIFICA AUTISTA del cambio stato
            // ============================================
            $ordine = DB::table('ordini_trasporto')->where('id', $idOrdine)->first();
            if ($ordine && $ordine->id_autista) {
                $statiLabel = [
                    'pianificato' => '📋 Pianificato',
                    'assegnato' => '👤 Assegnato a te',
                    'in_corso' => '🚛 In Corso',
                    'completato' => '✅ Completato',
                    'annullato' => '❌ Annullato'
                ];
                AutistaController::creaNotifica(
                    $ordine->id_autista,
                    $ordine->id_azienda,
                    'cambio_stato',
                    'Stato aggiornato: ' . ($statiLabel[$nuovoStato] ?? $nuovoStato),
                    'Ordine ' . $ordine->numero_ordine . ' → ' . ($statiLabel[$nuovoStato] ?? $nuovoStato),
                    $idOrdine
                );
            }

            return response()->json(['success' => true, 'message' => 'Stato aggiornato!']);
        }

        return response()->json(['success' => false, 'message' => 'Errore nell\'aggiornamento']);
    }



    /**
     * Dettaglio ordine singolo + DDT associato
     */
    public function dettaglioOrdine($id)
    {
        $this->is_loggato();
        $utente = session('utente');

        // Recupera ordine con tutti i dettagli (incluso numero_colli)
        $ordine = DB::table('ordini_trasporto as ot')
            ->leftJoin('clienti as c', 'ot.id_cliente', '=', 'c.id')
            ->leftJoin('mezzi as m', 'ot.id_mezzo', '=', 'm.id')
            ->leftJoin('utenti as u', 'ot.id_autista', '=', 'u.id')
            ->where('ot.id', $id)
            ->where('ot.id_azienda', $utente->id_azienda)
            ->select(
                'ot.*',
                'c.ragione_sociale as cliente_nome',
                'c.indirizzo as cliente_indirizzo',
                'c.telefono as cliente_telefono',
                'c.email as cliente_email',
                'c.partita_iva as cliente_piva',
                'c.codice_fiscale as cliente_cf',
                'm.targa',
                'm.tipo as mezzo_marca',
                'm.modello as mezzo_modello',
                'm.nome as mezzo_nome',
                'u.nome as autista_nome',
                'u.cognome as autista_cognome',
                'u.telefono as autista_telefono'
            )
            ->first();

        if (!$ordine) {
            return redirect('/azienda/ordini-trasporto')->with('error', 'Ordine non trovato');
        }

        // Recupera DDT associato all'ordine
        $ddt = DB::table('documenti_trasporto')
            ->where('id_ordine', $id)
            ->where('tipo_documento', 'ddt')
            ->first();

        // Recupera dati azienda per l'anteprima DDT
        $azienda = DB::table('aziende')
            ->where('id', $utente->id_azienda)
            ->first();

        return view('azienda.dettaglio_ordine_trasporto', compact('ordine', 'utente', 'ddt', 'azienda'));
    }

    /**
     * Genera PDF del DDT
     */
    public function stampaDDT($idOrdine)
    {
        $this->is_loggato();
        $utente = session('utente');

        // Recupera DDT
        $ddt = DB::table('documenti_trasporto')
            ->where('id_ordine', $idOrdine)
            ->where('tipo_documento', 'ddt')
            ->where('id_azienda', $utente->id_azienda)
            ->first();

        if (!$ddt) {
            return redirect()->back()->with('error', 'DDT non trovato');
        }

        // Recupera ordine
        $ordine = DB::table('ordini_trasporto as ot')
            ->leftJoin('clienti as c', 'ot.id_cliente', '=', 'c.id')
            ->leftJoin('mezzi as m', 'ot.id_mezzo', '=', 'm.id')
            ->leftJoin('utenti as u', 'ot.id_autista', '=', 'u.id')
            ->where('ot.id', $idOrdine)
            ->where('ot.id_azienda', $utente->id_azienda)
            ->select(
                'ot.*',
                'c.ragione_sociale as cliente_nome',
                'c.indirizzo as cliente_indirizzo',
                'c.partita_iva as cliente_piva',
                'c.codice_fiscale as cliente_cf',
                'm.targa',
                'm.tipo as mezzo_marca',
                'm.modello as mezzo_modello',
                'u.nome as autista_nome',
                'u.cognome as autista_cognome'
            )
            ->first();

        // Recupera dati azienda
        $azienda = DB::table('aziende')
            ->where('id', $utente->id_azienda)
            ->first();

        // Genera HTML per il PDF
        $html = $this->generaHTMLDDT($ddt, $ordine, $azienda);

        // Crea PDF con mPDF
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
        ]);

        $mpdf->WriteHTML($html);

        $filename = 'DDT_' . $ddt->numero_documento . '.pdf';
        $filename = str_replace('/', '-', $filename); // Sostituisci / con -

        return $mpdf->Output($filename, 'I'); // 'I' = inline (visualizza nel browser), 'D' = download
    }

    /**
     * Genera HTML per il DDT - Layout Professionale
     */
    private function generaHTMLDDT($ddt, $ordine, $azienda)
    {
        $dataDocumento = date('d/m/Y', strtotime($ddt->data_documento));
        $dataRitiro = isset($ordine->data_ritiro) ? date('d/m/Y', strtotime($ordine->data_ritiro)) : '';
        $oraRitiro = $ordine->ora_ritiro ?? '';

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; font-size: 10pt; }
                h1 { font-size: 20pt; margin: 0; text-align: center; }
                .subtitle { font-size: 9pt; text-align: center; color: #444; margin-bottom: 20px; }
                
                .main-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
                .main-table td { vertical-align: top; }
                
                .box { border: 1.5px solid #000; }
                .box-header { background: #e0e0e0; padding: 4px 8px; font-weight: bold; font-size: 9pt; border-bottom: 1px solid #000; }
                .box-body { padding: 8px; }
                .azienda-nome { font-size: 12pt; font-weight: bold; }
                
                .numero-box { text-align: center; }
                .numero-grande { font-size: 22pt; font-weight: bold; }
                
                .dati-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
                .dati-table td { padding: 5px 8px; border: 1px solid #000; font-size: 9pt; }
                .dati-label { font-weight: bold; background: #f5f5f5; width: 20%; }
                
                .merce-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
                .merce-table th { background: #e0e0e0; padding: 6px; border: 1.5px solid #000; font-size: 9pt; text-align: center; }
                .merce-table td { padding: 8px; border: 1.5px solid #000; font-size: 10pt; }
                .merce-table .center { text-align: center; }
                .merce-table .right { text-align: right; }
                
                .note-table { width: 100%; margin-bottom: 15px; }
                
                .firme-table { width: 100%; margin-top: 80px; }
                .firme-table td { width: 50%; padding: 0 20px; vertical-align: bottom; }
                .firma-linea { border-top: 1px solid #000; padding-top: 5px; font-size: 9pt; font-weight: bold; text-align: center; width: 180px; }
                .firma-img { max-height: 50px; max-width: 150px; }
                
                .footer { text-align: center; font-size: 8pt; color: #888; margin-top: 50px; border-top: 1px solid #ccc; padding-top: 8px; }
            </style>
        </head>
        <body>
            
            <h1>DOCUMENTO DI TRASPORTO</h1>
            <div class="subtitle">D.D.T. ai sensi del D.P.R. 472 del 14/08/1996</div>
            
            <!-- MITTENTE + NUMERO DDT -->
            <table class="main-table">
                <tr>
                    <td style="width: 65%; padding-right: 8px;">
                        <table class="box" style="width: 100%;">
                            <tr><td class="box-header">MITTENTE</td></tr>
                            <tr><td class="box-body">
                                <span class="azienda-nome">' . htmlspecialchars($ddt->mittente_nome ?: ($azienda->ragione_sociale ?? '')) . '</span><br>
                                ' . htmlspecialchars($ddt->mittente_indirizzo ?: ($azienda->indirizzo ?? '')) . '
                                ' . (isset($azienda->partita_iva) && $azienda->partita_iva ? '<br>P.IVA: ' . htmlspecialchars($azienda->partita_iva) : '') . '
                            </td></tr>
                        </table>
                    </td>
                    <td style="width: 35%;">
                        <table class="box" style="width: 100%;">
                            <tr><td class="box-header" style="text-align: center;">DOCUMENTO N.</td></tr>
                            <tr><td class="box-body numero-box">
                                <span class="numero-grande">' . htmlspecialchars($ddt->numero_documento) . '</span><br>
                                <span>del ' . $dataDocumento . '</span>
                            </td></tr>
                        </table>
                    </td>
                </tr>
            </table>
            
            <!-- DESTINATARIO + LUOGO DESTINAZIONE -->
            <table class="main-table">
                <tr>
                    <td style="width: 50%; padding-right: 4px;">
                        <table class="box" style="width: 100%;">
                            <tr><td class="box-header">DESTINATARIO</td></tr>
                            <tr><td class="box-body">
                                <span class="azienda-nome">' . htmlspecialchars($ddt->destinatario_nome ?: '') . '</span><br>
                                ' . htmlspecialchars($ddt->destinatario_indirizzo ?: '') . '
                                ' . (isset($ordine->cliente_piva) && $ordine->cliente_piva ? '<br>P.IVA: ' . htmlspecialchars($ordine->cliente_piva) : '') . '
                            </td></tr>
                        </table>
                    </td>
                    <td style="width: 50%; padding-left: 4px;">
                        <table class="box" style="width: 100%;">
                            <tr><td class="box-header">LUOGO DI DESTINAZIONE</td></tr>
                            <tr><td class="box-body">
                                ' . htmlspecialchars($ordine->indirizzo_consegna ?? '') . '
                            </td></tr>
                        </table>
                    </td>
                </tr>
            </table>
            
            <!-- DATI TRASPORTO -->
            <table class="dati-table">
                <tr>
                    <td class="dati-label">Causale</td>
                    <td>Vendita</td>
                    <td class="dati-label">Mezzo</td>
                    <td>' . htmlspecialchars(trim(($ordine->mezzo_marca ?? '') . ' ' . ($ordine->mezzo_modello ?? ''))) . '</td>
                    <td class="dati-label">Targa</td>
                    <td><strong>' . htmlspecialchars($ordine->targa ?? '-') . '</strong></td>
                </tr>
                <tr>
                    <td class="dati-label">Ritiro</td>
                    <td>' . htmlspecialchars($ordine->indirizzo_ritiro ?? '') . '</td>
                    <td class="dati-label">Data</td>
                    <td>' . $dataRitiro . ' ' . $oraRitiro . '</td>
                    <td class="dati-label">Autista</td>
                    <td>' . htmlspecialchars(trim(($ordine->autista_nome ?? '') . ' ' . ($ordine->autista_cognome ?? ''))) . '</td>
                </tr>
            </table>
            
            <!-- DESCRIZIONE MERCE -->
            <table class="merce-table">
                <tr>
                    <th style="width: 50%;">Descrizione Merce</th>
                    <th style="width: 12%;">Colli</th>
                    <th style="width: 18%;">Peso Kg</th>
                    <th style="width: 20%;">Valore €</th>
                </tr>
                <tr>
                    <td style="height: 50px; vertical-align: top;">' . nl2br(htmlspecialchars($ddt->descrizione_merce ?: '')) . '</td>
                    <td class="center">' . ($ddt->numero_colli ?: '-') . '</td>
                    <td class="center">' . ($ddt->peso_lordo ? number_format($ddt->peso_lordo, 2, ',', '.') : '-') . '</td>
                    <td class="right">' . ($ddt->valore_merce ? '€ ' . number_format($ddt->valore_merce, 2, ',', '.') : '-') . '</td>
                </tr>
            </table>
            
            ' . ($ddt->note ? '
            <table class="box note-table" style="width: 100%;">
                <tr><td class="box-header">NOTE</td></tr>
                <tr><td class="box-body" style="font-size: 9pt;">' . nl2br(htmlspecialchars($ddt->note)) . '</td></tr>
            </table>
            ' : '') . '
            
            <!-- SPACER per spingere firme in fondo -->
            <div style="height: 150px;"></div>
            
            <!-- FIRME -->
            <table class="firme-table">
                <tr>
                    <td style="text-align: left;">
                        ' . (!empty($ddt->firma_vettore) ? '<img src="' . $ddt->firma_vettore . '" class="firma-img"><br>' : '<br><br><br>') . '
                        <div class="firma-linea">Firma Vettore</div>
                    </td>
                    <td style="text-align: right;">
                        ' . (!empty($ddt->firma_destinatario) ? '<img src="' . $ddt->firma_destinatario . '" class="firma-img"><br>' : '<br><br><br>') . '
                        <div class="firma-linea" style="margin-left: auto;">Firma Destinatario</div>
                    </td>
                </tr>
            </table>
            
            <div class="footer">
                Documento generato il ' . date('d/m/Y H:i') . ' - ' . htmlspecialchars($azienda->ragione_sociale ?? $azienda->nome ?? '') . '
            </div>
            
        </body>
        </html>';

        return $html;
    }


    /**
     * Genera numero ordine progressivo
     */
    private function generaNumeroOrdine($idAzienda)
    {
        $anno = date('Y');
        $ultimoNumero = DB::table('ordini_trasporto')
            ->where('id_azienda', $idAzienda)
            ->where('numero_ordine', 'like', $anno . '%')
            ->max('numero_ordine');

        if ($ultimoNumero) {
            $progressivo = (int)substr($ultimoNumero, -4) + 1;
        } else {
            $progressivo = 1;
        }

        return $anno . sprintf('%04d', $progressivo);
    }

    /**
     * Gestione clienti semplice
     */
    public function clienti(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        if ($request->isMethod('post')) {
            $dati = $request->all();

            if (isset($dati['crea_cliente'])) {
                DB::table('clienti')->insert([
                    'ragione_sociale' => $dati['ragione_sociale'],
                    'indirizzo' => $dati['indirizzo'] ?? null,
                    'telefono' => $dati['telefono'] ?? null,
                    'email' => $dati['email'] ?? null,
                    'partita_iva' => $dati['partita_iva'] ?? null,
                    'codice_fiscale' => $dati['codice_fiscale'] ?? null,
                    'id_azienda' => $utente->id_azienda,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } elseif (isset($dati['modifica_cliente'])) {
                DB::table('clienti')
                    ->where('id', $dati['id_cliente'])
                    ->where('id_azienda', $utente->id_azienda)
                    ->update([
                        'ragione_sociale' => $dati['ragione_sociale'],
                        'indirizzo' => $dati['indirizzo'] ?? null,
                        'telefono' => $dati['telefono'] ?? null,
                        'email' => $dati['email'] ?? null,
                        'partita_iva' => $dati['partita_iva'] ?? null,
                        'codice_fiscale' => $dati['codice_fiscale'] ?? null,
                        'updated_at' => now()
                    ]);
            } elseif (isset($dati['elimina_cliente'])) {
                DB::table('clienti')
                    ->where('id', $dati['id_cliente'])
                    ->where('id_azienda', $utente->id_azienda)
                    ->delete();
            }

            return redirect('/azienda/clienti')->with('success', 'Operazione completata!');
        }

        $clienti = DB::table('clienti')
            ->where('id_azienda', $utente->id_azienda)
            ->orderBy('ragione_sociale')
            ->get();

        return view('azienda.clienti', compact('clienti', 'utente'));
    }

    /**
     * Calcola costo trasporto usando Google Maps
     */
    public function calcolaCostoTrasporto(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        $dati = $request->all();

        $indirizzoPartenza = $dati['indirizzo_partenza'];
        $indirizzoArrivo = $dati['indirizzo_arrivo'];
        $idCliente = $dati['id_cliente'] ?? null;
        $peso = $dati['peso'] ?? 0;
        $urgente = $dati['urgente'] ?? false;
        $tipoMezzo = $dati['tipo_mezzo'] ?? 'furgone';

        // Calcola distanza e tempo
        $risultatoMaps = $this->calcolaDistanzaGoogleMaps($indirizzoPartenza, $indirizzoArrivo);

        if (!$risultatoMaps['success']) {
            return response()->json([
                'success' => false,
                'error' => 'Errore nel calcolo del percorso: ' . $risultatoMaps['error']
            ]);
        }

        $distanzaKm = $risultatoMaps['distanza_km'];
        $tempoMinuti = $risultatoMaps['tempo_minuti'];
        $metodoCalcolo = $risultatoMaps['metodo'] ?? 'sconosciuto';

        // Recupera tariffario cliente se esiste
        $tariffa = null;
        if ($idCliente) {
            $tariffa = DB::table('tariffari_clienti')
                ->where('id_cliente', $idCliente)
                ->where('id_azienda', $utente->id_azienda)
                ->where('attivo', 1)
                ->where('valido_dal', '<=', date('Y-m-d'))
                ->where(function($query) {
                    $query->whereNull('valido_fino')
                        ->orWhere('valido_fino', '>=', date('Y-m-d'));
                })
                ->first();
        }

        // Calcola costo
        $costo = $this->calcolaCostoDettagliato($distanzaKm, $tempoMinuti, $peso, $tipoMezzo, $tariffa, $urgente);

        return response()->json([
            'success' => true,
            'distanza_km' => $distanzaKm,
            'tempo_minuti' => $tempoMinuti,
            'tempo_formattato' => $this->formattaTempo($tempoMinuti),
            'costo_dettaglio' => $costo,
            'costo_totale' => $costo['totale'],
            'ha_tariffa_personalizzata' => $tariffa !== null,
            'metodo_calcolo' => $metodoCalcolo // ✅ NUOVO
        ]);
    }

    /**
     * Calcola distanza e tempo con Google Maps Distance Matrix API
     */
    private function calcolaDistanzaGoogleMaps($partenza, $arrivo)
    {
        $apiKey = 'AIzaSyB0Kta9cMMAOEcpcGl0hwXij0I6_gqWeLM';

        // Prima prova con Google Maps API
        try {
            $url = 'https://maps.googleapis.com/maps/api/distancematrix/json?' . http_build_query([
                    'origins' => $partenza,
                    'destinations' => $arrivo,
                    'units' => 'metric',
                    'language' => 'it',
                    'key' => $apiKey
                ]);

            $context = stream_context_create([
                'http' => [
                    'timeout' => 10, // 10 secondi timeout
                    'user_agent' => 'TMS-Logistia/1.0'
                ]
            ]);

            $response = file_get_contents($url, false, $context);

            if ($response === false) {
                throw new \Exception('Impossibile contattare Google Maps API');
            }

            $data = json_decode($response, true);

            if ($data['status'] === 'OK') {
                $element = $data['rows'][0]['elements'][0];

                if ($element['status'] === 'OK') {
                    $distanzaMetri = $element['distance']['value'];
                    $tempoSecondi = $element['duration']['value'];

                    return [
                        'success' => true,
                        'distanza_km' => round($distanzaMetri / 1000, 2),
                        'tempo_minuti' => round($tempoSecondi / 60),
                        'distanza_testo' => $element['distance']['text'],
                        'tempo_testo' => $element['duration']['text'],
                        'metodo' => 'google_maps'
                    ];
                }
            }

            // Se arriva qui, Google Maps ha restituito un errore
            throw new \Exception('Google Maps API Error: ' . ($data['status'] ?? 'Unknown'));

        } catch (\Exception $e) {
            // Fallback: calcolo approssimativo
            return $this->calcoloApprossimativo($partenza, $arrivo);
        }
    }
    private function calcoloApprossimativo($partenza, $arrivo)
    {
        // Cerca di estrarre città dalle stringhe
        $cittaPartenza = $this->estraiCitta($partenza);
        $cittaArrivo = $this->estraiCitta($arrivo);

        // Database semplificato distanze tra città italiane principali
        $distanze = $this->getDistanzeCittaItaliane();

        $chiave = $cittaPartenza . '-' . $cittaArrivo;
        $chiaveInversa = $cittaArrivo . '-' . $cittaPartenza;

        if (isset($distanze[$chiave])) {
            $distanzaKm = $distanze[$chiave];
        } elseif (isset($distanze[$chiaveInversa])) {
            $distanzaKm = $distanze[$chiaveInversa];
        } else {
            // Calcolo basato su lunghezza stringhe (molto approssimativo)
            $distanzaKm = max(20, strlen($partenza . $arrivo) * 2);
        }

        // Tempo approssimativo: 60km/h media
        $tempoMinuti = round(($distanzaKm / 60) * 60);

        return [
            'success' => true,
            'distanza_km' => $distanzaKm,
            'tempo_minuti' => $tempoMinuti,
            'distanza_testo' => $distanzaKm . ' km',
            'tempo_testo' => $this->formattaTempo($tempoMinuti),
            'metodo' => 'approssimativo'
        ];
    }


    private function estraiCitta($indirizzo)
    {
        $indirizzo = strtolower(trim($indirizzo));

        // Lista città italiane principali
        $citta = [
            'roma', 'milano', 'napoli', 'torino', 'palermo', 'genova', 'bologna',
            'firenze', 'bari', 'catania', 'venezia', 'verona', 'messina', 'padova',
            'trieste', 'brescia', 'parma', 'prato', 'modena', 'reggio emilia',
            'perugia', 'livorno', 'cagliari', 'foggia', 'ravenna', 'salerno',
            'ferrara', 'rimini', 'siracusa', 'pescara', 'monza', 'bergamo',
            'vicenza', 'terni', 'novara', 'piacenza', 'ancona', 'andria',
            'arezzo', 'udine', 'cesena', 'lecce'
        ];

        foreach ($citta as $nome_citta) {
            if (strpos($indirizzo, $nome_citta) !== false) {
                return $nome_citta;
            }
        }

        return 'generic'; // Città generica
    }

    /**
     * Database semplificato distanze tra città italiane
     */
    private function getDistanzeCittaItaliane()
    {
        return [
            // Roma come centro
            'roma-milano' => 575,
            'roma-napoli' => 225,
            'roma-torino' => 660,
            'roma-firenze' => 275,
            'roma-bologna' => 365,
            'roma-venezia' => 530,
            'roma-bari' => 460,
            'roma-palermo' => 935,
            'roma-genova' => 500,
            'roma-catania' => 1050,

            // Milano come centro nord
            'milano-torino' => 140,
            'milano-venezia' => 280,
            'milano-bologna' => 210,
            'milano-firenze' => 295,
            'milano-napoli' => 770,
            'milano-bari' => 875,
            'milano-genova' => 140,
            'milano-verona' => 160,
            'milano-bergamo' => 50,
            'milano-brescia' => 90,

            // Napoli come centro sud
            'napoli-bari' => 260,
            'napoli-palermo' => 490,
            'napoli-catania' => 560,
            'napoli-firenze' => 470,
            'napoli-bologna' => 585,
            'napoli-venezia' => 715,

            // Altre distanze importanti
            'torino-genova' => 170,
            'firenze-bologna' => 105,
            'venezia-verona' => 115,
            'bari-palermo' => 590,
            'genova-bologna' => 220,
            'verona-bologna' => 145,

            // Distanze corte (stessa regione)
            'generic-generic' => 50, // Fallback generico
        ];
    }

// ========================================
// AGGIORNA ANCHE IL METODO calcolaCostoTrasporto
// Per mostrare il metodo usato
// ========================================



    /**
     * Calcola costo dettagliato del trasporto
     */
    private function calcolaCostoDettagliato($distanzaKm, $tempoMinuti, $peso, $tipoMezzo, $tariffa, $urgente)
    {
        $dettaglio = [
            'base' => 0,
            'chilometri' => 0,
            'peso' => 0,
            'tempo' => 0,
            'maggiorazioni' => 0,
            'sconti' => 0,
            'totale' => 0
        ];

        if ($tariffa) {
            // Usa tariffario personalizzato cliente
            $dettaglio['base'] = $tariffa->prezzo_base;

            if ($tariffa->tipo_calcolo === 'km' && $tariffa->prezzo_per_km) {
                $kmFatturabili = max($distanzaKm, $tariffa->km_minimi);
                $dettaglio['chilometri'] = $kmFatturabili * $tariffa->prezzo_per_km;
            }

            if ($tariffa->tipo_calcolo === 'peso' && $tariffa->prezzo_per_kg && $peso > 0) {
                $pesoFatturabile = max($peso, $tariffa->peso_minimo);
                $dettaglio['peso'] = $pesoFatturabile * $tariffa->prezzo_per_kg;
            }

            if ($tariffa->tipo_calcolo === 'tempo' && $tariffa->prezzo_per_ora) {
                $dettaglio['tempo'] = ($tempoMinuti / 60) * $tariffa->prezzo_per_ora;
            }

            // Maggiorazioni
            if ($urgente && $tariffa->maggiorazione_urgente > 0) {
                $dettaglio['maggiorazioni'] += ($dettaglio['base'] + $dettaglio['chilometri'] + $dettaglio['peso'] + $dettaglio['tempo']) * ($tariffa->maggiorazione_urgente / 100);
            }

            // Sconti fedeltà
            if ($tariffa->sconto_fedeltà > 0) {
                $dettaglio['sconti'] = ($dettaglio['base'] + $dettaglio['chilometri'] + $dettaglio['peso'] + $dettaglio['tempo'] + $dettaglio['maggiorazioni']) * ($tariffa->sconto_fedeltà / 100);
            }

        } else {
            // Usa tariffario standard
            $costiStandard = $this->getCostiStandardMezzo($tipoMezzo);

            $dettaglio['base'] = $costiStandard['costo_base'];
            $dettaglio['chilometri'] = $distanzaKm * $costiStandard['costo_per_km'];

            if ($urgente) {
                $dettaglio['maggiorazioni'] = ($dettaglio['base'] + $dettaglio['chilometri']) * 0.25; // 25% urgente
            }
        }

        $dettaglio['totale'] = $dettaglio['base'] + $dettaglio['chilometri'] + $dettaglio['peso'] + $dettaglio['tempo'] + $dettaglio['maggiorazioni'] - $dettaglio['sconti'];

        // Arrotonda a 2 decimali
        foreach ($dettaglio as $key => $value) {
            $dettaglio[$key] = round($value, 2);
        }

        return $dettaglio;
    }

    /**
     * Ottieni costi standard per tipologia mezzo
     */
    private function getCostiStandardMezzo($tipoMezzo)
    {
        $costi = [
            'furgone' => ['costo_base' => 25.00, 'costo_per_km' => 0.85],
            'camion' => ['costo_base' => 45.00, 'costo_per_km' => 1.25],
            'bilico' => ['costo_base' => 80.00, 'costo_per_km' => 1.65],
            'furgoncino' => ['costo_base' => 15.00, 'costo_per_km' => 0.65]
        ];

        return $costi[$tipoMezzo] ?? $costi['furgone'];
    }

    /**
     * Formatta tempo in ore e minuti
     */
    private function formattaTempo($minuti)
    {
        $ore = floor($minuti / 60);
        $minutiRimasti = $minuti % 60;

        if ($ore > 0) {
            return $ore . 'h ' . $minutiRimasti . 'm';
        }

        return $minutiRimasti . 'm';
    }

    /**
     * Gestione tariffari clienti
     */
    public function tariffari(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        if ($request->isMethod('post')) {
            $dati = $request->all();

            if (isset($dati['crea_tariffa'])) {
                DB::table('tariffari_clienti')->insert([
                    'id_cliente' => $dati['id_cliente'],
                    'nome_tariffa' => $dati['nome_tariffa'],
                    'tipo_calcolo' => $dati['tipo_calcolo'],
                    'prezzo_base' => $dati['prezzo_base'] ?? 0,
                    'prezzo_per_km' => $dati['prezzo_per_km'] ?? null,
                    'prezzo_per_kg' => $dati['prezzo_per_kg'] ?? null,
                    'prezzo_per_ora' => $dati['prezzo_per_ora'] ?? null,
                    'km_minimi' => $dati['km_minimi'] ?? 0,
                    'peso_minimo' => $dati['peso_minimo'] ?? 0,
                    'maggiorazione_urgente' => $dati['maggiorazione_urgente'] ?? 0,
                    'maggiorazione_festivo' => $dati['maggiorazione_festivo'] ?? 0,
                    'maggiorazione_notturno' => $dati['maggiorazione_notturno'] ?? 0,
                    'sconto_fedeltà' => $dati['sconto_fedeltà'] ?? 0,
                    'valido_dal' => $dati['valido_dal'],
                    'valido_fino' => $dati['valido_fino'] ?: null,
                    'id_azienda' => $utente->id_azienda,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } elseif (isset($dati['modifica_tariffa'])) {
                DB::table('tariffari_clienti')
                    ->where('id', $dati['id_tariffa'])
                    ->where('id_azienda', $utente->id_azienda)
                    ->update([
                        'nome_tariffa' => $dati['nome_tariffa'],
                        'tipo_calcolo' => $dati['tipo_calcolo'],
                        'prezzo_base' => $dati['prezzo_base'] ?? 0,
                        'prezzo_per_km' => $dati['prezzo_per_km'] ?? null,
                        'prezzo_per_kg' => $dati['prezzo_per_kg'] ?? null,
                        'prezzo_per_ora' => $dati['prezzo_per_ora'] ?? null,
                        'km_minimi' => $dati['km_minimi'] ?? 0,
                        'peso_minimo' => $dati['peso_minimo'] ?? 0,
                        'maggiorazione_urgente' => $dati['maggiorazione_urgente'] ?? 0,
                        'maggiorazione_festivo' => $dati['maggiorazione_festivo'] ?? 0,
                        'maggiorazione_notturno' => $dati['maggiorazione_notturno'] ?? 0,
                        'sconto_fedeltà' => $dati['sconto_fedeltà'] ?? 0,
                        'valido_dal' => $dati['valido_dal'],
                        'valido_fino' => $dati['valido_fino'] ?: null,
                        'updated_at' => now()
                    ]);
            } elseif (isset($dati['elimina_tariffa'])) {
                DB::table('tariffari_clienti')
                    ->where('id', $dati['id_tariffa'])
                    ->where('id_azienda', $utente->id_azienda)
                    ->delete();
            }

            return redirect('/azienda/tariffari')->with('success', 'Operazione completata!');
        }

        $tariffari = DB::table('tariffari_clienti as t')
            ->leftJoin('clienti as c', 't.id_cliente', '=', 'c.id')
            ->where('t.id_azienda', $utente->id_azienda)
            ->select('t.*', 'c.ragione_sociale as cliente_nome')
            ->orderBy('t.created_at', 'desc')
            ->get();

        $clienti = DB::table('clienti')->where('id_azienda', $utente->id_azienda)->get();

        return view('azienda.tariffari', compact('tariffari', 'clienti', 'utente'));
    }

    /**
     * Gestione documenti di trasporto (MODIFICATO)
     */
    public function documenti(Request $request, $idOrdine = null)
    {
        $this->is_loggato();
        $utente = session('utente');

        if ($request->isMethod('post')) {
            $dati = $request->all();

            if (isset($dati['crea_documento'])) {
                $numeroDocumento = $this->generaNumeroDocumento($dati['tipo_documento'], $utente->id_azienda);

                DB::table('documenti_trasporto')->insert([
                    'id_ordine' => $dati['id_ordine'],
                    'tipo_documento' => $dati['tipo_documento'],
                    'numero_documento' => $numeroDocumento,
                    'data_documento' => $dati['data_documento'],
                    'mittente_nome' => $dati['mittente_nome'],
                    'mittente_indirizzo' => $dati['mittente_indirizzo'],
                    'destinatario_nome' => $dati['destinatario_nome'],
                    'destinatario_indirizzo' => $dati['destinatario_indirizzo'],
                    'descrizione_merce' => $dati['descrizione_merce'],
                    'peso_lordo' => $dati['peso_lordo'] ?? null,
                    'peso_netto' => $dati['peso_netto'] ?? null,
                    'numero_colli' => $dati['numero_colli'] ?? null,
                    'valore_merce' => $dati['valore_merce'] ?? null,
                    'note' => $dati['note'] ?? null,
                    'id_azienda' => $utente->id_azienda,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            return redirect()->back()->with('success', 'Documento creato con successo!');
        }

        $query = DB::table('documenti_trasporto as d')
            ->leftJoin('ordini_trasporto as o', 'd.id_ordine', '=', 'o.id')
            ->leftJoin('clienti as c', 'o.id_cliente', '=', 'c.id')
            ->where('d.id_azienda', $utente->id_azienda)
            ->select('d.*', 'o.numero_ordine', 'c.ragione_sociale as cliente_nome');

        if ($idOrdine) {
            $query->where('d.id_ordine', $idOrdine);
        }

        $documenti = $query->orderBy('d.created_at', 'desc')->get();

        // Ordini con dati completi per il form di creazione
        $ordini = DB::table('ordini_trasporto as o')
            ->leftJoin('clienti as c', 'o.id_cliente', '=', 'c.id')
            ->where('o.id_azienda', $utente->id_azienda)
            ->select('o.id', 'o.numero_ordine', 'c.ragione_sociale as cliente_nome')
            ->orderBy('o.created_at', 'desc')
            ->get();

        // Dati azienda per mittente auto-compilato
        $azienda = DB::table('aziende')->where('id', $utente->id_azienda)->first();

        return view('azienda.documenti_trasporto', compact('documenti', 'ordini', 'utente', 'idOrdine', 'azienda'));
    }

    /**
     * Segna documento come consegnato (AJAX)
     */
    public function segnaConsegnato(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        $idDocumento = $request->input('id_documento');
        $dataConsegna = $request->input('data_consegna');
        $consegnatoA = $request->input('consegnato_a');
        $noteConsegna = $request->input('note_consegna');

        // Verifica che il documento appartenga all'azienda
        $documento = DB::table('documenti_trasporto')
            ->where('id', $idDocumento)
            ->where('id_azienda', $utente->id_azienda)
            ->first();

        if (!$documento) {
            return response()->json([
                'success' => false,
                'message' => 'Documento non trovato'
            ], 404);
        }

        // Aggiorna documento
        $updated = DB::table('documenti_trasporto')
            ->where('id', $idDocumento)
            ->update([
                'data_consegna' => $dataConsegna,
                'consegnato_a' => $consegnatoA,
                'note' => $noteConsegna ? ($documento->note ? $documento->note . "\n--- Nota consegna ---\n" . $noteConsegna : $noteConsegna) : $documento->note,
                'updated_at' => now()
            ]);

        if ($updated) {
            // Se c'è un ordine collegato, aggiorna anche lo stato ordine a "completato"
            if ($documento->id_ordine) {
                DB::table('ordini_trasporto')
                    ->where('id', $documento->id_ordine)
                    ->where('id_azienda', $utente->id_azienda)
                    ->where('stato', '!=', 'completato') // Non sovrascrivere se già completato
                    ->update([
                        'stato' => 'completato',
                        'updated_at' => now()
                    ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Documento segnato come consegnato!'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Errore durante l\'aggiornamento'
        ], 500);
    }


    /**
     * Restituisce dati ordine per compilazione DDT (AJAX)
     */
    public function getDatiOrdine($id)
    {
        $this->is_loggato();
        $utente = session('utente');

        $ordine = DB::table('ordini_trasporto as o')
            ->leftJoin('clienti as c', 'o.id_cliente', '=', 'c.id')
            ->where('o.id', $id)
            ->where('o.id_azienda', $utente->id_azienda)
            ->select(
                'o.*',
                'c.ragione_sociale as cliente_nome',
                'c.indirizzo as cliente_indirizzo',
                'c.telefono as cliente_telefono',
                'c.email as cliente_email'
            )
            ->first();

        if (!$ordine) {
            return response()->json(['success' => false, 'message' => 'Ordine non trovato'], 404);
        }

        return response()->json([
            'success' => true,
            'ordine' => $ordine
        ]);
    }



    /**
     * Genera numero progressivo documento
     */
    private function generaNumeroDocumento($tipoDocumento, $idAzienda)
    {
        $anno = date('Y');
        $prefisso = strtoupper(substr($tipoDocumento, 0, 3));

        $ultimoNumero = DB::table('documenti_trasporto')
            ->where('id_azienda', $idAzienda)
            ->where('tipo_documento', $tipoDocumento)
            ->where('numero_documento', 'like', $prefisso . $anno . '%')
            ->max('numero_documento');

        if ($ultimoNumero) {
            $progressivo = (int)substr($ultimoNumero, -4) + 1;
        } else {
            $progressivo = 1;
        }

        return $prefisso . $anno . sprintf('%04d', $progressivo);
    }
    /**
     * Genera DDT manualmente per un ordine che non ce l'ha
     */
    public function generaDDT($idOrdine)
    {
        $this->is_loggato();
        $utente = session('utente');

        // Verifica se esiste già un DDT
        $ddtEsistente = DB::table('documenti_trasporto')
            ->where('id_ordine', $idOrdine)
            ->where('tipo_documento', 'ddt')
            ->first();

        if ($ddtEsistente) {
            return response()->json([
                'success' => false,
                'message' => 'DDT già esistente per questo ordine'
            ]);
        }

        // Recupera l'ordine
        $ordine = DB::table('ordini_trasporto')
            ->where('id', $idOrdine)
            ->where('id_azienda', $utente->id_azienda)
            ->first();

        if (!$ordine) {
            return response()->json([
                'success' => false,
                'message' => 'Ordine non trovato'
            ]);
        }

        // Recupera dati cliente
        $cliente = null;
        if ($ordine->id_cliente) {
            $cliente = DB::table('clienti')
                ->where('id', $ordine->id_cliente)
                ->first();
        }

        // Recupera dati azienda
        $azienda = DB::table('aziende')
            ->where('id', $utente->id_azienda)
            ->first();

        // Genera numero DDT progressivo
        $anno = date('Y');
        $ultimoDdt = DB::table('documenti_trasporto')
            ->where('id_azienda', $utente->id_azienda)
            ->where('tipo_documento', 'ddt')
            ->whereYear('data_documento', $anno)
            ->orderBy('id', 'desc')
            ->first();

        if ($ultimoDdt && preg_match('/(\d+)\/' . $anno . '/', $ultimoDdt->numero_documento, $matches)) {
            $progressivo = intval($matches[1]) + 1;
        } else {
            $progressivo = 1;
        }
        $numeroDdt = $progressivo . '/' . $anno;

        // Crea DDT
        DB::table('documenti_trasporto')->insert([
            'id_ordine' => $idOrdine,
            'tipo_documento' => 'ddt',
            'numero_documento' => $numeroDdt,
            'token_pubblico' => md5(uniqid(rand(), true)),
            'data_documento' => $ordine->data_ritiro ?? date('Y-m-d'),
            'mittente_nome' => $azienda->ragione_sociale ?? $azienda->nome ?? '',
            'mittente_indirizzo' => $ordine->indirizzo_ritiro ?? '',
            'destinatario_nome' => $cliente->ragione_sociale ?? $cliente->nome ?? '',
            'destinatario_indirizzo' => $ordine->indirizzo_consegna ?? '',
            'descrizione_merce' => $ordine->descrizione_merce ?? '',
            'peso_lordo' => $ordine->peso_kg ?? null,
            'valore_merce' => $ordine->importo ?? null,
            'note' => $ordine->note ?? null,
            'id_azienda' => $utente->id_azienda,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'DDT generato con successo',
            'numero_ddt' => $numeroDdt
        ]);
    }

    /**
     * Salva firma sul DDT (chiamato via AJAX)
     * Tipo firma: 'vettore', 'destinatario'
     */
    public function salvaFirmaDDT(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        $idOrdine = $request->input('id_ordine');
        $tipoFirma = $request->input('tipo_firma'); // vettore, destinatario
        $firmaBase64 = $request->input('firma'); // data:image/png;base64,...

        // Verifica tipo firma valido (solo vettore e destinatario)
        if (!in_array($tipoFirma, ['vettore', 'destinatario'])) {
            return response()->json([
                'success' => false,
                'message' => 'Tipo firma non valido'
            ]);
        }

        // Trova il DDT
        $ddt = DB::table('documenti_trasporto')
            ->where('id_ordine', $idOrdine)
            ->where('tipo_documento', 'ddt')
            ->where('id_azienda', $utente->id_azienda)
            ->first();

        if (!$ddt) {
            return response()->json([
                'success' => false,
                'message' => 'DDT non trovato'
            ]);
        }

        // Prepara i campi da aggiornare
        $campoFirma = 'firma_' . $tipoFirma;
        $campoData = 'data_firma_' . $tipoFirma;

        // Aggiorna il DDT
        DB::table('documenti_trasporto')
            ->where('id', $ddt->id)
            ->update([
                $campoFirma => $firmaBase64,
                $campoData => now(),
                'updated_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Firma salvata con successo',
            'tipo_firma' => $tipoFirma,
            'data_firma' => date('d/m/Y H:i')
        ]);
    }

    /**
     * Rimuovi firma dal DDT
     */
    public function rimuoviFirmaDDT(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        $idOrdine = $request->input('id_ordine');
        $tipoFirma = $request->input('tipo_firma');

        if (!in_array($tipoFirma, ['vettore', 'destinatario'])) {
            return response()->json([
                'success' => false,
                'message' => 'Tipo firma non valido'
            ]);
        }

        $campoFirma = 'firma_' . $tipoFirma;
        $campoData = 'data_firma_' . $tipoFirma;

        DB::table('documenti_trasporto')
            ->where('id_ordine', $idOrdine)
            ->where('tipo_documento', 'ddt')
            ->where('id_azienda', $utente->id_azienda)
            ->update([
                $campoFirma => null,
                $campoData => null,
                'updated_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Firma rimossa'
        ]);
    }
    /**
     * API: Ottieni tariffario attivo del cliente (AJAX)
     */
    public function getTariffaCliente($idCliente)
    {
        $this->is_loggato();
        $utente = session('utente');

        $tariffa = DB::table('tariffari_clienti')
            ->where('id_cliente', $idCliente)
            ->where('id_azienda', $utente->id_azienda)
            ->where('attivo', 1)
            ->where('valido_dal', '<=', date('Y-m-d'))
            ->where(function ($q) {
                $q->whereNull('valido_fino')
                    ->orWhere('valido_fino', '>=', date('Y-m-d'));
            })
            ->orderBy('created_at', 'desc')
            ->first();

        if ($tariffa) {
            return response()->json([
                'found' => true,
                'tariffa' => $tariffa
            ]);
        }

        return response()->json(['found' => false]);
    }


// =====================================================
// 4. MODIFICA il metodo creaOrdine() esistente
//    nel TrasportiController (sostituisci tutto)
// =====================================================

    /**
     * Crea nuovo ordine di trasporto + DDT automatico
     */
    private function creaOrdine($dati, $utente)
    {
        $numeroOrdine = $this->generaNumeroOrdine($utente->id_azienda);

        // Calcola importo (manuale o da tariffario) - se hai la modifica tariffario
        $importo = $dati['importo'] ?? 0;
        $importoManuale = 1;
        $idTariffaApplicata = null;
        $dettaglioCosto = null;

        if (isset($dati['modalita_importo']) && $dati['modalita_importo'] === 'tariffario') {
            $importoManuale = 0;
            $idTariffaApplicata = $dati['id_tariffa_applicata'] ?? null;

            if ($idTariffaApplicata) {
                $tariffa = DB::table('tariffari_clienti')->where('id', $idTariffaApplicata)->first();
                if ($tariffa) {
                    $risultato = $this->calcolaImportoDaTariffa($tariffa, $dati);
                    $importo = $risultato['totale'];
                    $dettaglioCosto = json_encode($risultato);
                }
            }
        }

        // Inserisci ordine
        $idOrdine = DB::table('ordini_trasporto')->insertGetId([
            'numero_ordine' => $numeroOrdine,
            'id_cliente' => $dati['id_cliente'],
            'id_mezzo' => $dati['id_mezzo'] ?? null,
            'id_autista' => $dati['id_autista'] ?? null,
            'indirizzo_ritiro' => $dati['indirizzo_ritiro'],
            'indirizzo_consegna' => $dati['indirizzo_consegna'],
            'data_ritiro' => $dati['data_ritiro'],
            'ora_ritiro' => $dati['ora_ritiro'] ?? null,
            'data_consegna' => $dati['data_consegna'] ?? null,
            'ora_consegna' => $dati['ora_consegna'] ?? null,
            'descrizione_merce' => $dati['descrizione_merce'],
            'numero_colli' => $dati['numero_colli'] ?? null,
            'peso_kg' => $dati['peso_kg'] ?? null,
            'km_totali' => $dati['km_totali'] ?? null,
            'ore_stimate' => $dati['ore_stimate'] ?? null,
            'note' => $dati['note'] ?? null,
            'importo' => $importo,
            'importo_manuale' => $importoManuale,
            'id_tariffa_applicata' => $idTariffaApplicata,
            'dettaglio_costo' => $dettaglioCosto,
            'stato' => 'pianificato',
            'id_azienda' => $utente->id_azienda,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // ============================================
        // GENERAZIONE AUTOMATICA DDT (se checkbox attiva)
        // ============================================
        if (isset($dati['genera_ddt']) && $dati['genera_ddt'] == '1') {

            // Recupera dati azienda (mittente)
            $azienda = DB::table('aziende')->where('id', $utente->id_azienda)->first();

            // Recupera dati cliente (destinatario)
            $cliente = DB::table('clienti')->where('id', $dati['id_cliente'])->first();

            // Genera numero DDT progressivo
            $numeroDdt = $this->generaNumeroDocumento('ddt', $utente->id_azienda);

            // Componi indirizzo mittente
            $mittente_indirizzo = '';
            if ($azienda) {
                $parti = array_filter([
                    $azienda->indirizzo ?? null,
                    $azienda->cap ?? null,
                    $azienda->comune ?? null,
                    $azienda->provincia ? '(' . $azienda->provincia . ')' : null
                ]);
                $mittente_indirizzo = implode(', ', $parti);
            }

            // Inserisci DDT nella tabella documenti_trasporto
            DB::table('documenti_trasporto')->insert([
                'id_ordine' => $idOrdine,
                'tipo_documento' => 'ddt',
                'numero_documento' => $numeroDdt,
                'data_documento' => $dati['data_ritiro'], // Data DDT = data ritiro
                'mittente_nome' => $azienda->ragione_sociale ?? 'Azienda',
                'mittente_indirizzo' => $mittente_indirizzo,
                'destinatario_nome' => $cliente->ragione_sociale ?? '',
                'destinatario_indirizzo' => $dati['indirizzo_consegna'],
                'descrizione_merce' => $dati['descrizione_merce'],
                'peso_lordo' => $dati['peso_kg'] ?? null,
                'peso_netto' => null,
                'numero_colli' => $dati['numero_colli'] ?? null,
                'valore_merce' => $importo > 0 ? $importo : null,
                'note' => $dati['note'] ?? null,
                'id_azienda' => $utente->id_azienda,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Aggiorna l'ordine con il riferimento al DDT
            DB::table('ordini_trasporto')
                ->where('id', $idOrdine)
                ->update(['numero_ddt' => $numeroDdt]);
            // ============================================
            // NOTIFICA AUTISTA (se assegnato)
            // ============================================
            if (!empty($dati['id_autista'])) {
                AutistaController::creaNotifica(
                    $dati['id_autista'],
                    $utente->id_azienda,
                    'nuovo_ordine',
                    '📦 Nuovo ordine assegnato',
                    'Ordine ' . $numeroOrdine . ' - Consegna a: ' . $dati['indirizzo_consegna'],
                    $idOrdine
                );
            }
        }
    }

    /**
     * Modifica ordine - AGGIORNATO con tariffario
     */
    private function modificaOrdine($dati, $utente)
    {
        $importo = $dati['importo'] ?? 0;
        $importoManuale = 1;
        $idTariffaApplicata = null;
        $dettaglioCosto = null;

        if (isset($dati['modalita_importo']) && $dati['modalita_importo'] === 'tariffario') {
            $importoManuale = 0;
            $idTariffaApplicata = $dati['id_tariffa_applicata'] ?? null;

            if ($idTariffaApplicata) {
                $tariffa = DB::table('tariffari_clienti')->where('id', $idTariffaApplicata)->first();
                if ($tariffa) {
                    $risultato = $this->calcolaImportoDaTariffa($tariffa, $dati);
                    $importo = $risultato['totale'];
                    $dettaglioCosto = json_encode($risultato);
                }
            }
        }

        // ============================================
        // NOTIFICA: leggi autista PRIMA dell'update
        // ============================================
        $nuovoAutista = $dati['id_autista'] ?? null;
        $ordineCorrente = null;
        if ($nuovoAutista) {
            $ordineCorrente = DB::table('ordini_trasporto')->where('id', $dati['id_ordine'])->first();
        }

        DB::table('ordini_trasporto')
            ->where('id', $dati['id_ordine'])
            ->where('id_azienda', $utente->id_azienda)
            ->update([
                'id_cliente' => $dati['id_cliente'],
                'id_mezzo' => $dati['id_mezzo'] ?? null,
                'id_autista' => $dati['id_autista'] ?? null,
                'indirizzo_ritiro' => $dati['indirizzo_ritiro'],
                'indirizzo_consegna' => $dati['indirizzo_consegna'],
                'data_ritiro' => $dati['data_ritiro'],
                'ora_ritiro' => $dati['ora_ritiro'] ?? null,
                'data_consegna' => $dati['data_consegna'] ?? null,
                'ora_consegna' => $dati['ora_consegna'] ?? null,
                'descrizione_merce' => $dati['descrizione_merce'],
                'numero_colli' => $dati['numero_colli'] ?? null,
                'peso_kg' => $dati['peso_kg'] ?? null,
                'km_totali' => $dati['km_totali'] ?? null,
                'ore_stimate' => $dati['ore_stimate'] ?? null,
                'note' => $dati['note'] ?? null,
                'importo' => $importo,
                'importo_manuale' => $importoManuale,
                'id_tariffa_applicata' => $idTariffaApplicata,
                'dettaglio_costo' => $dettaglioCosto,
                'updated_at' => now()
            ]);

        // ============================================
        // NOTIFICA: invia se autista è cambiato
        // ============================================
        if ($nuovoAutista && $ordineCorrente && $nuovoAutista != $ordineCorrente->id_autista) {
            AutistaController::creaNotifica(
                $nuovoAutista,
                $utente->id_azienda,
                'nuovo_ordine',
                '📦 Ordine riassegnato a te',
                'Ordine ' . $ordineCorrente->numero_ordine . ' - Consegna a: ' . $dati['indirizzo_consegna'],
                $dati['id_ordine']
            );
        }
    }

    /**
     * Calcola importo da tariffario
     */
    private function calcolaImportoDaTariffa($tariffa, $dati)
    {
        $dettaglio = [
            'nome_tariffa' => $tariffa->nome_tariffa,
            'tipo_calcolo' => $tariffa->tipo_calcolo,
            'base' => (float) $tariffa->prezzo_base,
            'variabile' => 0,
            'maggiorazioni' => 0,
            'sconti' => 0,
            'totale' => 0,
            'descrizione' => ''
        ];

        $subtotale = $dettaglio['base'];

        switch ($tariffa->tipo_calcolo) {
            case 'fisso':
                $dettaglio['descrizione'] = 'Prezzo fisso';
                break;

            case 'km':
                $km = (float) ($dati['km_totali'] ?? 0);
                $kmFatturabili = max($km, (float) ($tariffa->km_minimi ?? 0));
                $prezzoKm = (float) ($tariffa->prezzo_per_km ?? 0);
                $dettaglio['variabile'] = round($kmFatturabili * $prezzoKm, 2);
                $dettaglio['descrizione'] = "{$kmFatturabili} km × € " . number_format($prezzoKm, 3, ',', '.');
                if ($km < ($tariffa->km_minimi ?? 0)) {
                    $dettaglio['descrizione'] .= " (min. {$tariffa->km_minimi} km)";
                }
                break;

            case 'peso':
                $peso = (float) ($dati['peso_kg'] ?? 0);
                $pesoFatturabile = max($peso, (float) ($tariffa->peso_minimo ?? 0));
                $prezzoKg = (float) ($tariffa->prezzo_per_kg ?? 0);
                $dettaglio['variabile'] = round($pesoFatturabile * $prezzoKg, 2);
                $dettaglio['descrizione'] = "{$pesoFatturabile} kg × € " . number_format($prezzoKg, 3, ',', '.');
                if ($peso < ($tariffa->peso_minimo ?? 0)) {
                    $dettaglio['descrizione'] .= " (min. {$tariffa->peso_minimo} kg)";
                }
                break;

            case 'tempo':
                $ore = (float) ($dati['ore_stimate'] ?? 0);
                $prezzoOra = (float) ($tariffa->prezzo_per_ora ?? 0);
                $dettaglio['variabile'] = round($ore * $prezzoOra, 2);
                $dettaglio['descrizione'] = "{$ore} ore × € " . number_format($prezzoOra, 2, ',', '.');
                break;

            case 'volume':
                // Se serve volume, si può aggiungere in futuro
                $dettaglio['descrizione'] = 'Calcolo a volume';
                break;
        }

        $subtotale += $dettaglio['variabile'];

        // Maggiorazioni (controlla se urgente / festivo / notturno)
        $maggiorazionePercent = 0;
        $descrizioniMagg = [];
        if (!empty($dati['urgente']) && $tariffa->maggiorazione_urgente > 0) {
            $maggiorazionePercent += (float) $tariffa->maggiorazione_urgente;
            $descrizioniMagg[] = "Urgente +{$tariffa->maggiorazione_urgente}%";
        }
        if (!empty($dati['festivo']) && $tariffa->maggiorazione_festivo > 0) {
            $maggiorazionePercent += (float) $tariffa->maggiorazione_festivo;
            $descrizioniMagg[] = "Festivo +{$tariffa->maggiorazione_festivo}%";
        }
        if (!empty($dati['notturno']) && $tariffa->maggiorazione_notturno > 0) {
            $maggiorazionePercent += (float) $tariffa->maggiorazione_notturno;
            $descrizioniMagg[] = "Notturno +{$tariffa->maggiorazione_notturno}%";
        }
        if ($maggiorazionePercent > 0) {
            $dettaglio['maggiorazioni'] = round($subtotale * ($maggiorazionePercent / 100), 2);
            $dettaglio['desc_maggiorazioni'] = implode(', ', $descrizioniMagg);
        }

        // Sconto fedeltà
        $totalePrimaDiSconto = $subtotale + $dettaglio['maggiorazioni'];
        if ($tariffa->{'sconto_fedeltà'} > 0) {
            $dettaglio['sconti'] = round($totalePrimaDiSconto * ((float) $tariffa->{'sconto_fedeltà'} / 100), 2);
            $dettaglio['desc_sconto'] = "Sconto fedeltà -{$tariffa->{'sconto_fedeltà'}}%";
        }

        $dettaglio['totale'] = round($totalePrimaDiSconto - $dettaglio['sconti'], 2);

        return $dettaglio;
    }
    /**
     * API: Restituisce il prossimo numero DDT (AJAX)
     */
    public function prossimoNumeroDdt()
    {
        $this->is_loggato();
        $utente = session('utente');

        $numeroDdt = $this->generaNumeroDocumento('ddt', $utente->id_azienda);

        return response()->json([
            'success' => true,
            'numero_ddt' => $numeroDdt
        ]);
    }




    /**
     * Centro Operativo - Pagina principale
     */
    public function centroOperativo()
    {
        $this->is_loggato();
        $utente = session('utente');
        $oggi = date('Y-m-d');

        // Tutti gli autisti dell'azienda (quelli con dispositivo tracking attivo)
        $autisti = DB::table('utenti as u')
            ->leftJoin('dispositivi_tracking as dt', function ($join) {
                $join->on('u.id', '=', 'dt.id_utente')
                    ->where('dt.is_active', 1);
            })
            ->where('u.id_azienda', $utente->id_azienda)
            ->where('u.abilitato', 1)
            ->select('u.id', 'u.nome', 'u.cognome', 'u.telefono', 'u.email', 'u.immagine', 'dt.id as id_dispositivo')
            ->orderBy('u.nome')
            ->get();

        // Ordini di OGGI per ogni autista
        $ordiniOggi = DB::table('ordini_trasporto as ot')
            ->leftJoin('clienti as c', 'ot.id_cliente', '=', 'c.id')
            ->where('ot.id_azienda', $utente->id_azienda)
            ->where('ot.data_ritiro', $oggi)
            ->whereNotNull('ot.id_autista')
            ->select(
                'ot.*',
                'c.ragione_sociale as cliente_nome'
            )
            ->orderBy('ot.ora_ritiro')
            ->get()
            ->groupBy('id_autista');

        // Posizioni GPS live degli autisti (tramite dispositivi)
        $posizioni = DB::table('posizioni_live as pl')
            ->join('dispositivi_tracking as dt', 'pl.id_dispositivo', '=', 'dt.id')
            ->leftJoin('mezzi as m', 'dt.id_mezzo', '=', 'm.id')
            ->where('pl.id_azienda', $utente->id_azienda)
            ->where('dt.is_active', 1)
            ->select(
                'dt.id_utente',
                'pl.lat',
                'pl.lng',
                'pl.speed',
                'pl.is_moving',
                'pl.updated_at as ultimo_aggiornamento',
                'm.targa',
                'm.nome as nome_mezzo'
            )
            ->get()
            ->keyBy('id_utente');

        // Costruisci dati aggregati per ogni autista
        $datiAutisti = [];
        foreach ($autisti as $autista) {
            $ordini = $ordiniOggi->get($autista->id, collect());
            $posizione = $posizioni->get($autista->id);

            $totale = $ordini->count();
            $completati = $ordini->where('stato', 'completato')->count();
            $inCorso = $ordini->where('stato', 'in_corso')->count();
            $assegnati = $ordini->where('stato', 'assegnato')->count();
            $pianificati = $ordini->where('stato', 'pianificato')->count();
            $annullati = $ordini->where('stato', 'annullato')->count();
            $rimanenti = $totale - $completati - $annullati;

            // Prossima consegna (primo ordine non completato/annullato)
            $prossima = $ordini->whereNotIn('stato', ['completato', 'annullato'])->first();

            // Calcola se è online (ultimo aggiornamento < 5 minuti)
            $isOnline = false;
            if ($posizione && $posizione->ultimo_aggiornamento) {
                $diffMinuti = now()->diffInMinutes($posizione->ultimo_aggiornamento);
                $isOnline = $diffMinuti < 5;
            }

            $datiAutisti[] = (object)[
                'id' => $autista->id,
                'nome' => $autista->nome,
                'cognome' => $autista->cognome,
                'telefono' => $autista->telefono,
                'email' => $autista->email,
                'immagine' => $autista->immagine,
                'totale_oggi' => $totale,
                'completati' => $completati,
                'in_corso' => $inCorso,
                'assegnati' => $assegnati,
                'pianificati' => $pianificati,
                'annullati' => $annullati,
                'rimanenti' => $rimanenti,
                'percentuale' => $totale > 0 ? round(($completati / $totale) * 100) : 0,
                'prossima_consegna' => $prossima,
                'lat' => $posizione->lat ?? null,
                'lng' => $posizione->lng ?? null,
                'speed' => $posizione->speed ?? 0,
                'is_moving' => $posizione->is_moving ?? false,
                'is_online' => $isOnline,
                'ultimo_aggiornamento' => $posizione->ultimo_aggiornamento ?? null,
                'targa' => $posizione->targa ?? null,
                'nome_mezzo' => $posizione->nome_mezzo ?? null,
                'ordini' => $ordini->values(),
            ];
        }

        $autistiConOrdini = collect($datiAutisti)->filter(function ($a) {
            return $a->totale_oggi > 0;
        })->sortByDesc('in_corso');
        $autistiSenzaOrdini = collect($datiAutisti)->filter(function ($a) {
            return $a->totale_oggi == 0;
        });

        // Statistiche globali
        $stats = (object)[
            'autisti_attivi' => $autistiConOrdini->count(),
            'autisti_online' => collect($datiAutisti)->where('is_online', true)->count(),
            'totale_consegne' => collect($datiAutisti)->sum('totale_oggi'),
            'completate' => collect($datiAutisti)->sum('completati'),
            'in_corso' => collect($datiAutisti)->sum('in_corso'),
            'rimanenti' => collect($datiAutisti)->sum('rimanenti'),
            'annullate' => collect($datiAutisti)->sum('annullati'),
            'percentuale_globale' => 0,
        ];
        if ($stats->totale_consegne > 0) {
            $stats->percentuale_globale = round(($stats->completate / $stats->totale_consegne) * 100);
        }

        return view('azienda.centro_operativo', compact(
            'utente', 'autistiConOrdini', 'autistiSenzaOrdini', 'stats'
        ));
    }

    /**
     * Centro Operativo - Dati live (AJAX)
     */
    public function centroOperativoLive()
    {
        $this->is_loggato();
        $utente = session('utente');
        $oggi = date('Y-m-d');

        // Ordini di oggi raggruppati per autista
        $ordiniOggi = DB::table('ordini_trasporto as ot')
            ->leftJoin('clienti as c', 'ot.id_cliente', '=', 'c.id')
            ->where('ot.id_azienda', $utente->id_azienda)
            ->where('ot.data_ritiro', $oggi)
            ->whereNotNull('ot.id_autista')
            ->select(
                'ot.id',
                'ot.id_autista',
                'ot.numero_ordine',
                'ot.stato',
                'ot.indirizzo_consegna',
                'ot.ora_ritiro',
                'c.ragione_sociale as cliente_nome'
            )
            ->orderBy('ot.ora_ritiro')
            ->get()
            ->groupBy('id_autista');

        // Posizioni live
        $posizioni = DB::table('posizioni_live as pl')
            ->join('dispositivi_tracking as dt', 'pl.id_dispositivo', '=', 'dt.id')
            ->leftJoin('mezzi as m', 'dt.id_mezzo', '=', 'm.id')
            ->where('pl.id_azienda', $utente->id_azienda)
            ->where('dt.is_active', 1)
            ->select(
                'dt.id_utente',
                'pl.lat',
                'pl.lng',
                'pl.speed',
                'pl.is_moving',
                'pl.updated_at as ultimo_aggiornamento',
                'm.targa',
                'm.nome as nome_mezzo'
            )
            ->get()
            ->keyBy('id_utente');

        // Autisti con dispositivo attivo
        $autisti = DB::table('utenti as u')
            ->join('dispositivi_tracking as dt', function ($join) {
                $join->on('u.id', '=', 'dt.id_utente')
                    ->where('dt.is_active', 1);
            })
            ->where('u.id_azienda', $utente->id_azienda)
            ->where('u.abilitato', 1)
            ->select('u.id', 'u.nome', 'u.cognome')
            ->get();

        $risultato = [];
        foreach ($autisti as $autista) {
            $ordini = $ordiniOggi->get($autista->id, collect());
            $pos = $posizioni->get($autista->id);

            $totale = $ordini->count();
            if ($totale == 0) continue; // Solo autisti con ordini oggi

            $completati = $ordini->where('stato', 'completato')->count();
            $inCorso = $ordini->where('stato', 'in_corso')->count();
            $annullati = $ordini->where('stato', 'annullato')->count();
            $prossima = $ordini->whereNotIn('stato', ['completato', 'annullato'])->first();

            $isOnline = false;
            if ($pos && $pos->ultimo_aggiornamento) {
                $isOnline = now()->diffInMinutes($pos->ultimo_aggiornamento) < 5;
            }

            $risultato[] = [
                'id' => $autista->id,
                'nome' => $autista->nome . ' ' . $autista->cognome,
                'totale_oggi' => $totale,  // Corretto: stesso nome della view
                'completati' => $completati,
                'in_corso' => $inCorso,
                'annullati' => $annullati,
                'rimanenti' => $totale - $completati - $annullati,
                'percentuale' => $totale > 0 ? round(($completati / $totale) * 100) : 0,
                'prossima_consegna' => $prossima ? [
                    'cliente_nome' => $prossima->cliente_nome,
                    'indirizzo_consegna' => $prossima->indirizzo_consegna,
                    'ora_ritiro' => $prossima->ora_ritiro,
                ] : null,
                'lat' => $pos->lat ?? null,
                'lng' => $pos->lng ?? null,
                'speed' => $pos->speed ?? 0,
                'is_moving' => $pos->is_moving ?? false,
                'is_online' => $isOnline,
                'targa' => $pos->targa ?? null,
            ];
        }

        // Stats globali
        $totConsegne = collect($risultato)->sum('totale_oggi');
        $totComplete = collect($risultato)->sum('completati');

        return response()->json([
            'success' => true,
            'autisti' => $risultato,
            'stats' => [
                'attivi' => count($risultato),
                'online' => collect($risultato)->where('is_online', true)->count(),
                'totale' => $totConsegne,
                'completate' => $totComplete,
                'in_corso' => collect($risultato)->sum('in_corso'),
                'rimanenti' => collect($risultato)->sum('rimanenti'),
                'percentuale' => $totConsegne > 0 ? round(($totComplete / $totConsegne) * 100) : 0,
            ],
            'timestamp' => now()->format('H:i:s')
        ]);
    }
    // =====================================================
// 2. AGGIUNGI QUESTO METODO IN TrasportiController.php
//    (dopo il metodo cambiaStatoOrdine o dove preferisci)
// =====================================================

    /**
     * Calcola Km e durata tra due indirizzi (AJAX dalla modal ordini)
     * Riutilizza il metodo calcolaDistanzaGoogleMaps già esistente
     */
    public function calcolaKm(Request $request)
    {
        $this->is_loggato();

        $indirizzoRitiro = $request->input('indirizzo_ritiro');
        $indirizzoConsegna = $request->input('indirizzo_consegna');

        if (!$indirizzoRitiro || !$indirizzoConsegna) {
            return response()->json([
                'success' => false,
                'message' => 'Inserisci entrambi gli indirizzi'
            ]);
        }

        // Riusa il metodo privato già esistente nel controller
        $risultato = $this->calcolaDistanzaGoogleMaps($indirizzoRitiro, $indirizzoConsegna);

        if (!$risultato['success']) {
            return response()->json([
                'success' => false,
                'message' => $risultato['error'] ?? 'Errore nel calcolo della distanza'
            ]);
        }

        return response()->json([
            'success' => true,
            'km' => $risultato['distanza_km'],
            'km_testo' => $risultato['distanza_testo'] ?? ($risultato['distanza_km'] . ' km'),
            'durata_minuti' => $risultato['tempo_minuti'],
            'durata_testo' => $risultato['tempo_testo'] ?? $this->formattaTempo($risultato['tempo_minuti']),
        ]);
    }



}