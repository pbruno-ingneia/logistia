<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class PlanningController extends Controller
{
    public function is_loggato()
    {
        if (!session()->has('utente')) {
            return Redirect::to('admin/login')->send();
        }
    }

    /**
     * Mostra griglia settimanale autisti con regole azienda
     */
    public function index(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        // Settimana corrente o navigazione
        $offset = (int) $request->get('settimana', 0);
        $lunedi = now()->startOfWeek(\Carbon\Carbon::MONDAY)->addWeeks($offset);
        $domenica = $lunedi->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);

        // Giorni della settimana
        $giorni = [];
        for ($i = 0; $i < 7; $i++) {
            $giorni[] = $lunedi->copy()->addDays($i);
        }

        // Regole azienda (prima delle query utenti, serve ruoli_ids)
        $regole = $this->getRegole($utente->id_azienda);

        // Tutti i ruoli dell'azienda (per il panel configurazione)
        $tuttiRuoli = DB::table('ruoli')
            ->where('id_azienda', $utente->id_azienda)
            ->orderBy('titolo')
            ->get();

        // Ruoli selezionati nelle regole
        $ruoliSelezionati = $regole->ruoli_ids ? json_decode($regole->ruoli_ids, true) : [];

        // Autisti filtrati per ruolo (se configurato, altrimenti tutti)
        if (!empty($ruoliSelezionati)) {
            $autisti = DB::table('utenti')
                ->join('utenti_ruoli', 'utenti.id', '=', 'utenti_ruoli.id_utente')
                ->where('utenti.id_azienda', $utente->id_azienda)
                ->whereIn('utenti_ruoli.id_ruolo', $ruoliSelezionati)
                ->select('utenti.*')
                ->distinct()
                ->orderBy('utenti.cognome')
                ->get();
        } else {
            $autisti = DB::table('utenti')
                ->where('id_azienda', $utente->id_azienda)
                ->orderBy('cognome')
                ->get();
        }

        // Planning settimana corrente
        $dataInizio = $lunedi->toDateString();
        $dataFine = $domenica->toDateString();

        $planningRows = DB::table('planning_autisti')
            ->where('id_azienda', $utente->id_azienda)
            ->whereBetween('data', [$dataInizio, $dataFine])
            ->get();

        // Indicizza planning per [id_autista][data]
        $planning = [];
        foreach ($planningRows as $row) {
            $planning[$row->id_autista][$row->data] = $row;
        }

        // Conta ordini per autista per giorno
        $ordiniPerGiorno = [];
        $ordiniRows = DB::table('ordini_trasporto')
            ->where('id_azienda', $utente->id_azienda)
            ->whereBetween('data_ritiro', [$dataInizio, $dataFine])
            ->whereNotNull('id_autista')
            ->select('id_autista', 'data_ritiro', DB::raw('COUNT(*) as totale'))
            ->groupBy('id_autista', 'data_ritiro')
            ->get();

        foreach ($ordiniRows as $row) {
            $ordiniPerGiorno[$row->id_autista][$row->data_ritiro] = $row->totale;
        }

        // Calcola stato cella per ogni autista/giorno
        $celle = [];
        foreach ($autisti as $autista) {
            foreach ($giorni as $giorno) {
                $dataStr = $giorno->toDateString();
                $stato = $planning[$autista->id][$dataStr]->tipo ?? null;
                $consecutivi = $this->calcolaGiorniConsecutivi($autista->id, $dataStr);

                $bloccato = false;
                $warning = false;

                if ($consecutivi >= $regole->max_giorni_consecutivi) {
                    $bloccato = true;
                } elseif ($consecutivi == $regole->max_giorni_consecutivi - 1 && $stato !== 'riposo' && $stato !== 'ferie' && $stato !== 'malattia') {
                    $warning = true;
                }

                $celle[$autista->id][$dataStr] = [
                    'tipo' => $stato,
                    'bloccato' => $bloccato,
                    'warning' => $warning,
                    'consecutivi' => $consecutivi,
                    'ordini' => $ordiniPerGiorno[$autista->id][$dataStr] ?? 0,
                    'row' => $planning[$autista->id][$dataStr] ?? null,
                ];
            }
        }

        // Ore settimanali per autista (warning)
        $oreSettimanali = [];
        foreach ($autisti as $autista) {
            $oreSettimanali[$autista->id] = $this->calcolaOreSettimana($autista->id, $dataInizio);
        }

        return view('azienda.planning_autisti', compact(
            'autisti', 'giorni', 'regole', 'celle', 'oreSettimanali',
            'lunedi', 'domenica', 'offset', 'utente',
            'tuttiRuoli', 'ruoliSelezionati'
        ));
    }

    /**
     * AJAX: salva tipo giorno (lavoro/riposo/ferie/malattia) + orari
     */
    public function salvaGiorno(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        $idAutista = (int) $request->input('id_autista');
        $data = $request->input('data');
        $tipo = $request->input('tipo');
        $oraInizio = $request->input('ora_inizio') ?: null;
        $oraFine = $request->input('ora_fine') ?: null;
        $note = $request->input('note') ?: null;

        // Verifica autista appartiene all'azienda
        $autista = DB::table('utenti')
            ->where('id', $idAutista)
            ->where('id_azienda', $utente->id_azienda)
            ->first();

        if (!$autista) {
            return response()->json(['success' => false, 'message' => 'Autista non trovato']);
        }

        // Controlla se giorno è bloccato per regole
        $regole = $this->getRegole($utente->id_azienda);
        $consecutivi = $this->calcolaGiorniConsecutivi($idAutista, $data);

        if ($consecutivi >= $regole->max_giorni_consecutivi && $tipo === 'lavoro') {
            return response()->json([
                'success' => false,
                'bloccato' => true,
                'message' => 'Autista in blocco riposo obbligatorio (' . $consecutivi . ' giorni consecutivi)'
            ]);
        }

        // Warning ore giornaliere
        $warningOre = null;
        if ($oraInizio && $oraFine) {
            $inizio = \Carbon\Carbon::createFromFormat('H:i', $oraInizio);
            $fine = \Carbon\Carbon::createFromFormat('H:i', $oraFine);
            $oreGiorno = $inizio->diffInMinutes($fine) / 60;

            if ($oreGiorno > $regole->ore_max_giornaliere) {
                $warningOre = 'Ore giornaliere superiori al limite (' . $regole->ore_max_giornaliere . 'h)';
            }

            // Warning ore riposo minime (controlla giorno precedente)
            $giornoPrecedente = \Carbon\Carbon::parse($data)->subDay()->toDateString();
            $planningPrec = DB::table('planning_autisti')
                ->where('id_autista', $idAutista)
                ->where('data', $giornoPrecedente)
                ->first();

            if ($planningPrec && $planningPrec->ora_fine) {
                $finePrecedente = \Carbon\Carbon::createFromFormat('H:i:s', $planningPrec->ora_fine);
                $oreRiposo = $finePrecedente->diffInMinutes($inizio) / 60;
                if ($oreRiposo < $regole->ore_riposo_minime) {
                    $warningOre = ($warningOre ? $warningOre . ' | ' : '') .
                        'Ore riposo insufficienti (' . round($oreRiposo, 1) . 'h < ' . $regole->ore_riposo_minime . 'h)';
                }
            }
        }

        // Salva o aggiorna
        $exists = DB::table('planning_autisti')
            ->where('id_autista', $idAutista)
            ->where('data', $data)
            ->first();

        $dati = [
            'id_azienda' => $utente->id_azienda,
            'id_autista' => $idAutista,
            'data' => $data,
            'tipo' => $tipo,
            'ora_inizio' => $oraInizio,
            'ora_fine' => $oraFine,
            'note' => $note,
            'updated_at' => now(),
        ];

        if ($exists) {
            DB::table('planning_autisti')
                ->where('id_autista', $idAutista)
                ->where('data', $data)
                ->update($dati);
        } else {
            $dati['created_at'] = now();
            DB::table('planning_autisti')->insert($dati);
        }

        return response()->json([
            'success' => true,
            'tipo' => $tipo,
            'warning' => $warningOre,
        ]);
    }

    /**
     * Salva regole lavoro azienda
     */
    public function salvaRegole(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        $exists = DB::table('regole_lavoro')
            ->where('id_azienda', $utente->id_azienda)
            ->first();

        $ruoliIds = $request->input('ruoli_ids', []);
        $ruoliIds = is_array($ruoliIds) ? array_map('intval', $ruoliIds) : [];

        $dati = [
            'max_giorni_consecutivi' => (int) $request->input('max_giorni_consecutivi', 5),
            'ore_max_giornaliere' => (float) $request->input('ore_max_giornaliere', 9.0),
            'ore_riposo_minime' => (float) $request->input('ore_riposo_minime', 11.0),
            'ore_max_settimanali' => (float) $request->input('ore_max_settimanali', 48.0),
            'giorni_riposo_obbligatori' => (int) $request->input('giorni_riposo_obbligatori', 1),
            'ruoli_ids' => !empty($ruoliIds) ? json_encode($ruoliIds) : null,
            'updated_at' => now(),
        ];

        if ($exists) {
            DB::table('regole_lavoro')
                ->where('id_azienda', $utente->id_azienda)
                ->update($dati);
        } else {
            $dati['id_azienda'] = $utente->id_azienda;
            $dati['created_at'] = now();
            DB::table('regole_lavoro')->insert($dati);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Helper: legge regole azienda o restituisce default
     */
    private function getRegole($idAzienda)
    {
        $regole = DB::table('regole_lavoro')
            ->where('id_azienda', $idAzienda)
            ->first();

        if (!$regole) {
            $regole = (object) [
                'max_giorni_consecutivi' => 5,
                'ore_max_giornaliere' => 9.0,
                'ore_riposo_minime' => 11.0,
                'ore_max_settimanali' => 48.0,
                'giorni_riposo_obbligatori' => 1,
                'ruoli_ids' => null,
            ];
        }

        return $regole;
    }

    /**
     * Conta giorni lavoro consecutivi PRIMA di $data per un autista
     */
    private function calcolaGiorniConsecutivi($idAutista, $data)
    {
        $consecutivi = 0;
        $giorno = \Carbon\Carbon::parse($data)->subDay();

        for ($i = 0; $i < 30; $i++) {
            $dataStr = $giorno->toDateString();
            $row = DB::table('planning_autisti')
                ->where('id_autista', $idAutista)
                ->where('data', $dataStr)
                ->first();

            if ($row && $row->tipo === 'lavoro') {
                $consecutivi++;
                $giorno->subDay();
            } else {
                break;
            }
        }

        return $consecutivi;
    }

    /**
     * Somma ore lavorate nella settimana (da lunedì $dataLunedi)
     */
    private function calcolaOreSettimana($idAutista, $dataLunedi)
    {
        $domenica = \Carbon\Carbon::parse($dataLunedi)->addDays(6)->toDateString();

        $rows = DB::table('planning_autisti')
            ->where('id_autista', $idAutista)
            ->where('tipo', 'lavoro')
            ->whereBetween('data', [$dataLunedi, $domenica])
            ->whereNotNull('ora_inizio')
            ->whereNotNull('ora_fine')
            ->get();

        $totaleMinuti = 0;
        foreach ($rows as $row) {
            $inizio = \Carbon\Carbon::createFromFormat('H:i:s', $row->ora_inizio);
            $fine = \Carbon\Carbon::createFromFormat('H:i:s', $row->ora_fine);
            $totaleMinuti += $inizio->diffInMinutes($fine);
        }

        return round($totaleMinuti / 60, 1);
    }

    /**
     * Verifica se un autista è bloccato in una data specifica (usato da TrasportiController)
     */
    public static function isAutistaBloccato($idAutista, $data, $idAzienda)
    {
        $regole = DB::table('regole_lavoro')
            ->where('id_azienda', $idAzienda)
            ->first();

        $maxGiorni = $regole ? $regole->max_giorni_consecutivi : 5;

        $consecutivi = 0;
        $giorno = \Carbon\Carbon::parse($data)->subDay();

        for ($i = 0; $i < 30; $i++) {
            $dataStr = $giorno->toDateString();
            $row = DB::table('planning_autisti')
                ->where('id_autista', $idAutista)
                ->where('data', $dataStr)
                ->first();

            if ($row && $row->tipo === 'lavoro') {
                $consecutivi++;
                $giorno->subDay();
            } else {
                break;
            }
        }

        return $consecutivi >= $maxGiorni;
    }

    /**
     * Storico ordini di un autista
     */
    public function storicoAutista(Request $request, $idAutista)
    {
        $this->is_loggato();
        $utente = session('utente');

        // Verifica che l'autista appartenga all'azienda
        $autista = DB::table('utenti')
            ->where('id', $idAutista)
            ->where('id_azienda', $utente->id_azienda)
            ->first();

        if (!$autista) {
            return redirect('/azienda/planning-autisti')->with('error', 'Autista non trovato');
        }

        // Filtri periodo
        $dataDa  = $request->get('data_da',  now()->startOfYear()->toDateString());
        $dataA   = $request->get('data_a',   now()->toDateString());
        $stato   = $request->get('stato', 'tutti');

        $query = DB::table('ordini_trasporto as ot')
            ->leftJoin('clienti as c', 'ot.id_cliente', '=', 'c.id')
            ->leftJoin('mezzi as m', 'ot.id_mezzo', '=', 'm.id')
            ->where('ot.id_autista', $idAutista)
            ->where('ot.id_azienda', $utente->id_azienda)
            ->whereBetween('ot.data_ritiro', [$dataDa, $dataA])
            ->select(
                'ot.id', 'ot.numero_ordine', 'ot.data_ritiro', 'ot.data_consegna',
                'ot.indirizzo_ritiro', 'ot.indirizzo_consegna',
                'ot.stato', 'ot.importo', 'ot.km_totali', 'ot.numero_colli',
                'ot.tipo_unita', 'ot.peso_kg', 'ot.note',
                'c.ragione_sociale as cliente_nome',
                'm.targa', 'm.nome as mezzo_nome'
            );

        if ($stato !== 'tutti') {
            $query->where('ot.stato', $stato);
        }

        $ordini = $query->orderBy('ot.data_ritiro', 'desc')->get();

        // Statistiche
        $stats = DB::table('ordini_trasporto')
            ->where('id_autista', $idAutista)
            ->where('id_azienda', $utente->id_azienda)
            ->whereBetween('data_ritiro', [$dataDa, $dataA])
            ->selectRaw('
                COUNT(*) as totale_ordini,
                SUM(importo) as totale_importo,
                SUM(km_totali) as totale_km,
                SUM(CASE WHEN stato = "completato" THEN 1 ELSE 0 END) as completati,
                SUM(CASE WHEN stato = "annullato" THEN 1 ELSE 0 END) as annullati
            ')
            ->first();

        // Giorni lavorati (dal planning) nel periodo
        $giorniLavorati = DB::table('planning_autisti')
            ->where('id_autista', $idAutista)
            ->where('tipo', 'lavoro')
            ->whereBetween('data', [$dataDa, $dataA])
            ->count();

        return view('azienda.storico_autista', compact(
            'autista', 'ordini', 'stats', 'giorniLavorati',
            'dataDa', 'dataA', 'stato', 'utente'
        ));
    }
}
