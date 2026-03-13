<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AutistaController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!session()->has('utente')) {
                return redirect('/admin/login');
            }
            return $next($request);
        });
    }

    /**
     * Dashboard principale autista
     */
    public function dashboard()
    {
        $utente = session('utente');

        // Prendi il dispositivo/mezzo associato
        $dispositivo = $this->getDispositivoUtente($utente->id);

        // Km percorsi oggi
        $kmOggi = 0;
        if ($dispositivo) {
            $kmOggi = DB::table('km_giornalieri')
                ->where('id_dispositivo', $dispositivo->id)
                ->where('data', date('Y-m-d'))
                ->value('km_percorsi') ?? 0;
        }

        // Km ultimi 7 giorni
        $kmSettimana = [];
        if ($dispositivo) {
            $kmSettimana = DB::table('km_giornalieri')
                ->where('id_dispositivo', $dispositivo->id)
                ->where('data', '>=', date('Y-m-d', strtotime('-7 days')))
                ->orderBy('data', 'desc')
                ->get();
        }

        // Consegne di oggi (completate)
        $consegneOggi = DB::table('ordini_trasporto')
            ->where('id_autista', $utente->id)
            ->where('stato', 'completato')
            ->where(function($query) {
                $query->whereDate('data_ritiro', date('Y-m-d'))
                    ->orWhereDate('data_consegna', date('Y-m-d'));
            })
            ->count();

        return view('autista.dashboard', compact('utente', 'dispositivo', 'kmOggi', 'kmSettimana', 'consegneOggi'));
    }

    /**
     * Pagina tracking GPS
     */
    public function tracking()
    {
        $utente = session('utente');
        $dispositivo = $this->getDispositivoUtente($utente->id);

        $kmOggi = 0;
        if ($dispositivo) {
            $kmOggi = DB::table('km_giornalieri')
                ->where('id_dispositivo', $dispositivo->id)
                ->where('data', date('Y-m-d'))
                ->value('km_percorsi') ?? 0;
        }

        return view('autista.tracking', compact('utente', 'dispositivo', 'kmOggi'));
    }

    /**
     * Lista consegne
     */
    public function consegne()
    {
        $utente = session('utente');
        $dispositivo = $this->getDispositivoUtente($utente->id);

        // Prendi gli ordini di oggi (filtro su data_ritiro O data_consegna)
        $consegne = DB::table('ordini_trasporto')
            ->leftJoin('clienti', 'ordini_trasporto.id_cliente', '=', 'clienti.id')
            ->leftJoin('mezzi', 'ordini_trasporto.id_mezzo', '=', 'mezzi.id')
            ->where('ordini_trasporto.id_autista', $utente->id)
            ->where(function($query) {
                // Ordini con consegna prevista oggi
                $query->whereDate('ordini_trasporto.data_consegna', date('Y-m-d'))
                    // OPPURE ordini senza data consegna ma con ritiro oggi
                    ->orWhere(function($q) {
                        $q->whereNull('ordini_trasporto.data_consegna')
                            ->whereDate('ordini_trasporto.data_ritiro', date('Y-m-d'));
                    })
                    // OPPURE ordini arretrati ancora non completati
                    ->orWhere(function($q) {
                        $q->whereDate('ordini_trasporto.data_consegna', '<', date('Y-m-d'))
                            ->whereIn('ordini_trasporto.stato', ['assegnato', 'in_corso']);
                    });
            })
            ->orderByRaw("CASE 
                WHEN ordini_trasporto.stato = 'in_corso' THEN 1 
                WHEN ordini_trasporto.stato = 'assegnato' THEN 2 
                WHEN ordini_trasporto.stato = 'pianificato' THEN 3 
                WHEN ordini_trasporto.stato = 'completato' THEN 4 
                WHEN ordini_trasporto.stato = 'annullato' THEN 5 
                ELSE 6 END")
            ->orderBy('ordini_trasporto.data_ritiro', 'asc')
            ->select([
                'ordini_trasporto.id',
                'ordini_trasporto.numero_ordine',
                'ordini_trasporto.indirizzo_ritiro',
                'ordini_trasporto.indirizzo_consegna',
                'ordini_trasporto.data_ritiro',
                'ordini_trasporto.ora_ritiro',
                'ordini_trasporto.data_consegna',
                'ordini_trasporto.ora_consegna',
                'ordini_trasporto.descrizione_merce',
                'ordini_trasporto.peso_kg',
                'ordini_trasporto.note',
                'ordini_trasporto.stato',
                'ordini_trasporto.importo',
                'clienti.ragione_sociale as cliente',
                'mezzi.targa as targa_mezzo'
            ])
            ->get();

        return view('autista.consegne', compact('utente', 'dispositivo', 'consegne'));
    }

    /**
     * Inizia una consegna
     */
    public function iniziaConsegna($id)
    {
        $utente = session('utente');

        $updated = DB::table('ordini_trasporto')
            ->where('id', $id)
            ->where('id_autista', $utente->id)
            ->whereIn('stato', ['pianificato', 'assegnato'])
            ->update([
                'stato' => 'in_corso',
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => $updated > 0,
            'message' => $updated > 0 ? 'Consegna iniziata' : 'Errore o stato non valido'
        ]);
    }

    /**
     * Completa una consegna con firme
     */
    public function completaConsegna(Request $request, $id)
    {
        $utente = session('utente');

        $firmaVettore = $request->input('firma_vettore');         // base64
        $firmaDestinatario = $request->input('firma_destinatario'); // base64

        // 1. Aggiorna l'ordine
        DB::table('ordini')
            ->where('id', $id)
            ->where('id_azienda', $utente->id_azienda)
            ->update([
                'stato' => 'completato',
                'data_completamento' => now(),
                'updated_at' => now(),
            ]);

        // 2. Aggiorna ANCHE il DDT con entrambe le firme
        DB::table('documenti_trasporto')
            ->where('id_ordine', $id)
            ->where('id_azienda', $utente->id_azienda)
            ->update([
                'firma_vettore' => $firmaVettore,
                'firma_destinatario' => $firmaDestinatario,
                'data_firma_vettore' => now(),
                'data_firma_destinatario' => now(),
                'data_consegna' => now(),
                'consegnato_a' => $request->input('consegnato_a'),
                'updated_at' => now(),
            ]);

        // 3. ORA genera il PDF (con entrambe le firme salvate)
        // ... generaHTMLDDT() qui troverà firma_destinatario popolato

        return response()->json(['success' => true]);
    }

    /**
     * Annulla una consegna
     */
    public function annullaConsegna(Request $request, $id)
    {
        $utente = session('utente');
        $motivo = $request->input('motivo', '');

        if (empty($motivo)) {
            return response()->json([
                'success' => false,
                'message' => 'Il motivo è obbligatorio'
            ]);
        }

        // Recupera note esistenti
        $consegna = DB::table('ordini_trasporto')
            ->where('id', $id)
            ->where('id_autista', $utente->id)
            ->first();

        if (!$consegna) {
            return response()->json([
                'success' => false,
                'message' => 'Consegna non trovata'
            ]);
        }

        $noteEsistenti = $consegna->note ?? '';
        $nuoveNote = trim($noteEsistenti . "\n\n[ANNULLATA " . date('d/m/Y H:i') . "]\nMotivo: " . $motivo);

        $data = [
            'stato' => 'annullato',
            'note' => $nuoveNote,
            'updated_at' => now(),
        ];

        // Firma cliente (base64)
        if ($request->has('firma_cliente') && !empty($request->input('firma_cliente'))) {
            $data['firma_cliente'] = $request->input('firma_cliente');
        }

        // Firma autista (base64)
        if ($request->has('firma_autista') && !empty($request->input('firma_autista'))) {
            $data['firma_autista'] = $request->input('firma_autista');
        }

        $updated = DB::table('ordini_trasporto')
            ->where('id', $id)
            ->where('id_autista', $utente->id)
            ->whereIn('stato', ['pianificato', 'assegnato', 'in_corso'])
            ->update($data);

        return response()->json([
            'success' => $updated > 0,
            'message' => $updated > 0 ? 'Consegna annullata' : 'Errore o stato non valido'
        ]);
    }

    /**
     * Rinvia una consegna
     */
    public function rinviaConsegna(Request $request, $id)
    {
        $utente = session('utente');
        $nuovaData = $request->input('nuova_data');
        $nuovaOra = $request->input('nuova_ora');
        $motivo = $request->input('motivo', '');

        if (empty($nuovaData)) {
            return response()->json([
                'success' => false,
                'message' => 'La nuova data è obbligatoria'
            ]);
        }

        if (empty($motivo)) {
            return response()->json([
                'success' => false,
                'message' => 'Il motivo è obbligatorio'
            ]);
        }

        // Recupera consegna esistente
        $consegna = DB::table('ordini_trasporto')
            ->where('id', $id)
            ->where('id_autista', $utente->id)
            ->first();

        if (!$consegna) {
            return response()->json([
                'success' => false,
                'message' => 'Consegna non trovata'
            ]);
        }

        $noteEsistenti = $consegna->note ?? '';
        $nuoveNote = trim($noteEsistenti . "\n\n[RINVIATA " . date('d/m/Y H:i') . "]\nNuova data: " . $nuovaData . ($nuovaOra ? " ore " . $nuovaOra : "") . "\nMotivo: " . $motivo);

        $data = [
            'data_consegna' => $nuovaData,
            'data_ritiro' => $nuovaData, // Aggiorna anche la data ritiro
            'stato' => 'pianificato', // Torna a pianificato
            'note' => $nuoveNote,
            'updated_at' => now(),
        ];

        if ($nuovaOra) {
            $data['ora_consegna'] = $nuovaOra;
        }

        // Firma cliente (base64)
        if ($request->has('firma_cliente') && !empty($request->input('firma_cliente'))) {
            $data['firma_cliente'] = $request->input('firma_cliente');
        }

        // Firma autista (base64)
        if ($request->has('firma_autista') && !empty($request->input('firma_autista'))) {
            $data['firma_autista'] = $request->input('firma_autista');
        }

        $updated = DB::table('ordini_trasporto')
            ->where('id', $id)
            ->where('id_autista', $utente->id)
            ->whereIn('stato', ['pianificato', 'assegnato', 'in_corso'])
            ->update($data);

        return response()->json([
            'success' => $updated > 0,
            'message' => $updated > 0 ? 'Consegna rinviata' : 'Errore o stato non valido'
        ]);
    }

    /**
     * Storico km
     */
    public function storico(Request $request)
    {
        $utente = session('utente');
        $dispositivo = $this->getDispositivoUtente($utente->id);

        $giorni = $request->input('giorni', 7);

        $kmGiornalieri = collect();
        $totaleKm = 0;
        $mediaGiornaliera = 0;
        $maxGiornaliero = 0;
        $giorniAttivi = 0;

        if ($dispositivo) {
            $kmGiornalieri = DB::table('km_giornalieri')
                ->where('id_dispositivo', $dispositivo->id)
                ->where('data', '>=', date('Y-m-d', strtotime("-{$giorni} days")))
                ->orderBy('data', 'desc')
                ->get();

            $totaleKm = $kmGiornalieri->sum('km_percorsi');
            $giorniAttivi = $kmGiornalieri->where('km_percorsi', '>', 0)->count();
            $mediaGiornaliera = $giorniAttivi > 0 ? $totaleKm / $giorniAttivi : 0;
            $maxGiornaliero = $kmGiornalieri->max('km_percorsi') ?? 0;
        }

        return view('autista.storico', compact(
            'utente',
            'dispositivo',
            'kmGiornalieri',
            'totaleKm',
            'mediaGiornaliera',
            'maxGiornaliero',
            'giorniAttivi'
        ));
    }

    /**
     * API: Ottieni storico km per periodo
     */
    public function apiStorico(Request $request)
    {
        $utente = session('utente');
        $dispositivo = $this->getDispositivoUtente($utente->id);

        if (!$dispositivo) {
            return response()->json([
                'success' => false,
                'message' => 'Nessun dispositivo associato'
            ]);
        }

        // Accetta sia 'periodo' che 'period' per retrocompatibilità
        $period = $request->input('periodo', $request->input('period', '7'));
        $giorni = intval($period);

        if ($giorni < 1 || $giorni > 365) {
            $giorni = 7;
        }

        $kmGiornalieri = DB::table('km_giornalieri')
            ->where('id_dispositivo', $dispositivo->id)
            ->where('data', '>=', date('Y-m-d', strtotime("-{$giorni} days")))
            ->orderBy('data', 'desc')
            ->get();

        $totaleKm = $kmGiornalieri->sum('km_percorsi');
        $giorniAttivi = $kmGiornalieri->where('km_percorsi', '>', 0)->count();
        $mediaGiornaliera = $giorniAttivi > 0 ? $totaleKm / $giorniAttivi : 0;
        $maxGiornaliero = $kmGiornalieri->max('km_percorsi') ?? 0;

        return response()->json([
            'success' => true,
            'km' => $kmGiornalieri,
            'stats' => [
                'totale' => $totaleKm,
                'media' => $mediaGiornaliera,
                'max' => $maxGiornaliero,
                'giorni' => $giorniAttivi
            ]
        ]);
    }

    /**
     * API: Ottieni statistiche real-time
     */
    public function apiStats()
    {
        $utente = session('utente');
        $dispositivo = $this->getDispositivoUtente($utente->id);

        $kmOggi = 0;
        if ($dispositivo) {
            $kmOggi = DB::table('km_giornalieri')
                ->where('id_dispositivo', $dispositivo->id)
                ->where('data', date('Y-m-d'))
                ->value('km_percorsi') ?? 0;
        }

        $consegneOggi = DB::table('ordini_trasporto')
            ->where('id_autista', $utente->id)
            ->whereDate('data_consegna', date('Y-m-d'))
            ->where('stato', 'completato')
            ->count();

        return response()->json([
            'success' => true,
            'kmOggi' => $kmOggi,
            'consegneOggi' => $consegneOggi
        ]);
    }

    /**
     * Profilo autista
     */
    public function profilo()
    {
        $utente = session('utente');
        $dispositivo = $this->getDispositivoUtente($utente->id);

        return view('autista.profilo', compact('utente', 'dispositivo'));
    }

    /**
     * Navigatore con percorso ottimizzato
     */
    public function navigatore()
    {
        $utente = session('utente');
        $dispositivo = $this->getDispositivoUtente($utente->id);

        // Prendi TUTTE le consegne del giorno (non completate prima, poi completate)
        $consegne = DB::table('ordini_trasporto')
            ->leftJoin('clienti', 'ordini_trasporto.id_cliente', '=', 'clienti.id')
            ->where('ordini_trasporto.id_autista', $utente->id)
            ->where(function($query) {
                $query->whereDate('ordini_trasporto.data_ritiro', date('Y-m-d'))
                    ->orWhereDate('ordini_trasporto.data_consegna', date('Y-m-d'));
            })
            ->whereIn('ordini_trasporto.stato', ['pianificato', 'assegnato', 'in_corso', 'completato'])
            ->orderByRaw("CASE 
                WHEN ordini_trasporto.stato = 'in_corso' THEN 1 
                WHEN ordini_trasporto.stato = 'assegnato' THEN 2 
                WHEN ordini_trasporto.stato = 'pianificato' THEN 3 
                WHEN ordini_trasporto.stato = 'completato' THEN 4 
                ELSE 5 END")
            ->select([
                'ordini_trasporto.id',
                'ordini_trasporto.numero_ordine',
                'ordini_trasporto.indirizzo_ritiro',
                'ordini_trasporto.indirizzo_consegna',
                'ordini_trasporto.data_ritiro',
                'ordini_trasporto.ora_ritiro',
                'ordini_trasporto.data_consegna',
                'ordini_trasporto.ora_consegna',
                'ordini_trasporto.descrizione_merce',
                'ordini_trasporto.peso_kg',
                'ordini_trasporto.stato',
                'clienti.ragione_sociale as cliente'
            ])
            ->get();

        // Posizione di partenza (sede azienda o null)
        $posizionePartenza = null;

        return view('autista.navigatore', compact('utente', 'dispositivo', 'consegne', 'posizionePartenza'));
    }

    /**
     * Apre navigatore per una singola consegna
     */
    public function navigatoreSingolo($id)
    {
        $utente = session('utente');

        $consegna = DB::table('ordini_trasporto')
            ->where('id', $id)
            ->where('id_autista', $utente->id)
            ->first();

        if (!$consegna) {
            return redirect('/autista/dashboard');
        }

        $dispositivo = $this->getDispositivoUtente($utente->id);

        return view('autista.navigatore', compact('utente', 'consegna', 'dispositivo'));
    }

    /**
     * Helper: Ottieni dispositivo associato all'utente
     */
    private function getDispositivoUtente($utenteId)
    {
        return DB::table('dispositivi_tracking')
            ->leftJoin('mezzi', 'dispositivi_tracking.id_mezzo', '=', 'mezzi.id')
            ->where('dispositivi_tracking.id_utente', $utenteId)
            ->where('dispositivi_tracking.is_active', 1)
            ->select([
                'dispositivi_tracking.*',
                'mezzi.nome as nome_mezzo',
                'mezzi.targa',
                'mezzi.km_attuali',
                'mezzi.km_iniziali_contachilometri',
                'mezzi.km_accumulati_gps',
            ])
            ->first();
    }

    /**
     * Metodi da aggiungere al controller Azienda per gestire gli ordini di trasporto
     * Aggiungi questi metodi nel tuo AziendaController.php
     */

    /**
     * Lista ordini di trasporto
     */
    public function ordiniTrasporto(Request $request)
    {
        $azienda = session('azienda');
        $filtroStato = $request->input('stato', 'tutti');

        // Gestione form POST
        if ($request->isMethod('post')) {

            // Crea nuovo ordine
            if ($request->has('crea_ordine')) {
                $numeroOrdine = 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

                DB::table('ordini_trasporto')->insert([
                    'numero_ordine' => $numeroOrdine,
                    'id_cliente' => $request->input('id_cliente'),
                    'id_mezzo' => $request->input('id_mezzo') ?: null,
                    'id_autista' => $request->input('id_autista') ?: null,
                    'indirizzo_ritiro' => $request->input('indirizzo_ritiro'),
                    'indirizzo_consegna' => $request->input('indirizzo_consegna'),
                    'data_ritiro' => $request->input('data_ritiro'),
                    'ora_ritiro' => $request->input('ora_ritiro') ?: null,
                    'data_consegna' => $request->input('data_consegna') ?: null,
                    'ora_consegna' => $request->input('ora_consegna') ?: null,
                    'descrizione_merce' => $request->input('descrizione_merce'),
                    'peso_kg' => $request->input('peso_kg') ?: null,
                    'note' => $request->input('note') ?: null,
                    'importo' => $request->input('importo') ?: 0,
                    'stato' => $request->input('id_autista') ? 'assegnato' : 'pianificato',
                    'id_azienda' => $azienda->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return redirect('/azienda/ordini-trasporto')->with('success', 'Ordine creato con successo');
            }

            // Modifica ordine
            if ($request->has('modifica_ordine')) {
                $idOrdine = $request->input('id_ordine');

                DB::table('ordini_trasporto')
                    ->where('id', $idOrdine)
                    ->where('id_azienda', $azienda->id)
                    ->update([
                        'id_cliente' => $request->input('id_cliente'),
                        'id_mezzo' => $request->input('id_mezzo') ?: null,
                        'id_autista' => $request->input('id_autista') ?: null,
                        'indirizzo_ritiro' => $request->input('indirizzo_ritiro'),
                        'indirizzo_consegna' => $request->input('indirizzo_consegna'),
                        'data_ritiro' => $request->input('data_ritiro'),
                        'ora_ritiro' => $request->input('ora_ritiro') ?: null,
                        'data_consegna' => $request->input('data_consegna') ?: null,
                        'ora_consegna' => $request->input('ora_consegna') ?: null,
                        'descrizione_merce' => $request->input('descrizione_merce'),
                        'peso_kg' => $request->input('peso_kg') ?: null,
                        'note' => $request->input('note') ?: null,
                        'importo' => $request->input('importo') ?: 0,
                        'stato' => $request->input('stato'),
                        'updated_at' => now(),
                    ]);

                return redirect('/azienda/ordini-trasporto')->with('success', 'Ordine modificato con successo');
            }

            // Elimina ordine
            if ($request->has('elimina_ordine')) {
                $idOrdine = $request->input('id_ordine');

                DB::table('ordini_trasporto')
                    ->where('id', $idOrdine)
                    ->where('id_azienda', $azienda->id)
                    ->delete();

                return redirect('/azienda/ordini-trasporto')->with('success', 'Ordine eliminato con successo');
            }
        }

        // Query ordini
        $query = DB::table('ordini_trasporto')
            ->leftJoin('clienti', 'ordini_trasporto.id_cliente', '=', 'clienti.id')
            ->leftJoin('mezzi', 'ordini_trasporto.id_mezzo', '=', 'mezzi.id')
            ->leftJoin('utenti as autisti', 'ordini_trasporto.id_autista', '=', 'autisti.id')
            ->where('ordini_trasporto.id_azienda', $azienda->id)
            ->select([
                'ordini_trasporto.*',
                'clienti.ragione_sociale as cliente_nome',
                'mezzi.targa',
                'mezzi.tipo as mezzo_marca',
                'mezzi.modello as mezzo_modello',
                'autisti.nome as autista_nome',
                'autisti.cognome as autista_cognome'
            ]);

        if ($filtroStato !== 'tutti') {
            $query->where('ordini_trasporto.stato', $filtroStato);
        }

        $ordini = $query->orderBy('ordini_trasporto.data_ritiro', 'desc')
            ->orderBy('ordini_trasporto.created_at', 'desc')
            ->get();

        // Dati per i select
        $clienti = DB::table('clienti')
            ->where('id_azienda', $azienda->id)
            ->orderBy('ragione_sociale')
            ->get();

        $mezzi = DB::table('mezzi')
            ->where('id_azienda', $azienda->id)
            ->where('stato', '!=', 'Manutenzione')
            ->orderBy('marca')
            ->orderBy('targa')
            ->get();

        $autisti = DB::table('utenti')
            ->where('id_azienda', $azienda->id)
            ->where('ruolo', 'autista')
            ->where('attivo', 1)
            ->orderBy('cognome')
            ->get();

        return view('azienda.ordini_trasporto', compact(
            'ordini',
            'clienti',
            'mezzi',
            'autisti',
            'filtroStato'
        ));
    }

    /**
     * Cambia stato ordine (AJAX)
     */
    public function cambiaStatoOrdine(Request $request)
    {
        $azienda = session('azienda');
        $idOrdine = $request->input('id_ordine');
        $nuovoStato = $request->input('stato');

        $updated = DB::table('ordini_trasporto')
            ->where('id', $idOrdine)
            ->where('id_azienda', $azienda->id)
            ->update([
                'stato' => $nuovoStato,
                'updated_at' => now()
            ]);

        return response()->json([
            'success' => $updated > 0
        ]);
    }

    /**
     * Dettaglio singolo ordine
     */
    public function dettaglioOrdine($id)
    {
        $azienda = session('azienda');

        $ordine = DB::table('ordini_trasporto')
            ->leftJoin('clienti', 'ordini_trasporto.id_cliente', '=', 'clienti.id')
            ->leftJoin('mezzi', 'ordini_trasporto.id_mezzo', '=', 'mezzi.id')
            ->leftJoin('utenti as autisti', 'ordini_trasporto.id_autista', '=', 'autisti.id')
            ->where('ordini_trasporto.id', $id)
            ->where('ordini_trasporto.id_azienda', $azienda->id)
            ->select([
                'ordini_trasporto.*',
                'clienti.ragione_sociale as cliente_nome',
                'clienti.indirizzo as cliente_indirizzo',
                'clienti.telefono as cliente_telefono',
                'clienti.email as cliente_email',
                'mezzi.targa',
                'mezzi.tipo as mezzo_marca',
                'mezzi.modello as mezzo_modello',
                'autisti.nome as autista_nome',
                'autisti.cognome as autista_cognome',
                'autisti.telefono as autista_telefono'
            ])
            ->first();

        if (!$ordine) {
            return redirect('/azienda/ordini-trasporto')->with('error', 'Ordine non trovato');
        }

        return view('azienda.dettaglio_ordine', compact('ordine'));
    }



    public function is_loggato()
    {
        if (!session()->has('utente')) {
            return Redirect::to('admin/login')->send();
        }
    }




    /**
     * =====================================================
     * FUNZIONI DA AGGIUNGERE AL TUO AutistaController.php
     * =====================================================
     */

    /**
     * Mostra pagina per completare ordine con DDT e firme
     * GET /autista/ordine/{id}/completa
     */
    public function completaOrdineView($id)
    {
        $this->is_loggato();
        $utente = session('utente');

        // Recupera ordine assegnato a questo autista
        $ordine = DB::table('ordini_trasporto as ot')
            ->leftJoin('mezzi as m', 'ot.id_mezzo', '=', 'm.id')
            ->leftJoin('clienti as c', 'ot.id_cliente', '=', 'c.id')
            ->where('ot.id', $id)
            ->where('ot.id_autista', $utente->id)
            ->select(
                'ot.*',
                'm.targa',
                'm.marca as mezzo_marca',
                'm.modello as mezzo_modello',
                'c.ragione_sociale as cliente_nome',
                'c.partita_iva as cliente_piva'
            )
            ->first();

        if (!$ordine) {
            return redirect('/autista/consegne')->with('error', 'Ordine non trovato');
        }

        // Recupera DDT dell'ordine
        $ddt = DB::table('documenti_trasporto')
            ->where('id_ordine', $id)
            ->where('tipo_documento', 'ddt')
            ->first();

        if (!$ddt) {
            return redirect('/autista/consegne')->with('error', 'DDT non trovato per questo ordine');
        }

        // Recupera dati azienda
        $azienda = DB::table('aziende')
            ->where('id', $ordine->id_azienda)
            ->first();

        return view('autista.completa_ordine', compact('ordine', 'ddt', 'azienda'));
    }

    /**
     * Salva firma sul DDT (vettore o destinatario)
     * POST /autista/ddt/salva-firma
     */
    public function salvaFirmaDDTAutista(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        $idDdt = $request->input('id_ddt');
        $tipoFirma = $request->input('tipo_firma'); // 'vettore' o 'destinatario'
        $firmaBase64 = $request->input('firma');

        // Validazione
        if (!in_array($tipoFirma, ['vettore', 'destinatario'])) {
            return response()->json(['success' => false, 'message' => 'Tipo firma non valido']);
        }

        // Verifica che il DDT appartenga a un ordine di questo autista
        $ddt = DB::table('documenti_trasporto as dt')
            ->join('ordini_trasporto as ot', 'dt.id_ordine', '=', 'ot.id')
            ->where('dt.id', $idDdt)
            ->where('ot.id_autista', $utente->id)
            ->select('dt.*')
            ->first();

        if (!$ddt) {
            return response()->json(['success' => false, 'message' => 'DDT non trovato']);
        }

        // Prepara campi
        $campoFirma = 'firma_' . $tipoFirma;
        $campoData = 'data_firma_' . $tipoFirma;

        // Salva firma
        DB::table('documenti_trasporto')
            ->where('id', $idDdt)
            ->update([
                $campoFirma => $firmaBase64,
                $campoData => now(),
                'updated_at' => now()
            ]);

        return response()->json(['success' => true, 'message' => 'Firma salvata']);
    }

    /**
     * Rimuovi firma dal DDT
     * POST /autista/ddt/rimuovi-firma
     */
    public function rimuoviFirmaDDTAutista(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        $idDdt = $request->input('id_ddt');
        $tipoFirma = $request->input('tipo_firma');

        if (!in_array($tipoFirma, ['vettore', 'destinatario'])) {
            return response()->json(['success' => false, 'message' => 'Tipo firma non valido']);
        }

        // Verifica DDT
        $ddt = DB::table('documenti_trasporto as dt')
            ->join('ordini_trasporto as ot', 'dt.id_ordine', '=', 'ot.id')
            ->where('dt.id', $idDdt)
            ->where('ot.id_autista', $utente->id)
            ->first();

        if (!$ddt) {
            return response()->json(['success' => false, 'message' => 'DDT non trovato']);
        }

        $campoFirma = 'firma_' . $tipoFirma;
        $campoData = 'data_firma_' . $tipoFirma;

        DB::table('documenti_trasporto')
            ->where('id', $idDdt)
            ->update([
                $campoFirma => null,
                $campoData => null,
                'updated_at' => now()
            ]);

        return response()->json(['success' => true, 'message' => 'Firma rimossa']);
    }

    /**
     * Completa ordine (dopo aver raccolto entrambe le firme)
     * POST /autista/ordine/completa
     */
    public function completaOrdine(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        $idOrdine = $request->input('id_ordine');

        // Verifica ordine
        $ordine = DB::table('ordini_trasporto')
            ->where('id', $idOrdine)
            ->where('id_autista', $utente->id)
            ->first();

        if (!$ordine) {
            return response()->json(['success' => false, 'message' => 'Ordine non trovato']);
        }

        // Verifica che ci siano entrambe le firme
        $ddt = DB::table('documenti_trasporto')
            ->where('id_ordine', $idOrdine)
            ->where('tipo_documento', 'ddt')
            ->first();

        if (!$ddt || !$ddt->firma_vettore || !$ddt->firma_destinatario) {
            return response()->json(['success' => false, 'message' => 'Raccogli entrambe le firme prima di completare']);
        }

        // Genera token pubblico per condivisione (se non esiste già)
        $tokenPubblico = $ddt->token_pubblico;
        if (!$tokenPubblico) {
            $tokenPubblico = bin2hex(random_bytes(32)); // 64 caratteri hex
        }

        // Aggiorna stato ordine a completato
        DB::table('ordini_trasporto')
            ->where('id', $idOrdine)
            ->update([
                'stato' => 'completato',
                'data_consegna' => now()->toDateString(),
                'ora_consegna' => now()->format('H:i'),
                'updated_at' => now()
            ]);

        // Aggiorna DDT con data consegna e token pubblico
        DB::table('documenti_trasporto')
            ->where('id', $ddt->id)
            ->update([
                'data_consegna' => now(),
                'token_pubblico' => $tokenPubblico,
                'updated_at' => now()
            ]);

        // Redirect alla pagina riepilogo
        return response()->json([
            'success' => true,
            'message' => 'Ordine completato',
            'redirect' => '/autista/ordine/' . $idOrdine . '/completato'
        ]);
    }

    /**
     * Mostra pagina riepilogo ordine completato con DDT firmato
     * GET /autista/ordine/{id}/completato
     */
    public function ordineCompletato($id)
    {
        $this->is_loggato();
        $utente = session('utente');

        // Recupera ordine
        $ordine = DB::table('ordini_trasporto as ot')
            ->leftJoin('clienti as c', 'ot.id_cliente', '=', 'c.id')
            ->where('ot.id', $id)
            ->where('ot.id_autista', $utente->id)
            ->select(
                'ot.*',
                'c.ragione_sociale as cliente_nome',
                'c.email as email_cliente',
                'c.telefono as telefono_cliente'
            )
            ->first();

        if (!$ordine) {
            return redirect('/autista/consegne')->with('error', 'Ordine non trovato');
        }

        // Recupera DDT
        $ddt = DB::table('documenti_trasporto')
            ->where('id_ordine', $id)
            ->where('tipo_documento', 'ddt')
            ->first();

        if (!$ddt) {
            return redirect('/autista/consegne')->with('error', 'DDT non trovato');
        }

        // Se non esiste token pubblico, generalo ora
        if (!$ddt->token_pubblico) {
            $tokenPubblico = bin2hex(random_bytes(32));
            DB::table('documenti_trasporto')
                ->where('id', $ddt->id)
                ->update(['token_pubblico' => $tokenPubblico]);
            $ddt->token_pubblico = $tokenPubblico;
        }

        // Recupera azienda
        $azienda = DB::table('aziende')
            ->where('id', $ordine->id_azienda)
            ->first();

        // Prepara dati per condivisione
        $telefono_cliente = $ordine->telefono_cliente ? preg_replace('/[^0-9]/', '', $ordine->telefono_cliente) : '';
        $email_cliente = $ordine->email_cliente ?? '';

        // Link pubblico al PDF (senza login richiesto!)
        $link_pdf_pubblico = url('/ddt/download/' . $ddt->token_pubblico);

        // Messaggio WhatsApp con link pubblico
        $messaggio_whatsapp = "Gentile Cliente,\n\n";
        $messaggio_whatsapp .= "La informiamo che la consegna è stata completata.\n\n";
        $messaggio_whatsapp .= "📄 DDT N. " . $ddt->numero_documento . "\n";
        $messaggio_whatsapp .= "📅 Data: " . date('d/m/Y', strtotime($ddt->data_documento)) . "\n";
        $messaggio_whatsapp .= "📦 Merce: " . $ddt->descrizione_merce . "\n";
        if ($ddt->numero_colli) {
            $messaggio_whatsapp .= "📦 Colli: " . $ddt->numero_colli . "\n";
        }

        $messaggio_whatsapp .= "\n\nGrazie per aver scelto " . ($azienda->ragione_sociale ?? $azienda->nome ?? 'i nostri servizi') . "!";

        // Messaggio Email
        $messaggio_email = "Gentile Cliente,\n\n";
        $messaggio_email .= "La informiamo che la consegna relativa al DDT N. " . $ddt->numero_documento . " è stata completata in data " . date('d/m/Y H:i') . ".\n\n";
        $messaggio_email .= "Dettagli consegna:\n";
        $messaggio_email .= "- Descrizione merce: " . $ddt->descrizione_merce . "\n";
        if ($ddt->numero_colli) {
            $messaggio_email .= "- Numero colli: " . $ddt->numero_colli . "\n";
        }
        if ($ddt->peso_lordo) {
            $messaggio_email .= "- Peso: " . $ddt->peso_lordo . " kg\n";
        }

        $messaggio_email .= "\n\nCordiali saluti,\n" . ($azienda->ragione_sociale ?? $azienda->nome ?? '');

        return view('autista.ordine_completato', compact(
            'ordine',
            'ddt',
            'azienda',
            'telefono_cliente',
            'email_cliente',
            'messaggio_whatsapp',
            'messaggio_email',
            'link_pdf_pubblico'
        ));
    }

    /**
     * Genera PDF del DDT per download
     * GET /autista/ddt/{id}/pdf
     */
    public function ddtPdf($id)
    {
        $this->is_loggato();
        $utente = session('utente');

        // Verifica che il DDT appartenga a un ordine di questo autista
        $ddt = DB::table('documenti_trasporto as dt')
            ->join('ordini_trasporto as ot', 'dt.id_ordine', '=', 'ot.id')
            ->leftJoin('mezzi as m', 'ot.id_mezzo', '=', 'm.id')
            ->leftJoin('utenti as u', 'ot.id_autista', '=', 'u.id')
            ->where('dt.id', $id)
            ->where('ot.id_autista', $utente->id)
            ->select('dt.*', 'ot.*', 'ot.id as ordine_id', 'dt.id as ddt_id', 'm.targa',
                'm.marca as mezzo_marca', 'm.modello as mezzo_modello',
                'u.nome as autista_nome', 'u.cognome as autista_cognome')
            ->first();

        if (!$ddt) {
            return redirect('/autista/consegne')->with('error', 'DDT non trovato');
        }

        // Recupera azienda
        $azienda = DB::table('aziende')->where('id', $ddt->id_azienda)->first();

        return $this->generaPdfDDT($ddt, $azienda);
    }

    /**
     * Download PDF pubblico (SENZA LOGIN!) tramite token
     * GET /ddt/download/{token}
     *
     * Questo endpoint è PUBBLICO e permette ai clienti di scaricare il DDT
     * usando un link univoco ricevuto via WhatsApp o email
     */
    public function ddtPdfPubblico($token)
    {
        // NESSUNA AUTENTICAZIONE RICHIESTA!

        // Trova DDT tramite token
        $ddt = DB::table('documenti_trasporto as dt')
            ->join('ordini_trasporto as ot', 'dt.id_ordine', '=', 'ot.id')
            ->leftJoin('mezzi as m', 'ot.id_mezzo', '=', 'm.id')
            ->leftJoin('utenti as u', 'ot.id_autista', '=', 'u.id')
            ->where('dt.token_pubblico', $token)
            ->select(
                'dt.*',
                'ot.*',
                'ot.id as ordine_id',
                'dt.id as ddt_id',
                'dt.firma_vettore as firma_vettore',           // ← forza dal DDT
                'dt.firma_destinatario as firma_destinatario',   // ← forza dal DDT
                'dt.firma_mittente as firma_mittente',           // ← per sicurezza
                'm.targa',
                'm.marca as mezzo_marca',
                'm.modello as mezzo_modello',
                'u.nome as autista_nome',
                'u.cognome as autista_cognome'
            )
            ->first();

        if (!$ddt) {
            abort(404, 'Documento non trovato o link non valido');
        }

        // Recupera azienda
        $azienda = DB::table('aziende')->where('id', $ddt->id_azienda)->first();


        return $this->generaPdfDDT($ddt, $azienda);
    }

    /**
     * Funzione helper per generare il PDF del DDT
     */
    private function generaPdfDDT($ddt, $azienda)
    {
        // Genera HTML per il PDF
        $html = $this->generaHTMLDDT($ddt, $ddt, $azienda);

        // Genera PDF con mPDF
        $mpdf = new \Mpdf\Mpdf([
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'format' => 'A4'
        ]);

        $mpdf->WriteHTML($html);

        $filename = 'DDT_' . str_replace('/', '-', $ddt->numero_documento) . '.pdf';

        return response($mpdf->Output($filename, 'S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }

    /**
     * Invia DDT via email con allegato PDF
     * POST /autista/ddt/invia-email
     */
    public function inviaDdtEmail(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        $idDdt = $request->input('id_ddt');
        $email = $request->input('email');
        $messaggio = $request->input('messaggio');

        // Validazione
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['success' => false, 'message' => 'Email non valida']);
        }

        // Verifica DDT
        $ddt = DB::table('documenti_trasporto as dt')
            ->join('ordini_trasporto as ot', 'dt.id_ordine', '=', 'ot.id')
            ->leftJoin('mezzi as m', 'ot.id_mezzo', '=', 'm.id')
            ->where('dt.id', $idDdt)
            ->where('ot.id_autista', $utente->id)
            ->select('dt.*', 'ot.*', 'ot.id as ordine_id', 'dt.id as ddt_id', 'm.targa')
            ->first();

        if (!$ddt) {
            return response()->json(['success' => false, 'message' => 'DDT non trovato']);
        }

        // Recupera azienda
        $azienda = DB::table('aziende')->where('id', $ddt->id_azienda)->first();

        // Genera PDF
        $html = $this->generaHTMLDDT($ddt, $ddt, $azienda);

        $mpdf = new \Mpdf\Mpdf([
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'format' => 'A4'
        ]);

        $mpdf->WriteHTML($html);
        $pdfContent = $mpdf->Output('', 'S');

        $filename = 'DDT_' . str_replace('/', '-', $ddt->numero_documento) . '.pdf';

        try {
            \Mail::raw($messaggio, function ($mail) use ($email, $pdfContent, $filename, $ddt, $azienda) {
                $mail->to($email)
                    ->subject('DDT ' . $ddt->numero_documento . ' - ' . ($azienda->ragione_sociale ?? $azienda->nome ?? 'Documento di Trasporto'))
                    ->attachData($pdfContent, $filename, [
                        'mime' => 'application/pdf'
                    ]);

                if (isset($azienda->email) && $azienda->email) {
                    $mail->from($azienda->email, $azienda->ragione_sociale ?? $azienda->nome ?? '');
                }
            });

            return response()->json(['success' => true, 'message' => 'Email inviata']);

        } catch (\Exception $e) {
            \Log::error('Errore invio email DDT: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Errore invio email: ' . $e->getMessage()]);
        }
    }

    /**
     * Genera HTML per il DDT (usato per PDF)
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
                            <span class="azienda-nome">' . htmlspecialchars($ddt->mittente_nome ?? ($azienda->ragione_sociale ?? '')) . '</span><br>
                            ' . htmlspecialchars($ddt->mittente_indirizzo ?? ($azienda->indirizzo ?? '')) . '
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
                            <span class="azienda-nome">' . htmlspecialchars($ddt->destinatario_nome ?? '') . '</span><br>
                            ' . htmlspecialchars($ddt->destinatario_indirizzo ?? '') . '
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
                <td style="height: 50px; vertical-align: top;">' . nl2br(htmlspecialchars($ddt->descrizione_merce ?? '')) . '</td>
                <td class="center">' . ($ddt->numero_colli ?? '-') . '</td>
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
        
        <!-- SPACER per firme -->
        <div style="height: 100px;"></div>
        
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
     * Mostra form segnalazione guasto
     */
    public function segnalaGuastoForm()
    {
        $this->is_loggato();
        $utente = session('utente');

        // Recupera mezzo associato all'autista
        $mezzo = DB::table('dispositivi_tracking as dt')
            ->join('mezzi as m', 'dt.id_mezzo', '=', 'm.id')
            ->where('dt.id_utente', $utente->id)
            ->where('dt.is_active', 1)
            ->select('m.id', 'm.nome', 'm.targa')
            ->first();

        return view('autista.segnala_guasto', compact('utente', 'mezzo'));
    }

    /**
     * Salva segnalazione guasto e invia email
     */
    public function segnalaGuastoSalva(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        try {
            // Recupera mezzo
            $mezzo = DB::table('dispositivi_tracking as dt')
                ->join('mezzi as m', 'dt.id_mezzo', '=', 'm.id')
                ->where('dt.id_utente', $utente->id)
                ->where('dt.is_active', 1)
                ->select('m.id', 'm.nome', 'm.targa')
                ->first();

            // Upload foto (se presente)
            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $foto = $request->file('foto');
                $nomeFile = 'guasto_' . time() . '_' . $utente->id . '.' . $foto->getClientOriginalExtension();

                // Crea cartella se non esiste
                $uploadPath = public_path('uploads/guasti');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $foto->move($uploadPath, $nomeFile);
                $fotoPath = 'uploads/guasti/' . $nomeFile;
            }

            // Salva nel database
            $idGuasto = DB::table('segnalazioni_guasti')->insertGetId([
                'id_azienda' => $utente->id_azienda,
                'id_autista' => $utente->id,
                'id_mezzo' => $mezzo->id ?? $request->get('id_mezzo'),
                'tipo_guasto' => $request->get('tipo_guasto'),
                'descrizione' => $request->get('descrizione'),
                'urgenza' => $request->get('urgenza', 'media'),
                'latitudine' => $request->get('latitudine'),
                'longitudine' => $request->get('longitudine'),
                'indirizzo' => $request->get('indirizzo'),
                'foto' => $fotoPath,
                'stato' => 'segnalato',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Recupera azienda per email
            $azienda = DB::table('aziende')->where('id', $utente->id_azienda)->first();

            // Email destinatario (priorità: email_segnalazioni_guasti > pec > email_smtp)
            $emailDest = $azienda->email_segnalazioni_guasti
                ?? $azienda->pec
                ?? $azienda->email_smtp
                ?? null;

            // Invia email
            $emailOk = false;
            if ($emailDest && $azienda->email_smtp && $azienda->password_smtp) {
                $emailOk = $this->inviaEmailGuasto($azienda, $emailDest, $utente, $mezzo, $request->all(), $fotoPath, $idGuasto);

                // Aggiorna stato email
                DB::table('segnalazioni_guasti')->where('id', $idGuasto)->update([
                    'email_inviata' => $emailOk ? 1 : 0,
                    'email_destinatario' => $emailOk ? $emailDest : null
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Segnalazione inviata!' . ($emailOk ? ' Notifica email inviata.' : '')
            ]);

        } catch (\Exception $e) {
            \Log::error('Errore segnalazione guasto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Errore: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Invia email di notifica guasto
     */
    private function inviaEmailGuasto($azienda, $emailDest, $utente, $mezzo, $dati, $fotoPath, $idGuasto)
    {
        try {
            $mail = new PHPMailer(true);

            // Config SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $azienda->email_smtp;
            $mail->Password = $azienda->password_smtp;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';

            // Mittente/Destinatario
            $mail->setFrom($azienda->email_smtp, $azienda->ragione_sociale ?? 'Logistia');
            $mail->addAddress($emailDest);

            // Colori urgenza
            $colori = [
                'bassa' => '#27ae60',
                'media' => '#f39c12',
                'alta' => '#e67e22',
                'critica' => '#e74c3c'
            ];
            $colore = $colori[$dati['urgenza']] ?? '#f39c12';
            $urgLabel = strtoupper($dati['urgenza'] ?? 'MEDIA');

            // Tipi guasto
            $tipiGuasto = [
                'meccanico' => '🔧 Meccanico',
                'elettrico' => '⚡ Elettrico',
                'pneumatico' => '🔘 Pneumatico',
                'carrozzeria' => '🚗 Carrozzeria',
                'altro' => '❓ Altro'
            ];
            $tipoLabel = $tipiGuasto[$dati['tipo_guasto']] ?? $dati['tipo_guasto'];

            // Link mappa
            $linkMappa = '';
            if (!empty($dati['latitudine']) && !empty($dati['longitudine'])) {
                $linkMappa = "https://www.google.com/maps?q={$dati['latitudine']},{$dati['longitudine']}";
            }

            // Oggetto
            $targa = $mezzo->targa ?? 'N/D';
            $mail->Subject = "🚨 [{$urgLabel}] Guasto Segnalato - {$targa}";

            // HTML
            $html = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%); color: white; padding: 25px; text-align: center; }
        .urgenza { display: inline-block; background: {$colore}; color: white; padding: 8px 20px; border-radius: 25px; font-weight: bold; font-size: 14px; }
        .content { padding: 25px; background: #f8f9fa; }
        .box { background: white; border-radius: 10px; padding: 20px; margin-bottom: 15px; border-left: 4px solid #3498db; }
        .box.danger { border-left-color: #e74c3c; }
        .label { font-size: 11px; text-transform: uppercase; color: #888; font-weight: bold; margin-bottom: 5px; }
        .value { font-size: 16px; color: #333; }
        .btn-mappa { display: inline-block; background: #e74c3c; color: white !important; padding: 12px 30px; border-radius: 8px; text-decoration: none; margin-top: 10px; }
        .footer { text-align: center; padding: 20px; color: #888; font-size: 12px; background: #ecf0f1; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1 style='margin:0; font-size: 24px;'>🚨 Segnalazione Guasto</h1>
            <p style='margin: 10px 0 0 0; opacity: 0.9;'>ID #{$idGuasto} - " . date('d/m/Y H:i') . "</p>
        </div>
        
        <div class='content'>
            <div style='text-align: center; margin-bottom: 20px;'>
                <span class='urgenza'>URGENZA: {$urgLabel}</span>
            </div>
            
            <div class='box'>
                <div class='label'>🚛 Mezzo</div>
                <div class='value'><strong>" . ($mezzo->nome ?? 'N/D') . "</strong> - {$targa}</div>
            </div>
            
            <div class='box'>
                <div class='label'>👤 Autista</div>
                <div class='value'>" . ($utente->nome ?? '') . " " . ($utente->cognome ?? '') . "</div>
            </div>
            
            <div class='box danger'>
                <div class='label'>⚠️ Tipo Guasto</div>
                <div class='value'><strong>{$tipoLabel}</strong></div>
            </div>
            
            <div class='box'>
                <div class='label'>📝 Descrizione</div>
                <div class='value'>" . nl2br(htmlspecialchars($dati['descrizione'])) . "</div>
            </div>
            
            " . (!empty($dati['indirizzo']) ? "
            <div class='box'>
                <div class='label'>📍 Posizione</div>
                <div class='value'>" . htmlspecialchars($dati['indirizzo']) . "</div>
                " . ($linkMappa ? "<a href='{$linkMappa}' class='btn-mappa'>🗺️ Apri in Google Maps</a>" : "") . "
            </div>
            " : "") . "
        </div>
        
        <div class='footer'>
            Email generata automaticamente da Logistia<br>
            <small>Non rispondere a questa email</small>
        </div>
    </div>
</body>
</html>";

            $mail->isHTML(true);
            $mail->Body = $html;

            // Testo alternativo
            $mail->AltBody = "SEGNALAZIONE GUASTO #{$idGuasto}\n" .
                "Urgenza: {$urgLabel}\n" .
                "Mezzo: " . ($mezzo->nome ?? '') . " - {$targa}\n" .
                "Autista: " . ($utente->nome ?? '') . " " . ($utente->cognome ?? '') . "\n" .
                "Tipo: {$tipoLabel}\n" .
                "Descrizione: {$dati['descrizione']}\n" .
                (!empty($dati['indirizzo']) ? "Posizione: {$dati['indirizzo']}\n" : "") .
                ($linkMappa ? "Mappa: {$linkMappa}\n" : "");

            // Allega foto
            if ($fotoPath && file_exists(public_path($fotoPath))) {
                $mail->addAttachment(public_path($fotoPath), 'foto_guasto.jpg');
            }

            $mail->send();
            return true;

        } catch (Exception $e) {
            \Log::error('Errore email guasto: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mostra form segnalazione guasto
     */
    public function segnalaGuasto()
    {
        $this->is_loggato();
        $utente = session('utente');

        // Recupera mezzo associato all'autista
        $mezzo = DB::table('dispositivi_tracking as dt')
            ->join('mezzi as m', 'dt.id_mezzo', '=', 'm.id')
            ->where('dt.id_utente', $utente->id)
            ->where('dt.is_active', 1)
            ->select('m.id', 'm.nome', 'm.targa')
            ->first();

        // Recupera dati azienda per email destinatario
        $azienda = DB::table('aziende')
            ->where('id', $utente->id_azienda)
            ->first();

        // Email destinatario (priorità: email_segnalazioni_guasti > pec > email_smtp)
        $emailDestinatario = $azienda->email_segnalazioni_guasti
            ?? $azienda->pec
            ?? $azienda->email_smtp
            ?? '';

        return view('autista.segnala_guasto', compact('utente', 'mezzo', 'azienda', 'emailDestinatario'));
    }


    public function rifornimenti(Request $request)
    {
        $autista = session('utente');

        // Recupera il mezzo dal dispositivo tracking
        $dispositivo = DB::table('dispositivi_tracking')
            ->where('id_utente', $autista->id)
            ->where('is_active', 1)
            ->first();

        if (!$dispositivo || !$dispositivo->id_mezzo) {
            return view('autista.content.rifornimenti', [
                'rifornimenti' => collect([]),
                'mezzo' => null,
                'stats' => ['totale_rifornimenti' => 0, 'spesa_totale' => 0, 'litri_totali' => 0, 'consumo_medio' => null],
                'mesi_disponibili' => [],
                'filtro_mese' => null,
            ]);
        }

        $idMezzo = $dispositivo->id_mezzo;
        $mezzo = DB::table('mezzi')->where('id', $idMezzo)->first();

        $query = DB::table('rifornimenti_carburante')
            ->where('id_mezzo', $idMezzo)
            ->where('id_azienda', $autista->id_azienda)
            ->orderBy('data_rifornimento', 'desc')
            ->orderBy('id', 'desc');

        $filtro_mese = $request->get('mese');
        if ($filtro_mese) {
            $query->whereRaw("DATE_FORMAT(data_rifornimento, '%Y-%m') = ?", [$filtro_mese]);
        }

        $rifornimenti = $query->get();

        $statsBase = DB::table('rifornimenti_carburante')
            ->where('id_mezzo', $idMezzo)
            ->where('id_azienda', $autista->id_azienda);

        if ($filtro_mese) {
            $statsBase->whereRaw("DATE_FORMAT(data_rifornimento, '%Y-%m') = ?", [$filtro_mese]);
        }

        $stats = [
            'totale_rifornimenti' => (clone $statsBase)->count(),
            'spesa_totale' => (clone $statsBase)->sum('importo_totale'),
            'litri_totali' => (clone $statsBase)->sum('litri'),
            'consumo_medio' => (clone $statsBase)->whereNotNull('consumo_calcolato')->avg('consumo_calcolato'),
        ];

        $mesi_disponibili = DB::table('rifornimenti_carburante')
            ->where('id_mezzo', $idMezzo)
            ->selectRaw("DATE_FORMAT(data_rifornimento, '%Y-%m') as valore, DATE_FORMAT(data_rifornimento, '%M %Y') as etichetta")
            ->groupBy('valore', 'etichetta')
            ->orderBy('valore', 'desc')
            ->get()
            ->toArray();

        return view('autista.rifornimenti', compact(
            'rifornimenti', 'mezzo', 'stats', 'mesi_disponibili', 'filtro_mese'
        ));
    }

    public function salvaRifornimento(Request $request)
    {
        $autista = session('utente');

        $dispositivo = DB::table('dispositivi_tracking')
            ->where('id_utente', $autista->id)
            ->where('is_active', 1)
            ->first();

        if (!$dispositivo || !$dispositivo->id_mezzo) {
            return response()->json(['success' => false, 'message' => 'Nessun mezzo assegnato']);
        }

        $idMezzo = $dispositivo->id_mezzo;



        $dati = [
            'id_mezzo' => $idMezzo,
            'id_azienda' => $autista->id_azienda,
            'data_rifornimento' => $request->input('data_rifornimento'),
            'km_rifornimento' => $request->input('km_rifornimento'),
            'litri' => $request->input('litri'),
            'importo_totale' => $request->input('importo_totale'),
            'prezzo_litro' => round($request->input('importo_totale') / $request->input('litri'), 3),
            'tipo_carburante' => $request->input('tipo_carburante', 'diesel'),
            'pieno' => $request->input('pieno', 1),
            'stazione_servizio' => $request->input('stazione_servizio'),
            'note' => $request->input('note'),
        ];

        if ($dati['pieno']) {
            $precedente = DB::table('rifornimenti_carburante')
                ->where('id_mezzo', $idMezzo)
                ->where('pieno', 1)
                ->where('data_rifornimento', '<=', $dati['data_rifornimento'])
                ->where('km_rifornimento', '<', $dati['km_rifornimento'])
                ->orderBy('km_rifornimento', 'desc')
                ->first();

            if ($precedente) {
                $kmPercorsi = $dati['km_rifornimento'] - $precedente->km_rifornimento;
                if ($kmPercorsi > 0 && $dati['litri'] > 0) {
                    $dati['consumo_calcolato'] = round($kmPercorsi / $dati['litri'], 2);
                }
            }
        }

        if ($request->hasFile('foto_scontrino')) {
            $file = $request->file('foto_scontrino');
            $nome = 'scontrino_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/scontrini'), $nome);
            $dati['foto_scontrino'] = 'uploads/scontrini/' . $nome;
        }

        $id = $request->input('id');

        if ($id) {
            DB::table('rifornimenti_carburante')->where('id', $id)
                ->where('id_azienda', $autista->id_azienda)
                ->update($dati);
        } else {
            DB::table('rifornimenti_carburante')->insert($dati);
        }

        return response()->json(['success' => true]);
    }

    public function dettaglioRifornimento($id)
    {
        $autista = session('utente');
        $rifornimento = DB::table('rifornimenti_carburante')
            ->where('id', $id)
            ->where('id_azienda', $autista->id_azienda)
            ->first();

        if (!$rifornimento) {
            return response()->json(['success' => false, 'message' => 'Non trovato']);
        }

        return response()->json(['success' => true, 'rifornimento' => $rifornimento]);
    }

    public function eliminaRifornimento($id)
    {
        $autista = session('utente');
        DB::table('rifornimenti_carburante')
            ->where('id', $id)
            ->where('id_azienda', $autista->id_azienda)
            ->delete();

        return response()->json(['success' => true]);
    }


// =====================================================
// NUOVI METODI - Aggiungere in AutistaController.php
// =====================================================

    // =====================================================
    // 1. SISTEMA NOTIFICHE
    // =====================================================

    /**
     * Pagina notifiche autista
     */
    public function notifiche()
    {
        $utente = session('utente');

        $notifiche = DB::table('notifiche_autista')
            ->leftJoin('ordini_trasporto', 'notifiche_autista.id_ordine', '=', 'ordini_trasporto.id')
            ->where('notifiche_autista.id_autista', $utente->id)
            ->select([
                'notifiche_autista.*',
                'ordini_trasporto.numero_ordine',
                'ordini_trasporto.indirizzo_consegna'
            ])
            ->orderBy('notifiche_autista.created_at', 'desc')
            ->limit(50)
            ->get();

        return view('autista.notifiche', compact('utente', 'notifiche'));
    }



    /**
     * Segna notifica come letta
     */
    public function segnaNotificaLetta($id)
    {
        $utente = session('utente');

        DB::table('notifiche_autista')
            ->where('id', $id)
            ->where('id_autista', $utente->id)
            ->update(['letta' => 1, 'letta_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Segna tutte come lette
     */
    public function segnaTutteLette()
    {
        $utente = session('utente');

        DB::table('notifiche_autista')
            ->where('id_autista', $utente->id)
            ->where('letta', 0)
            ->update(['letta' => 1, 'letta_at' => now()]);

        return response()->json(['success' => true]);
    }



    /**
     * Helper statico: crea notifica per l'autista
     * Chiamare questo metodo da TrasportiController quando si assegna/modifica un ordine
     */
    public static function creaNotifica($idAutista, $idAzienda, $tipo, $titolo, $messaggio = null, $idOrdine = null)
    {
        DB::table('notifiche_autista')->insert([
            'id_autista' => $idAutista,
            'id_azienda' => $idAzienda,
            'id_ordine' => $idOrdine,
            'tipo' => $tipo,
            'titolo' => $titolo,
            'messaggio' => $messaggio,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Conta notifiche non lette (polling per badge)
     */
    public function notificheNuove()
    {
        $utente = session('utente');

        $notifiche = DB::table('notifiche_autista')
            ->where('id_autista', $utente->id)
            ->where('letta', 0)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'count' => $notifiche->count(),
            'notifiche' => $notifiche->map(function ($n) {
                return [
                    'id' => $n->id,
                    'titolo' => $n->titolo,
                    'messaggio' => $n->messaggio,
                ];
            })
        ]);
    }

    /**
     * Lista notifiche per offcanvas (JSON)
     */
    public function notificheLista()
    {
        $utente = session('utente');

        $notifiche = DB::table('notifiche_autista')
            ->where('id_autista', $utente->id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'notifiche' => $notifiche->map(function ($n) {
                return [
                    'id' => $n->id,
                    'titolo' => $n->titolo,
                    'messaggio' => $n->messaggio,
                    'tipo' => $n->tipo,
                    'id_ordine' => $n->id_ordine,
                    'letta' => (bool) $n->letta,
                    'tempo_fa' => \Carbon\Carbon::parse($n->created_at)->locale('it')->diffForHumans(),
                    'data' => \Carbon\Carbon::parse($n->created_at)->format('d/m/Y H:i'),
                ];
            })
        ]);
    }

    /**
     * Segna notifica come letta
     */
    public function notificheSegnaLetta($id)
    {
        $utente = session('utente');

        DB::table('notifiche_autista')
            ->where('id', $id)
            ->where('id_autista', $utente->id)
            ->update([
                'letta' => 1,
                'letta_at' => now()
            ]);

        return response()->json(['success' => true]);
    }

    // =====================================================
    // 2. PROOF OF DELIVERY - FOTO CONSEGNA
    // =====================================================

    /**
     * Upload foto durante la consegna
     */
    public function uploadFotoConsegna(Request $request, $id)
    {
        $utente = session('utente');

        // Verifica che l'ordine esista e sia assegnato a questo autista
        $ordine = DB::table('ordini_trasporto')
            ->where('id', $id)
            ->where('id_autista', $utente->id)
            ->first();

        if (!$ordine) {
            return response()->json(['success' => false, 'message' => 'Ordine non trovato'], 404);
        }

        if (!$request->hasFile('foto')) {
            return response()->json(['success' => false, 'message' => 'Nessuna foto ricevuta'], 400);
        }

        $foto = $request->file('foto');
        $tipo = $request->input('tipo', 'merce');
        $nota = $request->input('nota', null);

        // Crea directory se non esiste
        $directory = 'uploads/consegne/' . $ordine->id_azienda . '/' . date('Y/m');
        $percorsoBase = public_path($directory);
        if (!file_exists($percorsoBase)) {
            mkdir($percorsoBase, 0755, true);
        }

        // Salva file
        $nomeFile = 'pod_' . $id . '_' . time() . '_' . rand(100, 999) . '.' . $foto->getClientOriginalExtension();
        $foto->move($percorsoBase, $nomeFile);

        // Salva record in DB
        $idFoto = DB::table('foto_consegna')->insertGetId([
            'id_ordine' => $id,
            'id_autista' => $utente->id,
            'id_azienda' => $ordine->id_azienda,
            'tipo' => $tipo,
            'percorso_file' => $directory . '/' . $nomeFile,
            'nome_file' => $nomeFile,
            'dimensione' => filesize($percorsoBase . '/' . $nomeFile),
            'nota' => $nota,
            'created_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'foto' => [
                'id' => $idFoto,
                'url' => '/' . $directory . '/' . $nomeFile,
                'tipo' => $tipo,
                'nome' => $nomeFile
            ]
        ]);
    }

    /**
     * Elimina foto consegna
     */
    public function eliminaFotoConsegna($idFoto)
    {
        $utente = session('utente');

        $foto = DB::table('foto_consegna')
            ->where('id', $idFoto)
            ->where('id_autista', $utente->id)
            ->first();

        if (!$foto) {
            return response()->json(['success' => false, 'message' => 'Foto non trovata'], 404);
        }

        // Elimina file fisico
        $percorsoCompleto = public_path($foto->percorso_file);
        if (file_exists($percorsoCompleto)) {
            unlink($percorsoCompleto);
        }

        // Elimina record
        DB::table('foto_consegna')->where('id', $idFoto)->delete();

        return response()->json(['success' => true]);
    }


    // =====================================================
    // 3. STORICO ORDINI AUTISTA
    // =====================================================

    /**
     * Storico ordini dell'autista con filtri
     */
    public function storicoOrdini(Request $request)
    {
        $utente = session('utente');
        $dispositivo = $this->getDispositivoUtente($utente->id);

        $periodo = $request->input('periodo', 'settimana');
        $stato = $request->input('stato', 'tutti');

        // Calcola date in base al periodo
        switch ($periodo) {
            case 'oggi':
                $dataInizio = date('Y-m-d');
                break;
            case 'settimana':
                $dataInizio = date('Y-m-d', strtotime('-7 days'));
                break;
            case 'mese':
                $dataInizio = date('Y-m-01');
                break;
            case 'mese_scorso':
                $dataInizio = date('Y-m-01', strtotime('-1 month'));
                $dataFine = date('Y-m-t', strtotime('-1 month'));
                break;
            default:
                $dataInizio = date('Y-m-d', strtotime('-7 days'));
        }

        $query = DB::table('ordini_trasporto')
            ->leftJoin('clienti', 'ordini_trasporto.id_cliente', '=', 'clienti.id')
            ->where('ordini_trasporto.id_autista', $utente->id)
            ->where('ordini_trasporto.data_consegna', '>=', $dataInizio)
            ->select([
                'ordini_trasporto.id',
                'ordini_trasporto.numero_ordine',
                'ordini_trasporto.indirizzo_ritiro',
                'ordini_trasporto.indirizzo_consegna',
                'ordini_trasporto.data_ritiro',
                'ordini_trasporto.data_consegna',
                'ordini_trasporto.data_completamento',
                'ordini_trasporto.descrizione_merce',
                'ordini_trasporto.peso_kg',
                'ordini_trasporto.km_percorsi',
                'ordini_trasporto.stato',
                'ordini_trasporto.note_autista',
                'clienti.ragione_sociale as cliente'
            ]);

        if (isset($dataFine)) {
            $query->where('ordini_trasporto.data_consegna', '<=', $dataFine);
        }

        if ($stato !== 'tutti') {
            $query->where('ordini_trasporto.stato', $stato);
        }

        $ordini = $query->orderBy('ordini_trasporto.data_consegna', 'desc')->get();

        // Statistiche riepilogo
        $stats = [
            'totale_ordini' => $ordini->count(),
            'completati' => $ordini->where('stato', 'completato')->count(),
            'km_totali' => $ordini->sum('km_percorsi') ?: 0,
        ];

        return view('autista.storico', compact('utente', 'dispositivo', 'ordini', 'stats', 'periodo', 'stato'));
    }


    // =====================================================
    // 4. COMPLETAMENTO CONSEGNA AVANZATO
    //    (firma + foto + note in un unico step)
    // =====================================================

    /**
     * Completa consegna con firma, foto e note
     */
    public function completaConsegnaAvanzato(Request $request, $id)
    {
        $utente = session('utente');

        $ordine = DB::table('ordini_trasporto')
            ->where('id', $id)
            ->where('id_autista', $utente->id)
            ->whereIn('stato', ['assegnato', 'in_corso'])
            ->first();

        if (!$ordine) {
            return response()->json(['success' => false, 'message' => 'Ordine non trovato o già completato'], 404);
        }

        // Aggiorna ordine
        $datiUpdate = [
            'stato' => 'completato',
            'data_completamento' => now(),
            'note_autista' => $request->input('note_autista'),
            'updated_at' => now()
        ];

        // Firma base64
        if ($request->input('firma')) {
            $datiUpdate['firma_destinatario'] = $request->input('firma');
            $datiUpdate['nome_firmatario'] = $request->input('nome_firmatario');
        }

        DB::table('ordini_trasporto')
            ->where('id', $id)
            ->update($datiUpdate);

        // Aggiorna anche il DDT collegato come "consegnato"
        DB::table('documenti_trasporto')
            ->where('id_ordine', $id)
            ->where('tipo_documento', 'ddt')
            ->update([
                'stato' => 'consegnato',
                'data_consegna_effettiva' => now(),
                'firma_destinatario' => $request->input('firma'),
                'nome_firmatario' => $request->input('nome_firmatario'),
                'updated_at' => now()
            ]);

        // Notifica all'admin
        // (opzionale: puoi creare una notifica anche per l'admin)

        return response()->json([
            'success' => true,
            'message' => 'Consegna completata con successo!'
        ]);
    }


    /**
     * ============================================================
     * METODI DA AGGIUNGERE A AutistaController.php
     * ============================================================
     *
     * Copia questi metodi dentro la classe AutistaController
     */

    /**
     * Percorso consegne ottimizzato del giorno
     * URL: /autista/percorso-consegne
     */
    public function percorsoConsegne(Request $request)
    {
        $utente = session('utente');

        // Data selezionata (default: oggi)
        $dataSelezionata = $request->get('data', date('Y-m-d'));

        // Prendi le consegne assegnate all'autista per la data selezionata
        $consegne = DB::table('ordini_trasporto as ot')
            ->leftJoin('clienti as c', 'ot.id_cliente', '=', 'c.id')
            ->where('ot.id_autista', $utente->id)
            ->where('ot.id_azienda', $utente->id_azienda)
            ->whereDate('ot.data_ritiro', $dataSelezionata)
            ->whereIn('ot.stato', ['assegnato', 'in_corso', 'completato'])
            ->select(
                'ot.id',
                'ot.numero_ordine',
                'ot.indirizzo_ritiro',
                'ot.indirizzo_consegna',
                'ot.data_ritiro',
                'ot.ora_ritiro',
                'ot.data_consegna',
                'ot.ora_consegna',
                'ot.descrizione_merce',
                'ot.numero_colli',
                'ot.peso_kg',
                'ot.km_totali',
                'ot.stato',
                'ot.note',
                'ot.importo',
                'c.ragione_sociale as cliente_nome'
            )
            ->orderBy('ot.ora_ritiro', 'asc')
            ->orderBy('ot.created_at', 'asc')
            ->get();

        // Prendi il mezzo associato all'autista
        $mezzo = DB::table('dispositivi_tracking as dt')
            ->join('mezzi as m', 'dt.id_mezzo', '=', 'm.id')
            ->where('dt.id_utente', $utente->id)
            ->where('dt.is_active', 1)
            ->select('m.id', 'm.targa', 'm.nome', 'm.marca', 'm.modello')
            ->first();

        // Se non trovato via dispositivi_tracking, cerca negli ordini del giorno
        if (!$mezzo && $consegne->count() > 0) {
            $primoOrdine = $consegne->first();
            $mezzoId = DB::table('ordini_trasporto')
                ->where('id', $primoOrdine->id)
                ->value('id_mezzo');

            if ($mezzoId) {
                $mezzo = DB::table('mezzi')
                    ->where('id', $mezzoId)
                    ->select('id', 'targa', 'nome', 'marca', 'modello')
                    ->first();
            }
        }

        // Google Maps API Key
        $googleMapsKey = 'AIzaSyB0Kta9cMMAOEcpcGl0hwXij0I6_gqWeLM';

        // Consumo medio del mezzo (litri per km)
        // Default: furgone ~12L/100km = 0.12 L/km
        // In futuro puoi salvare questo dato nella tabella mezzi
        $consumoMedioLtKm = 0.12;

        // Prezzo gasolio medio (aggiornabile)
        $prezzoGasolio = 1.65;

        return view('autista.percorso_consegne', compact(
            'utente',
            'consegne',
            'mezzo',
            'dataSelezionata',
            'googleMapsKey',
            'consumoMedioLtKm',
            'prezzoGasolio'
        ));
    }

    /**
     * Completa una consegna (ordini_trasporto)
     * URL: POST /autista/consegna-ordine/{id}/completa
     */
    public function completaConsegnaOrdine($id)
    {
        $utente = session('utente');

        $updated = DB::table('ordini_trasporto')
            ->where('id', $id)
            ->where('id_autista', $utente->id)
            ->where('id_azienda', $utente->id_azienda)
            ->update([
                'stato' => 'completato',
                'data_consegna' => now()->format('Y-m-d'),
                'ora_consegna' => now()->format('H:i:s'),
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => $updated > 0,
            'message' => $updated > 0 ? 'Consegna completata!' : 'Errore'
        ]);
    }

    /**
     * Salva l'ordine del percorso ottimizzato (opzionale)
     * URL: POST /autista/salva-ordine-percorso
     */
    public function salvaOrdinePercorso(Request $request)
    {
        $utente = session('utente');
        $ordine = $request->input('ordine', []);
        $data = $request->input('data', date('Y-m-d'));

        // Aggiorna l'ordine di percorso per ogni ordine di trasporto
        foreach ($ordine as $posizione => $idOrdine) {
            DB::table('ordini_trasporto')
                ->where('id', $idOrdine)
                ->where('id_autista', $utente->id)
                ->where('id_azienda', $utente->id_azienda)
                ->update([
                    'ordine_percorso' => $posizione + 1,
                    'updated_at' => now()
                ]);
        }

        return response()->json(['success' => true]);
    }


    /**
     * Piano giornaliero autista - mostra consegne e piano di carico
     */
    public function pianoGiornaliero(Request $request)
    {
        $utente = session('utente');
        $data = $request->get('data', date('Y-m-d'));

        // Recupera gli ordini assegnati all'autista per la data selezionata
        $ordini = DB::table('ordini_trasporto as ot')
            ->leftJoin('clienti as c', 'ot.id_cliente', '=', 'c.id')
            ->where('ot.id_autista', $utente->id)
            ->where('ot.id_azienda', $utente->id_azienda)
            ->where('ot.data_consegna', $data)
            ->whereIn('ot.stato', ['assegnato', 'pianificato', 'in_corso', 'completato'])
            ->select(
                'ot.id',
                'ot.numero_ordine',
                'ot.indirizzo_ritiro',
                'ot.indirizzo_consegna',
                'ot.descrizione_merce',
                'ot.peso_kg',
                'ot.note',
                'ot.ora_ritiro',
                'ot.ora_consegna',
                'ot.stato',
                'c.ragione_sociale as cliente_nome',
                'c.telefono as cliente_telefono',
                'c.indirizzo as cliente_indirizzo'
            )
            ->orderBy('ot.ora_ritiro', 'asc')
            ->get();

        // Recupera info mezzo dell'autista
        $mezzo = DB::table('mezzi as m')
            ->join('dispositivi_tracking as dt', 'm.id', '=', 'dt.id_mezzo')
            ->where('dt.id_utente', $utente->id)
            ->where('dt.is_active', 1)
            ->select('m.nome', 'm.targa')
            ->first();

        return view('autista.piano_giornaliero', compact('utente', 'ordini', 'mezzo', 'data'));
    }






}