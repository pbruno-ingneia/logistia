<?php

namespace App\Http\Controllers;

use App\Imports\TariffeImport;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PHPMailer\PHPMailer\PHPMailer;


class ApiController extends Controller{

    public function creaCantiere(Request $request)
    {
        // Abilita CORS
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');

        // Gestisce richiesta OPTIONS (preflight)
        if ($request->getMethod() == 'OPTIONS') {
            return response('', 200);
        }

        try {
            $data = $request->all();

            // Prende i dati dalla nuova struttura
            $cantiere = $data['cantiere'];
            $cliente = $data['cliente'];
            $indirizzo = $data['indirizzo'];
            $metadati = $data['metadati'];

            $cantiereId = DB::table('cantieri')->insertGetId([
                'titolo' => $cantiere['nome'],
                'descrizione' => $cantiere['descrizione'],
                'data_inizio' => $cantiere['data_inizio_prevista'] ?? now()->format('Y-m-d'),
                'data_fine' => null,
                'costo_stimato' => 0,
                'valore_stimato' => 0,
                'indirizzo' => $indirizzo['indirizzo_completo'],
                'colore' => '#007bff',
                'contabilizzato' => 1,
                'id_azienda' => 18, // Metti l'ID azienda giusto qui
                'stato' => 1,
                'costo_totale' => 0,
                'valore_totale' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'cantiere_id' => $cantiereId,
                    'nome_cantiere' => $cantiere['nome'],
                    'url_cantiere' => 'https://edilgestya.it/azienda/cantieri'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
