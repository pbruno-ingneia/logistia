<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrackingDashboardController;
use App\Http\Controllers\AutistaController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::post('/azienda/ordine/calcola-km', 'TrasportiController@calcolaKmGoogle');

// Stampa PDF DDT
Route::get('/azienda/ddt/{id}/pdf', [App\Http\Controllers\TrasportiController::class, 'stampaDDT']);

Route::post('/azienda/ddt/salva-firma', [TrasportiController::class, 'salvaFirmaDDT']);
Route::post('/azienda/ddt/rimuovi-firma', [TrasportiController::class, 'rimuoviFirmaDDT']);

// Genera DDT manualmente (se non esiste)
Route::post('/azienda/genera-ddt/{id}', [App\Http\Controllers\TrasportiController::class, 'generaDDT']);

Route::get('/ordini-trasporto', 'AziendaController@ordiniTrasporto');
Route::post('/ordini-trasporto', 'AziendaController@ordiniTrasporto');
Route::get('/ordine-trasporto/{id}', 'AziendaController@dettaglioOrdine');
Route::post('/ordine-trasporto/cambia-stato', 'AziendaController@cambiaStatoOrdine');

// route Admin
Route::any('',array('uses'=>'HomeController@index'));
Route::any('admin/login',array('uses'=>'AdminController@login'));
Route::any('admin/index',array('uses'=>'AdminController@index'));
Route::any('admin/aziende',array('uses'=>'AdminController@aziende'));
Route::any('admin/moduli',array('uses'=>'AdminController@moduli'));
Route::any('admin/utenti',array('uses'=>'AdminController@utenti'));
Route::any('admin/logout',array('uses'=>'AdminController@logout'));
Route::any('admin/effettua_login', ('AdminController@effettua_login'))->name('effettua_login');

//route Aziende
Route::any('azienda/index',array('uses'=>'AziendaController@index'));
Route::any('azienda/cantieri',array('uses'=>'AziendaController@cantieri'));
Route::any('azienda/logout',array('uses'=>'AziendaController@logout'));
Route::any('/azienda/cantiere/{id}', 'AziendaController@dettaglioCantiere')->name('azienda.cantiere.dettaglio');
Route::any('/azienda/cantieri/save', 'AziendaController@saveCantiere')->name('azienda.cantieri.save');
Route::any('azienda/utenti',array('uses'=>'AziendaController@utenti'));
Route::any('azienda/ruoli',array('uses'=>'AziendaController@ruoli'))->name('azienda.ruoli');
Route::get('/get-attivita-dipendente/{id}', 'AziendaController@getAttivitaDipendente');
Route::get('/get-dipendenti-attivita/{id}', 'AziendaController@getDipendentiAttivita');
Route::post('/salva-dipendenti-attivita', 'AziendaController@salvaDipendentiAttivita');
Route::any('/attivita/aggiorna-dipendenti', 'AziendaController@aggiornaDipendenti');
Route::any('azienda/mezzi',array('uses'=>'AziendaController@anagraficaMezzi'))->name('anagrafica_mezzi');;
Route::any('/azienda/mezzo/{id}', 'AziendaController@dettaglioMezzo')->name('dettaglio_mezzo');
Route::post('/azienda/mezzo/{id}/modifica-stato', 'AziendaController@modificaStatoMezzo')->name('modifica_stato_mezzo');
// NUOVE ROUTE per il gestionale smart
Route::post('/azienda/mezzo/{id}/aggiorna-km', 'AziendaController@aggiornaKmMezzo')->name('aggiorna_km_mezzo');
Route::post('/azienda/mezzo/{id}/sostituisci-gomma', 'AziendaController@sostituisciGomma')->name('sostituisci_gomma');
Route::post('/azienda/mezzo/{id}/impostazioni', 'AziendaController@impostazioniMezzo')->name('impostazioni_mezzo');
Route::post('/azienda/utente/update-vista-operaio', 'AziendaController@updateVistaOperaio')->name('update_vista_operaio');
Route::post('/azienda/update-stato', 'AziendaController@updateStato')->name('cantiere.updateStato');
Route::any('azienda/materiali',array('uses'=>'AziendaController@materiali'));
Route::any('azienda/strumenti',array('uses'=>'AziendaController@strumenti'));
Route::post('azienda/gestisci', 'AziendaController@gestisciArticolo')->name('magazzino.gestisci');
Route::post('/azienda/magazzino/impegna', 'AziendaController@impegnaArticolo')->name('magazzino.impegna');
Route::get('/azienda/magazzino/rimuovi', 'AziendaController@rimuoviImpegno')->name('magazzino.rimuovi');
// Rotta per la vista del timesheet
Route::any('/azienda/vista_cantiere', 'AziendaController@vistaOperaio');
Route::any('/magazzino/movimento', 'AziendaController@movimento')->name('magazzino.movimento');
Route::get('/azienda/movimenti', 'AziendaController@movimenti')->name('magazzino.movimenti');
Route::get('/azienda/magazzino/scarica', 'AziendaController@scaricaArticolo')->name('magazzino.scarica');
Route::get('/azienda/recuperaQuantita', 'AziendaController@recuperaArticolo');
Route::get('/azienda/dipendenti/visualizza', 'AziendaController@visualizzaDipendenti')
    ->name('azienda.dipendenti.visualizza');
// Route per aggiornare lo status responsabile
Route::post('/azienda/utente/update-responsabile', 'AziendaController@updateResponsabile')->name('update_responsabile');

// Route per recuperare responsabili di un cantiere
Route::get('/azienda/cantiere/{id}/responsabili', 'AziendaController@getResponsabiliCantiere')->name('cantiere_responsabili');



Route::post('/azienda/cantieri/{id}/assegna-dipendenti', 'AziendaController@assegnaDipendenti')->name('cantieri.assegnaDipendenti');
Route::delete('/azienda/cantieri/rimuovi-dipendente/{id}', 'AziendaController@rimuoviDipendente')->name('cantieri.rimuoviDipendente');
// Aggiungi questa route nel file web.php

Route::any('azienda/responsabili', array('uses'=>'AziendaController@responsabili'));
Route::post('/azienda/controlla-conflitti-dipendenti', 'AziendaController@controllaConflittiDipendenti');
Route::any('/azienda/cantieri/gestisci-pagamento', 'AziendaController@gestisciPagamento')->name('cantieri.gestisciPagamento');
// Rotte per gestione allegati cantieri
Route::any('/cantiere/upload-allegato', 'AziendaController@uploadAllegato')->name('cantiere.upload.allegato');
Route::any('/cantiere/salva-foto', 'AziendaController@salvaFoto')->name('cantiere.salva.foto');
Route::any('/cantiere/elimina-allegato', 'AziendaController@eliminaAllegato')->name('cantiere.elimina.allegato');
Route::get('/azienda/cantiere/{id}/allegati', 'AziendaController@getAllegatiCantiere')->name('cantiere.get.allegati');
Route::post('/azienda/aggiorna-soglia', 'AziendaController@aggiornaSoglia')->name('magazzino.aggiorna-soglia');
Route::post('/azienda/aggiorna-soglie-massive', 'AziendaController@aggiornaSoglieMassive')->name('magazzino.aggiorna-soglie-massive');
Route::post('/azienda/utente/update-responsabile', 'AziendaController@updateResponsabile')->name('update_responsabile');
Route::get('/azienda/utente/get-permessi/{id}', 'AziendaController@getPermessiUtente')->name('get_permessi_utente');
Route::post('/azienda/utente/aggiorna-permessi', 'AziendaController@aggiornaPermessi')->name('aggiorna_permessi');

Route::any('/azienda/responsabili/report/pdf', 'AziendaController@reportResponsabiliPDF');
Route::any('/azienda/responsabili/report/pdf/attivi', 'AziendaController@reportResponsabiliPDFAttivi');
Route::any('/azienda/responsabili/report/pdf/singolo/{id}', 'AziendaController@reportResponsabiliPDFSingolo');

Route::any('/azienda/responsabili/report/excel', 'AziendaController@reportResponsabiliExcel');
Route::any('/azienda/responsabili/report/excel/attivi', 'AziendaController@reportResponsabiliExcelAttivi');
Route::any('/azienda/responsabili/report/excel/singolo/{id}', 'AziendaController@reportResponsabiliExcelSingolo');
Route::any('/azienda/profilo', array('uses'=>'AziendaController@profilo'));


// Aggiungi queste rotte al tuo file routes/web.php

// Rotte per Flotta in Cloud
// Aggiungi queste rotte al tuo file routes/web.php

// Rotte per Flotta in Cloud
Route::get('azienda/flotta', 'FlottaController@index')->name('azienda.flotta');
Route::get('azienda/flotta/live-positions', 'FlottaController@livePositions')->name('azienda.flotta.live');
Route::get('azienda/flotta/test-connection', 'FlottaController@testConnection')->name('azienda.flotta.test');
Route::get('azienda/flotta/debug', 'FlottaController@debugApi')->name('azienda.flotta.debug'); // Solo per test
Route::get('azienda/flotta/dispositivi', 'FlottaController@getDispositivi')->name('azienda.flotta.dispositivi');
Route::get('azienda/flotta/storico/{veicoloId}', 'FlottaController@storicoVeicolo')->name('azienda.flotta.storico');
Route::get('azienda/flotta/esporta', 'FlottaController@esportaDati')->name('azienda.flotta.esporta');

// AGGIUNGI QUESTE 2 ROTTE NEL TUO routes/web.php

Route::get('azienda/sincronizza-mezzi', array('uses'=>'AziendaController@sincronizzaMezzi'));
Route::get('azienda/aggiorna-km', array('uses'=>'AziendaController@aggiornaKmMezzi'));
Route::get('/migra-dipendenti', 'AziendaController@migraDipendenti');
// Rotte per gestione assegnazioni per giorni
Route::post('/azienda/salva-assegnazione-giorni', 'AziendaController@salvaAssegnazioneGiorni')->name('salva.assegnazione.giorni');
Route::get('/azienda/cantiere/{id}/assegnazioni', 'AziendaController@getAssegnazioniCantiere')->name('get.assegnazioni.cantiere');
Route::get('/azienda/cantiere/{cantiereId}/dipendente/{dipendenteId}/giorni', 'AziendaController@getGiorniDipendente')->name('get.giorni.dipendente');
Route::post('/azienda/rimuovi-assegnazione-dipendente', 'AziendaController@rimuoviAssegnazioneDipendente')->name('rimuovi.assegnazione.dipendente');
Route::post('/azienda/mezzo/{id}/registra-tagliando', 'AziendaController@registraTagliando');

// ⚙️ Km
Route::post('/azienda/mezzo/{id}/aggiorna-km', 'AziendaController@aggiornaKm');

// 🔧 Manutenzioni
Route::post('/azienda/mezzo/{id}/manutenzione', 'AziendaController@aggiungiManutenzione');
Route::post('/azienda/mezzo/{id}/manutenzione/modifica', 'AziendaController@modificaManutenzione');
Route::post('/azienda/mezzo/{id}/manutenzione/elimina', 'AziendaController@eliminaManutenzione');
Route::post('/cantiere/crea', 'ApiController@creaCantiere');
Route::options('/cantiere/crea', 'ApiController@creaCantiere');
// Ordini di Trasporto
// Ordini di Trasporto
Route::any('/azienda/ordini-trasporto', 'TrasportiController@ordiniTrasporto');
Route::get('/azienda/ordine-trasporto/{id}', 'TrasportiController@dettaglioOrdine');
Route::post('/azienda/ordine-trasporto/cambia-stato', 'TrasportiController@cambiaStatoOrdine');

// Clienti TMS
Route::any('/azienda/clienti', 'TrasportiController@clienti');

// Tariffari e Calcolo Costi
Route::any('/azienda/tariffari', 'TrasportiController@tariffari');
Route::post('/azienda/calcola-costo-trasporto', 'TrasportiController@calcolaCostoTrasporto');

// Documenti di Trasporto
Route::any('/azienda/documenti-trasporto', 'TrasportiController@documenti');
Route::get('/azienda/documenti-trasporto/{idOrdine}', 'TrasportiController@documenti');

// Dashboard KPI principale
Route::get('/azienda/analytics/dashboard', 'AnalyticsController@dashboardKPI');

// Report predittivi e forecast
Route::get('/azienda/analytics/predittivi', 'AnalyticsController@reportPredittivi');
Route::get('/azienda/analytics/export/excel', 'AnalyticsController@exportExcel');
Route::get('/azienda/analytics/export/pdf', 'AnalyticsController@exportPDF');
// Export Predittivi
Route::get('/azienda/analytics/predittivi/export/excel', 'AnalyticsController@exportPredittiviExcel');
Route::get('/azienda/analytics/predittivi/export/pdf', 'AnalyticsController@exportPredittiviPDF');

// Aggiungi queste route al tuo file web.php

// Report TMS Routes
Route::get('/azienda/reports-tms', 'AziendaController@reportTMS')->name('reports.tms');
Route::post('/azienda/report-tms/genera', 'AziendaController@generaReportTMS')->name('reports.tms.genera');

// Report specifici
Route::post('/azienda/report-tms/operativo', 'AziendaController@generaReportOperativo')->name('reports.tms.operativo');
Route::post('/azienda/report-tms/finanziario', 'AziendaController@generaReportFinanziario')->name('reports.tms.finanziario');
Route::post('/azienda/report-tms/performance', 'AziendaController@generaReportPerformance')->name('reports.tms.performance');

// Export specifici
Route::get('/azienda/report-tms/export/{tipo}/{formato}', 'AziendaController@exportReport')->name('reports.tms.export');

Route::get('/azienda/tracking', 'TrackingDashboardController@index');
Route::get('/azienda/tracking/live-positions', 'TrackingDashboardController@livePositions');
Route::get('/azienda/tracking/report-km', 'TrackingDashboardController@reportKm');
Route::get('/azienda/tracking/dispositivi', 'TrackingDashboardController@listaDispositivi');
Route::post('/azienda/tracking/dispositivi/crea', 'TrackingDashboardController@creaDispositivo');
Route::post('/azienda/tracking/dispositivi/{id}/associa-mezzo', 'TrackingDashboardController@associaMezzo');
Route::delete('/azienda/tracking/dispositivi/{id}', 'TrackingDashboardController@eliminaDispositivo');

// === AREA AUTISTA ===
Route::prefix('autista')->group(function () {

    // Dashboard
    Route::get('/dashboard', 'AutistaController@dashboard');

    // Tracking GPS
    Route::get('/tracking', 'AutistaController@tracking');

    // Consegne
    Route::get('/consegne', 'AutistaController@consegne');
    Route::post('/consegna/{id}/inizia', 'AutistaController@iniziaConsegna');
    Route::post('/consegna/{id}/completa', 'AutistaController@completaConsegna');
    Route::post('/consegna/{id}/annulla', 'AutistaController@annullaConsegna');
    Route::post('/consegna/{id}/rinvia', 'AutistaController@rinviaConsegna');

    // Navigatore
    Route::get('/navigatore', 'AutistaController@navigatore'); // Percorso del giorno
    Route::get('/navigatore/{id}', 'AutistaController@navigatoreSingolo'); // Singola consegna

    // Storico km
    Route::get('/storico', 'AutistaController@storico');

    // Profilo
    Route::get('/profilo', 'AutistaController@profilo');

    // API per l'app
    Route::get('/api/stats', 'AutistaController@apiStats');
    Route::get('/api/storico', 'AutistaController@apiStorico');

    // 1. Notifiche
    Route::get('/notifiche', 'AutistaController@notifiche');
    Route::post('/notifiche/segna-tutte-lette', 'AutistaController@segnaTutteLette');
    Route::get('/notifiche/nuove', 'AutistaController@notificheNuove');
    Route::get('/notifiche/lista', 'AutistaController@notificheLista');
    Route::post('/notifiche/{id}/letta', 'AutistaController@notificheSegnaLetta');

    // 2. Proof of Delivery - Foto
    Route::post('/consegna/{id}/upload-foto', 'AutistaController@uploadFotoConsegna');
    Route::delete('/consegna/foto/{idFoto}', 'AutistaController@eliminaFotoConsegna');

    // 3. Storico ordini
    Route::get('/storico', 'AutistaController@storicoOrdini');

    // 4. Completamento consegna avanzato (firma + foto + note)
    Route::post('/consegna/{id}/completa-avanzato', 'AutistaController@completaConsegnaAvanzato');

    Route::get('/percorso-consegne', 'AutistaController@percorsoConsegne');
    Route::post('/consegna-ordine/{id}/completa', 'AutistaController@completaConsegnaOrdine');
    Route::post('/salva-ordine-percorso', 'AutistaController@salvaOrdinePercorso');

    Route::get('/piano-giornaliero', 'AutistaController@pianoGiornaliero');

});
Route::post('/azienda/ordine/calcola-km', 'TrasportiController@calcolaKm');


// Rifornimenti - IMPORTANTE: metti queste PRIMA di eventuali rotte con {id}
Route::get('/autista/rifornimenti', [AutistaController::class, 'rifornimenti']);
Route::post('/autista/rifornimenti/salva', [AutistaController::class, 'salvaRifornimento']);
Route::get('/autista/rifornimenti/{id}', [AutistaController::class, 'dettaglioRifornimento']);
Route::post('/autista/rifornimenti/{id}/elimina', [AutistaController::class, 'eliminaRifornimento']);


/**
 * ============================================
 * VERIFICA CHE QUESTE ROTTE ESISTANO
 * in routes/web.php o routes/api.php
 * ============================================
 */

// Se usi routes/web.php:
Route::post('/api/tracking/position', 'TrackingApiController@receivePosition');
Route::post('/api/tracking/setup', 'TrackingApiController@setupIniziale');
Route::post('/api/tracking/status', 'TrackingApiController@checkStatus');
Route::post('/api/tracking/batch', 'TrackingApiController@receiveBatch');


Route::get('/autista/ordine/{id}/completa', [AutistaController::class, 'completaOrdineView']);
Route::post('/autista/ddt/salva-firma', [AutistaController::class, 'salvaFirmaDDTAutista']);
Route::post('/autista/ddt/rimuovi-firma', [AutistaController::class, 'rimuoviFirmaDDTAutista']);
Route::post('/autista/ordine/completa', [AutistaController::class, 'completaOrdine']);

Route::get('/autista/ordine/{id}/completato', [AutistaController::class, 'ordineCompletato']);
Route::get('/autista/ddt/{id}/pdf', [AutistaController::class, 'ddtPdf']);
Route::post('/autista/ddt/invia-email', [AutistaController::class, 'inviaDdtEmail']);

Route::get('/ddt/download/{token}', [AutistaController::class, 'ddtPdfPubblico']);
Route::post('/azienda/mezzo/{id}/rifornimento', 'AziendaController@aggiungiRifornimento');
Route::post('/azienda/mezzo/{id}/rifornimento/elimina', 'AziendaController@eliminaRifornimento');
Route::post('/azienda/mezzo/{id}/rifornimento/upload-scontrino', 'AziendaController@uploadScontrino');

Route::get('/azienda/get-tariffa-cliente/{id_cliente}', 'TrasportiController@getTariffaCliente');
Route::get('/azienda/prossimo-numero-ddt', 'TrasportiController@prossimoNumeroDdt');

Route::post('/azienda/documenti-trasporto/segna-consegnato', 'TrasportiController@segnaConsegnato');
Route::get('/azienda/get-dati-ordine/{id}', 'TrasportiController@getDatiOrdine');

Route::get('/azienda/centro-operativo', 'TrasportiController@centroOperativo');
Route::get('/azienda/centro-operativo/live', 'TrasportiController@centroOperativoLive');

Route::post('/azienda/api/reports-tms', 'AziendaController@apiReportsTms');
Route::post('/azienda/api/reports-tms/export', 'AziendaController@apiReportsTmsExport');

// SEGNALAZIONE GUASTI
Route::get('/autista/segnala-guasto', 'AutistaController@segnalaGuastoForm');
Route::post('/autista/segnala-guasto', 'AutistaController@segnalaGuastoSalva');
Route::get('/autista/segnala-guasto', 'AutistaController@segnalaGuasto');
