<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TrackingApiController extends Controller
{
    /**
     * Riceve posizione dal tablet (ogni 30 sec)
     */
    public function receivePosition(Request $request)
    {
        $validated = $request->validate([
            'device_token' => 'required|string|size:64',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'speed' => 'nullable|numeric|min:0',
            'heading' => 'nullable|integer|between:0,360',
            'accuracy' => 'nullable|numeric|min:0',
            'altitude' => 'nullable|numeric',
            'battery' => 'nullable|integer|between:0,100',
            'timestamp' => 'nullable|integer',
        ]);

        // Trova dispositivo
        $dispositivo = DB::table('dispositivi_tracking')
            ->where('device_token', $validated['device_token'])
            ->where('is_active', 1)
            ->first();

        if (!$dispositivo) {
            return response()->json(['success' => false, 'error' => 'Dispositivo non autorizzato'], 401);
        }

        // Verifica setup completato
        if (!$dispositivo->configurato) {
            return response()->json([
                'success' => false,
                'error' => 'Inserire prima i km iniziali',
                'needs_setup' => true
            ], 400);
        }

        $timestamp = isset($validated['timestamp'])
            ? date('Y-m-d H:i:s', $validated['timestamp'])
            : now();

        $speed = $validated['speed'] ?? 0;
        $isMoving = $speed > 3;
        $accuracy = $validated['accuracy'] ?? 50;

        // Recupera ultima posizione per calcolare distanza
        $ultimaPosizione = DB::table('posizioni_live')
            ->where('id_dispositivo', $dispositivo->id)
            ->first();

        $kmPercorsi = 0;

        if ($ultimaPosizione && $accuracy < 50) {
            $kmPercorsi = $this->calcolaDistanzaKm(
                $ultimaPosizione->lat,
                $ultimaPosizione->lng,
                $validated['lat'],
                $validated['lng']
            );

            // Filtra salti GPS anomali (max 2km tra due punti)
            if ($kmPercorsi > 2) {
                $kmPercorsi = 0;
            }
        }

        try {
            DB::beginTransaction();

            // Aggiorna posizione LIVE
            DB::table('posizioni_live')->updateOrInsert(
                ['id_dispositivo' => $dispositivo->id],
                [
                    'id_azienda' => $dispositivo->id_azienda,
                    'id_mezzo' => $dispositivo->id_mezzo,
                    'lat' => $validated['lat'],
                    'lng' => $validated['lng'],
                    'speed' => $speed,
                    'heading' => $validated['heading'] ?? 0,
                    'accuracy' => $accuracy,
                    'altitude' => $validated['altitude'],
                    'is_moving' => $isMoving,
                    'battery_level' => $validated['battery'],
                    'recorded_at' => $timestamp,
                    'updated_at' => now(),
                ]
            );

            // Salva nello storico
            DB::table('posizioni_storico')->insert([
                'id_dispositivo' => $dispositivo->id,
                'id_azienda' => $dispositivo->id_azienda,
                'id_mezzo' => $dispositivo->id_mezzo,
                'lat' => $validated['lat'],
                'lng' => $validated['lng'],
                'speed' => $speed,
                'heading' => $validated['heading'] ?? 0,
                'altitude' => $validated['altitude'],
                'is_moving' => $isMoving,
                'recorded_at' => $timestamp,
            ]);

            // ACCUMULA KM sul mezzo
            if ($dispositivo->id_mezzo && $kmPercorsi > 0) {
                DB::table('mezzi')
                    ->where('id', $dispositivo->id_mezzo)
                    ->update([
                        'km_accumulati_gps' => DB::raw("km_accumulati_gps + {$kmPercorsi}"),
                        'km_attuali' => DB::raw("COALESCE(km_iniziali_contachilometri, 0) + km_accumulati_gps + {$kmPercorsi}"),
                        'ultima_lat' => $validated['lat'],
                        'ultima_lng' => $validated['lng'],
                        'ultima_velocita' => $speed,
                        'ultimo_aggiornamento_gps' => now(),
                    ]);

                // Aggiorna km giornalieri
                $oggi = date('Y-m-d');
                DB::table('km_giornalieri')->updateOrInsert(
                    ['id_dispositivo' => $dispositivo->id, 'data' => $oggi],
                    [
                        'id_mezzo' => $dispositivo->id_mezzo,
                        'id_azienda' => $dispositivo->id_azienda,
                        'km_percorsi' => DB::raw("COALESCE(km_percorsi, 0) + {$kmPercorsi}"),
                        'velocita_max' => DB::raw("GREATEST(COALESCE(velocita_max, 0), {$speed})"),
                        'updated_at' => now(),
                    ]
                );
            }

            // Aggiorna heartbeat
            DB::table('dispositivi_tracking')
                ->where('id', $dispositivo->id)
                ->update(['ultimo_heartbeat' => now()]);

            DB::commit();

            return response()->json([
                'success' => true,
                'km_aggiunti' => round($kmPercorsi, 3),
                'speed' => $speed
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Errore tracking: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Errore server'], 500);
        }
    }

    /**
     * Setup iniziale - Autista inserisce km contachilometri
     */
    public function setupIniziale(Request $request)
    {
        $validated = $request->validate([
            'device_token' => 'required|string|size:64',
            'km_contachilometri' => 'required|integer|min:0|max:9999999',
        ]);

        $dispositivo = DB::table('dispositivi_tracking')
            ->where('device_token', $validated['device_token'])
            ->where('is_active', 1)
            ->first();

        if (!$dispositivo) {
            return response()->json(['success' => false, 'error' => 'Dispositivo non trovato'], 404);
        }

        if (!$dispositivo->id_mezzo) {
            return response()->json(['success' => false, 'error' => 'Nessun mezzo associato'], 400);
        }

        try {
            DB::beginTransaction();

            DB::table('mezzi')
                ->where('id', $dispositivo->id_mezzo)
                ->update([
                    'km_iniziali_contachilometri' => $validated['km_contachilometri'],
                    'km_accumulati_gps' => 0,
                    'km_attuali' => $validated['km_contachilometri'],
                    'data_attivazione_tracking' => now(),
                    'tracking_attivo' => true,
                    'updated_at' => now(),
                ]);

            DB::table('dispositivi_tracking')
                ->where('id', $dispositivo->id)
                ->update(['configurato' => true, 'updated_at' => now()]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Configurazione completata!',
                'km_iniziali' => $validated['km_contachilometri']
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Verifica stato dispositivo
     */
    public function checkStatus(Request $request)
    {
        $validated = $request->validate([
            'device_token' => 'required|string|size:64',
        ]);

        $dispositivo = DB::table('dispositivi_tracking')
            ->where('device_token', $validated['device_token'])
            ->first();

        if (!$dispositivo) {
            return response()->json(['success' => false, 'error' => 'Dispositivo non registrato'], 404);
        }

        if (!$dispositivo->is_active) {
            return response()->json(['success' => false, 'error' => 'Dispositivo disattivato'], 403);
        }

        $mezzo = null;
        if ($dispositivo->id_mezzo) {
            $mezzo = DB::table('mezzi')
                ->where('id', $dispositivo->id_mezzo)
                ->first(['id', 'nome', 'targa', 'km_attuali', 'km_iniziali_contachilometri', 'km_accumulati_gps']);
        }

        return response()->json([
            'success' => true,
            'configurato' => (bool) $dispositivo->configurato,
            'needs_setup' => !$dispositivo->configurato,
            'mezzo' => $mezzo ? [
                'id' => $mezzo->id,
                'nome' => $mezzo->nome,
                'targa' => $mezzo->targa,
                'km_totali' => $mezzo->km_attuali,
            ] : null
        ]);
    }

    /**
     * Batch - Riceve più posizioni (quando era offline)
     */
    public function receiveBatch(Request $request)
    {
        $validated = $request->validate([
            'device_token' => 'required|string|size:64',
            'positions' => 'required|array|min:1|max:1000',
            'positions.*.lat' => 'required|numeric|between:-90,90',
            'positions.*.lng' => 'required|numeric|between:-180,180',
            'positions.*.speed' => 'nullable|numeric|min:0',
            'positions.*.timestamp' => 'required|integer',
        ]);

        $dispositivo = DB::table('dispositivi_tracking')
            ->where('device_token', $validated['device_token'])
            ->where('is_active', 1)
            ->where('configurato', 1)
            ->first();

        if (!$dispositivo) {
            return response()->json(['success' => false, 'error' => 'Dispositivo non valido'], 401);
        }

        $positions = collect($validated['positions'])->sortBy('timestamp')->values();

        $kmTotali = 0;
        $batch = [];
        $prevPos = null;

        foreach ($positions as $pos) {
            $timestamp = date('Y-m-d H:i:s', $pos['timestamp']);
            $speed = $pos['speed'] ?? 0;

            if ($prevPos) {
                $km = $this->calcolaDistanzaKm($prevPos['lat'], $prevPos['lng'], $pos['lat'], $pos['lng']);
                if ($km < 2) {
                    $kmTotali += $km;
                }
            }

            $batch[] = [
                'id_dispositivo' => $dispositivo->id,
                'id_azienda' => $dispositivo->id_azienda,
                'id_mezzo' => $dispositivo->id_mezzo,
                'lat' => $pos['lat'],
                'lng' => $pos['lng'],
                'speed' => $speed,
                'heading' => $pos['heading'] ?? 0,
                'is_moving' => $speed > 3,
                'recorded_at' => $timestamp,
            ];

            $prevPos = $pos;
        }

        try {
            DB::beginTransaction();

            foreach (array_chunk($batch, 100) as $chunk) {
                DB::table('posizioni_storico')->insert($chunk);
            }

            if ($dispositivo->id_mezzo && $kmTotali > 0) {
                DB::table('mezzi')
                    ->where('id', $dispositivo->id_mezzo)
                    ->update([
                        'km_accumulati_gps' => DB::raw("km_accumulati_gps + {$kmTotali}"),
                        'km_attuali' => DB::raw("COALESCE(km_iniziali_contachilometri, 0) + km_accumulati_gps + {$kmTotali}"),
                    ]);
            }

            $lastPos = end($batch);
            DB::table('posizioni_live')->updateOrInsert(
                ['id_dispositivo' => $dispositivo->id],
                [
                    'id_azienda' => $dispositivo->id_azienda,
                    'id_mezzo' => $dispositivo->id_mezzo,
                    'lat' => $lastPos['lat'],
                    'lng' => $lastPos['lng'],
                    'speed' => $lastPos['speed'],
                    'is_moving' => $lastPos['is_moving'],
                    'recorded_at' => $lastPos['recorded_at'],
                    'updated_at' => now(),
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'processed' => count($batch),
                'km_aggiunti' => round($kmTotali, 2)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Formula Haversine - calcola distanza in km
     */
    private function calcolaDistanzaKm($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
