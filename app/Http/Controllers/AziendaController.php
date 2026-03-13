<?php

namespace App\Http\Controllers;

use App\Exports\MassiveViewExport;
use App\Exports\MassiveViewExport2;
use App\Exports\MassiveViewExportGTS;
use App\Exports\SearchResultExport;
use App\Imports\ArticoliImport;
use App\Imports\BOMImport;
use App\Imports\MagazzinoImport;
use App\Imports\BPImport;
use App\Imports\StoricoImport;
use App\Imports\VenditeImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PHPMailer\PHPMailer\PHPMailer;
use phpseclib3\Net\SFTP;
use phpseclib3\Crypt\PublicKeyLoader;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\TariffeImport;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Ayeo\Barcode;
use NGT\Barcode\GS1Decoder\Decoder;
use Carbon\CarbonPeriod;
use DateTime;
use DateInterval;
class AziendaController extends Controller{





    /**
     * Crea nuovo ordine di trasporto
     */
    private function creaOrdine($dati, $utente)
    {
        DB::table('ordini_trasporto')->insert([
            'numero_ordine' => $this->generaNumeroOrdine($utente->id_azienda),
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
            'peso_kg' => $dati['peso_kg'] ?? null,
            'note' => $dati['note'] ?? null,
            'importo' => $dati['importo'] ?? 0,
            'stato' => 'pianificato',
            'id_azienda' => $utente->id_azienda,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Modifica ordine esistente
     */
    private function modificaOrdine($dati, $utente)
    {
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
                'peso_kg' => $dati['peso_kg'] ?? null,
                'note' => $dati['note'] ?? null,
                'importo' => $dati['importo'] ?? 0,
                'updated_at' => now()
            ]);
    }

    /**
     * Elimina ordine
     */
    private function eliminaOrdine($dati, $utente)
    {
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
            return response()->json(['success' => true, 'message' => 'Stato aggiornato!']);
        }

        return response()->json(['success' => false, 'message' => 'Errore nell\'aggiornamento']);
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

    public function getPermessiUtente($id)
    {
        $this->is_loggato();
        $utente = session('utente');

        $permessi = DB::select('SELECT solo_lettura, gestione_cantieri, gestione_mezzi, gestione_magazzino, gestione_utenti, visualizza_costi FROM utenti WHERE id = ? AND id_azienda = ?', [
            $id, $utente->id_azienda
        ]);

        if ($permessi) {
            return response()->json($permessi[0]);
        }

        return response()->json([
            'solo_lettura' => 0,
            'gestione_cantieri' => 1,
            'gestione_mezzi' => 0,
            'gestione_magazzino' => 0,
            'gestione_utenti' => 0,
            'visualizza_costi' => 0
        ]);
    }

    public function aggiornaPermessi(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        $id_utente = $request->input('id_utente');
        $solo_lettura = $request->has('solo_lettura') ? 1 : 0;
        $gestione_cantieri = $request->has('gestione_cantieri') ? 1 : 0;
        $gestione_mezzi = $request->has('gestione_mezzi') ? 1 : 0;
        $gestione_magazzino = $request->has('gestione_magazzino') ? 1 : 0;
        $gestione_utenti = $request->has('gestione_utenti') ? 1 : 0;
        $visualizza_costi = $request->has('visualizza_costi') ? 1 : 0;

        // Se è solo lettura, disabilita tutti gli altri permessi
        if ($solo_lettura) {
            $gestione_cantieri = 0;
            $gestione_mezzi = 0;
            $gestione_magazzino = 0;
            $gestione_utenti = 0;
        }

        DB::update('UPDATE utenti SET solo_lettura = ?, gestione_cantieri = ?, gestione_mezzi = ?, gestione_magazzino = ?, gestione_utenti = ?, visualizza_costi = ? WHERE id = ? AND id_azienda = ?', [
            $solo_lettura,
            $gestione_cantieri,
            $gestione_mezzi,
            $gestione_magazzino,
            $gestione_utenti,
            $visualizza_costi,
            $id_utente,
            $utente->id_azienda
        ]);

        return redirect()->back()->with('success', 'Permessi aggiornati con successo!');
    }

// Aggiorna anche la query nel metodo utenti() per includere i nuovi campi
    public function utenti()
    {
        $session = Session::get('utente');
        if (!$session) {
            return redirect('admin/login');
        }

        $utente = $session;

        // Query aggiornata per includere i nuovi campi permessi
        $utenti = collect(DB::select("
        SELECT u.*, 
               GROUP_CONCAT(r.titolo SEPARATOR ', ') as ruoli_nomi,
               GROUP_CONCAT(ur.id_ruolo) as ruoli_ids
        FROM utenti u
        LEFT JOIN utenti_ruoli ur ON u.id = ur.id_utente
        LEFT JOIN ruoli r ON ur.id_ruolo = r.id
        WHERE u.id_azienda = ?
        GROUP BY u.id, u.nome, u.cognome, u.email, u.costo_giornaliero, u.is_responsabile, u.vista_operaio, u.solo_lettura, u.gestione_cantieri, u.gestione_mezzi, u.gestione_magazzino, u.gestione_utenti, u.visualizza_costi
        ORDER BY u.nome, u.cognome
    ", [$session->id_azienda]));

        // Trasforma i ruoli in array
        $utenti = $utenti->map(function($utente_item) {
            $utente_item->ruoli = $utente_item->ruoli_nomi ? explode(', ', $utente_item->ruoli_nomi) : [];
            return $utente_item;
        });

        $ruoli = DB::table('ruoli')->where('id_azienda', $session->id_azienda)->get();

        // Gestione POST
        if ($_POST) {
            if (isset($_POST['crea_utente'])) {
                $this->creaUtente($_POST);
            } elseif (isset($_POST['modifica_utente'])) {
                $this->modificaUtente($_POST);
            } elseif (isset($_POST['elimina_utente'])) {
                $this->eliminaUtente($_POST);
            }
            return redirect('/azienda/utenti');
        }

        return view('azienda.utenti', compact('utenti', 'ruoli', 'utente'));
    }

// Funzione helper per controllare i permessi (da usare nelle altre viste)
    public function hasPermesso($permesso)
    {
        $utente = session('utente');

        if (!$utente) {
            return false;
        }

        // Se l'utente è solo lettura, non ha permessi di modifica
        if ($utente->solo_lettura == 1 && $permesso !== 'visualizza_costi') {
            return false;
        }

        // Controlla il permesso specifico
        switch ($permesso) {
            case 'gestione_cantieri':
                return $utente->gestione_cantieri == 1;
            case 'gestione_mezzi':
                return $utente->gestione_mezzi == 1;
            case 'gestione_magazzino':
                return $utente->gestione_magazzino == 1;
            case 'gestione_utenti':
                return $utente->gestione_utenti == 1;
            case 'visualizza_costi':
                return $utente->visualizza_costi == 1;
            default:
                return false;
        }
    }


    private function creaUtente($data)
    {
        $session = Session::get('utente');

        $userId = DB::table('utenti')->insertGetId([
            'nome' => $data['nome'],
            'cognome' => $data['cognome'],
            'email' => $data['email'],
            'password' => ($data['password']),
            'id_azienda' => $session->id_azienda,
            'admin_azienda' => 2,
            'costo_giornaliero' => $data['costo_giornaliero'] ?? null, // ✅ CAMBIATO: da costo_orario
            'is_responsabile' => isset($data['is_responsabile']) ? 1 : 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Gestione ruoli
        if (isset($data['id_ruolo']) && is_array($data['id_ruolo'])) {
            foreach ($data['id_ruolo'] as $ruoloId) {
                DB::table('utenti_ruoli')->insert([
                    'id_utente' => $userId,
                    'id_ruolo' => $ruoloId
                ]);
            }
        }
    }


    /**
     * Modifica del metodo modificaUtente() per gestire is_responsabile
     */
    private function modificaUtente($data)
    {
        $userId = $data['id_utente'];

        DB::table('utenti')->where('id', $userId)->update([
            'nome' => $data['nome'],
            'cognome' => $data['cognome'],
            'email' => $data['email'],
            'costo_giornaliero' => $data['costo_giornaliero'] ?? null, // ✅ CAMBIATO: da costo_orario
            'is_responsabile' => isset($data['is_responsabile']) ? 1 : 0,
            'updated_at' => now()
        ]);

        // Aggiorna ruoli
        DB::table('utenti_ruoli')->where('id_utente', $userId)->delete();

        if (isset($data['id_ruolo']) && is_array($data['id_ruolo'])) {
            foreach ($data['id_ruolo'] as $ruoloId) {
                DB::table('utenti_ruoli')->insert([
                    'id_utente' => $userId,
                    'id_ruolo' => $ruoloId
                ]);
            }
        }
    }
    private function eliminaUtente($data)
    {
        $userId = $data['id_utente'];
        $session = Session::get('utente');

        // Verifica che l'utente appartenga alla stessa azienda
        $utenteDaEliminare = DB::table('utenti')
            ->where('id', $userId)
            ->where('id_azienda', $session->id_azienda)
            ->first();

        if (!$utenteDaEliminare) {
            return false; // Utente non trovato o non autorizzato
        }

        // Impedisce l'auto-eliminazione
        if ($userId == $session->id) {
            return false; // Non puoi eliminare te stesso
        }

        try {
            DB::beginTransaction();

            // 1. Elimina le associazioni ruoli
            DB::table('utenti_ruoli')->where('id_utente', $userId)->delete();

            // 2. Rimuovi dai cantieri assegnati
            DB::table('cantieri_operai')->where('id_dipendente', $userId)->delete();

            // 3. Rimuovi dalle attività assegnate
            DB::table('cantieri_attivita_dipendenti')->where('id_dipendente', $userId)->delete();

            // 4. Elimina le presenze (opzionale - potresti volerle mantenere per storico)
            // DB::table('presenze')->where('id_dipendente', $userId)->delete();

            // 5. Elimina l'immagine profilo se esiste
            if ($utenteDaEliminare->immagine && file_exists($utenteDaEliminare->immagine)) {
                unlink($utenteDaEliminare->immagine);
            }

            // 6. Elimina l'utente
            DB::table('utenti')->where('id', $userId)->delete();

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
    }


    public function updateResponsabile(Request $request)
    {
        try {
            $userId = $request->input('id');
            $isResponsabile = $request->input('is_responsabile');

            DB::table('utenti')
                ->where('id', $userId)
                ->update(['is_responsabile' => $isResponsabile]);

            return response()->json([
                'success' => true,
                'message' => 'Status responsabile aggiornato con successo'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore durante l\'aggiornamento: ' . $e->getMessage()
            ]);
        }
    }


    public function index(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        // ✅ SPECIAL CASE: Per l'azienda ID 16 mostra il planning dipendenti di default
        if ($utente->id_azienda == 16 && !$request->has('view')) {
            return $this->visualizzaDipendenti($request, true); // true = modalità dashboard
        }

        // ✅ Query base per gli ordini di trasporto (TMS)
        $ordiniQuery = DB::table('ordini_trasporto as ot')
            ->leftJoin('clienti as c', 'ot.id_cliente', '=', 'c.id')
            ->leftJoin('mezzi as m', 'ot.id_mezzo', '=', 'm.id')
            ->leftJoin('utenti as u', 'ot.id_autista', '=', 'u.id')
            ->where('ot.id_azienda', $utente->id_azienda)
            ->select(
                'ot.id',
                'ot.numero_ordine',
                'ot.data_ritiro',
                'ot.data_consegna',
                'ot.stato',
                'ot.indirizzo_ritiro',
                'ot.indirizzo_consegna',
                'ot.descrizione_merce',
                'ot.importo',
                'c.ragione_sociale as cliente_nome',
                'm.targa',
                'm.nome as mezzo_nome',
                'u.nome as autista_nome',
                'u.cognome as autista_cognome'
            );

        // ✅ Se l'utente è autista, mostra solo i suoi ordini
        if (isset($utente->vista_operaio) && (int) $utente->vista_operaio === 1) {
            $ordiniQuery->where('ot.id_autista', $utente->id);
        }

        // ✅ Otteniamo gli ordini
        $ordini = $ordiniQuery->get();

        // ✅ Generazione eventi per il calendario (ordini di trasporto)
        $eventi = collect($ordini)->map(function ($ordine) use ($utente) {
            // Determina il colore in base allo stato
            switch($ordine->stato) {
                case 'pianificato':
                    $colore = '#6c757d';
                    break;
                case 'assegnato':
                    $colore = '#0dcaf0';
                    break;
                case 'in_corso':
                    $colore = '#ffc107';
                    break;
                case 'completato':
                    $colore = '#198754';
                    break;
                case 'annullato':
                    $colore = '#dc3545';
                    break;
                default:
                    $colore = '#6c757d';
            }

            // Emoji in base allo stato
            switch($ordine->stato) {
                case 'pianificato':
                    $emoji = '📋';
                    break;
                case 'assegnato':
                    $emoji = '👤';
                    break;
                case 'in_corso':
                    $emoji = '🚛';
                    break;
                case 'completato':
                    $emoji = '✅';
                    break;
                case 'annullato':
                    $emoji = '❌';
                    break;
                default:
                    $emoji = '📦';
            }

            return [
                'id' => $ordine->id,
                'title' => $emoji . ' ' . $ordine->numero_ordine . ' | ' . ($ordine->cliente_nome ?? 'Cliente N/A'),
                'start' => $ordine->data_ritiro,
                'end' => $ordine->data_consegna ?? $ordine->data_ritiro,
                'color' => $colore,
                'url' => (isset($utente->vista_operaio) && (int) $utente->vista_operaio === 1)
                    ? null  // ❌ Autista → Nessun URL
                    : url('/azienda/ordine-trasporto/' . $ordine->id), // ✅ Admin → URL presente
                'allDay' => true,
                'extendedProps' => [
                    'ordine_id' => $ordine->id,
                    'stato' => $ordine->stato,
                    'cliente' => $ordine->cliente_nome,
                    'mezzo' => $ordine->targa,
                    'autista' => $ordine->autista_nome ? ($ordine->autista_nome . ' ' . $ordine->autista_cognome) : null,
                    'ritiro' => $ordine->indirizzo_ritiro,
                    'consegna' => $ordine->indirizzo_consegna,
                    'importo' => $ordine->importo
                ]
            ];
        });

        // ✅ Statistiche TMS per la dashboard
        $statsTMS = [
            'ordini_totali' => $ordini->count(),
            'ordini_oggi' => $ordini->where('data_ritiro', date('Y-m-d'))->count(),
            'ordini_in_corso' => $ordini->where('stato', 'in_corso')->count(),
            'ordini_completati_mese' => $ordini->where('stato', 'completato')
                ->filter(function($ordine) {
                    return date('Y-m', strtotime($ordine->data_ritiro)) === date('Y-m');
                })->count(),
            'fatturato_mese' => $ordini->where('stato', 'completato')
                ->filter(function($ordine) {
                    return date('Y-m', strtotime($ordine->data_ritiro)) === date('Y-m');
                })->sum('importo'),
            'mezzi_disponibili' => DB::table('mezzi')
                ->where('id_azienda', $utente->id_azienda)
                ->where('stato', 1)
                ->count(),
            'clienti_attivi' => DB::table('clienti')
                ->where('id_azienda', $utente->id_azienda)
                ->count()
        ];

        // ✅ Ordini raggruppati per mezzo (per la sidebar)
        $ordiniPerMezzo = $ordini->where('targa', '!=', null)
            ->groupBy('targa')
            ->map(function($ordiniMezzo) {
                return [
                    'targa' => $ordiniMezzo->first()->targa,
                    'mezzo_nome' => $ordiniMezzo->first()->mezzo_nome,
                    'ordini' => $ordiniMezzo->map(function($ordine) {
                        return [
                            'id' => $ordine->id,
                            'numero_ordine' => $ordine->numero_ordine,
                            'data_ritiro' => $ordine->data_ritiro,
                            'stato' => $ordine->stato,
                            'cliente' => $ordine->cliente_nome
                        ];
                    })
                ];
            });
        $linkAnalytics = null;
        if ($utente->admin_azienda == 1 || $utente->gestione_trasporti == 1) {
            $linkAnalytics = [
                'fatturato_oggi' => $ordini->where('data_ritiro', date('Y-m-d'))->sum('importo'),
                'ordini_oggi' => $ordini->where('data_ritiro', date('Y-m-d'))->count(),
                'url_analytics' => '/azienda/analytics/dashboard'
            ];
        }


        // ✅ Per l'azienda 16, determina quale view mostrare
        if ($utente->id_azienda == 16) {
            $showCalendar = $request->get('view') === 'calendario';
            return view('azienda.index_special', compact('eventi', 'statsTMS', 'ordiniPerMezzo', 'utente', 'showCalendar', 'linkAnalytics'));
        }

        return view('azienda.index', compact('eventi', 'statsTMS', 'ordiniPerMezzo', 'utente'));
    }

    public function materiali()
    {
        $this->is_loggato();
        $utente = session('utente');

        // Recupera solo gli articoli con tipologia = 1 (Materiale)
        $articoli = DB::select('SELECT * FROM articoli WHERE tipologia = ? AND id_azienda = ?', [1, $utente->id_azienda]);

        // Recupera gli impegni dei materiali
        $impegni = DB::select("
        SELECT impegni_magazzino.id, impegni_magazzino.id_articolo, cantieri.titolo AS nome_cantiere, impegni_magazzino.quantita_impegnata
        FROM impegni_magazzino
        JOIN cantieri ON impegni_magazzino.id_cantiere = cantieri.id
        WHERE impegni_magazzino.id_azienda = ?
    ", [$utente->id_azienda]);

        // Controlla articoli sotto soglia
        $articoli_sotto_soglia = [];
        foreach ($articoli as $articolo) {
            $quantita_disponibile = $articolo->quantita - $articolo->quantita_impegnata;
            if ($articolo->soglia_riordino > 0 && $quantita_disponibile <= $articolo->soglia_riordino) {
                $articoli_sotto_soglia[] = $articolo;
            }
        }

        // Invia notifica OneSignal se ci sono articoli sotto soglia
        if (!empty($articoli_sotto_soglia)) {
            $messaggio = "⚠️ " . count($articoli_sotto_soglia) . " articoli sotto soglia di riordino!";
            $this->inviaNotificaOneSignal($messaggio);
        }

        return View::make('azienda.materiali', compact('utente', 'articoli', 'impegni', 'articoli_sotto_soglia'));
    }






    public function aggiornaSoglia(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        DB::table('articoli')
            ->where('id', $request->id_articolo)
            ->where('id_azienda', $utente->id_azienda)
            ->update([
                'soglia_riordino' => $request->soglia_riordino,
                'updated_at' => now()
            ]);

        return redirect()->back()->with('success', 'Soglia di riordino aggiornata!');
    }

// Funzione per aggiornare soglie massive
    public function aggiornaSoglieMassive(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        foreach ($request->soglie as $id_articolo => $soglia) {
            DB::table('articoli')
                ->where('id', $id_articolo)
                ->where('id_azienda', $utente->id_azienda)
                ->update([
                    'soglia_riordino' => $soglia,
                    'updated_at' => now()
                ]);
        }

        return redirect()->back()->with('success', 'Soglie di riordino aggiornate!');
    }

// Funzione semplice per inviare notifica OneSignal
    private function inviaNotificaOneSignal($messaggio)
    {
        $content = array(
            "en" => $messaggio,
            "it" => $messaggio
        );

        $fields = array(
            'app_id' => "TUA_APP_ID_ONESIGNAL", // Sostituisci con il tuo App ID
            'included_segments' => array('All'),
            'contents' => $content,
            'headings' => array("en" => "Riordino Magazzino", "it" => "Riordino Magazzino")
        );

        $fields = json_encode($fields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Basic TUA_REST_API_KEY' // Sostituisci con la tua REST API Key
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public function strumenti()
    {
        $this->is_loggato();
        $utente = session('utente');

        // Recupera solo gli articoli con tipologia = 2 (Strumenti)
        $articoli = DB::select('SELECT * FROM articoli WHERE tipologia = ? AND id_azienda = ?', [2, $utente->id_azienda]);

        // Recupera gli impegni degli strumenti
        $impegni = DB::select("
        SELECT impegni_magazzino.id, impegni_magazzino.id_articolo, cantieri.titolo AS nome_cantiere, impegni_magazzino.quantita_impegnata
        FROM impegni_magazzino
        JOIN cantieri ON impegni_magazzino.id_cantiere = cantieri.id
        WHERE impegni_magazzino.id_azienda = ?
    ", [$utente->id_azienda]);

        return View::make('azienda.strumenti', compact('utente', 'articoli', 'impegni'));
    }


    public function gestisciArticolo(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');
        $dati = $request->all();

        if (isset($dati['aggiungi'])) {
            unset($dati['aggiungi']);

            DB::insert('INSERT INTO articoli (titolo, descrizione, quantita, unita_misura, quantita_impegnata, costo, soglia_riordino, tipologia, id_azienda) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $dati['titolo'] ?? null,
                $dati['descrizione'] ?? null,
                $dati['quantita'] ?? 0,
                $dati['unita_misura'] ?? null,
                $dati['quantita_impegnata'] ?? 0,
                $dati['costo'] ?? 0,
                $dati['soglia_riordino'] ?? 0, // 🔹 AGGIUNTO CAMPO SOGLIA
                $dati['tipologia'],
                $utente->id_azienda
            ]);

            return Redirect::to('/azienda/' . ($dati['tipologia'] == 2 ? 'strumenti' : 'materiali'))->with('success', 'Articolo aggiunto con successo!');
        }

        if (isset($dati['modifica'])) {
            unset($dati['modifica']);

            $articolo = DB::select('SELECT id FROM articoli WHERE id = ? AND id_azienda = ?', [(int) $dati['id'], $utente->id_azienda]);

            if (!$articolo) {
                return Redirect::back()->withErrors(['error' => 'Articolo non trovato!']);
            }

            DB::update('UPDATE articoli SET titolo = ?, descrizione = ?, quantita = ?, unita_misura = ?, quantita_impegnata = ?, costo = ?, soglia_riordino = ? WHERE id = ? AND id_azienda = ?', [
                $dati['titolo'] ?? null,
                $dati['descrizione'] ?? null,
                $dati['quantita'] ?? 0,
                $dati['unita_misura'] ?? null,
                $dati['quantita_impegnata'] ?? 0,
                $dati['costo'] ?? 0,
                $dati['soglia_riordino'] ?? 0, // 🔹 AGGIUNTO CAMPO SOGLIA
                (int) $dati['id'],
                $utente->id_azienda
            ]);

            return Redirect::to('/azienda/' . ($dati['tipologia'] == 2 ? 'strumenti' : 'materiali'))->with('success', 'Articolo modificato con successo!');
        }

        if (isset($dati['elimina'])) {
            $articolo = DB::select('SELECT id FROM articoli WHERE id = ? AND id_azienda = ?', [(int) $dati['id'], $utente->id_azienda]);

            if (!$articolo) {
                return Redirect::back()->withErrors(['error' => 'Articolo non trovato!']);
            }

            DB::delete('DELETE FROM articoli WHERE id = ? AND id_azienda = ?', [(int) $dati['id'], $utente->id_azienda]);

            return Redirect::to('/azienda/' . ($dati['tipologia'] == 2 ? 'strumenti' : 'materiali'))->with('success', 'Articolo eliminato con successo!');
        }
    }

// AGGIORNO la tua funzione movimento esistente per controllare soglie
    public function movimento(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');
        $dati = $request->all();

        $articolo = DB::select('SELECT * FROM articoli WHERE id = ? AND id_azienda = ?', [(int) $dati['id_articolo'], $utente->id_azienda]);

        if (!$articolo) {
            return Redirect::back()->withErrors(['error' => 'Articolo non trovato!']);
        }

        $quantita = (int) $dati['quantita'];

        if ($dati['causale'] === 'carico') {
            // Aggiunge la quantità all'articolo
            DB::update('UPDATE articoli SET quantita = quantita + ? WHERE id = ? AND id_azienda = ?', [
                $quantita, (int) $dati['id_articolo'], $utente->id_azienda
            ]);

            $causale = "Carico";
        } else {
            // Controlla se lo scarico è possibile
            if ($articolo[0]->quantita < $quantita) {
                return Redirect::back()->withErrors(['error' => 'Quantità insufficiente in magazzino!']);
            }

            // Scarica la quantità dall'articolo
            DB::update('UPDATE articoli SET quantita = quantita - ? WHERE id = ? AND id_azienda = ?', [
                $quantita, (int) $dati['id_articolo'], $utente->id_azienda
            ]);

            $causale = "Scarico";

            // 🔹 CONTROLLO SOGLIA DOPO SCARICO
            $nuova_quantita = $articolo[0]->quantita - $quantita;
            $quantita_disponibile = $nuova_quantita - $articolo[0]->quantita_impegnata;

            if (isset($articolo[0]->soglia_riordino) && $articolo[0]->soglia_riordino > 0 && $quantita_disponibile <= $articolo[0]->soglia_riordino) {
                $messaggio = "⚠️ RIORDINO: {$articolo[0]->titolo} sotto soglia! Disponibile: {$quantita_disponibile}";
                $this->inviaNotificaOneSignal($messaggio);

                session()->flash('notifica_riordino', "Attenzione: {$articolo[0]->titolo} è sotto la soglia di riordino!");
            }
        }

        // Registra il movimento in mgmov
        DB::insert('INSERT INTO mgmov (id_utente, id_azienda, id_articolo, causale, qta) VALUES (?, ?, ?, ?, ?)', [
            $utente->id, $utente->id_azienda, (int) $dati['id_articolo'], $causale, $quantita
        ]);

        return Redirect::back()->with('success', 'Movimento registrato con successo!');
    }
/*questa funzoine è per lo scarico a magazzino dal dettaglio del cantiere*/
    public function scaricaArticolo()
    {
        $id_articolo = $_GET['id_articolo'];
        $id_cantiere = $_GET['id_cantiere'];
        $quantita = $_GET['quantita'];
        $nome_cantiere = $_GET['nome_cantiere'];
        $id_azienda = session('utente')->id_azienda ?? 1;

        // Recupera i dati dell'articolo
        $articolo = DB::table('articoli')->where('id', $id_articolo)->first();

        if (!$articolo) {
            return response()->json(['success' => false, 'message' => 'Articolo non trovato']);
        }

        // Calcola il costo totale per questa quantità
        $costo_scarico = $articolo->costo * $quantita;

        // 🔹 CONTROLLA SE È IMPEGNATO IN QUESTO CANTIERE
        $impegno = DB::table('impegni_magazzino')
            ->where('id_articolo', $id_articolo)
            ->where('id_cantiere', $id_cantiere)
            ->first();

        // Se è impegnato, rimuovi dall'impegno
        if ($impegno && $impegno->quantita_impegnata >= $quantita) {
            DB::table('impegni_magazzino')
                ->where('id_articolo', $id_articolo)
                ->where('id_cantiere', $id_cantiere)
                ->decrement('quantita_impegnata', $quantita);

            // Aggiorna quantità impegnata dell'articolo
            DB::table('articoli')
                ->where('id', $id_articolo)
                ->decrement('quantita_impegnata', $quantita);
        }

        // Scala sempre dalla quantità totale dell'articolo
        DB::table('articoli')
            ->where('id', $id_articolo)
            ->decrement('quantita', $quantita);

        // AGGIORNA IL COSTO TOTALE DEL CANTIERE
        DB::table('cantieri')
            ->where('id', $id_cantiere)
            ->increment('costo_totale', $costo_scarico);

        // Registra il movimento
        DB::table('mgmov')->insert([
            'id_articolo' => $id_articolo,
            'id_azienda' => $id_azienda,
            'id_cantiere' => $id_cantiere,
            'id_utente' => session('utente')->id,
            'causale' => 'Scarico',
            'qta' => $quantita,
            'datamov' => now(),
        ]);

        // Calcola le nuove quantità
        $articolo_aggiornato = DB::table('articoli')->where('id', $id_articolo)->first();

        return response()->json([
            'success' => true,
            'nuova_quantita_impegnata' => $articolo_aggiornato->quantita_impegnata,
            'nuova_quantita_articolo' => $articolo_aggiornato->quantita,
            'costo_scaricato' => number_format($costo_scarico, 2, ',', '.')
        ]);
    }



    public function movimenti(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        // Filtri opzionali
        $query = "SELECT mgmov.*, utenti.nome AS utente_nome, utenti.cognome AS utente_cognome, articoli.titolo AS articolo_nome 
              FROM mgmov
              JOIN utenti ON mgmov.id_utente = utenti.id
              JOIN articoli ON mgmov.id_articolo = articoli.id
              WHERE mgmov.id_azienda = ?";

        $params = [$utente->id_azienda];

        if ($request->has('causale') && !empty($request->causale)) {
            $query .= " AND mgmov.causale = ?";
            $params[] = $request->causale;
        }

        if ($request->has('articolo') && !empty($request->articolo)) {
            $query .= " AND mgmov.id_articolo = ?";
            $params[] = $request->articolo;
        }

        $query .= " ORDER BY mgmov.datamov DESC";

        $movimenti = DB::select($query, $params);
        $articoli = DB::select("SELECT id, titolo FROM articoli WHERE id_azienda = ?", [$utente->id_azienda]);

        return View::make('azienda.mgmov', compact('movimenti', 'articoli', 'utente'));
    }

    public function impegnaArticolo(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        $id_articolo = $request->id_articolo;
        $id_cantiere = $request->id_cantiere;
        $quantita = $request->quantita ?? 1; // Se non specificata, default a 1

        // Recupera l'articolo
        $articolo = DB::table('articoli')
            ->where('id', $id_articolo)
            ->where('id_azienda', $utente->id_azienda)
            ->first();

        if (!$articolo) {
            return response()->json(['success' => false, 'message' => 'Articolo non trovato'], 404);
        }

        // Controllo disponibilità per i materiali
        if ($articolo->tipologia == 1 && ($articolo->quantita_impegnata + $quantita > $articolo->quantita)) {
            return response()->json(['success' => false, 'message' => 'Quantità non disponibile'], 400);
        }

        // Controlla se l'articolo è già stato impegnato in questo cantiere
        $impegno = DB::table('impegni_magazzino')
            ->where('id_articolo', $id_articolo)
            ->where('id_cantiere', $id_cantiere)
            ->where('id_azienda', $utente->id_azienda)
            ->first();

        if ($impegno) {
            // Se esiste già, aggiorniamo la quantità
            DB::table('impegni_magazzino')
                ->where('id', $impegno->id)
                ->increment('quantita_impegnata', $quantita);
        } else {
            // Creiamo un nuovo record di impegno
            DB::table('impegni_magazzino')->insert([
                'id_articolo' => $id_articolo,
                'id_cantiere' => $id_cantiere,
                'quantita_impegnata' => $quantita,
                'id_azienda' => $utente->id_azienda
            ]);
        }

        // Aggiorniamo la quantità impegnata nella tabella articoli
        DB::table('articoli')
            ->where('id', $id_articolo)
            ->increment('quantita_impegnata', $quantita);


        return response()->json([
            'success' => true,
            'message' => 'Articolo impegnato con successo!',
            'nuova_quantita_impegnata' => $articolo->quantita_impegnata + $quantita,
            'nuova_quantita_disponibile' => $articolo->quantita - $articolo->quantita_impegnata - $quantita
        ]);
    }


    public function rimuoviImpegno(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        $id_articolo = $request->id_articolo;
        $id_cantiere = $request->id_cantiere;
        $quantita = $request->quantita ?? 1; // Default 1 per strumenti

        // Recupera l'impegno
        $impegno = DB::table('impegni_magazzino')
            ->where('id_articolo', $id_articolo)
            ->where('id_cantiere', $id_cantiere)
            ->where('id_azienda', $utente->id_azienda)
            ->first();

        if (!$impegno) {
            return response()->json(['success' => false, 'message' => 'Impegno non trovato'], 404);
        }

        // Recupera l'articolo
        $articolo = DB::table('articoli')
            ->where('id', $id_articolo)
            ->where('id_azienda', $utente->id_azienda)
            ->first();

        if (!$articolo) {
            return response()->json(['success' => false, 'message' => 'Articolo non trovato'], 404);
        }

        if ($articolo->tipologia == 1) { // Materiale
            if ($impegno->quantita_impegnata <= $quantita) {
                // Se la quantità da rimuovere è uguale o maggiore a quella impegnata, elimina l'impegno
                DB::table('impegni_magazzino')
                    ->where('id_articolo', $id_articolo)
                    ->where('id_cantiere', $id_cantiere)
                    ->where('id_azienda', $utente->id_azienda)
                    ->delete();
            } else {
                // Altrimenti, decrementa la quantità impegnata
                DB::table('impegni_magazzino')
                    ->where('id_articolo', $id_articolo)
                    ->where('id_cantiere', $id_cantiere)
                    ->where('id_azienda', $utente->id_azienda)
                    ->decrement('quantita_impegnata', $quantita);
            }
        } else { // Strumenti
            // Gli strumenti vengono sempre eliminati completamente
            DB::table('impegni_magazzino')
                ->where('id_articolo', $id_articolo)
                ->where('id_cantiere', $id_cantiere)
                ->where('id_azienda', $utente->id_azienda)
                ->delete();
        }

        // Aggiorna la quantità impegnata nella tabella articoli
        DB::table('articoli')
            ->where('id', $id_articolo)
            ->decrement('quantita_impegnata', $quantita);

        return response()->json([
            'success' => true,
            'message' => 'Impegno rimosso con successo!',
            'nuova_quantita_impegnata' => max(0, $articolo->quantita_impegnata - $quantita)
        ]);
    }


    /* public function rimuoviArticolo(Request $request)
     {
         $this->is_loggato();
         $dati = $request->all();
         $utente = session('utente');

         $articolo = DB::table('articoli')
             ->where('id', $dati['id_articolo'])
             ->where('id_azienda', $utente->id_azienda)
             ->first();

         if (!$articolo || $articolo->quantita_impegnata == 0) {
             return response()->json(['success' => false, 'message' => 'Nessuna quantità impegnata.']);
         }

         DB::table('articoli')
             ->where('id', $articolo->id)
             ->decrement('quantita_impegnata', 1);

         return response()->json([
             'success' => true,
             'nuova_quantita_impegnata' => $articolo->quantita_impegnata - 1
         ]);
     }*/


    public function anagraficaMezzi(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');
        $dati = $request->all();

        if(isset($dati['aggiungi'])) {
            unset($dati['aggiungi']);
            $dati['id_azienda'] = $utente->id_azienda;
            DB::table('mezzi')->insert($dati);
            return Redirect::to('azienda/mezzi');
        }

        if(isset($dati['modifica'])) {
            unset($dati['modifica']);
            DB::table('mezzi')->where('id', $dati['id'])->where('id_azienda', $utente->id_azienda)->update($dati);
            return Redirect::to('azienda/mezzi');
        }

        if(isset($dati['elimina'])) {
            unset($dati['elimina']);
            DB::table('mezzi')->where('id', $dati['id'])->where('id_azienda', $utente->id_azienda)->delete();
            return Redirect::to('azienda/mezzi');
        }

        $mezzi = DB::table('mezzi')->where('id_azienda', $utente->id_azienda)->get();

        return View::make('azienda.anagrafica_mezzi', compact('utente', 'mezzi'));
    }




    public function profilo(Request $request)
    {

        $this->is_loggato();
        $utente_sessione = session('utente');
        $ruolo = session('ruolo');


        $dati = $request->all();


        if(isset($dati['modifica'])){
            unset($dati['modifica']);

            if($_FILES['immagine-user']['name'] != ''){

                $vecchio_path_utente = DB::table('utenti')->where('id', $dati['id'])->first();

                if (file_exists($vecchio_path_utente->immagine)) {
                    unlink($vecchio_path_utente->immagine);
                }

                $pathinfo = pathinfo($_FILES['immagine-user']['name']);
                $nome = Str::random(35);
                if ($utente_sessione->super_admin == 1){
                    $target = 'immagini_user_super_admin/' .$nome.'.'.$pathinfo['extension'];
                }else{
                    $target = 'immagini_user/' .$nome.'.'.$pathinfo['extension'];

                }
                move_uploaded_file($_FILES['immagine-user']['tmp_name'], $target);
                $dati['immagine-user'] = $target;
            }


            DB::table('utenti')->where('id', $dati['id'])->update([
                'nome' => $dati['nome'],
                'cognome' => $dati['cognome'],
                'data_nascita' => $dati['data_nascita'],
                'luogo_nascita' => $dati['luogo_nascita'],
                'email' => $dati['email'],
                'password' => $dati['password'],
                'telefono' => $dati['telefono']
            ]);

            if(isset($dati['immagine-user'])){
                DB::table('utenti')->where('id', $dati['id'])->update([
                    'immagine' => $dati['immagine-user'],
                ]);
            }


            return Redirect::to('azienda/profilo');

        }

        $utente = Db::table('utenti')->where('id', $utente_sessione->id)->where('id_azienda', $utente_sessione->id_azienda)->first();

        return View::make('azienda.profilo', compact( 'utente', 'ruolo'));

    }

// Funzione per aggiornare i km del mezzo
    public function aggiornaKmMezzo(Request $request, $id) {
        $this->is_loggato();
        $utente = session('utente');

        DB::table('mezzi')
            ->where('id', $id)
            ->where('id_azienda', $utente->id_azienda)
            ->update([
                'km_attuali' => $request->km_attuali,
                'updated_at' => now()
            ]);

        return Redirect::to('azienda/mezzo/' . $id)
            ->with('success', 'Km aggiornati con successo!');
    }

// Funzione per sostituire una gomma
    // Funzione per sostituire una gomma con registrazione costo
    public function sostituisciGomma(Request $request, $id) {
        $this->is_loggato();
        $utente = session('utente');

        // Validazione dati
        $request->validate([
            'posizione' => 'required|string',
            'data_sostituzione' => 'required|date',
            'km_sostituzione' => 'required|numeric|min:0',
            'costo' => 'nullable|numeric|min:0',
            'fornitore' => 'nullable|string|max:255',
            'marca_modello' => 'nullable|string|max:255',
            'note' => 'nullable|string'
        ]);

        // Ottieni i dati del mezzo per verificare che esista
        $mezzo = DB::table('mezzi')
            ->where('id', $id)
            ->where('id_azienda', $utente->id_azienda)
            ->first();

        if (!$mezzo) {
            return redirect()->back()->with('error', 'Mezzo non trovato.');
        }

        // Prepara le note complete
        $note_complete = [];
        if ($request->marca_modello) {
            $note_complete[] = "Gomma: " . $request->marca_modello;
        }
        if ($request->fornitore) {
            $note_complete[] = "Fornitore: " . $request->fornitore;
        }
        if ($request->note) {
            $note_complete[] = $request->note;
        }
        $note_finali = implode(" | ", $note_complete);

        try {
            DB::beginTransaction();

            // 1. Inserisci la sostituzione nella tabella mezzi_gomme
            $id_sostituzione = DB::table('mezzi_gomme')->insertGetId([
                'id_mezzo' => $id,
                'id_azienda' => $utente->id_azienda,
                'posizione' => $request->posizione,
                'data_sostituzione' => $request->data_sostituzione,
                'km_sostituzione' => $request->km_sostituzione,
                'costo' => $request->costo ?? 0,
                'fornitore' => $request->fornitore,
                'marca_modello' => $request->marca_modello,
                'note' => $note_finali,
                'created_at' => now()
            ]);

            // 2. Se è stato inserito un costo, registra anche la manutenzione
            if ($request->costo && $request->costo > 0) {
                $posizione_italiana = $this->traduciPosizione($request->posizione);

                $descrizione_manutenzione = "Sostituzione gomma " . $posizione_italiana;
                if ($request->marca_modello) {
                    $descrizione_manutenzione .= " - " . $request->marca_modello;
                }
                if ($request->fornitore) {
                    $descrizione_manutenzione .= " (presso " . $request->fornitore . ")";
                }

                DB::table('mezzi_manutenzioni')->insert([
                    'id_mezzo' => $id,
                    'id_azienda' => $utente->id_azienda,
                    'tipo' => 'Sostituzione Gomma',
                    'descrizione' => $descrizione_manutenzione,
                    'importo' => $request->costo,
                    'data_operazione' => $request->data_sostituzione,
                    'km_operazione' => $request->km_sostituzione,
                    'riferimento_gomma_id' => $id_sostituzione, // Collegamento alla sostituzione
                    'created_at' => now()
                ]);
            }

            DB::commit();

            $messaggio = 'Sostituzione gomma registrata con successo!';
            if ($request->costo && $request->costo > 0) {
                $messaggio .= ' Il costo è stato registrato nelle manutenzioni.';
            }

            return Redirect::to('azienda/mezzo/' . $id)
                ->with('success', $messaggio);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Errore durante la registrazione della sostituzione: ' . $e->getMessage())
                ->withInput();
        }
    }

// Funzione helper per tradurre le posizioni
    private function traduciPosizione($posizione) {
        $traduzioni = [
            'anteriore_sx' => 'Anteriore Sinistra',
            'anteriore_dx' => 'Anteriore Destra',
            'posteriore_sx' => 'Posteriore Sinistra',
            'posteriore_dx' => 'Posteriore Destra'
        ];

        return $traduzioni[$posizione] ?? $posizione;
    }

// Funzione per salvare le impostazioni delle gomme
    public function impostazioniMezzo(Request $request, $id) {
        $this->is_loggato();
        $utente = session('utente');

        DB::table('mezzi')
            ->where('id', $id)
            ->where('id_azienda', $utente->id_azienda)
            ->update([
                'km_warning' => $request->km_warning ?? 30000,
                'km_danger' => $request->km_danger ?? 50000,
                'updated_at' => now()
            ]);

        return Redirect::to('azienda/mezzo/' . $id)
            ->with('success', 'Impostazioni salvate con successo!');
    }

// Funzione helper per calcolare lo stato delle gomme
    // Funzione helper per calcolare lo stato delle gomme
    private function calcolaStatiGomme($mezzo, $sostituzioni_gomme) {
        $posizioni = ['anteriore_sx', 'anteriore_dx', 'posteriore_sx', 'posteriore_dx'];
        $stati = [];

        $km_warning = $mezzo->km_warning ?? 30000;
        $km_danger = $mezzo->km_danger ?? 50000;
        $km_attuali = $mezzo->km_attuali ?? 0;

        foreach ($posizioni as $posizione) {
            // Trova l'ultima sostituzione per questa posizione
            $ultima_sostituzione = collect($sostituzioni_gomme)
                ->where('posizione', $posizione)
                ->first();

            if ($ultima_sostituzione) {
                // Se c'è stata una sostituzione, calcola i km dalla sostituzione
                $km_percorsi_dalla_sostituzione = $km_attuali - $ultima_sostituzione->km_sostituzione;

                // Assicurati che non sia negativo (nel caso qualcuno inserisca dati errati)
                $km_percorsi_dalla_sostituzione = max(0, $km_percorsi_dalla_sostituzione);
            } else {
                // Se non c'è mai stata una sostituzione, considera i km totali del veicolo
                // (assumendo che le gomme originali siano state montate a km 0)
                $km_percorsi_dalla_sostituzione = $km_attuali;
            }

            // Determina lo stato in base ai km percorsi DALLA SOSTITUZIONE
            if ($km_percorsi_dalla_sostituzione >= $km_danger) {
                $stato = 'danger'; // Rosso - da cambiare
                $colore = '#dc3545';
                $messaggio = 'Da cambiare';
            } elseif ($km_percorsi_dalla_sostituzione >= $km_warning) {
                $stato = 'warning'; // Arancione - da controllare
                $colore = '#ffc107';
                $messaggio = 'Da controllare';
            } else {
                $stato = 'success'; // Verde - buona
                $colore = '#28a745';
                $messaggio = 'Buona';
            }

            $stati[$posizione] = [
                'stato' => $stato,
                'colore' => $colore,
                'km_percorsi_dalla_sostituzione' => $km_percorsi_dalla_sostituzione,
                'km_rimanenti_warning' => max(0, $km_warning - $km_percorsi_dalla_sostituzione),
                'km_rimanenti_danger' => max(0, $km_danger - $km_percorsi_dalla_sostituzione),
                'messaggio' => $messaggio,
                'ultima_sostituzione' => $ultima_sostituzione,
                'data_ultima_sostituzione' => $ultima_sostituzione ? $ultima_sostituzione->data_sostituzione : null,
                'km_ultima_sostituzione' => $ultima_sostituzione ? $ultima_sostituzione->km_sostituzione : 0
            ];
        }

        return $stati;
    }




    public function modificaStatoMezzo(Request $request, $id)
    {
        $this->is_loggato();
        $utente = session('utente');

        // Aggiorna lo stato del mezzo
        DB::table('mezzi')
            ->where('id', $id)
            ->where('id_azienda', $utente->id_azienda)
            ->update(['stato' => $request->stato]);

        return Redirect::to('azienda/mezzo/' . $id)->with('success', 'Stato aggiornato con successo!');
    }

    public function updateVistaOperaio(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        // Aggiorna il campo vista_operaio
        DB::table('utenti')
            ->where('id', $request->id)
            ->where('id_azienda', $utente->id_azienda)
            ->update(['vista_operaio' => $request->vista_operaio]);

        return response()->json(['message' => 'Vista operaio aggiornata con successo.']);
    }

    public function vistaOperaio(Request $request) {
        $this->is_loggato();
        $utente = session('utente');
        $today = date('Y-m-d');

        $dati = $request->all();

        // Handle clock in
        if(isset($dati['inizio_lavoro'])) {
            DB::table('presenze')->insert([
                'id_dipendente' => $utente->id,
                'id_azienda' => $utente->id_azienda,
                'data' => $today,
                'ora_inizio' => date('H:i:s'),
                'id_cantiere' => $dati['id_cantiere'],
                'id_attivita' => $dati['id_attivita'] ?? null,
                'lat_inizio' => $dati['lat_inizio'],
                'long_inizio' => $dati['long_inizio'],
                'tipo_registrazione' => 'automatica',
                'created_at' => now()
            ]);
            return Redirect::to('azienda/vista_cantiere');
        }

        // Handle clock out
        if(isset($dati['fine_lavoro'])) {
            DB::table('presenze')
                ->where('id_dipendente', $utente->id)
                ->where('data', $today)
                ->whereNull('ora_fine')
                ->update([
                    'ora_fine' => date('H:i:s'),
                    'lat_fine' => $dati['lat_fine'],
                    'long_fine' => $dati['long_fine'],
                    'updated_at' => now()
                ]);
            return Redirect::to('azienda/vista_cantiere');
        }

        // ✅ QUERY OTTIMIZZATA: Ora usa direttamente id_cantiere dalla tabella cantieri_attivita_dipendenti
        // ✅ QUERY CORRETTA - Specifica esattamente da quale tabella prendere la descrizione
        $attivita_corrente = DB::select("
    SELECT 
        ca.id as id_attivita,
        ca.descrizione as attivita_descrizione,
        ca.data_inizio as attivita_inizio,
        ca.data_fine as attivita_fine,
        ca.id_cantiere,
        c.titolo as cantiere_titolo,
        c.immagine,
        c.descrizione as cantiere_descrizione
    FROM cantieri_attivita_dipendenti cad
    JOIN cantieri_attivita ca ON ca.id = cad.id_attivita
    JOIN cantieri c ON c.id = ca.id_cantiere
    WHERE cad.id_dipendente = ?
    AND c.id_azienda = ?
    AND ? BETWEEN ca.data_inizio AND ca.data_fine
    ORDER BY ca.data_inizio ASC
    LIMIT 1
", [$utente->id, $utente->id_azienda, $today]);



        // Get today's timesheet entry if exists
        $presenza_oggi = DB::select("
        SELECT * FROM presenze 
        WHERE id_dipendente = ? 
        AND data = ?
        ORDER BY id DESC
        LIMIT 1
    ", [$utente->id, $today]);


        return View::make('azienda.vista_operaio', compact('utente', 'attivita_corrente', 'presenza_oggi'));
    }



    public function saveCantiere(Request $request)
    {
        // Recupera l'id_azienda dalla sessione
        $id_azienda = session('utente')->id_azienda ?? 1; // Se non esiste, metti un valore di default
        // Gestione dell'upload immagine
        $immagine = null;
        if ($request->hasFile('immagine')) {
            $immagine = $request->file('immagine')->store('uploads/cantieri', 'public');
        }

        // Inserimento nel database
        DB::table('cantieri')->insert([
            'id_azienda' => $id_azienda,
            'titolo' => $request->titolo,
            'descrizione' => $request->descrizione,
            'immagine' => $immagine,
            'data_inizio' => $request->data_inizio,
            'data_fine' => $request->data_fine,
            'costo_stimato' => $request->costo_stimato,
            'valore_stimato' => $request->valore_stimato,

        ]);

        return response()->json(['success' => true, 'message' => 'Cantiere salvato con successo']);
    }


    public function getAttivitaDipendente($id) {
        // Query diretta per ottenere le attività
        $attivita = DB::table('cantieri_attivita')
            ->join('cantieri_attivita_dipendenti', 'cantieri_attivita.id', '=', 'cantieri_attivita_dipendenti.id_attivita')
            ->where('cantieri_attivita_dipendenti.id_dipendente', $id)
            ->select('cantieri_attivita.*')
            ->get();

        return response()->json($attivita);
    }

    public function getDipendentiAttivita($id) {
        $utente = session('utente');

        // Trova l'ID del cantiere associato a questa attività
        $id_cantiere = DB::table('cantieri_attivita')
            ->where('id', $id)
            ->value('id_cantiere');

        // Dipendenti già assegnati a questa attività
        $assegnati = DB::table('utenti')
            ->join('cantieri_attivita_dipendenti', 'utenti.id', '=', 'cantieri_attivita_dipendenti.id_dipendente')
            ->where('cantieri_attivita_dipendenti.id_attivita', $id)
            ->where('utenti.admin_azienda', 2)
            ->where('utenti.id_azienda', $utente->id_azienda)
            ->select('utenti.*')
            ->get();

        // Dipendenti disponibili per questa attività: solo quelli già assegnati al cantiere
        $disponibili = DB::table('utenti')
            ->join('cantieri_operai', 'utenti.id', '=', 'cantieri_operai.id_dipendente') // Prendiamo solo quelli già assegnati al cantiere
            ->where('cantieri_operai.id_cantiere', $id_cantiere)
            ->where('utenti.admin_azienda', 2)
            ->where('utenti.id_azienda', $utente->id_azienda)
            ->whereNotIn('utenti.id', function($query) use ($id) {
                $query->select('id_dipendente')
                    ->from('cantieri_attivita_dipendenti')
                    ->where('id_attivita', $id);
            })
            ->select('utenti.*')
            ->get();

        return response()->json([
            'assegnati' => $assegnati,
            'disponibili' => $disponibili
        ]);
    }

    public function salvaDipendentiAttivita(Request $request) {
        $attivitaId = $request->input('attivita_id');
        $dipendenti = $request->input('dipendenti');

        // Cancella le assegnazioni esistenti
        DB::table('cantieri_attivita_dipendenti')->where('id_attivita', $attivitaId)->delete();

        // Inserisce le nuove assegnazioni
        foreach($dipendenti as $dipendenteId) {
            DB::table('cantieri_attivita_dipendenti')->insert([
                'id_attivita' => $attivitaId,
                'id_dipendente' => $dipendenteId
            ]);
        }

        return response()->json(['success' => true]);
    }
    public function aggiornaDipendenti(Request $request)
    {
        $attivita = Attivita::find($request->attivita_id);
        $attivita->dipendenti()->sync($request->dipendenti);

        return response()->json(['success' => true]);
    }

    public function cantieri()
    {
        $session = Session::get('utente');
        if (!$session) {
            return redirect('admin/login');
        }

        $utente = $session;
        $cantieri = DB::table('cantieri')->where('id_azienda', $session->id_azienda)->get();

        // Recupera lista responsabili per il form
        $responsabili = DB::table('utenti')
            ->where('id_azienda', $session->id_azienda)
            ->where('is_responsabile', 1)
            ->select('id', 'nome', 'cognome')
            ->get();

        // Recupera responsabili per ogni cantiere per mostrarli nella tabella
        $responsabiliCantieri = [];
        foreach ($cantieri as $cantiere) {
            $responsabiliCantieri[$cantiere->id] = DB::select("
        SELECT cr.*, u.nome, u.cognome 
        FROM cantieri_responsabili cr 
        JOIN utenti u ON cr.id_responsabile = u.id 
        WHERE cr.id_cantiere = ?
        ORDER BY cr.id
    ", [$cantiere->id]);
        }

        // Gestione POST
        if ($_POST) {
            if (isset($_POST['aggiungi'])) {
                // AGGIUNTA CANTIERE
                $cantiereId = DB::table('cantieri')->insertGetId([
                    'titolo' => $_POST['titolo'],
                    'descrizione' => $_POST['descrizione'],
                    'data_inizio' => $_POST['data_inizio'],
                    'data_fine' => $_POST['data_fine'],
                    'costo_stimato' => $_POST['costo_stimato'],
                    'valore_stimato' => $_POST['valore_stimato'],
                    'indirizzo' => $_POST['indirizzo'] ?? null,
                    'latitudine' => $_POST['latitudine'] ?? null,
                    'longitudine' => $_POST['longitudine'] ?? null,
                    'colore' => $_POST['colore'] ?? '#007bff',
                    'contabilizzato' => $_POST['contabilizzato'] ?? 1, // ✅ AGGIUNTO
                    'id_azienda' => $session->id_azienda,
                    'stato' => 1,
                    'costo_totale' => 0,
                    'valore_totale' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Gestione responsabile
                if (!empty($_POST['responsabile']) && !empty($_POST['percentuale'])) {
                    DB::table('cantieri_responsabili')->insert([
                        'id_cantiere' => $cantiereId,
                        'id_responsabile' => $_POST['responsabile'],
                        'percentuale' => $_POST['percentuale'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

            } elseif (isset($_POST['modifica'])) {
                // MODIFICA CANTIERE
                $cantiereId = $_POST['id'];

                // Aggiorna dati cantiere
                DB::table('cantieri')->where('id', $cantiereId)->update([
                    'titolo' => $_POST['titolo'],
                    'descrizione' => $_POST['descrizione'],
                    'data_inizio' => $_POST['data_inizio'],
                    'data_fine' => $_POST['data_fine'],
                    'costo_stimato' => $_POST['costo_stimato'],
                    'valore_stimato' => $_POST['valore_stimato'],
                    'indirizzo' => $_POST['indirizzo'] ?? null,
                    'latitudine' => $_POST['latitudine'] ?? null,
                    'longitudine' => $_POST['longitudine'] ?? null,
                    'colore' => $_POST['colore'] ?? '#007bff',
                    'contabilizzato' => $_POST['contabilizzato'] ?? 1, // ✅ AGGIUNTO
                    'updated_at' => now()
                ]);

                // Rimuovi responsabile esistente
                DB::table('cantieri_responsabili')->where('id_cantiere', $cantiereId)->delete();

                // Aggiungi nuovo responsabile se specificato
                if (!empty($_POST['responsabile']) && !empty($_POST['percentuale'])) {
                    DB::table('cantieri_responsabili')->insert([
                        'id_cantiere' => $cantiereId,
                        'id_responsabile' => $_POST['responsabile'],
                        'percentuale' => $_POST['percentuale'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

            } elseif (isset($_POST['elimina'])) {
                // ELIMINAZIONE CANTIERE
                $cantiereId = $_POST['id'];

                // Prima elimina le associazioni responsabili
                DB::table('cantieri_responsabili')->where('id_cantiere', $cantiereId)->delete();

                // ✅ AGGIUNTO: Elimina anche i pagamenti se esistono
                DB::table('cantieri_pagamenti')->where('id_cantiere', $cantiereId)->delete();

                // Poi elimina il cantiere
                DB::table('cantieri')->where('id', $cantiereId)->delete();
            }

            return redirect('/azienda/cantieri');
        }

        return view('azienda.cantieri', compact('cantieri', 'responsabili', 'responsabiliCantieri', 'utente'));
    }
// Aggiungi questo metodo al AziendaController

    public function responsabili()
    {
        $session = Session::get('utente');
        if (!$session) {
            return redirect('admin/login');
        }

        $utente = $session;

        // Recupera tutti i responsabili con i loro cantieri
        $responsabili = DB::select("
        SELECT 
            u.id,
            u.nome,
            u.cognome,
            u.email
        FROM utenti u
        WHERE u.id_azienda = ? 
        AND u.is_responsabile = 1
        ORDER BY u.nome, u.cognome
    ", [$session->id_azienda]);

        // Per ogni responsabile, recupera i suoi cantieri
        foreach ($responsabili as $responsabile) {
            $responsabile->cantieri = DB::select("
            SELECT 
                c.*,
                cr.percentuale,
                cr.id_cantiere
            FROM cantieri_responsabili cr
            JOIN cantieri c ON cr.id_cantiere = c.id
            WHERE cr.id_responsabile = ?
            ORDER BY c.data_inizio DESC
        ", [$responsabile->id]);
        }

        return view('azienda.responsabili', compact('responsabili', 'utente'));
    }
    /**
     * Recupera responsabili di un cantiere
     */
    public function getResponsabiliCantiere($cantiereId)
    {
        $responsabili = DB::select("
        SELECT cr.*, u.nome, u.cognome 
        FROM cantieri_responsabili cr 
        JOIN utenti u ON cr.id_responsabile = u.id 
        WHERE cr.id_cantiere = ?
    ", [$cantiereId]);

        return response()->json($responsabili);
    }

// Aggiungi al tuo AziendaController

    public function reportResponsabiliPDF($tipo = 'completo', $id_responsabile = null)
    {
        $this->is_loggato();
        $utente = session('utente');

        // Recupera i dati
        if ($id_responsabile) {
            // Report singolo responsabile
            $responsabili = DB::select("
            SELECT u.id, u.nome, u.cognome, u.email
            FROM utenti u
            WHERE u.id_azienda = ? AND u.is_responsabile = 1 AND u.id = ?
            ORDER BY u.nome, u.cognome
        ", [$utente->id_azienda, $id_responsabile]);
        } else {
            // Report tutti i responsabili
            $responsabili = DB::select("
            SELECT u.id, u.nome, u.cognome, u.email
            FROM utenti u
            WHERE u.id_azienda = ? AND u.is_responsabile = 1
            ORDER BY u.nome, u.cognome
        ", [$utente->id_azienda]);
        }

        // Per ogni responsabile, recupera i cantieri
        foreach ($responsabili as $responsabile) {
            $query = "
            SELECT c.*, cr.percentuale, cr.id_cantiere
            FROM cantieri_responsabili cr
            JOIN cantieri c ON cr.id_cantiere = c.id
            WHERE cr.id_responsabile = ?
        ";

            if ($tipo === 'attivi') {
                $query .= " AND c.stato = 1";
            }

            $query .= " ORDER BY c.data_inizio DESC";

            $responsabile->cantieri = DB::select($query, [$responsabile->id]);
        }

        // Genera HTML per il PDF
        $html = $this->generaHTMLReportResponsabili($responsabili, $tipo, $utente);

        // Crea PDF con mPDF
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 16,
            'margin_bottom' => 16,
            'margin_header' => 9,
            'margin_footer' => 9
        ]);

        $mpdf->WriteHTML($html);

        $filename = 'Report_Responsabili_' . ($tipo === 'attivi' ? 'Attivi_' : '') . date('Y-m-d_H-i-s') . '.pdf';

        return $mpdf->Output($filename, 'D'); // 'D' per download
    }

    public function reportResponsabiliExcel($tipo = 'completo', $id_responsabile = null)
    {
        $this->is_loggato();
        $utente = session('utente');

        // Recupera i dati (stesso codice del PDF)
        if ($id_responsabile) {
            $responsabili = DB::select("
            SELECT u.id, u.nome, u.cognome, u.email
            FROM utenti u
            WHERE u.id_azienda = ? AND u.is_responsabile = 1 AND u.id = ?
            ORDER BY u.nome, u.cognome
        ", [$utente->id_azienda, $id_responsabile]);
        } else {
            $responsabili = DB::select("
            SELECT u.id, u.nome, u.cognome, u.email
            FROM utenti u
            WHERE u.id_azienda = ? AND u.is_responsabile = 1
            ORDER BY u.nome, u.cognome
        ", [$utente->id_azienda]);
        }

        foreach ($responsabili as $responsabile) {
            $query = "
            SELECT c.*, cr.percentuale, cr.id_cantiere
            FROM cantieri_responsabili cr
            JOIN cantieri c ON cr.id_cantiere = c.id
            WHERE cr.id_responsabile = ?
        ";

            if ($tipo === 'attivi') {
                $query .= " AND c.stato = 1";
            }

            $query .= " ORDER BY c.data_inizio DESC";

            $responsabile->cantieri = DB::select($query, [$responsabile->id]);
        }

        // Crea Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Intestazione
        $sheet->setCellValue('A1', 'REPORT RESPONSABILI CANTIERI');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'Generato il: ' . date('d/m/Y H:i'));
        $sheet->mergeCells('A2:H2');

        $row = 4;

        foreach ($responsabili as $responsabile) {
            // Header responsabile
            $sheet->setCellValue('A' . $row, 'RESPONSABILE: ' . $responsabile->nome . ' ' . $responsabile->cognome);
            $sheet->mergeCells('A' . $row . ':H' . $row);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $sheet->getStyle('A' . $row)->getFill()->getStartColor()->setARGB('FFCCCCCC');
            $row++;

            $sheet->setCellValue('A' . $row, 'Email: ' . $responsabile->email);
            $sheet->mergeCells('A' . $row . ':H' . $row);
            $row++;

            if (!empty($responsabile->cantieri)) {
                // Header tabella cantieri
                $headers = ['Titolo', 'Descrizione', 'Data Inizio', 'Data Fine', 'Percentuale', 'Stato', 'Valore Stimato', 'Costo Stimato'];
                foreach ($headers as $i => $header) {
                    $sheet->setCellValue(chr(65 + $i) . $row, $header);
                    $sheet->getStyle(chr(65 + $i) . $row)->getFont()->setBold(true);
                }
                $row++;

                // Dati cantieri
                foreach ($responsabile->cantieri as $cantiere) {
                    $stato = $cantiere->stato == 1 ? 'Attivo' : ($cantiere->stato == 2 ? 'Sospeso' : 'Chiuso');

                    $sheet->setCellValue('A' . $row, $cantiere->titolo);
                    $sheet->setCellValue('B' . $row, $cantiere->descrizione);
                    $sheet->setCellValue('C' . $row, date('d/m/Y', strtotime($cantiere->data_inizio)));
                    $sheet->setCellValue('D' . $row, date('d/m/Y', strtotime($cantiere->data_fine)));
                    $sheet->setCellValue('E' . $row, $cantiere->percentuale . '%');
                    $sheet->setCellValue('F' . $row, $stato);
                    $sheet->setCellValue('G' . $row, '€ ' . number_format($cantiere->valore_stimato ?? 0, 2, ',', '.'));
                    $sheet->setCellValue('H' . $row, '€ ' . number_format($cantiere->costo_stimato ?? 0, 2, ',', '.'));

                    $row++;
                }

                // Totali
                $totale_percentuale = array_sum(array_column($responsabile->cantieri, 'percentuale'));
                $totale_valore = array_sum(array_column($responsabile->cantieri, 'valore_stimato'));

                $sheet->setCellValue('D' . $row, 'TOTALI:');
                $sheet->setCellValue('E' . $row, $totale_percentuale . '%');
                $sheet->setCellValue('G' . $row, '€ ' . number_format($totale_valore, 2, ',', '.'));
                $sheet->getStyle('D' . $row . ':H' . $row)->getFont()->setBold(true);

            } else {
                $sheet->setCellValue('A' . $row, 'Nessun cantiere assegnato');
                $sheet->mergeCells('A' . $row . ':H' . $row);
            }

            $row += 3; // Spazio tra responsabili
        }

        // Auto-resize colonne
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Salva e scarica
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'Report_Responsabili_' . ($tipo === 'attivi' ? 'Attivi_' : '') . date('Y-m-d_H-i-s') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    private function generaHTMLReportResponsabili($responsabili, $tipo, $utente)
    {
        $html = '
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; }
        .responsabile { margin-bottom: 30px; page-break-inside: avoid; }
        .responsabile-header { background-color: #f8f9fa; padding: 10px; border: 1px solid #ddd; }
        .cantieri-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .cantieri-table th, .cantieri-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .cantieri-table th { background-color: #e9ecef; font-weight: bold; }
        .stato-attivo { color: #28a745; font-weight: bold; }
        .stato-sospeso { color: #ffc107; font-weight: bold; }
        .stato-chiuso { color: #6c757d; font-weight: bold; }
        .totali { background-color: #f8f9fa; font-weight: bold; }
    </style>
    
    <div class="header">
        <h1>REPORT RESPONSABILI CANTIERI</h1>
        <h3>' . ($tipo === 'attivi' ? 'SOLO CANTIERI ATTIVI' : 'REPORT COMPLETO') . '</h3>
        <p>Generato il: ' . date('d/m/Y H:i:s') . '</p>
    </div>';

        foreach ($responsabili as $responsabile) {
            $html .= '
        <div class="responsabile">
            <div class="responsabile-header">
                <h2>' . $responsabile->nome . ' ' . $responsabile->cognome . '</h2>
                <p><strong>Email:</strong> ' . $responsabile->email . '</p>
                <p><strong>Cantieri Assegnati:</strong> ' . count($responsabile->cantieri) . '</p>
            </div>';

            if (!empty($responsabile->cantieri)) {
                $html .= '
            <table class="cantieri-table">
                <thead>
                    <tr>
                        <th>Titolo</th>
                        <th>Descrizione</th>
                        <th>Data Inizio</th>
                        <th>Data Fine</th>
                        <th>%</th>
                        <th>Stato</th>
                        <th>Valore Stimato</th>
                    </tr>
                </thead>
                <tbody>';

                $totale_percentuale = 0;
                $totale_valore = 0;

                foreach ($responsabile->cantieri as $cantiere) {
                    $stato_classe = $cantiere->stato == 1 ? 'stato-attivo' : ($cantiere->stato == 2 ? 'stato-sospeso' : 'stato-chiuso');
                    $stato_testo = $cantiere->stato == 1 ? 'Attivo' : ($cantiere->stato == 2 ? 'Sospeso' : 'Chiuso');

                    $totale_percentuale += $cantiere->percentuale;
                    $totale_valore += $cantiere->valore_stimato ?? 0;

                    $html .= '
                <tr>
                    <td>' . $cantiere->titolo . '</td>
                    <td>' . substr($cantiere->descrizione, 0, 50) . '...</td>
                    <td>' . date('d/m/Y', strtotime($cantiere->data_inizio)) . '</td>
                    <td>' . date('d/m/Y', strtotime($cantiere->data_fine)) . '</td>
                    <td>' . $cantiere->percentuale . '%</td>
                    <td class="' . $stato_classe . '">' . $stato_testo . '</td>
                    <td>€ ' . number_format($cantiere->valore_stimato ?? 0, 2, ',', '.') . '</td>
                </tr>';
                }

                $html .= '
                <tr class="totali">
                    <td colspan="4"><strong>TOTALI</strong></td>
                    <td><strong>' . $totale_percentuale . '%</strong></td>
                    <td></td>
                    <td><strong>€ ' . number_format($totale_valore, 2, ',', '.') . '</strong></td>
                </tr>
                </tbody>
            </table>';
            } else {
                $html .= '<p><em>Nessun cantiere assegnato</em></p>';
            }

            $html .= '</div>';
        }

        return $html;
    }


    public function updateStato(Request $request)
    {
        try {
            $cantiereId = $request->input('id');
            $nuovoStato = $request->input('stato');

            $cantiere = DB::table('cantieri')->where('id', $cantiereId)->first();
            if (!$cantiere) {
                return response()->json(['error' => 'Cantiere non trovato'], 404);
            }

            // Se il cantiere viene chiuso (stato = 0), trasferisci il costo stimato al costo totale
            if ($nuovoStato == 0 && $cantiere->stato != 0) {
                DB::table('cantieri')
                    ->where('id', $cantiereId)
                    ->update([
                        'stato' => $nuovoStato,
                        'data_chiusura' => now(),
                        'updated_at' => now()
                    ]);
            } else {
                // Aggiornamento normale dello stato
                DB::table('cantieri')
                    ->where('id', $cantiereId)
                    ->update([
                        'stato' => $nuovoStato,
                        'updated_at' => now()
                    ]);
            }

            return response()->json(['success' => true, 'message' => 'Stato cantiere aggiornato']);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function ruoli(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        // ✅ Recuperiamo solo i dati inviati in modo sicuro
        $dati = $request->except(['_token']);

        if ($request->has('aggiungi')) {
            DB::table('ruoli')->insert([
                'titolo' => $dati['titolo'],
                'id_azienda' => $utente->id_azienda
            ]);
            return redirect()->route('azienda.ruoli')->with('success', 'Ruolo aggiunto con successo');
        }

        if ($request->has('modifica')) {
            DB::table('ruoli')->where('id', $dati['id'])->update([
                'titolo' => $dati['titolo']
            ]);
            return redirect()->route('azienda.ruoli')->with('success', 'Ruolo modificato con successo');
        }

        if ($request->has('elimina')) {
            DB::table('ruoli')->where('id', $dati['id'])->delete();
            return redirect()->route('azienda.ruoli')->with('success', 'Ruolo eliminato con successo');
        }

        // ✅ Recuperiamo i ruoli dal database in base all'azienda dell'utente
        $ruoli = DB::table('ruoli')->where('id_azienda', $utente->id_azienda)->get();

        return View::make('azienda.ruoli', compact('utente', 'ruoli'));
    }

    public function recuperaArticolo(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');
        $dati = $request->all();

        $articolo = DB::table('impegni_magazzino')->where('id_azienda', $utente->id_azienda)->where('id_articolo', $dati['id_articolo'])->where('id_cantiere', $dati['id_cantiere'])->first();

        return response()->json(['success' => true, 'articolo' => $articolo]);
    }



    public function dettaglioCantiere($id)
    {
        $this->is_loggato();
        $utente = session('utente');

        // Recupera il cantiere
        $cantiere = DB::table('cantieri')
            ->where('id_azienda', $utente->id_azienda)
            ->where('id', $id)
            ->first();

        if (!$cantiere) {
            abort(404, 'Cantiere non trovato');
        }

        // Recupera i dipendenti (INVARIATO)
        $dipendenti = DB::table('utenti')
            ->leftJoin('ruoli', function ($join) {
                $join->on(DB::raw('FIND_IN_SET(ruoli.id, utenti.id_ruolo)'), '>', DB::raw('0'));
            })
            ->where('utenti.id_azienda', $utente->id_azienda)
            ->where('utenti.admin_azienda', 2)
            ->select(
                'utenti.id',
                'utenti.nome',
                'utenti.cognome',
                DB::raw('GROUP_CONCAT(ruoli.titolo SEPARATOR ", ") as ruoli_titolo')
            )
            ->groupBy('utenti.id', 'utenti.nome', 'utenti.cognome')
            ->get();

        // Recupera le attività con i dipendenti associati (INVARIATO)
        $attivita = DB::table('cantieri_attivita as ca')
            ->select('ca.*', DB::raw('GROUP_CONCAT(u.id) as dipendenti_ids'),
                DB::raw('COUNT(cad.id_dipendente) as numero_dipendenti'))
            ->leftJoin('cantieri_attivita_dipendenti as cad', 'ca.id', '=', 'cad.id_attivita')
            ->leftJoin('utenti as u', 'cad.id_dipendente', '=', 'u.id')
            ->where('ca.id_cantiere', $id)
            ->groupBy('ca.id')
            ->orderBy('ca.data_schedulazione', 'desc')
            ->get();

        // Gestione delle richieste POST (INVARIATO - incluse le nuove presenze manuali)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // ✅ GESTIONE PRESENZE MANUALI (come nel codice precedente)
            if (isset($_POST['azione_presenza']) && $_POST['azione_presenza'] == 'aggiungi_presenza_manuale') {
                try {
                    // Debug per vedere cosa arriva
                    \Log::info('Form presenza ricevuto:', $_POST);

                    if (empty($_POST['id_dipendente']) || empty($_POST['data_presenza']) ||
                        empty($_POST['ora_inizio']) || empty($_POST['ora_fine'])) {
                        return redirect()->back()->with('error', 'Tutti i campi sono obbligatori');
                    }

                    $presenzaEsistente = DB::table('presenze')
                        ->where('id_dipendente', $_POST['id_dipendente'])
                        ->where('data', $_POST['data_presenza'])
                        ->where('id_cantiere', $id)
                        ->first();

                    $dipendente = DB::table('utenti')
                        ->where('id', $_POST['id_dipendente'])
                        ->first();

                    if (!$dipendente) {
                        return redirect()->back()->with('error', 'Dipendente non trovato');
                    }

                    $costoGiornaliero = $dipendente->costo_giornaliero ?? 0;

                    if ($presenzaEsistente) {
                        return redirect()->back()->with('error', 'Presenza già registrata per questo dipendente in questa data');
                    }

                    DB::table('presenze')->insert([
                        'id_dipendente' => $_POST['id_dipendente'],
                        'id_azienda' => $utente->id_azienda,
                        'id_cantiere' => $id,
                        'data' => $_POST['data_presenza'],
                        'ora_inizio' => $_POST['ora_inizio'],
                        'ora_fine' => $_POST['ora_fine'],
                        'note' => $_POST['note_presenza'] ?? null,
                        'tipo_registrazione' => 'manuale',
                        'registrata_da' => $utente->id,
                        'created_at' => now()
                    ]);

                    if ($costoGiornaliero > 0) {
                        DB::table('cantieri')
                            ->where('id', $id)
                            ->where('id_azienda', $utente->id_azienda)
                            ->increment('costo_totale', $costoGiornaliero);

                        $messaggioSuccesso = "Presenza registrata! Costo cantiere aggiornato (+{$costoGiornaliero}€)";
                    } else {
                        $messaggioSuccesso = "Presenza registrata con successo!";
                    }





                    return redirect()->back()->with('success', 'Presenza registrata con successo!');

                } catch (Exception $e) {
                    return redirect()->back()->with('error', 'Errore nella registrazione: ' . $e->getMessage());
                }
            }


            // Sostituisci questa parte nel tuo codice:

            if (isset($_POST['elimina_presenza'])) {
                try {
                    $presenzaId = $_POST['id_presenza'];

                    // ✅ PRIMA recupera la presenza per calcolare il costo da sottrarre
                    $presenzaDaEliminare = DB::table('presenze')
                        ->join('utenti', 'presenze.id_dipendente', '=', 'utenti.id')
                        ->where('presenze.id', $presenzaId)
                        ->where('presenze.id_azienda', $utente->id_azienda)
                        ->select(
                            'presenze.*',
                            'utenti.costo_giornaliero',
                            'utenti.costo_giornaliero',
                            'utenti.nome',
                            'utenti.cognome'
                        )
                        ->first();

                    if (!$presenzaDaEliminare) {
                        return redirect()->back()->with('error', 'Presenza non trovata');
                    }

                    // ✅ Calcola il costo da sottrarre
                    $costoReale = 0;

                    if ($presenzaDaEliminare->costo_giornaliero > 0) {
                        // Se c'è un costo giornaliero fisso
                        $costoReale = $presenzaDaEliminare->costo_giornaliero;
                    } elseif ($presenzaDaEliminare->costo_giornaliero > 0 && $presenzaDaEliminare->ora_inizio && $presenzaDaEliminare->ora_fine) {
                        // Se c'è un costo orario, calcoliamo le ore lavorate
                        $oraInizio = \Carbon\Carbon::parse($presenzaDaEliminare->ora_inizio);
                        $oraFine = \Carbon\Carbon::parse($presenzaDaEliminare->ora_fine);
                        $oreLavorate = $oraFine->diffInHours($oraInizio, true); // true per avere decimali
                        $costoReale = $oreLavorate * $presenzaDaEliminare->costo_giornaliero;
                    }

                    // ✅ Elimina la presenza
                    DB::table('presenze')
                        ->where('id', $presenzaId)
                        ->where('id_azienda', $utente->id_azienda)
                        ->delete();

                    // ✅ Decrementa il costo totale del cantiere se c'è un costo da sottrarre
                    if ($costoReale > 0) {
                        DB::table('cantieri')
                            ->where('id', $id)
                            ->where('id_azienda', $utente->id_azienda)
                            ->decrement('costo_totale', $costoReale);

                        $messaggioSuccesso = "Presenza eliminata! Costo cantiere aggiornato (-" . number_format($costoReale, 2) . "€)";
                    } else {
                        $messaggioSuccesso = "Presenza eliminata con successo!";
                    }

                    return redirect()->back()->with('success', $messaggioSuccesso);

                } catch (Exception $e) {
                    return redirect()->back()->with('error', 'Errore nell\'eliminazione: ' . $e->getMessage());
                }
            }

// ✅ ANCHE per la modifica presenza, dovresti gestire il ricalcolo:
            if (isset($_POST['modifica_presenza'])) {
                try {
                    $presenzaId = $_POST['id_presenza'];

                    // Recupera la presenza originale per calcolare la differenza di costo
                    $presenzaOriginale = DB::table('presenze')
                        ->join('utenti', 'presenze.id_dipendente', '=', 'utenti.id')
                        ->where('presenze.id', $presenzaId)
                        ->where('presenze.id_azienda', $utente->id_azienda)
                        ->select(
                            'presenze.*',
                            'utenti.costo_giornaliero',
                            'utenti.costo_orario'
                        )
                        ->first();

                    if (!$presenzaOriginale) {
                        return redirect()->back()->with('error', 'Presenza non trovata');
                    }

                    // Calcola il costo originale
                    $costoOriginale = 0;
                    if ($presenzaOriginale->costo_giornaliero > 0) {
                        $costoOriginale = $presenzaOriginale->costo_giornaliero;
                    } elseif ($presenzaOriginale->costo_orario > 0 && $presenzaOriginale->ora_inizio && $presenzaOriginale->ora_fine) {
                        $oraInizio = \Carbon\Carbon::parse($presenzaOriginale->ora_inizio);
                        $oraFine = \Carbon\Carbon::parse($presenzaOriginale->ora_fine);
                        $oreLavorate = $oraFine->diffInHours($oraInizio, true);
                        $costoOriginale = $oreLavorate * $presenzaOriginale->costo_giornaliero;
                    }

                    // Calcola il nuovo costo
                    $nuovoCosto = 0;
                    if ($presenzaOriginale->costo_giornaliero > 0) {
                        $nuovoCosto = $presenzaOriginale->costo_giornaliero;
                    } elseif ($presenzaOriginale->costo_giornaliero > 0) {
                        $nuovaOraInizio = \Carbon\Carbon::parse($_POST['ora_inizio']);
                        $nuovaOraFine = \Carbon\Carbon::parse($_POST['ora_fine']);
                        $nuoveOreLavorate = $nuovaOraFine->diffInHours($nuovaOraInizio, true);
                        $nuovoCosto = $nuoveOreLavorate * $presenzaOriginale->costo_giornaliero;
                    }

                    // Aggiorna la presenza
                    DB::table('presenze')
                        ->where('id', $presenzaId)
                        ->where('id_azienda', $utente->id_azienda)
                        ->update([
                            'data' => $_POST['data_presenza'],
                            'ora_inizio' => $_POST['ora_inizio'],
                            'ora_fine' => $_POST['ora_fine'],
                            'note' => $_POST['note_presenza'] ?? null,
                            'updated_at' => now(),
                            'modificata_da' => $utente->id
                        ]);

                    // Aggiorna il costo del cantiere solo se c'è una differenza
                    $differenzaCosto = $nuovoCosto - $costoOriginale;
                    if (abs($differenzaCosto) > 0.01) { // Evita problemi di arrotondamento
                        if ($differenzaCosto > 0) {
                            DB::table('cantieri')
                                ->where('id', $id)
                                ->where('id_azienda', $utente->id_azienda)
                                ->increment('costo_totale', $differenzaCosto);
                            $messaggioSuccesso = "Presenza modificata! Costo cantiere aggiornato (+" . number_format($differenzaCosto, 2) . "€)";
                        } else {
                            DB::table('cantieri')
                                ->where('id', $id)
                                ->where('id_azienda', $utente->id_azienda)
                                ->decrement('costo_totale', abs($differenzaCosto));
                            $messaggioSuccesso = "Presenza modificata! Costo cantiere aggiornato (" . number_format($differenzaCosto, 2) . "€)";
                        }
                    } else {
                        $messaggioSuccesso = "Presenza modificata con successo!";
                    }

                    return redirect()->back()->with('success', $messaggioSuccesso);

                } catch (Exception $e) {
                    return redirect()->back()->with('error', 'Errore nella modifica: ' . $e->getMessage());
                }
            }
            // Gestione attività (INVARIATO)
            if (isset($_POST['inserisci_attivita'])) {
                $id_attivita = DB::table('cantieri_attivita')->insertGetId([
                    'id_azienda' => $utente->id_azienda,
                    'id_cantiere' => $id,
                    'descrizione' => $_POST['descrizione'],
                    'data_schedulazione' => now(),
                    'data_inizio' => $_POST['data_inizio'],
                    'data_fine' => $_POST['data_fine'],
                    'note' => $_POST['note'],
                ]);

                // ✅ SOLO QUESTA PARTE AGGIORNATA - Aggiunge id_cantiere
                if (isset($_POST['dipendenti']) && is_array($_POST['dipendenti'])) {
                    foreach ($_POST['dipendenti'] as $id_dipendente) {
                        DB::table('cantieri_attivita_dipendenti')->insert([
                            'id_attivita' => $id_attivita,
                        'id_azienda' => $utente->id_azienda,
                            'id_dipendente' => $id_dipendente,
                            'id_cantiere' => $id  // ✅ AGGIUNTO SOLO QUESTO
                        ]);
                    }
                }

                return redirect()->back()->with('success', 'Attività inserita con successo!');
            }

            if (isset($_POST['modifica_attivita'])) {
                DB::table('cantieri_attivita')
                    ->where('id', $_POST['id'])
                    ->update([
                        'descrizione' => $_POST['descrizione'],
                        'data_inizio' => $_POST['data_inizio'],
                        'data_fine' => $_POST['data_fine'],
                        'note' => $_POST['note'],
                    ]);

                return redirect()->back()->with('success', 'Attività modificata con successo!');
            }

            if (isset($_POST['elimina_attivita'])) {
                DB::table('cantieri_attivita')->where('id', $_POST['id'])->delete();
                return redirect()->back()->with('success', 'Attività eliminata con successo!');
            }

            if (isset($_POST['aggiorna_dipendenti_attivita'])) {
                $id_attivita = $_POST['attivita_id'];
                $dipendenti = $_POST['dipendenti'] ?? [];

                // ✅ RECUPERA ID CANTIERE DALL'ATTIVITÀ
                $attivita_info = DB::table('cantieri_attivita')->where('id', $id_attivita)->first();

                DB::table('cantieri_attivita_dipendenti')
                    ->where('id_attivita', $id_attivita)
                    ->delete();

                // ✅ AGGIUNGE id_cantiere nei nuovi inserimenti
                foreach ($dipendenti as $id_dipendente) {
                    DB::table('cantieri_attivita_dipendenti')->insert([
                        'id_attivita' => $id_attivita,
                        'id_dipendente' => $id_dipendente,
                        'id_cantiere' => $attivita_info->id_cantiere  // ✅ AGGIUNTO
                    ]);
                }

                return response()->json(['success' => true]);
            }
        }

        // ✅ GESTIONE RICHIESTE AJAX - INVARIATA AL 100%
        if (request()->ajax()) {
            if (request()->is('azienda/dipendenti/*/attivita')) {
                $id_dipendente = request()->segment(3);
                $attivitaDipendente = DB::table('cantieri_attivita as ca')
                    ->join('cantieri_attivita_dipendenti as cad', 'ca.id', '=', 'cad.id_attivita')
                    ->where('cad.id_dipendente', $id_dipendente)
                    ->where('ca.id_cantiere', $id)
                    ->get();
                return response()->json($attivitaDipendente);
            }

            if (request()->is('azienda/attivita/*/dipendenti')) {
                $id_attivita = request()->segment(3);

                $assegnati = DB::table('utenti as u')
                    ->join('cantieri_attivita_dipendenti as cad', 'u.id', '=', 'cad.id_dipendente')
                    ->where('cad.id_attivita', $id_attivita)
                    ->get();

                $disponibili = DB::table('utenti as u')
                    ->whereNotIn('u.id', function($query) use ($id_attivita) {
                        $query->select('id_dipendente')
                            ->from('cantieri_attivita_dipendenti')
                            ->where('id_attivita', $id_attivita);
                    })
                    ->where('u.id_azienda', $utente->id_azienda)
                    ->where('u.admin_azienda', 2)
                    ->get();

                return response()->json([
                    'assegnati' => $assegnati,
                    'disponibili' => $disponibili
                ]);
            }
        }

        // Resto del codice INVARIATO (recupero dati per la view)
        $dipendentiAssegnati = DB::select("
        SELECT DISTINCT 
            cog.id_dipendente, 
            cog.nome, 
            cog.cognome, 
            cog.mansione,
            COUNT(cog.data_lavoro) as giorni_assegnati
        FROM cantieri_operai_giorni cog
        WHERE cog.id_cantiere = ? AND cog.id_azienda = ?
        GROUP BY cog.id_dipendente, cog.nome, cog.cognome, cog.mansione
        ORDER BY cog.nome, cog.cognome
    ", [$id, $utente->id_azienda]);

        $materiali = DB::select('SELECT * FROM articoli WHERE tipologia = 1 AND id_azienda = ? AND quantita > quantita_impegnata', [$utente->id_azienda]);
        $materiali_impegnati = DB::select("
        SELECT a.id, a.titolo, a.descrizione, im.quantita_impegnata
        FROM impegni_magazzino im
        JOIN articoli a ON im.id_articolo = a.id
        WHERE im.id_cantiere = ? AND a.tipologia = 1 AND im.id_azienda = ?
    ", [$id, $utente->id_azienda]);


        $strumenti = DB::table('articoli as a')
            ->leftJoin('impegni_magazzino as im', function($join) use ($id, $utente) {
                $join->on('a.id', '=', 'im.id_articolo')
                    ->where('im.id_cantiere', '=', $id)
                    ->where('im.id_azienda', '=', $utente->id_azienda);
            })
            ->where('a.tipologia', 2)
            ->where('a.id_azienda', $utente->id_azienda)
            ->select(
                'a.*',
                DB::raw('COALESCE(im.quantita_impegnata, 0) as quantita_impegnata_cantiere')
            )
            ->get();

        $strumenti_impegnati = DB::select("
        SELECT a.id, a.titolo, a.descrizione
        FROM impegni_magazzino im
        JOIN articoli a ON im.id_articolo = a.id
        WHERE im.id_cantiere = ? AND a.tipologia = 2 AND im.id_azienda = ?
    ", [$id, $utente->id_azienda]);

        // Recupera presenze + calcolo distanze (INVARIATO)
        $presenze = DB::table('presenze')
            ->join('utenti', 'presenze.id_dipendente', '=', 'utenti.id')
            ->where('presenze.id_azienda', $utente->id_azienda)
            ->where('presenze.id_cantiere', $cantiere->id)
            ->select(
                'presenze.*',
                'utenti.nome as nome_dipendente',
                'utenti.cognome as cognome_dipendente',
                'utenti.costo_giornaliero',
                DB::raw('(TIMESTAMPDIFF(MINUTE, presenze.ora_inizio, presenze.ora_fine) / 60) 
              * utenti.costo_giornaliero AS costo_dipendente')
            )
            ->orderBy('presenze.data', 'desc')
            ->orderBy('presenze.ora_inizio', 'asc')
            ->get();

        // Calcolo distanze (INVARIATO)
        $distanceInKm = function($lat1, $lon1, $lat2, $lon2) {
            $earthRadius = 6371;
            $dLat = deg2rad($lat2 - $lat1);
            $dLon = deg2rad($lon2 - $lon1);

            $a = sin($dLat / 2) * sin($dLat / 2) +
                cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
                sin($dLon / 2) * sin($dLon / 2);

            $c = 2 * asin(sqrt($a));
            return $earthRadius * $c;
        };

        foreach ($presenze as $p) {
            if ($p->lat_inizio && $p->long_inizio) {
                $distInizio = $distanceInKm(
                    $cantiere->latitudine,
                    $cantiere->longitudine,
                    $p->lat_inizio,
                    $p->long_inizio
                );
                $p->entroUnKmInizio = ($distInizio <= 1);
                $p->distInizioKm = $distInizio;
            } else {
                $p->entroUnKmInizio = false;
                $p->distInizioKm = null;
            }

            if ($p->lat_fine && $p->long_fine) {
                $distFine = $distanceInKm(
                    $cantiere->latitudine,
                    $cantiere->longitudine,
                    $p->lat_fine,
                    $p->long_fine
                );
                $p->entroUnKmFine = ($distFine <= 1);
                $p->distFineKm = $distFine;
            } else {
                $p->entroUnKmFine = false;
                $p->distFineKm = null;
            }
        }

        $allegati = DB::table('cantieri_allegati')
            ->where('id_cantiere', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        $presenzePerData = $presenze->groupBy('data');
        $dettaglioCosti = $this->getDettaglioCostiCantiere($id, $utente->id_azienda);

        return view('azienda.dettaglio_cantiere', compact(
            'cantiere',
            'allegati',
            'utente',
            'presenzePerData',
            'dipendenti',
            'attivita',
            'dipendentiAssegnati',
            'materiali',
            'strumenti',
            'presenze',
            'materiali_impegnati',
            'strumenti_impegnati',
            'dettaglioCosti'
        ));
    }
    /**
     * ✅ CORRETTO: Calcola il costo effettivo basato sui giorni di presenza
     */

    public function dettaglioMezzo(Request $request, $id) {
        $this->is_loggato();
        $utente = session('utente');
        $dati = $request->all();

        // ===== GESTIONE MANUTENZIONI (come prima) =====

        // ELIMINAZIONE Manutenzione
        if ($request->isMethod('post') && isset($dati['id_manutenzione']) && isset($dati['elimina'])) {
            DB::table('mezzi_manutenzioni')
                ->where('id', $dati['id_manutenzione'])
                ->where('id_azienda', $utente->id_azienda)
                ->delete();

            return Redirect::to('azienda/mezzo/' . $id)
                ->with('success', 'Manutenzione eliminata con successo!');
        }

        // MODIFICA Manutenzione
        if ($request->isMethod('post') && isset($dati['id_manutenzione']) && !isset($dati['elimina'])) {
            DB::table('mezzi_manutenzioni')
                ->where('id', $dati['id_manutenzione'])
                ->where('id_azienda', $utente->id_azienda)
                ->update([
                    'tipo' => $dati['tipo'],
                    'descrizione' => $dati['descrizione'],
                    'importo' => $dati['importo'],
                    'data_operazione' => $dati['data_operazione'],
                    'km_operazione' => $dati['km_operazione'] ?? null,
                    'updated_at' => now(),
                ]);

            return Redirect::to('azienda/mezzo/' . $id)
                ->with('success', 'Manutenzione modificata con successo!');
        }

        // AGGIUNTA Manutenzione
        if ($request->isMethod('post') && isset($dati['aggiungi_manutenzione'])) {
            DB::table('mezzi_manutenzioni')->insert([
                'id_mezzo' => $id,
                'id_azienda' => $utente->id_azienda,
                'tipo' => $dati['tipo'],
                'descrizione' => $dati['descrizione'],
                'importo' => $dati['importo'],
                'data_operazione' => $dati['data_operazione'],
                'km_operazione' => $dati['km_operazione'] ?? null,
                'created_at' => now(),
            ]);

            return Redirect::to('azienda/mezzo/' . $id)
                ->with('success', 'Manutenzione aggiunta con successo!');
        }

        // ===== RECUPERO DATI =====

        // GET dei dati del mezzo
        $mezzo = DB::table('mezzi')
            ->where('id', $id)
            ->where('id_azienda', $utente->id_azienda)
            ->first();

        if (!$mezzo) {
            return redirect()->back()->with('error', 'Mezzo non trovato.');
        }

        // ✅ FIX: GET TUTTE le manutenzioni
        $manutenzioni = DB::table('mezzi_manutenzioni')
            ->where('id_mezzo', $id)
            ->where('id_azienda', $utente->id_azienda)
            ->orderBy('data_operazione', 'DESC')
            ->get();

        // ✅ FIX: Filtra i tagliandi dalle manutenzioni (NON sovrascrivere $manutenzioni!)
        $storico_tagliandi = $manutenzioni->where('tipo', 'Tagliando');
        $ultimo_tagliando = $storico_tagliandi->first();

        // Calcola stato tagliando
        $stato_tagliando = [
            'classe' => 'success',
            'messaggio' => 'In regola',
            'km_percorsi' => $ultimo_tagliando ? (($mezzo->km_attuali ?? 0) - ($ultimo_tagliando->km_operazione ?? 0)) : ($mezzo->km_attuali ?? 0),
            'km_rimanenti' => $ultimo_tagliando ? (($ultimo_tagliando->km_operazione + 15000) - ($mezzo->km_attuali ?? 0)) : (15000 - ($mezzo->km_attuali ?? 0)),
            'km_intervallo' => 15000,
            'percentuale' => $ultimo_tagliando ? ((($mezzo->km_attuali ?? 0) - ($ultimo_tagliando->km_operazione ?? 0)) / 15000 * 100) : (($mezzo->km_attuali ?? 0) / 15000 * 100),
            'km_prossimo' => $ultimo_tagliando ? (($ultimo_tagliando->km_operazione ?? 0) + 15000) : 15000
        ];

        // GET sostituzioni gomme
        $sostituzioni_gomme = DB::table('mezzi_gomme')
            ->where('id_mezzo', $id)
            ->where('id_azienda', $utente->id_azienda)
            ->orderBy('data_sostituzione', 'DESC')
            ->get();

        // Calcola stati gomme (se hai la funzione)
        $stati_gomme = $this->calcolaStatiGomme($mezzo, $sostituzioni_gomme);



        // ===== DATI CARBURANTE =====
        $rifornimenti = DB::table('rifornimenti_carburante')
            ->where('id_mezzo', $id)
            ->where('id_azienda', $utente->id_azienda)
            ->orderBy('data_rifornimento', 'DESC')
            ->orderBy('km_rifornimento', 'DESC')
            ->get();

        // Statistiche carburante
        $stats_carburante = [
            'totale_speso' => $rifornimenti->sum('importo_totale'),
            'totale_litri' => $rifornimenti->sum('litri'),
            'num_rifornimenti' => $rifornimenti->count(),
            'prezzo_medio_litro' => $rifornimenti->where('prezzo_litro', '>', 0)->count() > 0
                ? round($rifornimenti->where('prezzo_litro', '>', 0)->avg('prezzo_litro'), 3) : 0,
            'consumo_medio' => 0,
            'costo_per_km' => 0,
            'spesa_ultimo_mese' => 0,
            'litri_ultimo_mese' => 0,
            'previsione_mensile' => 0,
        ];

        // Consumo medio (solo da rifornimenti con consumo calcolato)
        $rifornimentiConConsumo = $rifornimenti->whereNotNull('consumo_calcolato')->where('consumo_calcolato', '>', 0);
        if ($rifornimentiConConsumo->count() > 0) {
            $stats_carburante['consumo_medio'] = round($rifornimentiConConsumo->avg('consumo_calcolato'), 2);
        }

        // Costo per km
        if ($rifornimenti->count() >= 2) {
            $kmTotali = $rifornimenti->max('km_rifornimento') - $rifornimenti->min('km_rifornimento');
            if ($kmTotali > 0) {
                $stats_carburante['costo_per_km'] = round($stats_carburante['totale_speso'] / $kmTotali, 3);
            }
        }

        // Spesa ultimo mese
        $unMeseFa = \Carbon\Carbon::now()->subMonth()->format('Y-m-d');
        $rifornimentiUltimoMese = $rifornimenti->where('data_rifornimento', '>=', $unMeseFa);
        $stats_carburante['spesa_ultimo_mese'] = $rifornimentiUltimoMese->sum('importo_totale');
        $stats_carburante['litri_ultimo_mese'] = $rifornimentiUltimoMese->sum('litri');

        // Previsione mensile (media ultimi 3 mesi)
        $treMesiFa = \Carbon\Carbon::now()->subMonths(3)->format('Y-m-d');
        $rifornimentiTreMesi = $rifornimenti->where('data_rifornimento', '>=', $treMesiFa);
        if ($rifornimentiTreMesi->count() > 0) {
            $stats_carburante['previsione_mensile'] = round($rifornimentiTreMesi->sum('importo_totale') / 3, 2);
        }

        // Dati per grafico (ultimi 12 mesi aggregati)
        $grafico_carburante = DB::table('rifornimenti_carburante')
            ->where('id_mezzo', $id)
            ->where('id_azienda', $utente->id_azienda)
            ->where('data_rifornimento', '>=', \Carbon\Carbon::now()->subMonths(12)->format('Y-m-d'))
            ->select(
                DB::raw('DATE_FORMAT(data_rifornimento, "%Y-%m") as mese'),
                DB::raw('SUM(importo_totale) as totale_speso'),
                DB::raw('SUM(litri) as totale_litri'),
                DB::raw('AVG(prezzo_litro) as prezzo_medio'),
                DB::raw('AVG(consumo_calcolato) as consumo_medio'),
                DB::raw('COUNT(*) as num_rifornimenti')
            )
            ->groupBy(DB::raw('DATE_FORMAT(data_rifornimento, "%Y-%m")'))
            ->orderBy('mese', 'ASC')
            ->get();

        return view('azienda.dettaglio_mezzo', compact(
            'mezzo',
            'manutenzioni',
            'sostituzioni_gomme',
            'stati_gomme',
            'utente',
            'storico_tagliandi',
            'ultimo_tagliando',
            'stato_tagliando',
            'rifornimenti',
            'stats_carburante',
            'grafico_carburante'
        ));
    }

    public function registraTagliando(Request $request, $id)
    {
        $utente = session('utente');

        try {
            $mezzo = DB::table('mezzi')->where('id', $id)->first();
            if (!$mezzo) {
                return redirect()->back()->with('error', 'Mezzo non trovato');
            }

            // Salva direttamente in mezzi_manutenzioni
            DB::table('mezzi_manutenzioni')->insert([
                'id_mezzo' => $id,
                'id_azienda' => $utente->id_azienda,
                'tipo' => 'Tagliando',
                'descrizione' => "Tagliando {$request->tipo_tagliando} - " . ($request->officina ?: 'Non specificata'),
                'importo' => $request->costo ?? 0,
                'data_operazione' => $request->data_tagliando,
                'km_operazione' => $request->km_tagliando,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Aggiorna i Km del mezzo se necessario
            if ($request->km_tagliando > ($mezzo->km_attuali ?? 0)) {
                DB::table('mezzi')->where('id', $id)->update([
                    'km_attuali' => $request->km_tagliando,
                    'updated_at' => now()
                ]);
            }

            return redirect()->back()->with('success', 'Tagliando registrato con successo!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Errore: ' . $e->getMessage());
        }
    }


    public function aggiungiManutenzione(Request $request, $id)
    {
        $utente = session('utente');

        try {
            DB::table('mezzi_manutenzioni')->insert([
                'id_mezzo' => $id,
                'id_azienda' => $utente->id_azienda,
                'tipo' => $request->tipo,
                'descrizione' => $request->descrizione,
                'importo' => $request->importo ?? 0,
                'data_operazione' => $request->data_operazione,
                'km_operazione' => $request->km_operazione,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return redirect()->back()->with('success', 'Manutenzione aggiunta!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Errore: ' . $e->getMessage());
        }
    }

    /**
     * ✏️ MODIFICA MANUTENZIONE
     */
    public function modificaManutenzione(Request $request, $id)
    {
        $utente = session('utente');

        try {
            DB::table('mezzi_manutenzioni')
                ->where('id', $request->id_manutenzione)
                ->where('id_mezzo', $id)
                ->where('id_azienda', $utente->id_azienda)
                ->update([
                    'tipo' => $request->tipo,
                    'descrizione' => $request->descrizione,
                    'importo' => $request->importo ?? 0,
                    'data_operazione' => $request->data_operazione,
                    'km_operazione' => $request->km_operazione,
                    'updated_at' => now()
                ]);

            return redirect()->back()->with('success', 'Manutenzione modificata!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Errore: ' . $e->getMessage());
        }
    }

    /**
     * 🗑️ ELIMINA MANUTENZIONE
     */
    public function eliminaManutenzione(Request $request, $id)
    {
        $utente = session('utente');

        try {
            $eliminati = DB::table('mezzi_manutenzioni')
                ->where('id', $request->id_manutenzione)
                ->where('id_mezzo', $id)
                ->where('id_azienda', $utente->id_azienda)
                ->delete();

            if ($eliminati > 0) {
                return redirect()->back()->with('success', 'Manutenzione eliminata!');
            } else {
                return redirect()->back()->with('error', 'Manutenzione non trovata');
            }

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Errore: ' . $e->getMessage());
        }
    }


    private function calcolaCostoEffettivoCantiere($cantiereId)
    {
        // Calcola il costo basato sui giorni di presenza effettiva
        $costoPresenze = DB::select("
        SELECT 
            SUM(
                COUNT(DISTINCT p.data) * u.costo_giornaliero
            ) as costo_totale_presenze
        FROM presenze p
        JOIN utenti u ON u.id = p.id_dipendente
        WHERE p.id_cantiere = ?
        AND p.ora_fine IS NOT NULL  -- Solo presenze complete
        GROUP BY p.id_dipendente, u.costo_giornaliero
    ", [$cantiereId]);

        // Somma tutti i costi dei dipendenti
        $totale = 0;
        foreach ($costoPresenze as $costo) {
            $totale += $costo->costo_totale_presenze ?? 0;
        }

        return $totale;
    }

    /**
     * ✅ VERSIONE SEMPLIFICATA: Calcola direttamente con una query
     */
    private function calcolaCostoEffettivoCantiereSemplice($cantiereId)
    {
        $risultato = DB::select("
        SELECT 
            SUM(giorni_lavorati * costo_giornaliero) as costo_totale
        FROM (
            SELECT 
                p.id_dipendente,
                u.costo_giornaliero,
                COUNT(DISTINCT p.data) as giorni_lavorati
            FROM presenze p
            JOIN utenti u ON u.id = p.id_dipendente
            WHERE p.id_cantiere = ?
            AND p.ora_fine IS NOT NULL
            GROUP BY p.id_dipendente, u.costo_giornaliero
        ) as costi_per_dipendente
    ", [$cantiereId]);

        return $risultato[0]->costo_totale ?? 0;
    }

    /**
     * ✅ AGGIORNA: Usa il costo effettivo basato su giorni di presenza
     */
    private function aggiornaCostoEffettivoCantiere($cantiereId)
    {
        // Calcola costo dipendenti dai giorni di presenza effettiva
        $costoDipendenti = $this->calcolaCostoEffettivoCantiereSemplice($cantiereId);

        // Calcola costo materiali scaricati
        $costoMateriali = DB::select("
        SELECT 
            COALESCE(SUM(m.qta * a.costo), 0) as costo_totale_materiali
        FROM mgmov m
        JOIN articoli a ON a.id = m.id_articolo
        WHERE m.id_cantiere = ?
        AND m.causale = 'Scarico'
    ", [$cantiereId]);

        $costoTotaleMateriali = $costoMateriali[0]->costo_totale_materiali ?? 0;

        // Costo totale effettivo
        $costoTotaleEffettivo = $costoDipendenti + $costoTotaleMateriali;

        // Aggiorna il cantiere
        DB::table('cantieri')
            ->where('id', $cantiereId)
            ->update([
                'costo_totale' => $costoTotaleEffettivo,
                'updated_at' => now()
            ]);

        return $costoTotaleEffettivo;
    }

    /**
     * ✅ CORRETTO: Dettaglio costi per la view - Per giorni
     */
    private function getDettaglioCostiCantiere($cantiereId, $idAzienda)
    {
        // Costi dipendenti dettagliati basati su giorni di presenza
        $costiDipendenti = DB::select("
        SELECT 
            p.id_dipendente,
            u.nome,
            u.cognome,
            u.costo_giornaliero,
            COUNT(DISTINCT p.data) as giorni_presenza,
            SUM(
                CASE 
                    WHEN p.ora_fine IS NOT NULL THEN 
                        TIMESTAMPDIFF(MINUTE, p.ora_inizio, p.ora_fine) / 60.0
                    ELSE 0 
                END
            ) as ore_totali,
            (COUNT(DISTINCT p.data) * u.costo_giornaliero) as costo_totale_dipendente
        FROM presenze p
        JOIN utenti u ON u.id = p.id_dipendente
        WHERE p.id_cantiere = ?
        AND p.id_azienda = ?
        AND p.ora_fine IS NOT NULL  -- Solo presenze complete
        GROUP BY p.id_dipendente, u.nome, u.cognome, u.costo_giornaliero
        ORDER BY u.cognome, u.nome
    ", [$cantiereId, $idAzienda]);

        // Costi materiali (invariato)
        $costiMateriali = DB::select("
        SELECT
            m.*,
            a.titolo as articolo_nome,
            a.costo as costo_unitario,
            (m.qta * a.costo) as costo_totale
        FROM mgmov m
        JOIN articoli a ON m.id_articolo = a.id
        WHERE m.causale = 'Scarico'
        AND m.id_cantiere = ?
        ORDER BY m.datamov DESC
    ", [$cantiereId]);

        return [
            'dipendenti' => $costiDipendenti,
            'materiali' => $costiMateriali,
            'totale_dipendenti' => array_sum(array_column($costiDipendenti, 'costo_totale_dipendente')),
            'totale_materiali' => array_sum(array_column($costiMateriali, 'costo_totale'))
        ];
    }

    private function verificaConflittiDipendente($dipendenteId, $dataInizio, $dataFine, $cantiereEscluso = null)
    {
        $query = DB::table('cantieri_operai as co')
            ->join('cantieri as c', 'co.id_cantiere', '=', 'c.id')
            ->where('co.id_dipendente', $dipendenteId)
            ->where('c.stato', '!=', 0) // Escludi cantieri chiusi
            ->where(function($q) use ($dataInizio, $dataFine) {
                // Controlla sovrapposizioni di date
                $q->where(function($subQ) use ($dataInizio, $dataFine) {
                    $subQ->where('c.data_inizio', '<=', $dataFine)
                        ->where('c.data_fine', '>=', $dataInizio);
                });
            })
            ->select('c.id', 'c.titolo', 'c.data_inizio', 'c.data_fine');

        // Escludi il cantiere corrente se stiamo modificando
        if ($cantiereEscluso) {
            $query->where('c.id', '!=', $cantiereEscluso);
        }

        return $query->get();
    }

    /**
     * Assegna dipendenti al cantiere (MODIFICATO CON CONTROLLO CONFLITTI)
     */
    public function assegnaDipendenti(Request $request, $cantiereId)
    {
        try {
            $session = Session::get('utente');
            $dipendentiIds = $request->input('dipendenti', []);

            if (empty($dipendentiIds)) {
                return redirect()->back()->withErrors(['Seleziona almeno un dipendente']);
            }

            // ✅ NUOVO: Recupera le date del cantiere corrente
            $cantiere = DB::table('cantieri')->where('id', $cantiereId)->first();
            if (!$cantiere) {
                return redirect()->back()->withErrors(['Cantiere non trovato']);
            }

            // ✅ NUOVO: Controlla conflitti per ogni dipendente
            $conflitti = [];
            foreach ($dipendentiIds as $dipendenteId) {
                $dipendente = DB::table('utenti')->where('id', $dipendenteId)->first();
                $conflittiDipendente = $this->verificaConflittiDipendente(
                    $dipendenteId,
                    $cantiere->data_inizio,
                    $cantiere->data_fine,
                    $cantiereId
                );

                if ($conflittiDipendente->count() > 0) {
                    $nomiCantieri = $conflittiDipendente->pluck('titolo')->implode(', ');
                    $conflitti[] = "{$dipendente->nome} {$dipendente->cognome} è già assegnato ai cantieri: {$nomiCantieri}";
                }
            }

            // ✅ NUOVO: Se ci sono conflitti, ferma tutto e mostra errori
            if (!empty($conflitti)) {
                return redirect()->back()->withErrors([
                    'Impossibile assegnare i dipendenti per i seguenti conflitti:',
                ]);
            }

            // Se non ci sono conflitti, procedi normalmente
            DB::table('cantieri_operai')->where('id_cantiere', $cantiereId)->delete();

            foreach ($dipendentiIds as $dipendenteId) {
                $dipendente = DB::table('utenti')->where('id', $dipendenteId)->first();

                if ($dipendente) {
                    DB::table('cantieri_operai')->insert([
                        'id_cantiere' => $cantiereId,
                        'id_dipendente' => $dipendenteId,
                        'nome' => $dipendente->nome,
                        'cognome' => $dipendente->cognome,
                        'id_azienda' => $session->id_azienda,
                        'mansione' => $dipendente->ruoli_titolo ?? 'N/A',
                    ]);
                }
            }

            // Aggiorna il costo totale del cantiere
            /*$this->aggiornaCostoStimatoCantiere($cantiereId);*/

            return redirect()->back()->with('success', 'Dipendenti assegnati con successo! Costo cantiere aggiornato.');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['Errore nell\'assegnazione dipendenti: ' . $e->getMessage()]);
        }
    }


    /**
     * Rimuovi dipendente dal cantiere (MODIFICATO)
     */
    public function rimuoviDipendente($id)
    {
        try {
            // ✅ CORRETTO: cantieri_operai invece di cantieri_dipendenti
            $assegnazione = DB::table('cantieri_operai')->where('id', $id)->first();
            if (!$assegnazione) {
                return redirect()->back()->withErrors(['Assegnazione non trovata']);
            }

            $cantiereId = $assegnazione->id_cantiere;

            // Rimuovi l'assegnazione
            DB::table('cantieri_operai')->where('id', $id)->delete();

            // ✅ AGGIORNA IL COSTO TOTALE DEL CANTIERE
           /* $this->aggiornaCostoStimatoCantiere($cantiereId);*/

            return redirect()->back()->with('success', 'Dipendente rimosso e costo cantiere aggiornato!');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['Errore nella rimozione: ' . $e->getMessage()]);
        }
    }





    // In una implementazione reale, dovresti utilizzare questo metodo come base
    // ma prendendo i dati dal database invece che hardcoded
    public function visualizzaDipendenti(Request $request, $isDashboard = false)
    {
        $session = Session::get('utente');
        if (!$session) {
            return redirect('admin/login');
        }

        $utente = $session;

        // Recupera le date di inizio e fine
        $dataInizio = $request->input('data_inizio', Carbon::now()->startOfWeek()->format('Y-m-d'));
        $dataFine = $request->input('data_fine', Carbon::now()->endOfWeek()->format('Y-m-d'));

        // Crea un array con i giorni della settimana
        $period = CarbonPeriod::create($dataInizio, $dataFine);
        $giorni = [];

        foreach ($period as $date) {
            $giorni[] = [
                'data' => $date->format('Y-m-d'),
                'nome' => $date->locale('it')->dayName,
                'numero' => $date->format('d')
            ];
        }

        // Recupera i cantieri attivi nel periodo selezionato
        $cantieri = DB::table('cantieri')
            ->where('id_azienda', $session->id_azienda)
            ->where(function($query) use ($dataInizio, $dataFine) {
                $query->whereBetween('data_inizio', [$dataInizio, $dataFine])
                    ->orWhereBetween('data_fine', [$dataInizio, $dataFine])
                    ->orWhere(function ($q) use ($dataInizio, $dataFine) {
                        $q->where('data_inizio', '<=', $dataInizio)
                            ->where('data_fine', '>=', $dataFine);
                    });
            })
            ->get();

        // Array per i risultati
        $cantieriPerGiorno = [];

        // Per ogni cantiere, recupera i dipendenti associati
        foreach ($cantieri as $cantiere) {
            // Determina il colore in base al cliente/tipo
            $colore = $this->getColorePerCantiere($cantiere);

            // Recupera il responsabile del cantiere
            $responsabile = DB::select("
            SELECT u.cognome 
            FROM cantieri_responsabili cr
            JOIN utenti u ON cr.id_responsabile = u.id 
            WHERE cr.id_cantiere = ?
            LIMIT 1
        ", [$cantiere->id]);

            $nomeResponsabile = $responsabile ? strtoupper($responsabile[0]->cognome) : 'N/A';

            // Estrai tipo lavoro dalla descrizione
            $tipoLavoro = $this->getTipoLavoro($cantiere->descrizione);

            // Tronca il nome cliente se troppo lungo
            $nomeCliente = strtoupper(Str::limit($cantiere->titolo, 25, ''));
            $posizione = Str::limit($cantiere->indirizzo ?? 'N/D', 20, '');

            // Crea l'array base per questo cantiere
            $cantieriPerGiorno[$cantiere->id] = [
                'cliente' => $nomeCliente,
                'posizione' => $posizione,
                'lavorazione' => $tipoLavoro,
                'responsabile' => $nomeResponsabile,
                'colore' => $colore,
                'giorni' => []
            ];

            // Per ogni giorno nel periodo, recupera i dipendenti assegnati specificamente per quel giorno
            foreach ($giorni as $giorno) {
                // ✅ NUOVO: Usa la tabella cantieri_operai_giorni invece di cantieri_operai
                $dipendenti = DB::select("
                SELECT DISTINCT cog.nome, cog.cognome
                FROM cantieri_operai_giorni cog
                WHERE cog.id_cantiere = ? 
                AND cog.data_lavoro = ?
                AND cog.id_azienda = ?
                ORDER BY cog.cognome
            ", [$cantiere->id, $giorno['data'], $session->id_azienda]);

                // Se ci sono dipendenti per questo giorno, aggiungili
                if (!empty($dipendenti)) {
                    $dipendentiNomi = [];
                    foreach ($dipendenti as $dipendente) {
                        $dipendentiNomi[] = strtoupper(trim($dipendente->nome . ' ' . $dipendente->cognome));
                    }

                    // Determina lo stato del cantiere per questo giorno
                    $stato = null;
                    if ($cantiere->stato == 2) {
                        $stato = 'IN CORSO';
                    }

                    // Aggiungi i dati per questo giorno
                    $cantieriPerGiorno[$cantiere->id]['giorni'][$giorno['data']] = [
                        'dipendenti' => $dipendentiNomi,
                        'stato' => $stato
                    ];
                }
            }
        }

        // ✅ NUOVO: Calcola i dipendenti liberi per ogni giorno
        $dipendentiLiberi = [];

        // Recupera tutti i dipendenti dell'azienda
        $tuttiDipendenti = DB::select("
        SELECT DISTINCT u.id, u.nome, u.cognome
        FROM utenti u
        WHERE u.id_azienda = ?
        AND u.admin_azienda = 2
        ORDER BY u.cognome
    ", [$session->id_azienda]);

        foreach ($giorni as $giorno) {
            $dipendentiImpegnati = [];

            // Raccogli tutti i dipendenti impegnati in questo giorno
            $dipendentiOccupati = DB::select("
            SELECT DISTINCT cog.nome, cog.cognome
            FROM cantieri_operai_giorni cog
            WHERE cog.data_lavoro = ?
            AND cog.id_azienda = ?
        ", [$giorno['data'], $session->id_azienda]);

            foreach ($dipendentiOccupati as $occupato) {
                $dipendentiImpegnati[] = strtoupper(trim($occupato->cognome . ' ' . $occupato->nome));
            }

            // Trova i dipendenti liberi
            $liberi = [];
            foreach ($tuttiDipendenti as $dipendente) {
                $nomeCompleto = strtoupper(trim($dipendente->cognome . ' ' . $dipendente->nome));
                if (!in_array($nomeCompleto, $dipendentiImpegnati)) {
                    $liberi[] = strtoupper(trim($dipendente->nome . ' ' . $dipendente->cognome));
                }
            }

            $dipendentiLiberi[$giorno['data']] = $liberi;
        }

        // Aggiungi righe speciali
        $specialRows = $this->getSpecialRows($giorni);

        // ✅ Se è modalità dashboard, ritorna la view speciale
        if ($isDashboard) {
            // Recupera anche i dati del calendario per la modalità mista
            $eventi = $this->getEventiCalendario($utente);
            $dipendentiCantieri = DB::table('cantieri_operai')
                ->select('id_cantiere', 'nome', 'cognome')
                ->get()
                ->groupBy('id_cantiere');

            return view('azienda.index_special', compact('giorni', 'cantieriPerGiorno', 'specialRows', 'utente', 'dipendentiLiberi', 'eventi', 'dipendentiCantieri', 'isDashboard'));
        }

        return view('azienda.dipendenti_visualizza', compact('giorni', 'cantieriPerGiorno', 'specialRows', 'utente', 'dipendentiLiberi'));
    }

    private function getEventiCalendario($utente)
    {
        $attivitaQuery = DB::table('cantieri_attivita')
            ->join('cantieri', 'cantieri_attivita.id_cantiere', '=', 'cantieri.id')
            ->where('cantieri.id_azienda', $utente->id_azienda)
            ->where('cantieri.stato', 1)
            ->select(
                'cantieri_attivita.id',
                'cantieri_attivita.descrizione',
                'cantieri_attivita.data_inizio',
                'cantieri_attivita.data_fine',
                'cantieri.titolo as cantiere_titolo',
                'cantieri.colore as cantiere_colore',
                'cantieri.id as id_cantiere'
            );

        if (isset($utente->vista_operaio) && (int) $utente->vista_operaio === 1) {
            $attivitaQuery->join('cantieri_attivita_dipendenti', 'cantieri_attivita.id', '=', 'cantieri_attivita_dipendenti.id_attivita')
                ->where('cantieri_attivita_dipendenti.id_dipendente', $utente->id);
        }

        $attivita = $attivitaQuery->get();

        return collect($attivita)->map(function ($att) use ($utente) {
            return [
                'id_cantiere' => $att->id_cantiere,
                'title' => '🔹 ' . $att->descrizione . ' | ' . $att->cantiere_titolo,
                'start' => date('Y-m-d\TH:i:s', strtotime($att->data_inizio)),
                'end' => date('Y-m-d\TH:i:s', strtotime($att->data_fine)),
                'color' => $att->cantiere_colore ?? '#28a745',
                'url' => (isset($utente->vista_operaio) && (int) $utente->vista_operaio === 1)
                    ? null
                    : url('/azienda/cantiere/' . $att->id_cantiere . '?highlight=' . $att->id . '#attivita'),
                'allDay' => true
            ];
        });
    }

    private function getColorePerCantiere($cantiere)
    {
        $titolo = strtolower($cantiere->titolo);
        $descrizione = strtolower($cantiere->descrizione ?? '');

        // Colori basati sui clienti come nell'immagine
        if (stripos($titolo, 'royal fruit') !== false) {
            return '#C8E6C9'; // Verde chiaro
        } elseif (stripos($titolo, 'squicciarini') !== false) {
            return '#E1F5FE'; // Azzurro chiaro
        } elseif (stripos($descrizione, 'taglio') !== false || stripos($descrizione, 'tagli') !== false) {
            return '#FFCCCB'; // Rosa/rosso chiaro per "DA TAGLIARE"
        } elseif (stripos($descrizione, 'spolvero') !== false) {
            return '#E0E0E0'; // Grigio chiaro
        } elseif (stripos($descrizione, 'stampato') !== false) {
            return '#FFF3E0'; // Arancione chiaro
        } elseif (stripos($descrizione, 'resina') !== false) {
            return '#F3E5F5'; // Viola chiaro
        } elseif (stripos($titolo, 'antonio ventura') !== false) {
            return '#FFFDE7'; // Giallo chiaro
        } elseif (stripos($titolo, 'coires') !== false) {
            return '#E8EAF6'; // Indaco chiaro
        } else {
            return '#F5F5F5'; // Grigio neutro default
        }
    }

    private function getTipoLavoro($descrizione)
    {
        if (!$descrizione) return 'N/A';

        $descrizione = strtolower($descrizione);

        if (stripos($descrizione, 'spolvero') !== false) {
            return 'SPOLVERO';
        } elseif (stripos($descrizione, 'taglio') !== false || stripos($descrizione, 'tagli') !== false) {
            return 'TAGLI';
        } elseif (stripos($descrizione, 'stampato') !== false) {
            return 'STAMPATO';
        } elseif (stripos($descrizione, 'resina') !== false) {
            return 'RESINA';
        } else {
            return strtoupper(Str::limit($descrizione, 15, ''));
        }
    }


    /**
     * Ottiene le righe speciali per la visualizzazione dei dipendenti
     */
    private function getSpecialRows($giorni)
    {
        // Qui puoi personalizzare le righe speciali in base alle tue necessità
        $specialRows = [];

        // Aggiungi eventuali righe speciali come "DA TAGLIARE" o "COIRES"
        // Controlla se hai delle tabelle specifiche per questi dati
        // altrimenti puoi crearle come nel tuo esempio

        // Esempio: Controlla se ci sono cantieri in fase "da tagliare"
        $cantieriDaTagliare = DB::table('cantieri')
            ->where('descrizione', 'like', '%tagli%')
            ->where('stato', '=', 1)
            ->first();

        if ($cantieriDaTagliare) {
            $specialRows['da_tagliare'] = [
                'colore' => '#FFEBEE',
                'giorni' => [
                    $giorni[0]['data'] => [
                        'titoli' => ['DA TAGLIARE']
                    ]
                ]
            ];
        }

        // Aggiungi anche altre righe speciali come COIRES, DELLA PORTA, ecc.
        // che potresti avere nel tuo sistema

        // Controlla se hai utenti con ruoli speciali (es. autisti)
        $autisti = DB::table('cantieri_operai')
            ->where('mansione', 'like', '%Autista%')
            ->orWhere('mansione', 'like', '%autista%')
            ->get();

        if (count($autisti) > 0) {
            $specialRows['autisti'] = [
                'colore' => '#E8EAF6',
                'giorni' => []
            ];

            foreach ($giorni as $giorno) {
                $specialRows['autisti']['giorni'][$giorno['data']] = [
                    'titoli' => ['DELLA PORTA (AUTISTA)'],
                    'dipendenti' => $autisti->pluck('cognome')->map(function($cognome) {
                        return strtoupper($cognome);
                    })->toArray()
                ];
            }
        }

        return $specialRows;
    }

    public function logout(){

        session()->flush();
        return Redirect::to('admin/login');
    }

    public function is_loggato(){

        if (!session()->has('utente')) return Redirect::to('admin/login')->send();

    }


    public function gestisciPagamento(Request $request)
    {
        try {
            $session = Session::get('utente');
            $azione = $request->input('azione');

            switch ($azione) {
                case 'aggiungi':
                    return $this->aggiungiPagamento($request, $session);

                case 'modifica':
                    return $this->modificaPagamento($request);

                case 'elimina':
                    return $this->eliminaPagamento($request);

                case 'segna_pagato':
                    return $this->segnaPagato($request);

                default:
                    return response()->json(['success' => false, 'message' => 'Azione non valida']);
            }

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Aggiunge un nuovo pagamento
     */
    private function aggiungiPagamento($request, $session)
    {
        $idCantiere = $request->input('id_cantiere');
        $tipo = $request->input('tipo');
        $importo = $request->input('importo');
        $descrizione = $request->input('descrizione');
        $note = $request->input('note');

        $data = [
            'id_cantiere' => $idCantiere,
            'id_azienda' => $session->id_azienda,
            'tipo' => $tipo,
            'importo' => $importo,
            'descrizione' => $descrizione,
            'note' => $note,
            'created_at' => now(),
            'updated_at' => now()
        ];

        if ($tipo === 'ricevuto') {
            $data['data_pagamento'] = $request->input('data_pagamento');

            // ✅ AGGIORNA IL VALORE TOTALE DEL CANTIERE
            DB::table('cantieri')
                ->where('id', $idCantiere)
                ->increment('valore_totale', $importo);

        } else {
            $data['data_scadenza'] = $request->input('data_scadenza');
        }

        DB::table('cantieri_pagamenti')->insert($data);

        return redirect()->back()->with('success', 'Pagamento aggiunto con successo!');
    }

    /**
     * Modifica un pagamento esistente
     */
    private function modificaPagamento($request)
    {
        $id = $request->input('id_pagamento');
        $tipo = $request->input('tipo');
        $importo = $request->input('importo');
        $descrizione = $request->input('descrizione');
        $note = $request->input('note');

        // Recupera il pagamento precedente per calcolare la differenza
        $pagamentoPrecedente = DB::table('cantieri_pagamenti')->where('id', $id)->first();

        $data = [
            'tipo' => $tipo,
            'importo' => $importo,
            'descrizione' => $descrizione,
            'note' => $note,
            'updated_at' => now()
        ];

        if ($tipo === 'ricevuto') {
            $data['data_pagamento'] = $request->input('data_pagamento');
            $data['data_scadenza'] = null;

            // Se era "da_ricevere" e ora diventa "ricevuto", aggiorna valore_totale
            if ($pagamentoPrecedente->tipo === 'da_ricevere') {
                DB::table('cantieri')
                    ->where('id', $pagamentoPrecedente->id_cantiere)
                    ->increment('valore_totale', $importo);
            } elseif ($pagamentoPrecedente->tipo === 'ricevuto') {
                // Se era già ricevuto, aggiorna la differenza
                $differenza = $importo - $pagamentoPrecedente->importo;
                DB::table('cantieri')
                    ->where('id', $pagamentoPrecedente->id_cantiere)
                    ->increment('valore_totale', $differenza);
            }

        } else {
            $data['data_scadenza'] = $request->input('data_scadenza');
            $data['data_pagamento'] = null;

            // Se era "ricevuto" e ora diventa "da_ricevere", decrementa valore_totale
            if ($pagamentoPrecedente->tipo === 'ricevuto') {
                DB::table('cantieri')
                    ->where('id', $pagamentoPrecedente->id_cantiere)
                    ->decrement('valore_totale', $pagamentoPrecedente->importo);
            }
        }

        DB::table('cantieri_pagamenti')->where('id', $id)->update($data);

        return redirect()->back()->with('success', 'Pagamento modificato con successo!');
    }

    /**
     * Elimina un pagamento
     */
    private function eliminaPagamento($request)
    {
        $id = $request->input('id_pagamento');

        $pagamento = DB::table('cantieri_pagamenti')->where('id', $id)->first();

        if ($pagamento && $pagamento->tipo === 'ricevuto') {
            // Se era un pagamento ricevuto, decrementa il valore_totale
            DB::table('cantieri')
                ->where('id', $pagamento->id_cantiere)
                ->decrement('valore_totale', $pagamento->importo);
        }

        DB::table('cantieri_pagamenti')->where('id', $id)->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Segna un pagamento "da_ricevere" come "ricevuto"
     */
    private function segnaPagato($request)
    {
        $id = $request->input('id_pagamento');
        $dataPagamento = $request->input('data_pagamento');

        $pagamento = DB::table('cantieri_pagamenti')->where('id', $id)->first();

        if ($pagamento && $pagamento->tipo === 'da_ricevere') {
            // Aggiorna il pagamento
            DB::table('cantieri_pagamenti')->where('id', $id)->update([
                'tipo' => 'ricevuto',
                'data_pagamento' => $dataPagamento,
                'data_scadenza' => null,
                'updated_at' => now()
            ]);

            // Aggiorna il valore_totale del cantiere
            DB::table('cantieri')
                ->where('id', $pagamento->id_cantiere)
                ->increment('valore_totale', $pagamento->importo);
        }

        return response()->json(['success' => true]);
    }



    public function uploadAllegato(Request $request)
    {
        try {
            // Test connessione
            if ($request->input('test')) {
                return response()->json(['success' => true, 'message' => 'Route OK!']);
            }

            // Prendi i dati
            $cantiere_id = $request->input('id_cantiere');
            $descrizione = $request->input('descrizione', '');

            // Log per debug
            \Log::info('Upload iniziato', [
                'cantiere_id' => $cantiere_id,
                'has_file' => $request->hasFile('allegato')
            ]);

            // Controlla se c'è il file
            if (!$request->hasFile('allegato')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nessun file caricato'
                ]);
            }

            $file = $request->file('allegato');

            // Info file
            $nome_originale = $file->getClientOriginalName();
            $estensione = strtolower($file->getClientOriginalExtension());
            $nome_file = time() . '_' . uniqid() . '.' . $estensione;
            $dimensione = $file->getSize();

            // Crea cartella
            $directory = public_path('uploads/cantieri/' . $cantiere_id);
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }

            // Sposta file
            $file->move($directory, $nome_file);

            // Determina tipo
            $tipo = 'document';
            if (in_array($estensione, ['jpg', 'jpeg', 'png', 'gif'])) {
                $tipo = 'image';
            } elseif ($estensione == 'pdf') {
                $tipo = 'pdf';
            }

            // Formatta dimensione
            $size_formatted = round($dimensione / 1024, 2) . ' KB';
            if ($dimensione > 1048576) {
                $size_formatted = round($dimensione / 1048576, 2) . ' MB';
            }

            // Path relativo per salvare nel DB
            $path_db = 'uploads/cantieri/' . $cantiere_id . '/' . $nome_file;

            // Salva nel DB - metodo semplice
            $id = DB::table('cantieri_allegati')->insertGetId([
                'id_cantiere' => (int)$cantiere_id,
                'nome_file' => $nome_file,
                'nome_originale' => $nome_originale,
                'tipo' => $tipo,
                'dimensione' => $size_formatted,
                'descrizione' => $descrizione,
                'path' => $path_db,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            \Log::info('File salvato', [
                'id' => $id,
                'path' => $path_db
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File caricato!',
                'id' => $id,
                'path' => $path_db
            ]);

        } catch (\Exception $e) {
            \Log::error('Errore upload: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

            return response()->json([
                'success' => false,
                'message' => 'Errore: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Elimina allegato - versione semplificata
     */
    public function eliminaAllegato(Request $request)
    {
        try {
            $id = $request->input('id');

            // Trova allegato
            $allegato = DB::table('cantieri_allegati')->where('id', $id)->first();

            if (!$allegato) {
                return response()->json(['success' => false, 'message' => 'Allegato non trovato']);
            }

            // Elimina file
            $file_path = public_path($allegato->path);
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Elimina da DB
            DB::table('cantieri_allegati')->where('id', $id)->delete();

            return response()->json(['success' => true, 'message' => 'Eliminato!']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Errore: ' . $e->getMessage()]);
        }
    }

    /**
     * Lista allegati - versione semplificata
     */
    public function getAllegatiCantiere($cantiere_id)
    {
        try {
            $allegati = DB::table('cantieri_allegati')
                ->where('id_cantiere', $cantiere_id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($allegati);

        } catch (\Exception $e) {
            \Log::error('Errore lista allegati: ' . $e->getMessage());
            return response()->json([]);
        }
    }

    /**
     * Salva foto dalla camera - versione semplificata
     */
    public function salvaFoto(Request $request)
    {
        try {
            $cantiere_id = $request->input('id_cantiere');
            $foto_data = $request->input('foto_data');
            $descrizione = $request->input('descrizione', 'Foto scattata');

            // Decodifica base64
            $foto_data = str_replace('data:image/jpeg;base64,', '', $foto_data);
            $foto_data = str_replace(' ', '+', $foto_data);
            $imageData = base64_decode($foto_data);

            // Nome file
            $nome_file = 'foto_' . time() . '_' . uniqid() . '.jpg';
            $nome_originale = 'Foto_' . date('Y-m-d_H-i-s') . '.jpg';

            // Crea cartella
            $directory = public_path('uploads/cantieri/' . $cantiere_id);
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }

            // Salva file
            file_put_contents($directory . '/' . $nome_file, $imageData);

            // Dimensione
            $size = strlen($imageData);
            $size_formatted = round($size / 1024, 2) . ' KB';
            if ($size > 1048576) {
                $size_formatted = round($size / 1048576, 2) . ' MB';
            }

            // Path per DB
            $path_db = 'uploads/cantieri/' . $cantiere_id . '/' . $nome_file;

            // Salva in DB
            DB::table('cantieri_allegati')->insert([
                'id_cantiere' => (int)$cantiere_id,
                'nome_file' => $nome_file,
                'nome_originale' => $nome_originale,
                'tipo' => 'image',
                'dimensione' => $size_formatted,
                'descrizione' => $descrizione,
                'path' => $path_db,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Foto salvata!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Elimina allegato
     */


    /**
     * Funzione helper per determinare il tipo di file
     */
    private function determinaTipoFile($estensione)
    {
        $estensione = strtolower($estensione);

        $immagini = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
        $documenti = ['doc', 'docx', 'xls', 'xlsx', 'txt', 'rtf', 'odt', 'ods'];
        $pdf = ['pdf'];

        if (in_array($estensione, $immagini)) {
            return 'image';
        } elseif (in_array($estensione, $pdf)) {
            return 'pdf';
        } elseif (in_array($estensione, $documenti)) {
            return 'document';
        } else {
            return 'other';
        }
    }

    /**
     * Funzione helper per formattare le dimensioni file
     */
    private function formatBytes($size, $precision = 2)
    {
        if ($size <= 0) return '0 B';

        $base = log($size, 1024);
        $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');

        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
    }


// ================================
// MODIFICA ANCHE LA FUNZIONE dettaglioCantiere ESISTENTE
// ================================

// AGGIUNGI QUESTE FUNZIONI NEL TUO AziendaController ESISTENTE

    /**
     * Sincronizza mezzi da Flotta in Cloud
     */
    public function sincronizzaMezzi()
    {
        $this->is_loggato();
        $utente = session('utente');

        // Chiama API Flotta in Cloud
        $dispositivi = $this->chiamataFlottaAPI('devices');

        if (!$dispositivi) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero dati da Flotta in Cloud'
            ]);
        }

        $mezziSincronizzati = 0;
        $mezziAggiornati = 0;

        foreach ($dispositivi as $dispositivo) {
            $nome = $dispositivo['name'] ?? 'Dispositivo Sconosciuto';
            $kmAttuali = isset($dispositivo['odometer']) ? round($dispositivo['odometer'] / 1000) : 0; // Da metri a km

            // Verifica se il mezzo esiste già
            $mezzoEsistente = DB::table('mezzi')
                ->where('id_azienda', $utente->id_azienda)
                ->where('nome', $nome)
                ->where('flotta_in_cloud', 1)
                ->first();

            if ($mezzoEsistente) {
                // Aggiorna km esistente
                DB::table('mezzi')
                    ->where('id', $mezzoEsistente->id)
                    ->update([
                        'km_attuali' => $kmAttuali,
                        'updated_at' => now()
                    ]);
                $mezziAggiornati++;
            } else {
                // Inserisci nuovo mezzo
                DB::table('mezzi')->insert([
                    'id_azienda' => $utente->id_azienda,
                    'nome' => $nome,
                    'tipo' => $this->determinaTipoMezzo($nome),
                    'targa' => isset($dispositivo['numeric_label']) ? 'GPS-' . $dispositivo['numeric_label'] : 'N/A',
                    'anno_immatricolazione' => date('Y'),
                    'stato' => ($dispositivo['moving'] ?? false) ? 'In uso' : 'Disponibile',
                    'km_attuali' => $kmAttuali,
                    'km_warning' => 30000, // Default
                    'km_danger' => 50000,  // Default
                    'flotta_in_cloud' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $mezziSincronizzati++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Sincronizzazione completata! {$mezziSincronizzati} mezzi aggiunti, {$mezziAggiornati} aggiornati.",
            'mezzi_sincronizzati' => $mezziSincronizzati,
            'mezzi_aggiornati' => $mezziAggiornati
        ]);
    }

    /**
     * Aggiorna solo i km di tutti i mezzi da Flotta in Cloud
     */
    public function aggiornaKmMezzi()
    {
        $this->is_loggato();
        $utente = session('utente');

        // Chiama API Flotta in Cloud
        $dispositivi = $this->chiamataFlottaAPI('devices');

        if (!$dispositivi) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero dati da Flotta in Cloud'
            ]);
        }

        $mezziAggiornati = 0;
        $aggiornamenti = [];

        foreach ($dispositivi as $dispositivo) {
            $nome = $dispositivo['name'] ?? 'Dispositivo Sconosciuto';
            $kmAttuali = isset($dispositivo['odometer']) ? round($dispositivo['odometer'] / 1000) : 0;

            // Trova il mezzo corrispondente
            $mezzo = DB::table('mezzi')
                ->where('id_azienda', $utente->id_azienda)
                ->where('flotta_in_cloud', 1)
                ->where('nome', $nome)
                ->first();

            if ($mezzo) {
                $kmPrecedenti = $mezzo->km_attuali ?? 0;

                // Aggiorna i km
                DB::table('mezzi')
                    ->where('id', $mezzo->id)
                    ->update([
                        'km_attuali' => $kmAttuali,
                        'updated_at' => now()
                    ]);

                $aggiornamenti[] = [
                    'nome' => $nome,
                    'km_precedenti' => $kmPrecedenti,
                    'km_attuali' => $kmAttuali,
                    'differenza' => $kmAttuali - $kmPrecedenti
                ];

                $mezziAggiornati++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Km aggiornati per {$mezziAggiornati} mezzi",
            'mezzi_aggiornati' => $mezziAggiornati,
            'dettagli' => $aggiornamenti
        ]);
    }

    /**
     * Chiamata alle API di Flotta in Cloud
     */
    private function chiamataFlottaAPI($endpoint)
    {
        try {
            $email = 'pietro@coires.it';
            $token = '5746ba5b2a153828a27d3c6bb6a2505c';
            $apiUrl = 'https://api.flottaincloud.it/external_api/v1/';

            $auth = base64_encode($email . ':' . $token);

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $apiUrl . $endpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Basic ' . $auth,
                    'Accept: application/json'
                ],
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_USERAGENT => 'Laravel-App/1.0'
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error || $httpCode !== 200) {
                return null;
            }

            return json_decode($response, true);

        } catch (\Exception $e) {
            return null;
        }
    }


// Aggiungi questo metodo al tuo AziendaController
    public function migraDipendenti()
    {
        try {
            // Svuota la tabella per rifare tutto da capo
            DB::table('cantieri_operai_giorni')->truncate();

            $assegnazioni = DB::table('cantieri_operai')->get();
            $totale = 0;

            foreach ($assegnazioni as $ass) {
                $cantiere = DB::table('cantieri')->where('id', $ass->id_cantiere)->first();
                if (!$cantiere) continue;

                $inizio = new DateTime($cantiere->data_inizio);
                $fine = new DateTime($cantiere->data_fine);

                while ($inizio <= $fine) {
                    $giorno = (int)$inizio->format('w');
                    // Escludi weekend (0=domenica, 6=sabato)
                    if ($giorno !== 0 && $giorno !== 6) {
                        DB::table('cantieri_operai_giorni')->insert([
                            'id_cantiere' => $ass->id_cantiere,
                            'id_dipendente' => $ass->id_dipendente,
                            'data_lavoro' => $inizio->format('Y-m-d'),
                            'nome' => $ass->nome,
                            'cognome' => $ass->cognome,
                            'mansione' => $ass->mansione,
                            'id_azienda' => $ass->id_azienda,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        $totale++;
                    }
                    $inizio->add(new DateInterval('P1D'));
                }
            }

            return "FATTO! Migrati " . $assegnazioni->count() . " dipendenti in " . $totale . " giorni totali.";

        } catch (Exception $e) {
            return "ERRORE: " . $e->getMessage();
        }
    }

    /**
     * Determina il tipo di mezzo dal nome
     */
    private function determinaTipoMezzo($nome)
    {
        $nome = strtolower($nome);

        if (strpos($nome, 'escavat') !== false || strpos($nome, 'cat') !== false || strpos($nome, 'bobcat') !== false) {
            return 'Escavatore';
        } elseif (strpos($nome, 'pala') !== false) {
            return 'Pala meccanica';
        } elseif (strpos($nome, 'dumper') !== false) {
            return 'Dumper';
        } elseif (strpos($nome, 'auto') !== false || strpos($nome, 'furg') !== false) {
            return 'Automezzo';
        } else {
            return 'Mezzo d\'opera';
        }
    }



    public function salvaAssegnazioneGiorni(Request $request)
    {
        try {
            $session = Session::get('utente');
            $data = $request->json()->all();

            $idCantiere = $data['id_cantiere'];
            $idDipendente = $data['id_dipendente'];
            $giorni = $data['giorni'] ?? [];
            $dipendenteInfo = $data['dipendente_info'];

            // Rimuovi le assegnazioni esistenti per questo dipendente e cantiere
            DB::table('cantieri_operai_giorni')
                ->where('id_cantiere', $idCantiere)
                ->where('id_dipendente', $idDipendente)
                ->delete();

            // Inserisci le nuove assegnazioni
            foreach ($giorni as $giorno) {
                DB::table('cantieri_operai_giorni')->insert([
                    'id_cantiere' => $idCantiere,
                    'id_dipendente' => $idDipendente,
                    'data_lavoro' => $giorno,
                    'nome' => $dipendenteInfo['nome'],
                    'cognome' => $dipendenteInfo['cognome'],
                    'mansione' => $dipendenteInfo['mansione'],
                    'id_azienda' => $session->id_azienda,
                    'created_at' => now()
                ]);
            }

            // Aggiorna il costo del cantiere
            /*$this->aggiornaCostoStimatoCantiere($idCantiere);*/

            return response()->json([
                'success' => true,
                'message' => 'Assegnazioni salvate con successo!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nel salvataggio: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recupera le assegnazioni di un cantiere
     */
    public function getAssegnazioniCantiere($cantiereId)
    {
        try {
            $session = Session::get('utente');

            $assegnazioni = DB::table('cantieri_operai_giorni')
                ->where('id_cantiere', $cantiereId)
                ->where('id_azienda', $session->id_azienda)
                ->orderBy('data_lavoro')
                ->orderBy('nome')
                ->get();

            return response()->json($assegnazioni);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Errore nel caricamento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recupera i giorni assegnati per un dipendente specifico
     */
    public function getGiorniDipendente($cantiereId, $dipendenteId)
    {
        try {
            $session = Session::get('utente');

            $giorni = DB::table('cantieri_operai_giorni')
                ->where('id_cantiere', $cantiereId)
                ->where('id_dipendente', $dipendenteId)
                ->where('id_azienda', $session->id_azienda)
                ->pluck('data_lavoro')
                ->toArray();

            return response()->json($giorni);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Errore nel caricamento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rimuove tutte le assegnazioni di un dipendente da un cantiere
     */
    public function rimuoviAssegnazioneDipendente(Request $request)
    {
        try {
            $session = Session::get('utente');
            $data = $request->json()->all();

            $idCantiere = $data['id_cantiere'];
            $idDipendente = $data['id_dipendente'];

            DB::table('cantieri_operai_giorni')
                ->where('id_cantiere', $idCantiere)
                ->where('id_dipendente', $idDipendente)
                ->where('id_azienda', $session->id_azienda)
                ->delete();

            // Aggiorna il costo del cantiere
          /*  $this->aggiornaCostoStimatoCantiere($idCantiere);*/

            return response()->json([
                'success' => true,
                'message' => 'Dipendente rimosso con successo!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore nella rimozione: ' . $e->getMessage()
            ], 500);
        }
    }



// ===== AGGIUNGI QUESTI METODI AL TUO AziendaController ESISTENTE =====

    /**
     * Dashboard Report TMS - Pagina principale
     */
    public function reportTMS()
    {
        $this->is_loggato();
        $utente = session('utente');

        return view('azienda.reports_tms', compact('utente'));
    }

    /**
     * Genera report generico TMS (router principale)
     */
    public function generaReportTMS(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        $tipo = $request->get('tipo');
        $dataInizio = $request->get('data_inizio', Carbon::now()->subDays(30)->format('Y-m-d'));
        $dataFine = $request->get('data_fine', Carbon::now()->format('Y-m-d'));

        $data = [];

        switch($tipo) {
            case 'dispatch':
            case 'utilizzo_mezzi':
                // Report Mezzi
                $mezzi = DB::table('mezzi')
                    ->where('id_azienda', $utente->id_azienda)
                    ->select('id', 'targa', 'nome', 'tipo', 'km_attuali', 'stato')
                    ->get();

                $ordiniPerMezzo = DB::table('ordini_trasporto as ot')
                    ->leftJoin('mezzi as m', 'ot.id_mezzo', '=', 'm.id')
                    ->where('ot.id_azienda', $utente->id_azienda)
                    ->whereBetween('ot.data_ritiro', [$dataInizio, $dataFine])
                    ->select(
                        'm.targa',
                        'm.nome as nome_mezzo',
                        DB::raw('COUNT(ot.id) as ordini_assegnati'),
                        DB::raw('SUM(CASE WHEN ot.stato = "completato" THEN 1 ELSE 0 END) as ordini_completati'),
                        DB::raw('SUM(ot.importo) as fatturato_totale')
                    )
                    ->groupBy('m.id', 'm.targa', 'm.nome')
                    ->get();

                $data = [
                    'mezzi' => $mezzi,
                    'ordini_per_mezzo' => $ordiniPerMezzo,
                    'totale_mezzi' => $mezzi->count(),
                    'mezzi_attivi' => $mezzi->where('stato', 1)->count()
                ];
                break;

            case 'performance_autisti':
                // Report Autisti
                $autisti = DB::table('utenti')
                    ->where('id_azienda', $utente->id_azienda)
                    ->select('id', 'nome', 'cognome', 'email', 'costo_giornaliero')
                    ->get();

                $ordiniPerAutista = DB::table('ordini_trasporto as ot')
                    ->leftJoin('utenti as u', 'ot.id_autista', '=', 'u.id')
                    ->where('ot.id_azienda', $utente->id_azienda)
                    ->whereBetween('ot.data_ritiro', [$dataInizio, $dataFine])
                    ->select(
                        'u.nome',
                        'u.cognome',
                        DB::raw('COUNT(ot.id) as ordini_completati'),
                        DB::raw('SUM(ot.importo) as fatturato_generato')
                    )
                    ->groupBy('u.id', 'u.nome', 'u.cognome')
                    ->get();

                $data = [
                    'autisti' => $autisti,
                    'ordini_per_autista' => $ordiniPerAutista,
                    'totale_autisti' => $autisti->count()
                ];
                break;

            case 'costi_operativi':
            case 'roi_mezzi':
                // Report Costi
                $costiManutenzioni = DB::table('mezzi_manutenzioni as mm')
                    ->join('mezzi as m', 'mm.id_mezzo', '=', 'm.id')
                    ->where('m.id_azienda', $utente->id_azienda)
                    ->whereBetween('mm.data_operazione', [$dataInizio, $dataFine])
                    ->select(
                        'm.targa',
                        'm.nome',
                        DB::raw('SUM(mm.importo) as costo_manutenzioni'),
                        DB::raw('COUNT(mm.id) as numero_interventi')
                    )
                    ->groupBy('m.id', 'm.targa', 'm.nome')
                    ->get();

                $costiPerTipo = DB::table('mezzi_manutenzioni as mm')
                    ->join('mezzi as m', 'mm.id_mezzo', '=', 'm.id')
                    ->where('m.id_azienda', $utente->id_azienda)
                    ->whereBetween('mm.data_operazione', [$dataInizio, $dataFine])
                    ->select(
                        'mm.tipo',
                        DB::raw('SUM(mm.importo) as costo_totale'),
                        DB::raw('COUNT(mm.id) as numero_interventi')
                    )
                    ->groupBy('mm.tipo')
                    ->get();

                $data = [
                    'costi_per_mezzo' => $costiManutenzioni,
                    'costi_per_tipo' => $costiPerTipo,
                    'costo_totale' => $costiManutenzioni->sum('costo_manutenzioni')
                ];
                break;

            case 'manutenzioni_predittive':
                // Report Manutenzioni
                $manutenzioniRecenti = DB::table('mezzi_manutenzioni as mm')
                    ->join('mezzi as m', 'mm.id_mezzo', '=', 'm.id')
                    ->where('m.id_azienda', $utente->id_azienda)
                    ->whereBetween('mm.data_operazione', [$dataInizio, $dataFine])
                    ->select('mm.*', 'm.targa', 'm.nome as nome_mezzo')
                    ->orderBy('mm.data_operazione', 'desc')
                    ->limit(20)
                    ->get();

                $mezziManutenzione = DB::table('mezzi as m')
                    ->leftJoin('mezzi_manutenzioni as mm', 'm.id', '=', 'mm.id_mezzo')
                    ->where('m.id_azienda', $utente->id_azienda)
                    ->where('m.stato', 1)
                    ->select(
                        'm.id', 'm.targa', 'm.nome', 'm.km_attuali',
                        DB::raw('MAX(mm.km_operazione) as ultimo_tagliando_km'),
                        DB::raw('m.km_attuali - COALESCE(MAX(mm.km_operazione), 0) as km_dall_ultimo_tagliando')
                    )
                    ->groupBy('m.id', 'm.targa', 'm.nome', 'm.km_attuali')
                    ->get();

                $data = [
                    'manutenzioni_recenti' => $manutenzioniRecenti,
                    'mezzi_manutenzione' => $mezziManutenzione
                ];
                break;

            case 'scadenze_patenti':
                // Report Scadenze (simulato)
                $utentiSenzaDocumenti = DB::table('utenti')
                    ->where('id_azienda', $utente->id_azienda)
                    ->select('id', 'nome', 'cognome', 'email')
                    ->get();

                $data = [
                    'utenti_senza_documenti' => $utentiSenzaDocumenti,
                    'totale_utenti' => $utentiSenzaDocumenti->count()
                ];
                break;

            default:
                // Report Generale
                $totaleMezzi = DB::table('mezzi')->where('id_azienda', $utente->id_azienda)->count();
                $mezziAttivi = DB::table('mezzi')->where('id_azienda', $utente->id_azienda)->where('stato', 1)->count();
                $totaleOrdini = DB::table('ordini_trasporto')->where('id_azienda', $utente->id_azienda)->count();
                $totaleClienti = DB::table('clienti')->where('id_azienda', $utente->id_azienda)->count();

                $costiTotali = DB::table('mezzi_manutenzioni as mm')
                    ->join('mezzi as m', 'mm.id_mezzo', '=', 'm.id')
                    ->where('m.id_azienda', $utente->id_azienda)
                    ->whereBetween('mm.data_operazione', [$dataInizio, $dataFine])
                    ->sum('mm.importo');

                $data = [
                    'kpi' => [
                        'totale_mezzi' => $totaleMezzi,
                        'mezzi_attivi' => $mezziAttivi,
                        'totale_ordini' => $totaleOrdini,
                        'totale_clienti' => $totaleClienti,
                        'costi_periodo' => $costiTotali
                    ]
                ];
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'periodo' => [
                'inizio' => $dataInizio,
                'fine' => $dataFine
            ]
        ]);
    }

    /**
     * Genera Report Operativo TMS
     */
    public function generaReportOperativo(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        $dataInizio = $request->get('data_inizio', Carbon::now()->subDays(30)->format('Y-m-d'));
        $dataFine = $request->get('data_fine', Carbon::now()->format('Y-m-d'));
        $formato = $request->get('formato', 'view');

        $data = [
            'ordini_giornalieri' => $this->getOrdiniGiornalieri($utente->id_azienda, $dataInizio, $dataFine),
            'utilizzo_mezzi' => $this->getUtilizzoMezzi($utente->id_azienda, $dataInizio, $dataFine),
            'performance_autisti' => $this->getPerformanceAutisti($utente->id_azienda, $dataInizio, $dataFine),
            'stato_spedizioni' => $this->getStatoSpedizioni($utente->id_azienda, $dataInizio, $dataFine),
            'rotte_top' => $this->getRotteTop($utente->id_azienda, $dataInizio, $dataFine)
        ];

        if ($formato === 'pdf') {
            return $this->generaPDFReport('operativo', $data, $dataInizio, $dataFine);
        } elseif ($formato === 'excel') {
            return $this->generaExcelReport('operativo', $data, $dataInizio, $dataFine);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'periodo' => [
                'inizio' => $dataInizio,
                'fine' => $dataFine
            ]
        ]);
    }

    /**
     * Genera Report Finanziario TMS
     */
    public function generaReportFinanziario(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        $dataInizio = $request->get('data_inizio', Carbon::now()->subMonths(3)->format('Y-m-d'));
        $dataFine = $request->get('data_fine', Carbon::now()->format('Y-m-d'));

        $data = [
            'ricavi_mensili' => $this->getRicaviMensili($utente->id_azienda, $dataInizio, $dataFine),
            'costi_carburante' => $this->getCostiCarburante($utente->id_azienda, $dataInizio, $dataFine),
            'marginalita_clienti' => $this->getMarginalitaClienti($utente->id_azienda, $dataInizio, $dataFine),
            'roi_mezzi' => $this->getROIMezzi($utente->id_azienda, $dataInizio, $dataFine),
            'budget_vs_actual' => $this->getBudgetVsActual($utente->id_azienda, $dataInizio, $dataFine)
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Report Performance KPI
     */
    public function generaReportPerformance(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        $dataInizio = $request->get('data_inizio', Carbon::now()->subDays(7)->format('Y-m-d'));
        $dataFine = $request->get('data_fine', Carbon::now()->format('Y-m-d'));

        $data = [
            'on_time_delivery' => $this->getOnTimeDelivery($utente->id_azienda, $dataInizio, $dataFine),
            'efficienza_rotte' => $this->getEfficienzaRotte($utente->id_azienda, $dataInizio, $dataFine),
            'customer_satisfaction' => $this->getCustomerSatisfaction($utente->id_azienda, $dataInizio, $dataFine),
            'tempo_medio_consegna' => $this->getTempoMedioConsegna($utente->id_azienda, $dataInizio, $dataFine),
            'km_vuoto_vs_carico' => $this->getKmVuotoVsCarico($utente->id_azienda, $dataInizio, $dataFine)
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Report Compliance
     */
    public function generaReportCompliance(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        $dataInizio = $request->get('data_inizio', Carbon::now()->subDays(30)->format('Y-m-d'));
        $dataFine = $request->get('data_fine', Carbon::now()->format('Y-m-d'));

        $data = [
            'tempi_guida' => $this->getTempiGuida($utente->id_azienda, $dataInizio, $dataFine),
            'scadenze_documenti' => $this->getScadenzeDocumenti($utente->id_azienda),
            'trasporti_adr' => $this->getTrasportiADR($utente->id_azienda, $dataInizio, $dataFine),
            'violazioni' => $this->getViolazioni($utente->id_azienda, $dataInizio, $dataFine)
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Report Predittivi
     */
    public function generaReportPredittivo(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        $data = [
            'previsioni_domanda' => $this->getPrevisioniDomanda($utente->id_azienda),
            'manutenzioni_predittive' => $this->getManutenzioniPredittive($utente->id_azienda),
            'ottimizzazione_flotta' => $this->getOttimizzazioneFlotta($utente->id_azienda),
            'trend_costi' => $this->getTrendCosti($utente->id_azienda)
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

// ===== HELPER METHODS PER I DATI =====

    private function getOrdiniGiornalieri($idAzienda, $dataInizio, $dataFine)
    {
        return DB::table('ordini_trasporto')
            ->where('id_azienda', $idAzienda)
            ->whereBetween('data_ritiro', [$dataInizio, $dataFine])
            ->select(
                DB::raw('DATE(data_ritiro) as data'),
                DB::raw('COUNT(*) as totale_ordini'),
                DB::raw('SUM(CASE WHEN stato = "completato" THEN 1 ELSE 0 END) as completati'),
                DB::raw('SUM(CASE WHEN stato = "in_corso" THEN 1 ELSE 0 END) as in_corso'),
                DB::raw('SUM(CASE WHEN stato = "pianificato" THEN 1 ELSE 0 END) as pianificati'),
                DB::raw('SUM(importo) as fatturato_giorno')
            )
            ->groupBy(DB::raw('DATE(data_ritiro)'))
            ->orderBy('data')
            ->get();
    }

    private function getUtilizzoMezzi($idAzienda, $dataInizio, $dataFine)
    {
        return DB::table('mezzi as m')
            ->leftJoin('ordini_trasporto as ot', function ($join) use ($dataInizio, $dataFine) {
                $join->on('m.id', '=', 'ot.id_mezzo')
                    ->whereBetween('ot.data_ritiro', [$dataInizio, $dataFine]);
            })
            ->where('m.id_azienda', $idAzienda)
            ->where('m.stato', 1)
            ->select(
                'm.id',
                'm.targa',
                'm.nome',
                DB::raw('COUNT(ot.id) as ordini_assegnati'),
                DB::raw('SUM(CASE WHEN ot.stato = "completato" THEN 1 ELSE 0 END) as ordini_completati'),
                DB::raw('SUM(COALESCE(ot.km_percorsi, 0)) as km_totali'),
                DB::raw('AVG(CASE WHEN ot.stato = "completato" THEN ot.importo ELSE NULL END) as ricavo_medio')
            )
            ->groupBy('m.id', 'm.targa', 'm.nome')
            ->get();
    }

    private function getPerformanceAutisti($idAzienda, $dataInizio, $dataFine)
    {
        return DB::table('utenti as u')
            ->join('ordini_trasporto as ot', 'u.id', '=', 'ot.id_autista')
            ->where('u.id_azienda', $idAzienda)
            ->whereBetween('ot.data_ritiro', [$dataInizio, $dataFine])
            ->select(
                'u.id',
                'u.nome',
                'u.cognome',
                DB::raw('COUNT(ot.id) as ordini_completati'),
                DB::raw('SUM(ot.importo) as fatturato_generato'),
                DB::raw('SUM(COALESCE(ot.km_percorsi, 0)) as km_percorsi'),
                DB::raw('AVG(CASE WHEN ot.valutazione_cliente IS NOT NULL THEN ot.valutazione_cliente END) as rating_medio')
            )
            ->groupBy('u.id', 'u.nome', 'u.cognome')
            ->orderBy('fatturato_generato', 'desc')
            ->get();
    }

    private function getStatoSpedizioni($idAzienda, $dataInizio, $dataFine)
    {
        return DB::table('ordini_trasporto')
            ->where('id_azienda', $idAzienda)
            ->whereBetween('data_ritiro', [$dataInizio, $dataFine])
            ->select(
                'stato',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(importo) as valore_totale')
            )
            ->groupBy('stato')
            ->get();
    }

    private function getRotteTop($idAzienda, $dataInizio, $dataFine)
    {
        return DB::table('ordini_trasporto')
            ->where('id_azienda', $idAzienda)
            ->whereBetween('data_ritiro', [$dataInizio, $dataFine])
            ->where('stato', 'completato')
            ->select(
                DB::raw('CONCAT(indirizzo_ritiro, " → ", indirizzo_consegna) as rotta'),
                DB::raw('COUNT(*) as frequenza'),
                DB::raw('SUM(importo) as fatturato'),
                DB::raw('AVG(importo) as ricavo_medio'),
                DB::raw('SUM(COALESCE(km_percorsi, 45)) as km_totali')
            )
            ->groupBy('indirizzo_ritiro', 'indirizzo_consegna')
            ->orderBy('fatturato', 'desc')
            ->limit(10)
            ->get();
    }

    private function getRicaviMensili($idAzienda, $dataInizio, $dataFine)
    {
        return DB::table('ordini_trasporto')
            ->where('id_azienda', $idAzienda)
            ->whereBetween('data_ritiro', [$dataInizio, $dataFine])
            ->where('stato', 'completato')
            ->select(
                DB::raw('YEAR(data_ritiro) as anno'),
                DB::raw('MONTH(data_ritiro) as mese'),
                DB::raw('SUM(importo) as ricavi'),
                DB::raw('COUNT(*) as numero_ordini'),
                DB::raw('AVG(importo) as ricavo_medio')
            )
            ->groupBy(DB::raw('YEAR(data_ritiro)'), DB::raw('MONTH(data_ritiro)'))
            ->orderBy('anno', 'desc')
            ->orderBy('mese', 'desc')
            ->get();
    }

    private function getCostiCarburante($idAzienda, $dataInizio, $dataFine)
    {
        // Stima costi carburante basata su km
        return DB::table('ordini_trasporto as ot')
            ->join('mezzi as m', 'ot.id_mezzo', '=', 'm.id')
            ->where('m.id_azienda', $idAzienda)
            ->whereBetween('ot.data_ritiro', [$dataInizio, $dataFine])
            ->where('ot.stato', 'completato')
            ->select(
                'm.targa',
                'm.nome as nome_mezzo',
                DB::raw('SUM(COALESCE(ot.km_percorsi, 45)) as km_totali'),
                DB::raw('SUM(COALESCE(ot.km_percorsi, 45)) * 0.15 as costo_stimato')
            )
            ->groupBy('m.id', 'm.targa', 'm.nome')
            ->get();
    }

    private function getMarginalitaClienti($idAzienda, $dataInizio, $dataFine)
    {
        return DB::table('ordini_trasporto as ot')
            ->join('clienti as c', 'ot.id_cliente', '=', 'c.id')
            ->where('ot.id_azienda', $idAzienda)
            ->whereBetween('ot.data_ritiro', [$dataInizio, $dataFine])
            ->where('ot.stato', 'completato')
            ->select(
                'c.ragione_sociale',
                DB::raw('COUNT(ot.id) as numero_ordini'),
                DB::raw('SUM(ot.importo) as fatturato'),
                DB::raw('SUM(COALESCE(ot.km_percorsi, 45) * 0.20) as costi_stimati'),
                DB::raw('SUM(ot.importo) - SUM(COALESCE(ot.km_percorsi, 45) * 0.20) as margine'),
                DB::raw('(SUM(ot.importo) - SUM(COALESCE(ot.km_percorsi, 45) * 0.20)) / SUM(ot.importo) * 100 as margine_percentuale')
            )
            ->groupBy('c.id', 'c.ragione_sociale')
            ->orderBy('margine', 'desc')
            ->get();
    }

    private function getROIMezzi($idAzienda, $dataInizio, $dataFine)
    {
        return DB::table('mezzi as m')
            ->leftJoin('ordini_trasporto as ot', function ($join) use ($dataInizio, $dataFine) {
                $join->on('m.id', '=', 'ot.id_mezzo')
                    ->whereBetween('ot.data_ritiro', [$dataInizio, $dataFine])
                    ->where('ot.stato', 'completato');
            })
            ->leftJoin('mezzi_manutenzioni as mm', function ($join) use ($dataInizio, $dataFine) {
                $join->on('m.id', '=', 'mm.id_mezzo')
                    ->whereBetween('mm.data_operazione', [$dataInizio, $dataFine]);
            })
            ->where('m.id_azienda', $idAzienda)
            ->where('m.stato', 1)
            ->select(
                'm.id',
                'm.targa',
                'm.nome',
                'm.valore_acquisto',
                DB::raw('SUM(COALESCE(ot.importo, 0)) as ricavi'),
                DB::raw('SUM(COALESCE(mm.importo, 0)) as costi_manutenzione'),
                DB::raw('SUM(COALESCE(ot.km_percorsi, 0)) * 0.15 as costi_carburante'),
                DB::raw('(SUM(COALESCE(ot.importo, 0)) - SUM(COALESCE(mm.importo, 0)) - SUM(COALESCE(ot.km_percorsi, 0)) * 0.15) as profitto_netto'),
                DB::raw('CASE WHEN m.valore_acquisto > 0 THEN ((SUM(COALESCE(ot.importo, 0)) - SUM(COALESCE(mm.importo, 0)) - SUM(COALESCE(ot.km_percorsi, 0)) * 0.15) / m.valore_acquisto * 100) ELSE 0 END as roi_percentuale')
            )
            ->groupBy('m.id', 'm.targa', 'm.nome', 'm.valore_acquisto')
            ->orderBy('roi_percentuale', 'desc')
            ->get();
    }

    private function getBudgetVsActual($idAzienda, $dataInizio, $dataFine)
    {
        $actual = DB::table('ordini_trasporto')
            ->where('id_azienda', $idAzienda)
            ->whereBetween('data_ritiro', [$dataInizio, $dataFine])
            ->where('stato', 'completato')
            ->select(
                DB::raw('MONTH(data_ritiro) as mese'),
                DB::raw('SUM(importo) as fatturato_actual'),
                DB::raw('COUNT(*) as ordini_actual')
            )
            ->groupBy(DB::raw('MONTH(data_ritiro)'))
            ->get();

        // Budget di esempio - sostituisci con dati reali
        $budget = collect([
            ['mese' => 1, 'fatturato_budget' => 50000, 'ordini_budget' => 200],
            ['mese' => 2, 'fatturato_budget' => 52000, 'ordini_budget' => 210],
            ['mese' => 3, 'fatturato_budget' => 48000, 'ordini_budget' => 190],
            ['mese' => 4, 'fatturato_budget' => 55000, 'ordini_budget' => 220],
            ['mese' => 5, 'fatturato_budget' => 53000, 'ordini_budget' => 215],
            ['mese' => 6, 'fatturato_budget' => 58000, 'ordini_budget' => 230],
        ]);

        return $budget->map(function ($budgetItem) use ($actual) {
            $actualItem = $actual->firstWhere('mese', $budgetItem['mese']);
            return [
                'mese' => $budgetItem['mese'],
                'mese_nome' => Carbon::create()->month($budgetItem['mese'])->locale('it')->monthName,
                'fatturato_budget' => $budgetItem['fatturato_budget'],
                'fatturato_actual' => $actualItem ? $actualItem->fatturato_actual : 0,
                'ordini_budget' => $budgetItem['ordini_budget'],
                'ordini_actual' => $actualItem ? $actualItem->ordini_actual : 0,
                'variance_fatturato' => $actualItem ?
                    (($actualItem->fatturato_actual - $budgetItem['fatturato_budget']) / $budgetItem['fatturato_budget'] * 100) : -100
            ];
        });
    }

    private function getOnTimeDelivery($idAzienda, $dataInizio, $dataFine)
    {
        return DB::table('ordini_trasporto')
            ->where('id_azienda', $idAzienda)
            ->whereBetween('data_ritiro', [$dataInizio, $dataFine])
            ->where('stato', 'completato')
            ->select(
                DB::raw('DATE(data_ritiro) as data'),
                DB::raw('COUNT(*) as totale_consegne'),
                DB::raw('SUM(CASE WHEN data_consegna_effettiva <= data_consegna_prevista THEN 1 ELSE 0 END) as consegne_puntuali'),
                DB::raw('SUM(CASE WHEN data_consegna_effettiva <= data_consegna_prevista THEN 1 ELSE 0 END) / COUNT(*) * 100 as otd_percentuale')
            )
            ->groupBy(DB::raw('DATE(data_ritiro)'))
            ->orderBy('data')
            ->get();
    }

    private function getEfficienzaRotte($idAzienda, $dataInizio, $dataFine)
    {
        return DB::table('ordini_trasporto')
            ->where('id_azienda', $idAzienda)
            ->whereBetween('data_ritiro', [$dataInizio, $dataFine])
            ->where('stato', 'completato')
            ->select(
                DB::raw('CONCAT(indirizzo_ritiro, " → ", indirizzo_consegna) as rotta'),
                DB::raw('COUNT(*) as numero_trasporti'),
                DB::raw('AVG(COALESCE(km_percorsi, 45)) as km_medio'),
                DB::raw('AVG(importo) as ricavo_medio'),
                DB::raw('AVG(importo) / AVG(COALESCE(km_percorsi, 45)) as efficienza_euro_km')
            )
            ->groupBy('indirizzo_ritiro', 'indirizzo_consegna')
            ->having('numero_trasporti', '>=', 3)
            ->orderBy('efficienza_euro_km', 'desc')
            ->limit(20)
            ->get();
    }

    private function getCustomerSatisfaction($idAzienda, $dataInizio, $dataFine)
    {
        return DB::table('ordini_trasporto as ot')
            ->join('clienti as c', 'ot.id_cliente', '=', 'c.id')
            ->where('ot.id_azienda', $idAzienda)
            ->whereBetween('ot.data_ritiro', [$dataInizio, $dataFine])
            ->where('ot.stato', 'completato')
            ->whereNotNull('ot.valutazione_cliente')
            ->select(
                'c.ragione_sociale',
                DB::raw('COUNT(ot.id) as numero_valutazioni'),
                DB::raw('AVG(ot.valutazione_cliente) as rating_medio'),
                DB::raw('SUM(CASE WHEN ot.valutazione_cliente >= 4 THEN 1 ELSE 0 END) / COUNT(ot.id) * 100 as percentuale_soddisfazione')
            )
            ->groupBy('c.id', 'c.ragione_sociale')
            ->orderBy('rating_medio', 'desc')
            ->get();
    }

    private function getTempoMedioConsegna($idAzienda, $dataInizio, $dataFine)
    {
        return DB::table('ordini_trasporto')
            ->where('id_azienda', $idAzienda)
            ->whereBetween('data_ritiro', [$dataInizio, $dataFine])
            ->where('stato', 'completato')
            ->select(
                DB::raw('AVG(TIMESTAMPDIFF(HOUR, data_ritiro, data_consegna_effettiva)) as tempo_medio_ore'),
                DB::raw('COUNT(*) as totale_consegne')
            )
            ->first();
    }

    private function getKmVuotoVsCarico($idAzienda, $dataInizio, $dataFine)
    {
        return DB::table('ordini_trasporto')
            ->where('id_azienda', $idAzienda)
            ->whereBetween('data_ritiro', [$dataInizio, $dataFine])
            ->where('stato', 'completato')
            ->select(
                DB::raw('SUM(COALESCE(km_percorsi, 45)) as km_carico'),
                DB::raw('SUM(COALESCE(km_vuoto, 0)) as km_vuoto'),
                DB::raw('SUM(COALESCE(km_vuoto, 0)) / (SUM(COALESCE(km_percorsi, 45)) + SUM(COALESCE(km_vuoto, 0))) * 100 as percentuale_vuoto')
            )
            ->first();
    }

    private function getTempiGuida($idAzienda, $dataInizio, $dataFine)
    {
        return DB::table('utenti as u')
            ->leftJoin('ordini_trasporto as ot', 'u.id', '=', 'ot.id_autista')
            ->where('u.id_azienda', $idAzienda)
            ->whereBetween('ot.data_ritiro', [$dataInizio, $dataFine])
            ->select(
                'u.id',
                'u.nome',
                'u.cognome',
                DB::raw('COUNT(ot.id) as ordini_guidati'),
                DB::raw('SUM(COALESCE(ot.km_percorsi, 45)) as km_totali')
            )
            ->groupBy('u.id', 'u.nome', 'u.cognome')
            ->get();
    }

    private function getScadenzeDocumenti($idAzienda)
    {
        return DB::table('utenti')
            ->where('id_azienda', $idAzienda)
            ->select(
                'id',
                'nome',
                'cognome',
                DB::raw('CASE WHEN LENGTH(patente_scadenza) > 0 THEN DATEDIFF(STR_TO_DATE(patente_scadenza, "%Y-%m-%d"), CURDATE()) ELSE NULL END as giorni_scadenza_patente')
            )
            ->havingRaw('giorni_scadenza_patente IS NOT NULL AND giorni_scadenza_patente <= 90')
            ->orderBy('giorni_scadenza_patente', 'asc')
            ->get();
    }

    private function getTrasportiADR($idAzienda, $dataInizio, $dataFine)
    {
        // Placeholder - adatta secondo la tua struttura
        return collect([]);
    }

    private function getViolazioni($idAzienda, $dataInizio, $dataFine)
    {
        // Placeholder - adatta secondo la tua struttura
        return collect([]);
    }

    private function getPrevisioniDomanda($idAzienda)
    {
        $storico = DB::table('ordini_trasporto')
            ->where('id_azienda', $idAzienda)
            ->where('data_ritiro', '>=', Carbon::now()->subMonths(12))
            ->select(
                DB::raw('MONTH(data_ritiro) as mese'),
                DB::raw('COUNT(*) as numero_ordini'),
                DB::raw('SUM(importo) as fatturato')
            )
            ->groupBy(DB::raw('MONTH(data_ritiro)'))
            ->get();

        $mediaOrdini = $storico->avg('numero_ordini');

        $previsioni = [];
        for ($i = 1; $i <= 3; $i++) {
            $dataPrevisione = Carbon::now()->addMonths($i);
            $previsioni[] = [
                'mese' => $dataPrevisione->locale('it')->monthName,
                'ordini_previsti' => round($mediaOrdini * (1 + ($i * 0.05))) // Crescita 5% mensile
            ];
        }

        return ['previsioni' => $previsioni];
    }

    private function getManutenzioniPredittive($idAzienda)
    {
        return DB::table('mezzi as m')
            ->leftJoin('mezzi_manutenzioni as mm', 'm.id', '=', 'mm.id_mezzo')
            ->where('m.id_azienda', $idAzienda)
            ->where('m.stato', 1)
            ->select(
                'm.id',
                'm.targa',
                'm.nome',
                'm.km_attuali',
                DB::raw('MAX(mm.km_operazione) as km_ultima_manutenzione'),
                DB::raw('m.km_attuali - COALESCE(MAX(mm.km_operazione), 0) as km_dalla_manutenzione'),
                DB::raw('CASE 
                WHEN m.km_attuali - COALESCE(MAX(mm.km_operazione), 0) > 30000 THEN "URGENTE"
                WHEN m.km_attuali - COALESCE(MAX(mm.km_operazione), 0) > 20000 THEN "PROGRAMMATA"
                ELSE "OK"
            END as priorita_manutenzione')
            )
            ->groupBy('m.id', 'm.targa', 'm.nome', 'm.km_attuali')
            ->orderBy('km_dalla_manutenzione', 'desc')
            ->get();
    }

    private function getOttimizzazioneFlotta($idAzienda)
    {
        $utilizzoMezzi = DB::table('mezzi as m')
            ->leftJoin('ordini_trasporto as ot', function ($join) {
                $join->on('m.id', '=', 'ot.id_mezzo')
                    ->where('ot.data_ritiro', '>=', Carbon::now()->subDays(30));
            })
            ->where('m.id_azienda', $idAzienda)
            ->where('m.stato', 1)
            ->select(
                'm.id',
                'm.targa',
                'm.nome',
                DB::raw('COUNT(ot.id) as ordini_ultimi_30_giorni'),
                DB::raw('COUNT(ot.id) / 30 as utilizzo_medio_giornaliero')
            )
            ->groupBy('m.id', 'm.targa', 'm.nome')
            ->get();

        $raccomandazioni = [];
        foreach ($utilizzoMezzi as $mezzo) {
            if ($mezzo->utilizzo_medio_giornaliero < 0.3) {
                $raccomandazioni[] = [
                    'mezzo' => $mezzo->targa,
                    'tipo' => 'SOTTOUTILIZZATO',
                    'messaggio' => 'Mezzo sottoutilizzato'
                ];
            }
        }

        return [
            'utilizzo_mezzi' => $utilizzoMezzi,
            'raccomandazioni' => $raccomandazioni
        ];
    }

    private function getTrendCosti($idAzienda)
    {
        return DB::table('mezzi_manutenzioni as mm')
            ->join('mezzi as m', 'mm.id_mezzo', '=', 'm.id')
            ->where('m.id_azienda', $idAzienda)
            ->where('mm.data_operazione', '>=', Carbon::now()->subMonths(12))
            ->select(
                DB::raw('MONTH(mm.data_operazione) as mese'),
                DB::raw('SUM(mm.importo) as costi_manutenzione'),
                DB::raw('COUNT(mm.id) as numero_interventi')
            )
            ->groupBy(DB::raw('MONTH(mm.data_operazione)'))
            ->get();
    }


    /**
     * Metodi da aggiungere al controller Azienda per gestire gli ordini di trasporto
     * Aggiungi questi metodi nel tuo AziendaController.php
     */

    /**
     * Lista ordini di trasporto
     */

    /**
     * COPIA QUESTO METODO NEL TUO AziendaController.php
     * Sostituisci completamente il metodo ordiniTrasporto esistente
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

                // Inserisci l'ordine
                $idOrdine = DB::table('ordini_trasporto')->insertGetId([
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

                // DEBUG 1: Verifica ID ordine creato
                // dd('DEBUG 1 - ID Ordine creato:', $idOrdine);

                // Recupera dati cliente per il DDT
                $cliente = null;
                if ($request->input('id_cliente')) {
                    $cliente = DB::table('clienti')
                        ->where('id', $request->input('id_cliente'))
                        ->first();
                }

                // DEBUG 2: Verifica cliente
                // dd('DEBUG 2 - Cliente:', $cliente);

                // Genera numero DDT progressivo per l'anno corrente
                $anno = date('Y');
                $ultimoDdt = DB::table('documenti_trasporto')
                    ->where('id_azienda', $azienda->id)
                    ->where('tipo_documento', 'ddt')
                    ->whereYear('data_documento', $anno)
                    ->orderBy('id', 'desc')
                    ->first();

                // DEBUG 3: Verifica ultimo DDT
                // dd('DEBUG 3 - Ultimo DDT:', $ultimoDdt, 'Anno:', $anno);

                if ($ultimoDdt && preg_match('/(\d+)\/' . $anno . '/', $ultimoDdt->numero_documento, $matches)) {
                    $progressivo = intval($matches[1]) + 1;
                } else {
                    $progressivo = 1;
                }
                $numeroDdt = $progressivo . '/' . $anno;

                // DEBUG 4: Verifica numero DDT generato
                // dd('DEBUG 4 - Numero DDT:', $numeroDdt);

                // Prepara dati DDT
                $datiDdt = [
                    'id_ordine' => $idOrdine,
                    'tipo_documento' => 'ddt',
                    'numero_documento' => $numeroDdt,
                    'data_documento' => $request->input('data_ritiro'),
                    'mittente_nome' => $azienda->ragione_sociale ?? $azienda->nome ?? '',
                    'mittente_indirizzo' => $request->input('indirizzo_ritiro'),
                    'destinatario_nome' => $cliente->ragione_sociale ?? $cliente->nome ?? '',
                    'destinatario_indirizzo' => $request->input('indirizzo_consegna'),
                    'descrizione_merce' => $request->input('descrizione_merce'),
                    'peso_lordo' => $request->input('peso_kg') ?: null,
                    'peso_netto' => null,
                    'numero_colli' => null,
                    'valore_merce' => $request->input('importo') ?: null,
                    'note' => $request->input('note') ?: null,
                    'id_azienda' => $azienda->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // DEBUG 5: Verifica dati DDT prima dell'inserimento
                dd('DEBUG 5 - Dati DDT da inserire:', $datiDdt);

                // Crea DDT collegato all'ordine
                try {
                    DB::table('documenti_trasporto')->insert($datiDdt);
                } catch (\Exception $e) {
                    // DEBUG 6: Se c'è errore, mostralo
                    dd('DEBUG 6 - ERRORE inserimento DDT:', $e->getMessage(), $datiDdt);
                }

                return redirect('/azienda/ordini-trasporto')->with('success', 'Ordine e DDT creati con successo');
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

                // Aggiorna anche il DDT collegato (se esiste)
                $cliente = null;
                if ($request->input('id_cliente')) {
                    $cliente = DB::table('clienti')
                        ->where('id', $request->input('id_cliente'))
                        ->first();
                }

                DB::table('documenti_trasporto')
                    ->where('id_ordine', $idOrdine)
                    ->where('tipo_documento', 'ddt')
                    ->update([
                        'data_documento' => $request->input('data_ritiro'),
                        'mittente_indirizzo' => $request->input('indirizzo_ritiro'),
                        'destinatario_nome' => $cliente->ragione_sociale ?? $cliente->nome ?? '',
                        'destinatario_indirizzo' => $request->input('indirizzo_consegna'),
                        'descrizione_merce' => $request->input('descrizione_merce'),
                        'peso_lordo' => $request->input('peso_kg') ?: null,
                        'valore_merce' => $request->input('importo') ?: null,
                        'note' => $request->input('note') ?: null,
                        'updated_at' => now(),
                    ]);

                return redirect('/azienda/ordini-trasporto')->with('success', 'Ordine modificato con successo');
            }

            // Elimina ordine
            if ($request->has('elimina_ordine')) {
                $idOrdine = $request->input('id_ordine');

                // Elimina anche i documenti collegati
                DB::table('documenti_trasporto')
                    ->where('id_ordine', $idOrdine)
                    ->delete();

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

        return view('azienda.dettaglio_ordine_trasporto', compact('ordine'));
    }

    /**
     * Aggiungi rifornimento carburante
     */
    public function aggiungiRifornimento(Request $request, $id)
    {
        $this->is_loggato();
        $utente = session('utente');
        $dati = $request->all();

        // Calcola prezzo al litro
        $prezzoLitro = null;
        if (!empty($dati['litri']) && $dati['litri'] > 0 && !empty($dati['importo_totale']) && $dati['importo_totale'] > 0) {
            $prezzoLitro = round($dati['importo_totale'] / $dati['litri'], 3);
        }

        // Upload scontrino se presente
        $fotoScontrino = null;
        if ($request->hasFile('foto_scontrino')) {
            $file = $request->file('foto_scontrino');
            $nomeFile = 'scontrino_' . $id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/scontrini'), $nomeFile);
            $fotoScontrino = 'uploads/scontrini/' . $nomeFile;
        }

        // Calcola consumo (km/l) confrontando con rifornimento precedente (solo se pieno completo)
        $consumoCalcolato = null;
        if (!empty($dati['pieno']) && $dati['pieno'] == 1) {
            $precedente = DB::table('rifornimenti_carburante')
                ->where('id_mezzo', $id)
                ->where('id_azienda', $utente->id_azienda)
                ->where('pieno', 1)
                ->where('km_rifornimento', '<', $dati['km_rifornimento'])
                ->orderBy('km_rifornimento', 'desc')
                ->first();

            if ($precedente && $dati['litri'] > 0) {
                $kmPercorsi = $dati['km_rifornimento'] - $precedente->km_rifornimento;
                if ($kmPercorsi > 0) {
                    $consumoCalcolato = round($kmPercorsi / $dati['litri'], 2);
                }
            }
        }

        DB::table('rifornimenti_carburante')->insert([
            'id_mezzo' => $id,
            'id_azienda' => $utente->id_azienda,
            'data_rifornimento' => $dati['data_rifornimento'],
            'km_rifornimento' => $dati['km_rifornimento'],
            'litri' => $dati['litri'],
            'importo_totale' => $dati['importo_totale'] ?? 0,
            'prezzo_litro' => $prezzoLitro,
            'tipo_carburante' => $dati['tipo_carburante'] ?? 'diesel',
            'stazione_servizio' => $dati['stazione_servizio'] ?? null,
            'pieno' => $dati['pieno'] ?? 1,
            'foto_scontrino' => $fotoScontrino,
            'note' => $dati['note'] ?? null,
            'consumo_calcolato' => $consumoCalcolato,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Aggiorna km mezzo se quelli del rifornimento sono maggiori
        $mezzo = DB::table('mezzi')->where('id', $id)->first();
        if ($mezzo && $dati['km_rifornimento'] > ($mezzo->km_attuali ?? 0)) {
            DB::table('mezzi')->where('id', $id)->update([
                'km_attuali' => $dati['km_rifornimento'],
                'updated_at' => now(),
            ]);
        }

        return Redirect::to('azienda/mezzo/' . $id . '#carburante')
            ->with('success', 'Rifornimento registrato con successo!' .
                ($consumoCalcolato ? " Consumo calcolato: {$consumoCalcolato} km/l" : ''));
    }

    /**
     * Elimina rifornimento
     */
    public function eliminaRifornimento(Request $request, $id)
    {
        $this->is_loggato();
        $utente = session('utente');

        DB::table('rifornimenti_carburante')
            ->where('id', $request->input('id_rifornimento'))
            ->where('id_azienda', $utente->id_azienda)
            ->delete();

        return Redirect::to('azienda/mezzo/' . $id . '#carburante')
            ->with('success', 'Rifornimento eliminato!');
    }

    /**
     * Upload scontrino per rifornimento esistente
     */
    public function uploadScontrino(Request $request, $id)
    {
        $this->is_loggato();
        $utente = session('utente');

        if ($request->hasFile('foto_scontrino')) {
            $file = $request->file('foto_scontrino');
            $nomeFile = 'scontrino_' . $id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/scontrini'), $nomeFile);
            $fotoScontrino = 'uploads/scontrini/' . $nomeFile;

            DB::table('rifornimenti_carburante')
                ->where('id', $request->input('id_rifornimento'))
                ->where('id_azienda', $utente->id_azienda)
                ->update([
                    'foto_scontrino' => $fotoScontrino,
                    'updated_at' => now(),
                ]);
        }

        return Redirect::to('azienda/mezzo/' . $id . '#carburante')
            ->with('success', 'Scontrino caricato!');
    }





    /**
     * API per caricare tutti i report TMS
     */
    public function apiReportsTms(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        $dataInizio = $request->get('data_inizio', Carbon::now()->subDays(30)->format('Y-m-d'));
        $dataFine = $request->get('data_fine', Carbon::now()->format('Y-m-d'));

        try {
            // === KPI GENERALI ===
            $mezziAttivi = DB::table('mezzi')
                ->where('id_azienda', $utente->id_azienda)
                ->whereIn('stato', ['Disponibile', 'In uso'])
                ->count();

            $ordiniPeriodo = DB::table('ordini_trasporto')
                ->where('id_azienda', $utente->id_azienda)
                ->whereBetween('data_ritiro', [$dataInizio, $dataFine])
                ->selectRaw("
                COUNT(*) as totale,
                SUM(CASE WHEN stato = 'completato' THEN 1 ELSE 0 END) as completati,
                SUM(COALESCE(importo, 0)) as fatturato
            ")
                ->first();

            $costiPeriodo = DB::table('mezzi_manutenzioni')
                ->where('id_azienda', $utente->id_azienda)
                ->whereBetween('data_operazione', [$dataInizio, $dataFine])
                ->sum('importo');

            $kmPeriodo = DB::table('km_giornalieri')
                ->where('id_azienda', $utente->id_azienda)
                ->whereBetween('data', [$dataInizio, $dataFine])
                ->sum('km_percorsi');

            // === UTILIZZO MEZZI ===
            $utilizzoMezzi = DB::table('mezzi as m')
                ->leftJoin('ordini_trasporto as ot', function ($join) use ($dataInizio, $dataFine, $utente) {
                    $join->on('m.id', '=', 'ot.id_mezzo')
                        ->whereBetween('ot.data_ritiro', [$dataInizio, $dataFine])
                        ->where('ot.id_azienda', $utente->id_azienda);
                })
                ->where('m.id_azienda', $utente->id_azienda)
                ->whereIn('m.stato', ['Disponibile', 'In uso'])
                ->groupBy('m.id', 'm.nome', 'm.targa')
                ->selectRaw("
                m.id,
                m.nome,
                m.targa,
                COUNT(ot.id) as ordini_completati,
                SUM(COALESCE(ot.importo, 0)) as fatturato
            ")
                ->orderByDesc('ordini_completati')
                ->limit(10)
                ->get();

            // === PERFORMANCE AUTISTI ===
            $performanceAutisti = DB::table('utenti as u')
                ->join('dispositivi_tracking as dt', 'u.id', '=', 'dt.id_utente')
                ->leftJoin('ordini_trasporto as ot', function ($join) use ($dataInizio, $dataFine) {
                    $join->on('u.id', '=', 'ot.id_autista')
                        ->whereBetween('ot.data_ritiro', [$dataInizio, $dataFine]);
                })
                ->leftJoin('km_giornalieri as kg', function ($join) use ($dataInizio, $dataFine) {
                    $join->on('dt.id', '=', 'kg.id_dispositivo')
                        ->whereBetween('kg.data', [$dataInizio, $dataFine]);
                })
                ->where('u.id_azienda', $utente->id_azienda)
                ->where('dt.is_active', 1)
                ->groupBy('u.id', 'u.nome', 'u.cognome')
                ->selectRaw("
                u.id,
                u.nome,
                u.cognome,
                COUNT(DISTINCT ot.id) as ordini_completati,
                SUM(DISTINCT COALESCE(ot.importo, 0)) as fatturato,
                SUM(COALESCE(kg.km_percorsi, 0)) as km_percorsi
            ")
                ->orderByDesc('km_percorsi')
                ->limit(10)
                ->get();

            // === ANDAMENTO ORDINI (ultimi giorni del periodo) ===
            $andamentoOrdini = DB::table('ordini_trasporto')
                ->where('id_azienda', $utente->id_azienda)
                ->whereBetween('data_ritiro', [$dataInizio, $dataFine])
                ->groupBy('data_ritiro')
                ->selectRaw("
                data_ritiro as data,
                COUNT(*) as totale,
                SUM(CASE WHEN stato = 'completato' THEN 1 ELSE 0 END) as completati,
                SUM(COALESCE(importo, 0)) as fatturato
            ")
                ->orderByDesc('data_ritiro')
                ->limit(14)
                ->get();

            // === COSTI OPERATIVI (Manutenzioni) ===
            $costiOperativi = DB::table('mezzi_manutenzioni as mm')
                ->join('mezzi as m', 'mm.id_mezzo', '=', 'm.id')
                ->where('mm.id_azienda', $utente->id_azienda)
                ->whereBetween('mm.data_operazione', [$dataInizio, $dataFine])
                ->groupBy('m.id', 'm.nome', 'm.targa', 'mm.tipo')
                ->selectRaw("
                m.nome as nome_mezzo,
                m.targa,
                mm.tipo,
                COUNT(mm.id) as interventi,
                SUM(mm.importo) as costo
            ")
                ->orderByDesc('costo')
                ->limit(15)
                ->get();

            // === KM GPS per mezzo ===
            $kmGps = DB::table('km_giornalieri as kg')
                ->join('mezzi as m', 'kg.id_mezzo', '=', 'm.id')
                ->where('kg.id_azienda', $utente->id_azienda)
                ->whereBetween('kg.data', [$dataInizio, $dataFine])
                ->groupBy('m.id', 'm.nome', 'm.targa')
                ->selectRaw("
                m.nome as nome_mezzo,
                m.targa,
                COUNT(DISTINCT kg.data) as giorni_attivi,
                SUM(kg.km_percorsi) as km_totali,
                AVG(kg.km_percorsi) as media_giornaliera
            ")
                ->orderByDesc('km_totali')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'kpi' => [
                        'mezzi_attivi' => $mezziAttivi,
                        'totale_ordini' => $ordiniPeriodo->totale ?? 0,
                        'ordini_completati' => $ordiniPeriodo->completati ?? 0,
                        'fatturato_totale' => $ordiniPeriodo->fatturato ?? 0,
                        'km_totali' => round($kmPeriodo ?? 0, 1),
                        'costi_totali' => $costiPeriodo ?? 0
                    ],
                    'utilizzo_mezzi' => $utilizzoMezzi,
                    'performance_autisti' => $performanceAutisti,
                    'andamento_ordini' => $andamentoOrdini,
                    'costi_operativi' => $costiOperativi,
                    'km_gps' => $kmGps
                ],
                'periodo' => [
                    'inizio' => $dataInizio,
                    'fine' => $dataFine
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Errore Report TMS: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Errore nel caricamento dei report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export report in Excel/CSV
     */
    public function apiReportsTmsExport(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        $tipo = $request->get('tipo');
        $dataInizio = $request->get('data_inizio', Carbon::now()->subDays(30)->format('Y-m-d'));
        $dataFine = $request->get('data_fine', Carbon::now()->format('Y-m-d'));

        $data = [];
        $headers = [];
        $filename = "report_{$tipo}_{$dataInizio}_{$dataFine}";

        switch ($tipo) {
            case 'mezzi':
                $headers = ['Mezzo', 'Targa', 'Ordini Completati', 'Fatturato €'];
                $results = DB::table('mezzi as m')
                    ->leftJoin('ordini_trasporto as ot', function ($join) use ($dataInizio, $dataFine) {
                        $join->on('m.id', '=', 'ot.id_mezzo')
                            ->whereBetween('ot.data_ritiro', [$dataInizio, $dataFine]);
                    })
                    ->where('m.id_azienda', $utente->id_azienda)
                    ->groupBy('m.id', 'm.nome', 'm.targa')
                    ->selectRaw('m.nome, m.targa, COUNT(ot.id) as ordini, SUM(COALESCE(ot.importo, 0)) as fatturato')
                    ->orderByDesc('ordini')
                    ->get();
                foreach ($results as $r) {
                    $data[] = [$r->nome, $r->targa, $r->ordini, number_format($r->fatturato, 2, ',', '.')];
                }
                break;

            case 'autisti':
                $headers = ['Autista', 'Ordini', 'Km Percorsi', 'Fatturato €'];
                $results = DB::table('utenti as u')
                    ->join('dispositivi_tracking as dt', 'u.id', '=', 'dt.id_utente')
                    ->leftJoin('ordini_trasporto as ot', function ($join) use ($dataInizio, $dataFine) {
                        $join->on('u.id', '=', 'ot.id_autista')
                            ->whereBetween('ot.data_ritiro', [$dataInizio, $dataFine]);
                    })
                    ->leftJoin('km_giornalieri as kg', function ($join) use ($dataInizio, $dataFine) {
                        $join->on('dt.id', '=', 'kg.id_dispositivo')
                            ->whereBetween('kg.data', [$dataInizio, $dataFine]);
                    })
                    ->where('u.id_azienda', $utente->id_azienda)
                    ->groupBy('u.id', 'u.nome', 'u.cognome')
                    ->selectRaw("CONCAT(u.nome, ' ', COALESCE(u.cognome, '')) as autista, COUNT(DISTINCT ot.id) as ordini, SUM(COALESCE(kg.km_percorsi, 0)) as km, SUM(DISTINCT COALESCE(ot.importo, 0)) as fatturato")
                    ->get();
                foreach ($results as $r) {
                    $data[] = [$r->autista, $r->ordini, number_format($r->km, 1, ',', '.'), number_format($r->fatturato, 2, ',', '.')];
                }
                break;

            case 'ordini':
                $headers = ['Data', 'Totale Ordini', 'Completati', 'Fatturato €'];
                $results = DB::table('ordini_trasporto')
                    ->where('id_azienda', $utente->id_azienda)
                    ->whereBetween('data_ritiro', [$dataInizio, $dataFine])
                    ->groupBy('data_ritiro')
                    ->selectRaw("data_ritiro, COUNT(*) as totale, SUM(CASE WHEN stato = 'completato' THEN 1 ELSE 0 END) as completati, SUM(COALESCE(importo, 0)) as fatturato")
                    ->orderBy('data_ritiro')
                    ->get();
                foreach ($results as $r) {
                    $data[] = [$r->data_ritiro, $r->totale, $r->completati, number_format($r->fatturato, 2, ',', '.')];
                }
                break;

            case 'costi':
                $headers = ['Mezzo', 'Tipo Intervento', 'N. Interventi', 'Costo Totale €'];
                $results = DB::table('mezzi_manutenzioni as mm')
                    ->join('mezzi as m', 'mm.id_mezzo', '=', 'm.id')
                    ->where('mm.id_azienda', $utente->id_azienda)
                    ->whereBetween('mm.data_operazione', [$dataInizio, $dataFine])
                    ->groupBy('m.nome', 'mm.tipo')
                    ->selectRaw('m.nome, mm.tipo, COUNT(mm.id) as interventi, SUM(mm.importo) as costo')
                    ->orderByDesc('costo')
                    ->get();
                foreach ($results as $r) {
                    $data[] = [$r->nome, $r->tipo, $r->interventi, number_format($r->costo, 2, ',', '.')];
                }
                break;

            case 'km':
                $headers = ['Mezzo', 'Targa', 'Giorni Attivi', 'Km Totali', 'Media Km/Giorno'];
                $results = DB::table('km_giornalieri as kg')
                    ->join('mezzi as m', 'kg.id_mezzo', '=', 'm.id')
                    ->where('kg.id_azienda', $utente->id_azienda)
                    ->whereBetween('kg.data', [$dataInizio, $dataFine])
                    ->groupBy('m.nome', 'm.targa')
                    ->selectRaw('m.nome, m.targa, COUNT(DISTINCT kg.data) as giorni, SUM(kg.km_percorsi) as km_totali, AVG(kg.km_percorsi) as media')
                    ->orderByDesc('km_totali')
                    ->get();
                foreach ($results as $r) {
                    $data[] = [$r->nome, $r->targa, $r->giorni, number_format($r->km_totali, 1, ',', '.'), number_format($r->media, 1, ',', '.')];
                }
                break;

            default:
                return response()->json(['error' => 'Tipo report non valido'], 400);
        }

        // Genera CSV
        $output = fopen('php://temp', 'r+');

        // BOM per Excel UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Header
        fputcsv($output, $headers, ';');

        // Data
        foreach ($data as $row) {
            fputcsv($output, $row, ';');
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}.csv\"");
    }
}
