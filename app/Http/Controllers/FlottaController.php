<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FlottaController extends Controller
{
    private $apiUrl = 'https://api.flottaincloud.it/external_api/v1/';

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
     * Verifica se l'azienda corrente ha FlottaInCloud attivato
     */
    private function hasFlottaInCloudEnabled()
    {
        $utente = session('utente');
        $aziendaId = $utente->id_azienda ?? null;

        if (!$aziendaId) {
            return false;
        }

        // Verifica nella tabella aziende se ha flotta abilitata
        $azienda = DB::table('aziende')
            ->where('id', $aziendaId)
            ->first();

        return $azienda &&
            !empty($azienda->flotta_email) &&
            !empty($azienda->flotta_token) &&
            ($azienda->flotta_abilitato ?? 0) == 1;
    }

    /**
     * Ottieni credenziali API per l'azienda corrente
     */
    private function getFlottaCredentials()
    {
        $utente = session('utente');
        $aziendaId = $utente->id_azienda ?? null;

        if (!$aziendaId) {
            return null;
        }

        $azienda = DB::table('aziende')
            ->where('id', $aziendaId)
            ->first();

        if (!$azienda || empty($azienda->flotta_email) || empty($azienda->flotta_token)) {
            return null;
        }

        return [
            'email' => $azienda->flotta_email,
            'token' => $azienda->flotta_token
        ];
    }

    /**
     * Chiamata API con credenziali dinamiche
     */
    private function makeAuthenticatedRequest($endpoint)
    {
        $credentials = $this->getFlottaCredentials();

        if (!$credentials) {
            return null;
        }

        try {
            $auth = base64_encode($credentials['email'] . ':' . $credentials['token']);

            $ch = curl_init();

            curl_setopt_array($ch, [
                CURLOPT_URL => $this->apiUrl . $endpoint,
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

            if ($error) {
                Log::error('cURL Error: ' . $error);
                return null;
            }

            if ($httpCode !== 200) {
                Log::error('HTTP Error: ' . $httpCode . ' - ' . $response);
                return null;
            }

            return json_decode($response, true);

        } catch (\Exception $e) {
            Log::error('API Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Vista principale
     */
    public function index()
    {
        $utente = session('utente');
        $hasFlottaEnabled = $this->hasFlottaInCloudEnabled();

        $posizioni = [];
        $stats = ['totali' => 0, 'in_movimento' => 0, 'fermi' => 0, 'connessi' => 0];
        $flottaConfig = null;

        if ($hasFlottaEnabled) {
            // Azienda con FlottaInCloud - carica i dati reali
            $response = $this->makeAuthenticatedRequest('devices');

            if ($response && is_array($response)) {
                $posizioni = $response;
                $stats = $this->calcolaStatistiche($posizioni);
            }

            $flottaConfig = [
                'enabled' => true,
                'message' => 'FlottaInCloud attivo per la tua azienda'
            ];
        } else {
            // Azienda senza FlottaInCloud - mostra vista demo/disabilitata
            $flottaConfig = [
                'enabled' => false,
                'message' => 'FlottaInCloud non è attivo per la tua azienda',
                'contact_message' => 'Contatta l\'amministratore per attivare il servizio'
            ];
        }

        return view('azienda.flotta_tracking', compact(
            'posizioni',
            'stats',
            'utente',
            'hasFlottaEnabled',
            'flottaConfig'
        ));
    }

    /**
     * API per AJAX
     */
    public function livePositions()
    {
        if (!$this->hasFlottaInCloudEnabled()) {
            return response()->json([
                'error' => 'FlottaInCloud non abilitato per questa azienda',
                'enabled' => false
            ]);
        }

        $response = $this->makeAuthenticatedRequest('devices');

        if ($response && is_array($response)) {
            return response()->json($response);
        }

        return response()->json([]);
    }

    /**
     * Test connessione
     */
    public function testConnection()
    {
        if (!$this->hasFlottaInCloudEnabled()) {
            return response()->json([
                'success' => false,
                'error' => 'FlottaInCloud non è abilitato per la tua azienda',
                'enabled' => false
            ]);
        }

        $response = $this->makeAuthenticatedRequest('devices');

        if ($response && is_array($response)) {
            return response()->json([
                'success' => true,
                'devices_count' => count($response),
                'has_data' => count($response) > 0,
                'timestamp' => now()->toISOString(),
                'sample_data' => isset($response[0]) ? $response[0] : null,
                'enabled' => true
            ]);
        } else {
            return response()->json([
                'success' => false,
                'error' => 'Nessuna risposta dall\'API o formato non valido',
                'timestamp' => now()->toISOString(),
                'enabled' => true
            ]);
        }
    }

    /**
     * Calcola statistiche
     */
    private function calcolaStatistiche($dispositivi)
    {
        if (!is_array($dispositivi)) {
            return ['totali' => 0, 'in_movimento' => 0, 'fermi' => 0, 'connessi' => 0];
        }

        $stats = [
            'totali' => count($dispositivi),
            'in_movimento' => 0,
            'fermi' => 0,
            'connessi' => 0
        ];

        foreach ($dispositivi as $dispositivo) {
            if (isset($dispositivo['moving']) && $dispositivo['moving']) {
                $stats['in_movimento']++;
            } else {
                $stats['fermi']++;
            }

            if (isset($dispositivo['is_connected']) && $dispositivo['is_connected']) {
                $stats['connessi']++;
            }
        }

        return $stats;
    }
}