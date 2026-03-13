<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Barryvdh\DomPdf\Facade\Pdf as PDF;
use Mpdf\Mpdf;
class AnalyticsController extends Controller
{
    public function is_loggato()
    {
        if (!session()->has('utente')) {
            return Redirect::to('admin/login')->send();
        }
    }

    /**
     * Dashboard KPI principale
     */
    public function dashboardKPI(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        // Periodo di analisi (default: ultimi 3 mesi)
        $dataInizio = $request->get('data_inizio', Carbon::now()->subMonths(3)->format('Y-m-d'));
        $dataFine = $request->get('data_fine', Carbon::now()->format('Y-m-d'));

        // KPI Principali
        $kpiPrincipali = $this->calcolaKPIPrincipali($utente->id_azienda, $dataInizio, $dataFine);

        // Performance per Cliente
        $performanceClienti = $this->getPerformanceClienti($utente->id_azienda, $dataInizio, $dataFine);

        // Performance per Mezzo
        $performanceMezzi = $this->getPerformanceMezzi($utente->id_azienda, $dataInizio, $dataFine);

        // Trend mensili
        $trendMensili = $this->getTrendMensili($utente->id_azienda, $dataInizio, $dataFine);

        // Analisi redditività per rotta
        $redditivitaRotte = $this->getRedditivitaRotte($utente->id_azienda, $dataInizio, $dataFine);

        return view('azienda.analytics_dashboard', compact(
            'kpiPrincipali',
            'performanceClienti',
            'performanceMezzi',
            'trendMensili',
            'redditivitaRotte',
            'dataInizio',
            'dataFine',
            'utente'
        ));
    }

    /**
     * Calcola KPI principali
     */
    private function calcolaKPIPrincipali($idAzienda, $dataInizio, $dataFine)
    {
        // Query base ordini nel periodo
        $ordini = DB::table('ordini_trasporto')
            ->where('id_azienda', $idAzienda)
            ->whereBetween('data_ritiro', [$dataInizio, $dataFine])
            ->where('stato', 'completato')
            ->get();

        $fatturato = $ordini->sum('importo');
        $numeroOrdini = $ordini->count();
        $kmTotali = $ordini->sum('km_percorsi') ?: $ordini->count() * 45; // Fallback se km non registrati

        // Calcola costi operativi stimati
        $costiOperativi = $this->calcolaCostiOperativi($idAzienda, $dataInizio, $dataFine, $kmTotali);

        $margineOperativo = $fatturato - $costiOperativi;
        $marginePct = $fatturato > 0 ? ($margineOperativo / $fatturato) * 100 : 0;

        // Ricavi per ordine e per km
        $ricavoMedioOrdine = $numeroOrdini > 0 ? $fatturato / $numeroOrdini : 0;
        $ricavoPerKm = $kmTotali > 0 ? $fatturato / $kmTotali : 0;

        // Confronto periodo precedente
        $dataInizioPrecedente = Carbon::parse($dataInizio)->subDays(
            Carbon::parse($dataFine)->diffInDays(Carbon::parse($dataInizio))
        )->format('Y-m-d');

        $fatturatoPresedente = DB::table('ordini_trasporto')
            ->where('id_azienda', $idAzienda)
            ->whereBetween('data_ritiro', [$dataInizioPrecedente, $dataInizio])
            ->where('stato', 'completato')
            ->sum('importo');

        $crescitaFatturato = $fatturatoPresedente > 0 ?
            (($fatturato - $fatturatoPresedente) / $fatturatoPresedente) * 100 : 0;

        return [
            'fatturato' => $fatturato,
            'numero_ordini' => $numeroOrdini,
            'km_totali' => $kmTotali,
            'costi_operativi' => $costiOperativi,
            'margine_operativo' => $margineOperativo,
            'margine_percentuale' => $marginePct,
            'ricavo_medio_ordine' => $ricavoMedioOrdine,
            'ricavo_per_km' => $ricavoPerKm,
            'crescita_fatturato' => $crescitaFatturato,
            'tasso_completamento' => $this->getTassoCompletamento($idAzienda, $dataInizio, $dataFine)
        ];
    }

    /**
     * Calcola costi operativi stimati
     */
    private function calcolaCostiOperativi($idAzienda, $dataInizio, $dataFine, $kmTotali)
    {
        // Costi carburante (stima €0.15/km)
        $costiCarburante = $kmTotali * 0.15;

        // Costi manutenzione nel periodo
        $costiManutenzione = DB::table('mezzi_manutenzioni as mm')
            ->join('mezzi as m', 'mm.id_mezzo', '=', 'm.id')
            ->where('m.id_azienda', $idAzienda)
            ->whereBetween('mm.data_operazione', [$dataInizio, $dataFine])
            ->sum('mm.importo');

        // Costi fissi stimati (ammortamento, assicurazioni, etc.)
        $giorniPeriodo = Carbon::parse($dataFine)->diffInDays(Carbon::parse($dataInizio)) + 1;
        $costiFissiGiornalieri = 50; // €50/giorno per mezzo

        $numeroMezzi = DB::table('mezzi')
            ->where('id_azienda', $idAzienda)
            ->where('stato', 1)
            ->count();

        $costiFissi = $numeroMezzi * $costiFissiGiornalieri * $giorniPeriodo;

        return $costiCarburante + $costiManutenzione + $costiFissi;
    }

    /**
     * Performance per cliente
     */
    private function getPerformanceClienti($idAzienda, $dataInizio, $dataFine)
    {
        return DB::table('ordini_trasporto as ot')
            ->join('clienti as c', 'ot.id_cliente', '=', 'c.id')
            ->where('ot.id_azienda', $idAzienda)
            ->whereBetween('ot.data_ritiro', [$dataInizio, $dataFine])
            ->where('ot.stato', 'completato')
            ->select(
                'c.id',
                'c.ragione_sociale as nome_cliente',
                DB::raw('COUNT(ot.id) as numero_ordini'),
                DB::raw('SUM(ot.importo) as fatturato'),
                DB::raw('AVG(ot.importo) as ricavo_medio'),
                DB::raw('SUM(COALESCE(ot.km_percorsi, 45)) as km_totali'),
                DB::raw('SUM(ot.importo) / SUM(COALESCE(ot.km_percorsi, 45)) as ricavo_per_km')
            )
            ->groupBy('c.id', 'c.ragione_sociale')
            ->orderBy('fatturato', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Performance per mezzo
     */
    private function getPerformanceMezzi($idAzienda, $dataInizio, $dataFine)
    {
        $performance = DB::table('ordini_trasporto as ot')
            ->leftJoin('mezzi as m', 'ot.id_mezzo', '=', 'm.id')
            ->where('ot.id_azienda', $idAzienda)
            ->whereBetween('ot.data_ritiro', [$dataInizio, $dataFine])
            ->where('ot.stato', 'completato')
            ->whereNotNull('ot.id_mezzo')
            ->select(
                'm.id',
                'm.targa',
                'm.nome as nome_mezzo',
                DB::raw('COUNT(ot.id) as numero_ordini'),
                DB::raw('SUM(ot.importo) as fatturato'),
                DB::raw('SUM(COALESCE(ot.km_percorsi, 45)) as km_totali'),
                DB::raw('AVG(ot.importo) as ricavo_medio')
            )
            ->groupBy('m.id', 'm.targa', 'm.nome')
            ->orderBy('fatturato', 'desc')
            ->get();

        // Aggiungi costi e marginalità per ogni mezzo
        foreach ($performance as $mezzo) {
            $costiCarburante = $mezzo->km_totali * 0.15;
            $costiManutenzione = DB::table('mezzi_manutenzioni')
                ->where('id_mezzo', $mezzo->id)
                ->whereBetween('data_operazione', [$dataInizio, $dataFine])
                ->sum('importo');

            $mezzo->costi_totali = $costiCarburante + $costiManutenzione;
            $mezzo->margine = $mezzo->fatturato - $mezzo->costi_totali;
            $mezzo->margine_percentuale = $mezzo->fatturato > 0 ?
                ($mezzo->margine / $mezzo->fatturato) * 100 : 0;
        }

        return $performance;
    }

    /**
     * Trend mensili
     */
    private function getTrendMensili($idAzienda, $dataInizio, $dataFine)
    {
        return DB::table('ordini_trasporto')
            ->where('id_azienda', $idAzienda)
            ->whereBetween('data_ritiro', [$dataInizio, $dataFine])
            ->where('stato', 'completato')
            ->select(
                DB::raw('YEAR(data_ritiro) as anno'),
                DB::raw('MONTH(data_ritiro) as mese'),
                DB::raw('COUNT(*) as numero_ordini'),
                DB::raw('SUM(importo) as fatturato'),
                DB::raw('SUM(COALESCE(km_percorsi, 45)) as km_totali'),
                DB::raw('AVG(importo) as ricavo_medio')
            )
            ->groupBy(DB::raw('YEAR(data_ritiro)'), DB::raw('MONTH(data_ritiro)'))
            ->orderBy('anno', 'asc')
            ->orderBy('mese', 'asc')
            ->get()
            ->map(function($item) {
                $item->mese_nome = Carbon::create()->month($item->mese)->locale('it')->monthName;
                $item->periodo = $item->mese_nome . ' ' . $item->anno;
                return $item;
            });
    }

    /**
     * Redditività per rotta (città di partenza - arrivo)
     */
    private function getRedditivitaRotte($idAzienda, $dataInizio, $dataFine)
    {
        $ordini = DB::table('ordini_trasporto')
            ->where('id_azienda', $idAzienda)
            ->whereBetween('data_ritiro', [$dataInizio, $dataFine])
            ->where('stato', 'completato')
            ->select('indirizzo_ritiro', 'indirizzo_consegna', 'importo', 'km_percorsi')
            ->get();

        $rotte = [];
        foreach ($ordini as $ordine) {
            $cittaPartenza = $this->estraiCitta($ordine->indirizzo_ritiro);
            $cittaArrivo = $this->estraiCitta($ordine->indirizzo_consegna);
            $rotta = $cittaPartenza . ' → ' . $cittaArrivo;

            if (!isset($rotte[$rotta])) {
                $rotte[$rotta] = [
                    'rotta' => $rotta,
                    'numero_ordini' => 0,
                    'fatturato' => 0,
                    'km_totali' => 0
                ];
            }

            $rotte[$rotta]['numero_ordini']++;
            $rotte[$rotta]['fatturato'] += $ordine->importo;
            $rotte[$rotta]['km_totali'] += $ordine->km_percorsi ?: 45;
        }

        // Calcola redditività
        foreach ($rotte as &$rotta) {
            $rotta['ricavo_medio'] = $rotta['numero_ordini'] > 0 ?
                $rotta['fatturato'] / $rotta['numero_ordini'] : 0;
            $rotta['ricavo_per_km'] = $rotta['km_totali'] > 0 ?
                $rotta['fatturato'] / $rotta['km_totali'] : 0;
            $rotta['costi_stimati'] = $rotta['km_totali'] * 0.20; // €0.20/km costo totale
            $rotta['margine'] = $rotta['fatturato'] - $rotta['costi_stimati'];
            $rotta['margine_percentuale'] = $rotta['fatturato'] > 0 ?
                ($rotta['margine'] / $rotta['fatturato']) * 100 : 0;
        }

        // Ordina per fatturato decrescente
        usort($rotte, function($a, $b) {
            return $b['fatturato'] <=> $a['fatturato'];
        });

        return array_slice($rotte, 0, 15); // Top 15 rotte
    }

    /**
     * Estrae città da indirizzo
     */
    private function estraiCitta($indirizzo)
    {
        $indirizzo = strtolower(trim($indirizzo));

        $citta = [
            'roma', 'milano', 'napoli', 'torino', 'palermo', 'genova', 'bologna',
            'firenze', 'bari', 'catania', 'venezia', 'verona', 'messina', 'padova',
            'trieste', 'brescia', 'parma', 'prato', 'modena', 'reggio emilia',
            'perugia', 'livorno', 'cagliari', 'foggia', 'ravenna', 'salerno'
        ];

        foreach ($citta as $nome_citta) {
            if (strpos($indirizzo, $nome_citta) !== false) {
                return ucfirst($nome_citta);
            }
        }

        // Prova a estrarre dalla stringa (prima parola significativa)
        $parole = explode(' ', $indirizzo);
        foreach ($parole as $parola) {
            if (strlen($parola) > 3 && !in_array($parola, ['via', 'viale', 'corso', 'piazza'])) {
                return ucfirst($parola);
            }
        }

        return 'Generico';
    }

    /**
     * Tasso di completamento ordini
     */
    private function getTassoCompletamento($idAzienda, $dataInizio, $dataFine)
    {
        $ordiniTotali = DB::table('ordini_trasporto')
            ->where('id_azienda', $idAzienda)
            ->whereBetween('data_ritiro', [$dataInizio, $dataFine])
            ->count();

        $ordiniCompletati = DB::table('ordini_trasporto')
            ->where('id_azienda', $idAzienda)
            ->whereBetween('data_ritiro', [$dataInizio, $dataFine])
            ->where('stato', 'completato')
            ->count();

        return $ordiniTotali > 0 ? ($ordiniCompletati / $ordiniTotali) * 100 : 0;
    }

    /**
     * Report predittivi
     */
    public function reportPredittivi(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        // Previsioni domanda prossimi 3 mesi
        $previsioniDomanda = $this->calcolaPrevisioniDomanda($utente->id_azienda);

        // Forecast costi carburante
        $forecastCarburante = $this->getForecastCarburante($utente->id_azienda);

        // Analisi stagionalità
        $analisiStagionalita = $this->getAnalisiStagionalita($utente->id_azienda);

        return view('azienda.analytics_predittivi', compact(
            'previsioniDomanda',
            'forecastCarburante',
            'analisiStagionalita',
            'utente'
        ));
    }

    /**
     * Calcola previsioni domanda basate su trend storici
     */
    private function calcolaPrevisioniDomanda($idAzienda)
    {
        // Dati ultimi 12 mesi
        $datiStorici = DB::table('ordini_trasporto')
            ->where('id_azienda', $idAzienda)
            ->where('data_ritiro', '>=', Carbon::now()->subMonths(12))
            ->select(
                DB::raw('YEAR(data_ritiro) as anno'),
                DB::raw('MONTH(data_ritiro) as mese'),
                DB::raw('COUNT(*) as numero_ordini'),
                DB::raw('SUM(importo) as fatturato')
            )
            ->groupBy(DB::raw('YEAR(data_ritiro)'), DB::raw('MONTH(data_ritiro)'))
            ->orderBy('anno', 'asc')
            ->orderBy('mese', 'asc')
            ->get();

        // Calcola trend lineare semplice
        $mediaOrdini = $datiStorici->avg('numero_ordini');
        $mediaFatturato = $datiStorici->avg('fatturato');

        // Crescita media mensile
        if (count($datiStorici) > 1) {
            $primoMese = $datiStorici->first();
            $ultimoMese = $datiStorici->last();
            $mesiTrascorsi = count($datiStorici);

            $crescitaOrdini = $mesiTrascorsi > 1 ?
                (($ultimoMese->numero_ordini - $primoMese->numero_ordini) / $mesiTrascorsi) : 0;
            $crescitaFatturato = $mesiTrascorsi > 1 ?
                (($ultimoMese->fatturato - $primoMese->fatturato) / $mesiTrascorsi) : 0;
        } else {
            $crescitaOrdini = 0;
            $crescitaFatturato = 0;
        }

        // Previsioni prossimi 3 mesi
        $previsioni = [];
        for ($i = 1; $i <= 3; $i++) {
            $dataPrevisione = Carbon::now()->addMonths($i);
            $previsioni[] = [
                'mese' => $dataPrevisione->locale('it')->monthName,
                'anno' => $dataPrevisione->year,
                'ordini_previsti' => round($mediaOrdini + ($crescitaOrdini * $i)),
                'fatturato_previsto' => round($mediaFatturato + ($crescitaFatturato * $i), 2)
            ];
        }

        return [
            'dati_storici' => $datiStorici,
            'previsioni' => $previsioni,
            'trend_ordini' => $crescitaOrdini,
            'trend_fatturato' => $crescitaFatturato
        ];
    }

    /**
     * Forecast costi carburante
     */
    private function getForecastCarburante($idAzienda)
    {
        // Km medi mensili
        $kmMediMensili = DB::table('ordini_trasporto')
                ->where('id_azienda', $idAzienda)
                ->where('data_ritiro', '>=', Carbon::now()->subMonths(6))
                ->avg(DB::raw('COALESCE(km_percorsi, 45)')) *
            DB::table('ordini_trasporto')
                ->where('id_azienda', $idAzienda)
                ->where('data_ritiro', '>=', Carbon::now()->subMonths(6))
                ->count() / 6;

        // Prezzo carburante attuale (simulato)
        $prezzoDieselAttuale = 1.45; // €/litro
        $consumoMedio = 8; // litri/100km

        // Scenari forecast
        return [
            'km_medi_mensili' => round($kmMediMensili),
            'prezzo_diesel_attuale' => $prezzoDieselAttuale,
            'consumo_medio' => $consumoMedio,
            'costo_mensile_attuale' => round(($kmMediMensili / 100) * $consumoMedio * $prezzoDieselAttuale, 2),
            'scenari' => [
                'ottimistico' => round(($kmMediMensili / 100) * $consumoMedio * ($prezzoDieselAttuale * 0.95), 2),
                'realistico' => round(($kmMediMensili / 100) * $consumoMedio * ($prezzoDieselAttuale * 1.05), 2),
                'pessimistico' => round(($kmMediMensili / 100) * $consumoMedio * ($prezzoDieselAttuale * 1.15), 2)
            ]
        ];
    }

    /**
     * Analisi stagionalità
     */
    private function getAnalisiStagionalita($idAzienda)
    {
        return DB::table('ordini_trasporto')
            ->where('id_azienda', $idAzienda)
            ->where('data_ritiro', '>=', Carbon::now()->subMonths(24))
            ->select(
                DB::raw('MONTH(data_ritiro) as mese'),
                DB::raw('AVG(importo) as fatturato_medio'),
                DB::raw('COUNT(*) as ordini_totali')
            )
            ->groupBy(DB::raw('MONTH(data_ritiro)'))
            ->orderBy('mese')
            ->get()
            ->map(function($item) {
                $item->mese_nome = Carbon::create()->month($item->mese)->locale('it')->monthName;
                return $item;
            });
    }

    /**
     * Export Excel - Dashboard completa
     */
    public function exportExcel(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        $dataInizio = $request->get('data_inizio', Carbon::now()->subMonths(3)->format('Y-m-d'));
        $dataFine = $request->get('data_fine', Carbon::now()->format('Y-m-d'));

        $kpiPrincipali = $this->calcolaKPIPrincipali($utente->id_azienda, $dataInizio, $dataFine);
        $performanceClienti = $this->getPerformanceClienti($utente->id_azienda, $dataInizio, $dataFine);
        $performanceMezzi = $this->getPerformanceMezzi($utente->id_azienda, $dataInizio, $dataFine);
        $trendMensili = $this->getTrendMensili($utente->id_azienda, $dataInizio, $dataFine);
        $redditivitaRotte = $this->getRedditivitaRotte($utente->id_azienda, $dataInizio, $dataFine);

        $nomeFile = 'Dashboard_BI_' . Carbon::parse($dataInizio)->format('d-m-Y') . '_' . Carbon::parse($dataFine)->format('d-m-Y') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\DashboardBIExport(
            $kpiPrincipali,
            $performanceClienti,
            $performanceMezzi,
            $trendMensili,
            $redditivitaRotte,
            $dataInizio,
            $dataFine
        ), $nomeFile);
    }


    /**
     * Export PDF - Dashboard riepilogativa (senza dompdf)
     */
    public function exportPDF(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        $dataInizio = $request->get('data_inizio', Carbon::now()->subMonths(3)->format('Y-m-d'));
        $dataFine = $request->get('data_fine', Carbon::now()->format('Y-m-d'));

        $kpiPrincipali = $this->calcolaKPIPrincipali($utente->id_azienda, $dataInizio, $dataFine);
        $performanceClienti = $this->getPerformanceClienti($utente->id_azienda, $dataInizio, $dataFine);
        $performanceMezzi = $this->getPerformanceMezzi($utente->id_azienda, $dataInizio, $dataFine);
        $redditivitaRotte = $this->getRedditivitaRotte($utente->id_azienda, $dataInizio, $dataFine);

        // Renderizza come HTML stampabile con bottone "Stampa PDF"
        return view('azienda.analytics_export_pdf', compact(
            'kpiPrincipali',
            'performanceClienti',
            'performanceMezzi',
            'redditivitaRotte',
            'dataInizio',
            'dataFine',
            'utente'
        ));
    }
    /**
     * Export Excel - Report Predittivi
     */
    public function exportPredittiviExcel(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        $previsioniDomanda = $this->calcolaPrevisioniDomanda($utente->id_azienda);
        $forecastCarburante = $this->getForecastCarburante($utente->id_azienda);
        $analisiStagionalita = $this->getAnalisiStagionalita($utente->id_azienda);

        $nomeFile = 'Report_Predittivi_' . Carbon::now()->format('d-m-Y') . '.xlsx';

        return Excel::download(new \App\Exports\PredittiviExport(
            $previsioniDomanda,
            $forecastCarburante,
            $analisiStagionalita
        ), $nomeFile);
    }

    /**
     * Export PDF - Report Predittivi con mPDF
     */
    public function exportPredittiviPDF(Request $request)
    {
        $this->is_loggato();
        $utente = session('utente');

        $previsioniDomanda = $this->calcolaPrevisioniDomanda($utente->id_azienda);
        $forecastCarburante = $this->getForecastCarburante($utente->id_azienda);
        $analisiStagionalita = $this->getAnalisiStagionalita($utente->id_azienda);

        $html = view('azienda.analytics_predittivi_export_pdf', compact(
            'previsioniDomanda',
            'forecastCarburante',
            'analisiStagionalita',
            'utente'
        ))->render();

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 15,
        ]);

        $mpdf->SetTitle('Report Predittivi & Forecast');
        $mpdf->SetAuthor('Logistia');
        $mpdf->WriteHTML($html);

        $nomeFile = 'Report_Predittivi_' . Carbon::now()->format('d-m-Y') . '.pdf';

        return response($mpdf->Output($nomeFile, \Mpdf\Output\Destination::STRING_RETURN), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $nomeFile . '"',
        ]);
    }



}