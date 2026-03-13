<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TrackingDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!session()->has('utente')) {
                return redirect('/azienda/login');
            }
            return $next($request);
        });
    }

    /**
     * Dashboard principale con mappa
     */
    public function index()
    {
        $utente = session('utente');
        $idAzienda = $utente->id_azienda;

        $mezzi = DB::table('mezzi')
            ->leftJoin('posizioni_live', 'mezzi.id', '=', 'posizioni_live.id_mezzo')
            ->leftJoin('dispositivi_tracking', function ($join) {
                $join->on('mezzi.id', '=', 'dispositivi_tracking.id_mezzo')
                    ->where('dispositivi_tracking.is_active', 1);
            })
            ->where('mezzi.id_azienda', $idAzienda)
            ->where('mezzi.tracking_attivo', 1)
            ->select([
                'mezzi.id',
                'mezzi.nome',
                'mezzi.targa',
                'mezzi.km_attuali',
                'posizioni_live.lat',
                'posizioni_live.lng',
                'posizioni_live.speed',
                'posizioni_live.is_moving',
                'posizioni_live.battery_level',
                'posizioni_live.recorded_at',
                'dispositivi_tracking.configurato',
            ])
            ->get();

        $stats = [
            'totali' => $mezzi->count(),
            'in_movimento' => $mezzi->where('is_moving', true)->count(),
            'fermi' => $mezzi->where('is_moving', false)->whereNotNull('lat')->count(),
            'offline' => $mezzi->whereNull('lat')->count(),
            'km_oggi' => DB::table('km_giornalieri')
                ->where('id_azienda', $idAzienda)
                ->where('data', date('Y-m-d'))
                ->sum('km_percorsi'),
        ];

        $kmOggiPerMezzo = DB::table('km_giornalieri')
            ->where('id_azienda', $idAzienda)
            ->where('data', date('Y-m-d'))
            ->pluck('km_percorsi', 'id_mezzo');

        return view('azienda.tracking_dashboard', compact('mezzi', 'stats', 'utente', 'kmOggiPerMezzo'));
    }

    /**
     * API AJAX per aggiornamento mappa
     */
    public function livePositions()
    {
        $utente = session('utente');
        $idAzienda = $utente->id_azienda;

        $posizioni = DB::table('posizioni_live')
            ->join('mezzi', 'posizioni_live.id_mezzo', '=', 'mezzi.id')
            ->where('posizioni_live.id_azienda', $idAzienda)
            ->select([
                'mezzi.id',
                'mezzi.nome',
                'mezzi.targa',
                'mezzi.km_attuali',
                'posizioni_live.lat',
                'posizioni_live.lng',
                'posizioni_live.speed',
                'posizioni_live.is_moving',
                'posizioni_live.battery_level',
                'posizioni_live.recorded_at',
            ])
            ->get();

        $kmOggi = DB::table('km_giornalieri')
            ->where('id_azienda', $idAzienda)
            ->where('data', date('Y-m-d'))
            ->pluck('km_percorsi', 'id_mezzo');

        $posizioni = $posizioni->map(function ($pos) use ($kmOggi) {
            $pos->km_oggi = round($kmOggi[$pos->id] ?? 0, 1);
            $pos->online = strtotime($pos->recorded_at) > strtotime('-5 minutes');
            return $pos;
        });

        return response()->json([
            'success' => true,
            'posizioni' => $posizioni,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Report km
     */
    public function reportKm(Request $request)
    {
        $utente = session('utente');
        $idAzienda = $utente->id_azienda;

        $dataInizio = $request->get('da', date('Y-m-01'));
        $dataFine = $request->get('a', date('Y-m-d'));

        $report = DB::table('km_giornalieri')
            ->join('mezzi', 'km_giornalieri.id_mezzo', '=', 'mezzi.id')
            ->where('km_giornalieri.id_azienda', $idAzienda)
            ->whereBetween('data', [$dataInizio, $dataFine])
            ->groupBy('mezzi.id', 'mezzi.nome', 'mezzi.targa')
            ->select([
                'mezzi.id',
                'mezzi.nome',
                'mezzi.targa',
                DB::raw('SUM(km_percorsi) as km_totali'),
                DB::raw('SUM(tempo_movimento_minuti) as minuti_movimento'),
                DB::raw('AVG(velocita_media) as velocita_media'),
                DB::raw('MAX(velocita_max) as velocita_max'),
                DB::raw('COUNT(DISTINCT data) as giorni_attivi'),
            ])
            ->orderBy('km_totali', 'desc')
            ->get();

        $kmGiornalieri = DB::table('km_giornalieri')
            ->where('id_azienda', $idAzienda)
            ->whereBetween('data', [$dataInizio, $dataFine])
            ->groupBy('data')
            ->select(['data', DB::raw('SUM(km_percorsi) as km_totali')])
            ->orderBy('data')
            ->get();

        return view('azienda.tracking_report_km', compact('report', 'kmGiornalieri', 'dataInizio', 'dataFine', 'utente'));
    }

    /**
     * Lista dispositivi
     */
    public function listaDispositivi()
    {
        $utente = session('utente');
        $idAzienda = $utente->id_azienda;

        $dispositivi = DB::table('dispositivi_tracking')
            ->leftJoin('mezzi', 'dispositivi_tracking.id_mezzo', '=', 'mezzi.id')
            ->leftJoin('utenti', 'dispositivi_tracking.id_utente', '=', 'utenti.id')
            ->where('dispositivi_tracking.id_azienda', $idAzienda)
            ->select([
                'dispositivi_tracking.*',
                'mezzi.nome as nome_mezzo',
                'mezzi.targa',
                'utenti.nome as nome_utente',
                'utenti.cognome as cognome_utente',
                'utenti.email as email_utente',
            ])
            ->orderBy('dispositivi_tracking.created_at', 'desc')
            ->get();

        // Mezzi senza dispositivo
        $mezziLiberi = DB::table('mezzi')
            ->where('id_azienda', $idAzienda)
            ->whereNotIn('id', function ($query) use ($idAzienda) {
                $query->select('id_mezzo')
                    ->from('dispositivi_tracking')
                    ->where('id_azienda', $idAzienda)
                    ->where('is_active', 1)
                    ->whereNotNull('id_mezzo');
            })
            ->get(['id', 'nome', 'targa']);

        // Utenti dell'azienda (per il dropdown)
        $utentiAzienda = DB::table('utenti')
            ->where('id_azienda', $idAzienda)
            ->get(['id', 'nome', 'cognome', 'email']);

        return view('azienda.tracking_dispositivi', compact('dispositivi', 'mezziLiberi', 'utentiAzienda', 'utente'));
    }

    /**
     * Crea dispositivo
     */
    public function creaDispositivo(Request $request)
    {
        $utente = session('utente');

        $validated = $request->validate([
            'nome' => 'required|string|max:100',
            'id_mezzo' => 'required|exists:mezzi,id',
            'id_utente' => 'required|exists:utenti,id',
        ]);

        $token = Str::random(64);

        $id = DB::table('dispositivi_tracking')->insertGetId([
            'id_azienda' => $utente->id_azienda,
            'id_mezzo' => $validated['id_mezzo'],
            'id_utente' => $validated['id_utente'],
            'device_token' => $token,
            'nome' => $validated['nome'],
            'targa_mezzo' => DB::table('mezzi')->where('id', $validated['id_mezzo'])->value('targa'),
            'is_active' => true,
            'configurato' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'dispositivo' => [
                'id' => $id,
                'nome' => $validated['nome'],
            ]
        ]);
    }

    /**
     * Associa mezzo
     */
    public function associaMezzo(Request $request, $id)
    {
        $utente = session('utente');

        $validated = $request->validate([
            'id_mezzo' => 'required|exists:mezzi,id',
        ]);

        DB::table('dispositivi_tracking')
            ->where('id', $id)
            ->where('id_azienda', $utente->id_azienda)
            ->update([
                'id_mezzo' => $validated['id_mezzo'],
                'targa_mezzo' => DB::table('mezzi')->where('id', $validated['id_mezzo'])->value('targa'),
                'configurato' => false,
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    /**
     * Disattiva dispositivo
     */
    public function eliminaDispositivo($id)
    {
        $utente = session('utente');

        $dispositivo = DB::table('dispositivi_tracking')
            ->where('id', $id)
            ->where('id_azienda', $utente->id_azienda)
            ->first();

        DB::table('dispositivi_tracking')
            ->where('id', $id)
            ->update(['is_active' => false, 'updated_at' => now()]);

        if ($dispositivo && $dispositivo->id_mezzo) {
            DB::table('mezzi')
                ->where('id', $dispositivo->id_mezzo)
                ->update(['tracking_attivo' => false]);
        }

        return response()->json(['success' => true]);
    }
}
