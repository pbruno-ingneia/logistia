# REPLICATE.md — Replica completa di Nusa Logistia

Questo documento è autocontenuto: contiene **tutto** quello che serve a una nuova istanza di Claude Code per ricostruire la piattaforma da zero su un progetto vuoto. Le scelte architetturali sono replicate **identiche all'originale** (auth custom, password in chiaro, CSRF off, query DB facade, naming italiano, tema admin di terze parti copiato così com'è).

Lo schema DB è fornito **come 39 migration Laravel** (`Schema::create`). I controller, le view, gli import/export, le integrazioni e gli upload sono descritti metodo per metodo / file per file con i puntatori al codice sorgente che va copiato dal progetto originale.

---

## Indice

1. [Cosa devi sapere prima di iniziare](#1-cosa-devi-sapere-prima-di-iniziare)
2. [Setup nuovo progetto — passo passo](#2-setup-nuovo-progetto--passo-passo)
3. [composer.json / package.json / webpack.mix.js](#3-composerjson--packagejson--webpackmixjs)
4. [`.env`](#4-env)
5. [Database — 39 migration](#5-database--39-migration)
6. [Routes — `routes/web.php`](#6-routes--routeswebphp)
7. [Routes — `routes/api.php`](#7-routes--routesapiphp)
8. [Authentication flow (replica identica)](#8-authentication-flow-replica-identica)
9. [Controller — inventario completo](#9-controller--inventario-completo)
10. [View Blade — inventario per area](#10-view-blade--inventario-per-area)
11. [Imports / Exports (Maatwebsite)](#11-imports--exports-maatwebsite)
12. [Integrazioni esterne (`.env` vars)](#12-integrazioni-esterne-env-vars)
13. [File upload — directory map](#13-file-upload--directory-map)
14. [Tema admin & asset](#14-tema-admin--asset)
15. [Checklist di replica](#15-checklist-di-replica)

---

## 1. Cosa devi sapere prima di iniziare

- **Stack**: Laravel 8.40 / PHP 7.3+/8.0 / MySQL / Laravel Mix (NON Vite). Stack di sviluppo: XAMPP su Windows.
- **Tutta l'app è in italiano**: tabelle, colonne, route, view, campi form. Mantieni il naming italiano nelle migration e nel codice nuovo.
- **Multi-tenant via `id_azienda`**: ogni tabella di dominio ha `id_azienda`; il filtro è manuale (niente global scope, niente policy). **Mai dimenticare** `where('id_azienda', $utente->id_azienda)`.
- **Auth custom**: niente `Auth::`, niente Eloquent User, niente middleware `auth`. Login con SQL diretto contro `utenti`, password in chiaro, sessione `utente` (oggetto stdClass del row), guard `is_loggato()` chiamato dentro ogni action.
- **CSRF disattivato**: `App\Http\Middleware\VerifyCsrfToken::class` è commentato in `App\Http\Kernel`. I form non includono `@csrf`.
- **Schema vive in MySQL**, non nelle migration originali (ce n'è solo una). Questo MD ne ricostruisce il **set completo** come Laravel migration → puoi rifare il DB con `php artisan migrate`.
- **3 aree URL**: `admin/*` (super-admin tenant), `azienda/*` (UI tenant), `autista/*` (mobile autista). L'instradamento al post-login è dinamico: utenti con riga attiva in `dispositivi_tracking` → `autista/dashboard`, `admin_azienda IN (1,2)` → `azienda/index`, altrimenti `admin/index`.
- **Le route sono per lo più `Route::any(...)`** e protette dentro al controller (non a livello di middleware di route).
- **Tema admin**: `public/default/` è un tema admin di terze parti. Va **copiato così com'è** dal progetto originale (è il presupposto della scelta 4a).
- **Le tre integrazioni esterne** (FlottaInCloud, Google Maps, OpenAPI P.IVA) e SMTP/IMAP/SFTP sono **documentate con .env vars vuote**: vanno configurate dall'utente sul nuovo ambiente (scelta 5a).

---

## 2. Setup nuovo progetto — passo passo

```bash
# 0. Prerequisiti: PHP 8.0+, Composer, Node 14+, MySQL 5.7+/MariaDB 10+, XAMPP (consigliato)

# 1. Crea progetto Laravel 8 vuoto
composer create-project laravel/laravel:^8.40 nusa_replica
cd nusa_replica

# 2. Sostituisci composer.json e package.json con quelli forniti in §3
#    Sostituisci webpack.mix.js (è già quasi identico).
composer install
npm install

# 3. Copia il file .env (vedi §4) e genera APP_KEY se vuoto
php artisan key:generate

# 4. Crea il database MySQL vuoto
mysql -u root -e "CREATE DATABASE \`nusa.logistia.it\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"

# 5. Genera tutti i file migration in database/migrations/ (vedi §5)
#    Lancia migrazioni
php artisan migrate

# 6. Copia interi i seguenti rami dal progetto originale (file fisici, non rigenerabili):
#    - app/Http/Controllers/      (12 controller, vedi §9)
#    - app/Http/Middleware/       (Authenticate, EncryptCookies, ecc. — sono Laravel default)
#    - app/Http/Kernel.php        (con VerifyCsrfToken commentato — vedi §8)
#    - app/Imports/               (6 classe Maatwebsite — vedi §11)
#    - app/Exports/               (8 classe Maatwebsite — vedi §11)
#    - resources/views/           (4 sottocartelle: admin, azienda, autista, default, stampa — vedi §10)
#    - resources/css/app.css      (vuoto/quasi)
#    - resources/js/app.js        (entry Mix)
#    - public/default/            (tema admin completo — §14)
#    - public/.htaccess
#    - public/allegati_*          (cartelle vuote, create dai controller via mkdir; basta crearle)

# 7. routes/web.php e routes/api.php: sostituisci interi i contenuti con quelli in §6 e §7

# 8. Build asset
npm run dev

# 9. Server di sviluppo
php artisan serve
# oppure servi public/ via XAMPP/Apache.

# 10. Primo accesso: l'app non ha seed. Devi inserire manualmente:
#     INSERT INTO aziende (...) VALUES (...);
#     INSERT INTO utenti (id_azienda, nome, cognome, email, password, admin_azienda) 
#         VALUES (1, 'Admin', '', 'admin@example.it', 'password_in_chiaro', 1);
#     Poi vai a http://localhost/admin/login
```

---

## 3. `composer.json` / `package.json` / `webpack.mix.js`

### `composer.json`

```json
{
    "name": "laravel/laravel",
    "type": "project",
    "description": "The Laravel Framework.",
    "keywords": ["framework", "laravel"],
    "license": "MIT",
    "require": {
        "php": "^7.3|^8.0",
        "ayeo/gs1_barcode": "1.0.4",
        "fideloper/proxy": "^4.4",
        "fruitcake/laravel-cors": "^2.0",
        "guzzlehttp/guzzle": "^7.0.1",
        "laravel/framework": "^8.40",
        "laravel/tinker": "^2.5",
        "maatwebsite/excel": "^3.1",
        "mailchimp/marketing": "^3.0",
        "mpdf/mpdf": "^8.0",
        "nextgen-tech/gs1-decoder": "^0.3.1",
        "phpmailer/phpmailer": "^6.6",
        "phpseclib/phpseclib": "^3.0",
        "webklex/laravel-imap": "^2.4"
    },
    "require-dev": {
        "facade/ignition": "^2.5",
        "fakerphp/faker": "^1.9.1",
        "laravel/sail": "^1.0.1",
        "mockery/mockery": "^1.4.2",
        "nunomaduro/collision": "^5.0",
        "phpunit/phpunit": "^9.3.3"
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Database\\Factories\\": "database/factories/",
            "Database\\Seeders\\": "database/seeders/"
        }
    },
    "autoload-dev": {
        "psr-4": { "Tests\\": "tests/" }
    },
    "scripts": {
        "post-autoload-dump": [
            "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
            "@php artisan package:discover --ansi"
        ],
        "post-root-package-install": [
            "@php -r \"file_exists('.env') || copy('.env.example', '.env');\""
        ],
        "post-create-project-cmd": ["@php artisan key:generate --ansi"]
    },
    "extra": { "laravel": { "dont-discover": [] } },
    "config": {
        "optimize-autoloader": true,
        "preferred-install": "dist",
        "sort-packages": true
    },
    "minimum-stability": "dev",
    "prefer-stable": true
}
```

### `package.json`

```json
{
    "private": true,
    "scripts": {
        "dev": "npm run development",
        "development": "mix",
        "watch": "mix watch",
        "watch-poll": "mix watch -- --watch-options-poll=1000",
        "hot": "mix watch --hot",
        "prod": "npm run production",
        "production": "mix --production"
    },
    "devDependencies": {
        "axios": "^0.21",
        "laravel-mix": "^6.0.6",
        "lodash": "^4.17.19",
        "postcss": "^8.1.14"
    }
}
```

### `webpack.mix.js`

```js
const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
    .postCss('resources/css/app.css', 'public/css', []);
```

---

## 4. `.env`

```dotenv
APP_NAME=Laravel
APP_ENV=local
APP_KEY=                           # → genera con `php artisan key:generate`
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nusa.logistia.it
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=525600            # un anno (importante: replica originale)

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null

# Vedi §12 per le variabili delle integrazioni esterne (Google Maps, FlottaInCloud, OpenAPI P.IVA).
```

---

## 5. Database — 39 migration

**Crea i seguenti file in `database/migrations/`** (mantieni l'ordine: alcune tabelle hanno FK a `cantieri`).

I timestamp dei nomi file sono sequenziali per garantire l'ordine di esecuzione.

> **Nota generale**: la maggior parte delle tabelle usa `utf8mb4` general/unicode collation. Solo `articoli`, `ateco_*`, `mgmov`, `utenti` usano `utf8`/`utf8_general_ci` (replicato). `utenti` è MyISAM nell'originale, le altre InnoDB.

### `2024_01_01_000001_create_aziende_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('aziende', function (Blueprint $t) {
            $t->charset = 'utf8mb4';
            $t->collation = 'utf8mb4_general_ci';
            $t->increments('id');
            $t->boolean('flotta_abilitato')->default(0)->comment('FlottaInCloud attivo (0=No, 1=Sì)');
            $t->string('flotta_email')->nullable()->comment('Email per API FlottaInCloud');
            $t->string('flotta_token')->nullable()->comment('Token API FlottaInCloud');
            $t->timestamp('flotta_ultimo_sync')->nullable()->comment('Ultimo aggiornamento dati GPS');
            $t->json('flotta_config')->nullable()->comment('Configurazioni aggiuntive FlottaInCloud');
            $t->string('titolo', 50)->nullable();
            $t->string('descrizione', 50)->nullable();
            $t->string('ragione_sociale', 50)->nullable();
            $t->string('partita_iva', 50)->nullable();
            $t->string('comune', 50)->nullable();
            $t->string('indirizzo', 50)->nullable();
            $t->integer('dipendenti')->nullable();
            $t->integer('codice_ateco')->nullable();
            $t->string('descrizione_codice_ateco', 50)->nullable();
            $t->string('regione', 50)->nullable();
            $t->integer('cap')->nullable();
            $t->string('provincia', 50)->nullable();
            $t->string('codice_sdi', 50)->nullable();
            $t->string('pec', 50)->nullable();
            $t->integer('id_utente')->nullable();
            $t->string('regime_fiscale', 50)->nullable();
            $t->string('nazione', 2)->nullable();
            $t->integer('id_modulo')->nullable();
            $t->integer('invio_mail_sollecito')->default(0);
            $t->text('template_oggetto_sollecito')->nullable();
            $t->text('template_testo_sollecito')->nullable();
            $t->text('template_testo_sollecito_rate')->nullable();
            $t->string('email_smtp')->nullable();
            $t->string('password_smtp')->nullable();
            $t->string('immagine', 50)->default('base_logo_white_background.png');
            $t->string('email_segnalazioni_guasti')->nullable()->comment('Email per segnalazioni guasti (se vuota usa email azienda)');
        });
    }
    public function down() { Schema::dropIfExists('aziende'); }
};
```

### `2024_01_01_000002_create_utenti_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('utenti', function (Blueprint $t) {
            $t->engine = 'MyISAM';
            $t->charset = 'utf8';
            $t->collation = 'utf8_unicode_ci';
            $t->increments('id');
            $t->string('fish')->nullable();
            $t->integer('id_agente')->default(0);
            $t->integer('id_azienda')->nullable();
            $t->integer('id_reparto')->nullable();
            $t->integer('id_sezione')->nullable();
            $t->integer('super_admin')->default(0);
            $t->integer('admin_azienda')->nullable();
            $t->string('immagine', 500)->default('/default/assets/images/users/user-dummy-img.jpg');
            $t->string('nome', 200)->nullable();
            $t->string('cognome', 200)->nullable();
            $t->date('data_nascita')->nullable();
            $t->string('luogo_nascita', 150)->nullable();
            $t->string('piva', 200)->nullable();
            $t->string('ragione_sociale', 150)->nullable();
            $t->string('cciaa', 150)->nullable();
            $t->string('rea', 150)->nullable();
            $t->string('email', 100)->default('');
            $t->string('telefono', 100)->default('');
            $t->string('indirizzo', 100)->nullable();
            $t->string('cap', 100)->nullable();
            $t->string('comune', 200)->nullable();
            $t->string('provincia', 200)->nullable();
            $t->string('regione', 200)->nullable();
            $t->integer('fatturato')->nullable();
            $t->integer('dipendenti')->default(0);
            $t->string('ateco_codice', 50)->nullable();
            $t->string('ateco_descrizione', 200)->nullable();
            $t->string('grandezza_azienda', 200)->nullable();
            $t->string('cf', 200)->nullable();
            $t->string('sdi', 200)->nullable();
            $t->string('pec', 200)->nullable();
            $t->string('mail_recapito', 200)->nullable();
            $t->string('mail_leads', 200)->nullable();
            $t->string('referente', 200)->nullable();
            $t->string('telefono_referente', 200)->nullable();
            $t->string('password', 100)->default('');
            $t->integer('id_tipologia')->default(0);
            $t->string('verification_token', 100)->nullable();
            $t->string('token_recupero_password', 100)->nullable();
            $t->integer('abilitato')->default(1);
            $t->integer('accesso_inviato')->default(0);
            $t->dateTime('timeins')->useCurrent();
            $t->string('token_utente_per_bando', 50)->nullable();
            $t->string('onesignal_token', 200)->nullable();
            $t->string('onesignal_token_mobile', 200)->nullable();
            $t->string('id_ruolo', 50)->nullable();
            $t->integer('vista_operaio')->default(0);
            $t->decimal('costo_giornaliero', 20, 6)->default(0);
            $t->boolean('is_responsabile')->default(0);
            $t->timestamps();
            $t->boolean('solo_lettura')->default(0);
            $t->boolean('gestione_cantieri')->default(1);
            $t->boolean('gestione_mezzi')->default(0);
            $t->boolean('gestione_magazzino')->default(0);
            $t->boolean('gestione_utenti')->default(0);
            $t->boolean('visualizza_costi')->default(0);
            $t->boolean('gestione_trasporti')->default(0)->comment('Può gestire ordini trasporto');
            $t->string('ruolo', 50)->default('admin');
            $t->boolean('vista_autista')->default(0);
            $t->index('email', 'email');
        });
    }
    public function down() { Schema::dropIfExists('utenti'); }
};
```

### `2024_01_01_000003_create_articoli_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('articoli', function (Blueprint $t) {
            $t->charset = 'utf8'; $t->collation = 'utf8_general_ci';
            $t->increments('id');
            $t->integer('id_azienda')->nullable();
            $t->string('codice', 100)->nullable();
            $t->string('immagine', 200)->default('/placehold_immagine.png');
            $t->string('titolo', 500)->nullable();
            $t->text('descrizione')->nullable();
            $t->decimal('prezzo', 10, 2)->nullable();
            $t->integer('quantita')->default(0);
            $t->string('unita_misura', 100)->nullable();
            $t->integer('quantita_impegnata')->nullable();
            $t->integer('tipologia')->nullable();
            $t->timestamps();
            $t->decimal('costo', 10, 2)->default(0);
            $t->integer('soglia_riordino')->default(0);
        });
    }
    public function down() { Schema::dropIfExists('articoli'); }
};
```

### `2024_01_01_000004_create_ateco_sezioni_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('ateco_sezioni', function (Blueprint $t) {
            $t->charset = 'utf8'; $t->collation = 'utf8_general_ci';
            $t->increments('id');
            $t->string('sezione', 250)->default('0');
            $t->string('descrizione', 250)->default('0');
        });
    }
    public function down() { Schema::dropIfExists('ateco_sezioni'); }
};
```

### `2024_01_01_000005_create_ateco_codici_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('ateco_codici', function (Blueprint $t) {
            $t->charset = 'utf8'; $t->collation = 'utf8_general_ci';
            $t->increments('id');
            $t->integer('id_sezione')->default(0);
            $t->string('sezione', 50);
            $t->string('codice', 20)->nullable();
            $t->text('descrizione')->nullable();
        });
    }
    public function down() { Schema::dropIfExists('ateco_codici'); }
};
```

### `2024_01_01_000006_create_cantieri_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('cantieri', function (Blueprint $t) {
            $t->charset = 'utf8mb4'; $t->collation = 'utf8mb4_general_ci';
            $t->increments('id');
            $t->integer('id_azienda')->nullable();
            $t->string('titolo')->nullable();
            $t->integer('stato')->default(1);
            $t->string('immagine')->nullable();
            $t->text('descrizione')->nullable();
            $t->date('data_inizio')->nullable();
            $t->date('data_fine')->nullable();
            $t->double('costo_totale')->default(0);
            $t->double('costo_stimato')->default(0);
            $t->double('valore_totale')->default(0);
            $t->double('valore_stimato')->default(0);
            $t->string('colore', 50)->nullable();
            $t->string('latitudine', 50)->nullable();
            $t->string('longitudine', 50)->nullable();
            $t->string('indirizzo', 250)->nullable();
            $t->decimal('costo_manodopera', 20, 6)->default(0);
            $t->timestamps();
            $t->double('costo_stimato_originale')->nullable();
            $t->dateTime('data_chiusura')->nullable();
            $t->boolean('contabilizzato')->default(1)->comment('1=Contabilizzato, 0=Non contabilizzato');
        });
    }
    public function down() { Schema::dropIfExists('cantieri'); }
};
```

### `2024_01_01_000007_create_cantieri_allegati_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('cantieri_allegati', function (Blueprint $t) {
            $t->charset = 'utf8mb4'; $t->collation = 'utf8mb4_unicode_ci';
            $t->bigIncrements('id');
            $t->unsignedBigInteger('id_cantiere');
            $t->string('nome_file');
            $t->string('nome_originale');
            $t->enum('tipo', ['image', 'document', 'pdf']);
            $t->string('dimensione', 20);
            $t->text('descrizione')->nullable();
            $t->string('path', 500);
            $t->timestamps();
            $t->index('id_cantiere', 'idx_id_cantiere');
            $t->index('tipo', 'idx_tipo');
        });
    }
    public function down() { Schema::dropIfExists('cantieri_allegati'); }
};
```

### `2024_01_01_000008_create_cantieri_attivita_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('cantieri_attivita', function (Blueprint $t) {
            $t->charset = 'utf8mb4'; $t->collation = 'utf8mb4_general_ci';
            $t->increments('id');
            $t->integer('id_azienda')->default(0);
            $t->integer('id_cantiere')->default(0);
            $t->string('descrizione', 200)->default('0');
            $t->date('data_schedulazione')->nullable();
            $t->timestamp('data_inizio')->nullable();
            $t->timestamp('data_fine')->nullable();
            $t->integer('id_operatore_start')->nullable();
            $t->integer('id_operatore_stop')->nullable();
            $t->text('note');
        });
    }
    public function down() { Schema::dropIfExists('cantieri_attivita'); }
};
```

### `2024_01_01_000009_create_cantieri_attivita_dipendenti_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('cantieri_attivita_dipendenti', function (Blueprint $t) {
            $t->charset = 'utf8mb4'; $t->collation = 'utf8mb4_general_ci';
            $t->increments('id');
            $t->integer('id_attivita')->nullable();
            $t->integer('id_cantiere')->nullable();
            $t->integer('id_dipendente')->nullable();
            $t->timestamps();
            $t->integer('id_azienda')->nullable();
        });
    }
    public function down() { Schema::dropIfExists('cantieri_attivita_dipendenti'); }
};
```

### `2024_01_01_000010_create_cantieri_attivita_operaio_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('cantieri_attivita_operaio', function (Blueprint $t) {
            $t->charset = 'utf8mb4'; $t->collation = 'utf8mb4_general_ci';
            $t->increments('id');
            $t->integer('id_attivita')->default(0);
            $t->integer('id_azienda')->default(0);
            $t->integer('id_operaio_cantiere')->default(0);
            $t->timestamp('tempo_inizio')->nullable();
            $t->string('lat_inizio', 250)->default('');
            $t->string('lng_inizio', 250)->default('');
            $t->timestamp('tempo_fine')->nullable();
            $t->string('lat_fine', 250)->nullable();
            $t->string('lng_fine', 250)->nullable();
            $t->text('note');
        });
    }
    public function down() { Schema::dropIfExists('cantieri_attivita_operaio'); }
};
```

### `2024_01_01_000011_create_cantieri_operai_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('cantieri_operai', function (Blueprint $t) {
            $t->charset = 'utf8mb4'; $t->collation = 'utf8mb4_general_ci';
            $t->increments('id');
            $t->integer('id_azienda')->default(0);
            $t->integer('id_cantiere')->default(0);
            $t->string('nome', 200)->nullable();
            $t->string('cognome', 200)->nullable();
            $t->string('mansione', 50)->nullable();
            $t->integer('id_dipendente')->nullable();
        });
    }
    public function down() { Schema::dropIfExists('cantieri_operai'); }
};
```

### `2024_01_01_000012_create_cantieri_operai_giorni_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('cantieri_operai_giorni', function (Blueprint $t) {
            $t->charset = 'utf8mb4'; $t->collation = 'utf8mb4_general_ci';
            $t->increments('id');
            $t->integer('id_cantiere');
            $t->integer('id_dipendente');
            $t->date('data_lavoro');
            $t->string('nome')->nullable();
            $t->string('cognome')->nullable();
            $t->string('mansione')->nullable();
            $t->integer('id_azienda')->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->nullable();
            $t->unique(['id_cantiere', 'id_dipendente', 'data_lavoro'], 'unique_assignment');
        });
    }
    public function down() { Schema::dropIfExists('cantieri_operai_giorni'); }
};
```

### `2024_01_01_000013_create_cantieri_pagamenti_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('cantieri_pagamenti', function (Blueprint $t) {
            $t->charset = 'utf8mb4'; $t->collation = 'utf8mb4_general_ci';
            $t->increments('id');
            $t->integer('id_cantiere');
            $t->integer('id_azienda');
            $t->enum('tipo', ['ricevuto', 'da_ricevere'])->comment('Tipo pagamento');
            $t->decimal('importo', 10, 2)->comment('Importo del pagamento');
            $t->date('data_scadenza')->nullable()->comment('Data scadenza per pagamenti da ricevere');
            $t->date('data_pagamento')->nullable()->comment('Data effettiva pagamento');
            $t->text('descrizione')->nullable()->comment('Descrizione del pagamento');
            $t->text('note')->nullable()->comment('Note aggiuntive');
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->useCurrent();
            $t->index('id_cantiere');
            $t->foreign('id_cantiere')->references('id')->on('cantieri')->onDelete('cascade');
        });
    }
    public function down() { Schema::dropIfExists('cantieri_pagamenti'); }
};
```

### `2024_01_01_000014_create_cantieri_responsabili_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('cantieri_responsabili', function (Blueprint $t) {
            $t->charset = 'utf8mb4'; $t->collation = 'utf8mb4_general_ci';
            $t->increments('id');
            $t->integer('id_cantiere');
            $t->integer('id_responsabile');
            $t->decimal('percentuale', 5, 2)->default(0);
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->useCurrent();
            $t->unique(['id_cantiere', 'id_responsabile'], 'unique_cantiere_responsabile');
        });
    }
    public function down() { Schema::dropIfExists('cantieri_responsabili'); }
};
```

### `2024_01_01_000015_create_clienti_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('clienti', function (Blueprint $t) {
            $t->charset = 'utf8mb4'; $t->collation = 'utf8mb4_general_ci';
            $t->increments('id');
            $t->string('ragione_sociale');
            $t->string('piva', 20)->nullable();
            $t->string('codice_fiscale', 20)->nullable();
            $t->text('indirizzo')->nullable();
            $t->string('citta', 100)->nullable();
            $t->string('cap', 10)->nullable();
            $t->string('provincia', 5)->nullable();
            $t->string('telefono', 20)->nullable();
            $t->string('email')->nullable();
            $t->text('note')->nullable();
            $t->integer('id_azienda');
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->useCurrent();
        });
    }
    public function down() { Schema::dropIfExists('clienti'); }
};
```

### `2024_01_01_000016_create_dispositivi_tracking_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('dispositivi_tracking', function (Blueprint $t) {
            $t->charset = 'utf8mb4'; $t->collation = 'utf8mb4_unicode_ci';
            $t->bigIncrements('id');
            $t->unsignedBigInteger('id_azienda');
            $t->unsignedBigInteger('id_mezzo')->nullable();
            $t->unsignedBigInteger('id_utente')->nullable();
            $t->string('device_token', 64)->unique('device_token_unique');
            $t->string('nome', 100)->nullable();
            $t->string('targa_mezzo', 20)->nullable();
            $t->timestamp('ultimo_heartbeat')->nullable();
            $t->boolean('is_active')->default(1);
            $t->boolean('configurato')->default(0);
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->useCurrent();
            $t->index(['id_azienda', 'is_active'], 'idx_azienda_active');
            $t->index('id_utente', 'idx_utente');
        });
    }
    public function down() { Schema::dropIfExists('dispositivi_tracking'); }
};
```

### `2024_01_01_000017_create_documenti_trasporto_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('documenti_trasporto', function (Blueprint $t) {
            $t->charset = 'utf8mb4'; $t->collation = 'utf8mb4_general_ci';
            $t->increments('id');
            $t->integer('id_ordine');
            $t->enum('tipo_documento', ['ddt', 'cmr', 'fattura', 'bolla', 'ricevuta']);
            $t->string('numero_documento', 50);
            $t->date('data_documento');
            $t->string('mittente_nome')->nullable();
            $t->text('mittente_indirizzo')->nullable();
            $t->string('destinatario_nome')->nullable();
            $t->text('destinatario_indirizzo')->nullable();
            $t->text('descrizione_merce')->nullable();
            $t->decimal('peso_lordo', 8, 2)->nullable();
            $t->decimal('peso_netto', 8, 2)->nullable();
            $t->integer('numero_colli')->nullable();
            $t->decimal('valore_merce', 10, 2)->nullable();
            $t->text('note')->nullable();
            $t->longText('firma_mittente')->nullable()->comment('Base64 firma mittente');
            $t->longText('firma_vettore')->nullable()->comment('Base64 firma autista/vettore');
            $t->longText('firma_destinatario')->nullable()->comment('Base64 firma destinatario/cliente');
            $t->dateTime('data_firma_mittente')->nullable();
            $t->dateTime('data_firma_vettore')->nullable();
            $t->dateTime('data_firma_destinatario')->nullable();
            $t->string('file_path', 500)->nullable();
            $t->text('firma_digitale')->nullable()->comment('Base64 della firma');
            $t->string('foto_consegna', 500)->nullable();
            $t->string('consegnato_a')->nullable();
            $t->dateTime('data_consegna')->nullable();
            $t->string('token_pubblico', 64)->nullable();
            $t->integer('id_azienda');
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->useCurrent();
        });
    }
    public function down() { Schema::dropIfExists('documenti_trasporto'); }
};
```

### `2024_01_01_000018_create_foto_consegna_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('foto_consegna', function (Blueprint $t) {
            $t->charset = 'utf8mb4'; $t->collation = 'utf8mb4_unicode_ci';
            $t->bigIncrements('id');
            $t->unsignedBigInteger('id_ordine')->comment('FK ordini_trasporto.id');
            $t->unsignedBigInteger('id_autista')->comment('FK utenti.id');
            $t->unsignedBigInteger('id_azienda');
            $t->enum('tipo', ['merce', 'firma', 'danno', 'ricevuta', 'altro'])->default('merce');
            $t->string('percorso_file', 500);
            $t->string('nome_file');
            $t->unsignedInteger('dimensione')->nullable();
            $t->text('nota')->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->index('id_ordine', 'idx_ordine');
            $t->index('id_autista', 'idx_autista');
        });
    }
    public function down() { Schema::dropIfExists('foto_consegna'); }
};
```

### `2024_01_01_000019_create_impegni_magazzino_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('impegni_magazzino', function (Blueprint $t) {
            $t->charset = 'utf8mb4'; $t->collation = 'utf8mb4_general_ci';
            $t->increments('id');
            $t->integer('id_articolo');
            $t->integer('id_cantiere');
            $t->integer('quantita_impegnata')->default(1);
            $t->integer('id_azienda');
        });
    }
    public function down() { Schema::dropIfExists('impegni_magazzino'); }
};
```

### `2024_01_01_000020_create_km_giornalieri_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('km_giornalieri', function (Blueprint $t) {
            $t->charset = 'utf8mb4'; $t->collation = 'utf8mb4_unicode_ci';
            $t->bigIncrements('id');
            $t->unsignedBigInteger('id_dispositivo');
            $t->unsignedBigInteger('id_mezzo')->nullable();
            $t->unsignedBigInteger('id_azienda');
            $t->date('data');
            $t->decimal('km_percorsi', 10, 2)->default(0);
            $t->integer('tempo_movimento_minuti')->default(0);
            $t->integer('tempo_fermo_minuti')->default(0);
            $t->decimal('velocita_media', 5, 2)->nullable();
            $t->decimal('velocita_max', 5, 2)->nullable();
            $t->integer('num_soste')->default(0);
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->useCurrent();
            $t->unique(['id_dispositivo', 'data'], 'unique_dispositivo_data');
            $t->index(['id_azienda', 'data'], 'idx_azienda_data');
            $t->index(['id_mezzo', 'data'], 'idx_mezzo_data');
        });
    }
    public function down() { Schema::dropIfExists('km_giornalieri'); }
};
```

### `2024_01_01_000021_create_mezzi_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('mezzi', function (Blueprint $t) {
            $t->charset = 'utf8mb4'; $t->collation = 'utf8mb4_general_ci';
            $t->increments('id');
            $t->integer('id_azienda');
            $t->string('nome');
            $t->string('tipo');
            $t->string('targa', 50)->unique('targa');
            $t->year('anno_immatricolazione');
            $t->enum('stato', ['Disponibile', 'In uso', 'Manutenzione'])->default('Disponibile');
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->useCurrent();
            $t->integer('km_attuali')->default(0);
            $t->integer('km_warning')->default(30000);
            $t->integer('km_danger')->default(50000);
            $t->integer('flotta_in_cloud')->nullable();
            $t->string('modello', 50)->nullable();
            $t->string('marca', 100)->nullable();
            $t->integer('km_iniziali_contachilometri')->nullable()->comment('Km letti dal contachilometri al primo setup');
            $t->decimal('km_accumulati_gps', 12, 2)->default(0)->comment('Km calcolati da GPS');
            $t->timestamp('data_attivazione_tracking')->nullable();
            $t->boolean('tracking_attivo')->default(0);
            $t->decimal('ultima_lat', 10, 8)->nullable();
            $t->decimal('ultima_lng', 11, 8)->nullable();
            $t->decimal('ultima_velocita', 6, 2)->nullable()->comment('Ultima velocità km/h');
            $t->timestamp('ultimo_aggiornamento_gps')->nullable();
        });
    }
    public function down() { Schema::dropIfExists('mezzi'); }
};
```

### `2024_01_01_000022_create_mezzi_gomme_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('mezzi_gomme', function (Blueprint $t) {
            $t->charset = 'utf8mb4'; $t->collation = 'utf8mb4_general_ci';
            $t->increments('id');
            $t->integer('id_mezzo');
            $t->integer('id_azienda');
            $t->enum('posizione', ['anteriore_sx', 'anteriore_dx', 'posteriore_sx', 'posteriore_dx']);
            $t->date('data_sostituzione');
            $t->integer('km_sostituzione');
            $t->decimal('costo', 10, 2)->default(0);
            $t->string('fornitore')->nullable();
            $t->string('marca_modello')->nullable();
            $t->text('note')->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->useCurrent();
            $t->index(['id_mezzo', 'id_azienda'], 'idx_mezzo_azienda');
            $t->index('posizione', 'idx_posizione');
            $t->index('data_sostituzione', 'idx_data');
        });
    }
    public function down() { Schema::dropIfExists('mezzi_gomme'); }
};
```

### `2024_01_01_000023_create_mezzi_manutenzioni_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('mezzi_manutenzioni', function (Blueprint $t) {
            $t->charset = 'utf8mb4'; $t->collation = 'utf8mb4_general_ci';
            $t->increments('id');
            $t->integer('id_mezzo');
            $t->integer('id_azienda');
            $t->string('tipo');
            $t->text('descrizione');
            $t->decimal('importo', 10, 2);
            $t->date('data_operazione');
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->useCurrent();
            $t->integer('km_operazione')->nullable();
            $t->integer('riferimento_gomma_id')->nullable();
            $t->index('riferimento_gomma_id', 'idx_riferimento_gomma');
        });
    }
    public function down() { Schema::dropIfExists('mezzi_manutenzioni'); }
};
```

### `2024_01_01_000024_create_mgmov_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('mgmov', function (Blueprint $t) {
            $t->charset = 'utf8'; $t->collation = 'utf8_general_ci';
            $t->increments('id');
            $t->integer('id_azienda')->default(0);
            $t->integer('id_cantiere')->default(0);
            $t->timestamp('datamov')->useCurrent();
            $t->string('causale', 400)->nullable();
            $t->integer('id_articolo')->default(0);
            $t->decimal('qta', 10, 6)->nullable();
            $t->integer('car')->default(0);
            $t->integer('sca')->default(0);
            $t->integer('ret')->default(0);
            $t->integer('id_utente')->nullable();
        });
    }
    public function down() { Schema::dropIfExists('mgmov'); }
};
```

### `2024_01_01_000025_create_notifiche_autista_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('notifiche_autista', function (Blueprint $t) {
            $t->charset = 'utf8mb4'; $t->collation = 'utf8mb4_unicode_ci';
            $t->bigIncrements('id');
            $t->unsignedBigInteger('id_autista')->comment('FK utenti.id');
            $t->unsignedBigInteger('id_azienda');
            $t->unsignedBigInteger('id_ordine')->nullable()->comment('FK ordini_trasporto.id (opzionale)');
            $t->enum('tipo', ['nuovo_ordine', 'cambio_stato', 'modifica_ordine', 'messaggio', 'urgente'])->default('nuovo_ordine');
            $t->string('titolo');
            $t->text('messaggio')->nullable();
            $t->boolean('letta')->default(0);
            $t->timestamp('letta_at')->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->useCurrent();
            $t->index(['id_autista', 'letta'], 'idx_autista_letta');
            $t->index('id_azienda', 'idx_azienda');
            $t->index('id_ordine', 'idx_ordine');
        });
    }
    public function down() { Schema::dropIfExists('notifiche_autista'); }
};
```

### `2024_01_01_000026_create_ordini_trasporto_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('ordini_trasporto', function (Blueprint $t) {
            $t->charset = 'utf8mb4'; $t->collation = 'utf8mb4_general_ci';
            $t->increments('id');
            $t->string('numero_ordine', 50);
            $t->string('numero_ddt', 50)->nullable()->comment('Numero DDT associato');
            $t->integer('id_cliente');
            $t->integer('id_mezzo')->nullable();
            $t->integer('id_autista')->nullable();
            $t->text('indirizzo_ritiro');
            $t->text('indirizzo_consegna');
            $t->date('data_ritiro');
            $t->time('ora_ritiro')->nullable();
            $t->date('data_consegna')->nullable();
            $t->time('ora_consegna')->nullable();
            $t->text('descrizione_merce');
            $t->decimal('peso_kg', 10, 2)->nullable();
            $t->unsignedInteger('km_totali')->nullable()->comment('Km totali del trasporto');
            $t->decimal('ore_stimate', 5, 2)->nullable()->comment('Ore stimate trasporto');
            $t->integer('numero_colli')->nullable();
            $t->enum('tipo_unita', ['colli', 'pedane'])->default('colli');
            $t->text('note')->nullable();
            $t->longText('firma_cliente')->nullable();
            $t->longText('firma_autista')->nullable();
            $t->decimal('importo', 10, 2)->default(0);
            $t->boolean('importo_manuale')->default(1)->comment('1=importo manuale, 0=calcolato da tariffario');
            $t->integer('id_tariffa_applicata')->nullable()->comment('ID tariffario usato per il calcolo');
            $t->text('dettaglio_costo')->nullable()->comment('JSON con breakdown del costo calcolato');
            $t->enum('stato', ['pianificato', 'assegnato', 'in_corso', 'completato', 'annullato'])->default('pianificato');
            $t->integer('ordine_percorso')->nullable();
            $t->integer('km_percorsi')->nullable();
            $t->integer('tempo_percorrenza')->nullable()->comment('minuti');
            $t->integer('id_azienda');
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->useCurrent();
            $t->timestamp('data_completamento')->nullable()->comment('Data/ora completamento effettivo');
            $t->text('note_autista')->nullable()->comment("Note inserite dall autista al completamento");
            $t->longText('firma_destinatario')->nullable()->comment('Firma base64 del destinatario');
            $t->string('nome_firmatario')->nullable()->comment('Nome di chi ha firmato');
            $t->integer('pedane_consegnate')->default(0)->comment('Pedane consegnate con la merce');
            $t->integer('pedane_da_ritirare')->default(0)->comment('Pedane attese in reso dal cliente');
            $t->integer('pedane_ritirate')->nullable()->comment("Pedane effettivamente ritirate dall autista");
            $t->integer('pasti_normali')->nullable()->comment('Pasti normali mensa');
            $t->integer('pasti_bianchi')->nullable()->comment('Pasti bianchi mensa');
            $t->integer('pasti_allergici')->nullable()->comment('Pasti allergici mensa');
            $t->integer('pasti_docenti')->nullable()->comment('Pasti docenti/ATA mensa');
            $t->string('fonte_importazione', 50)->nullable()->comment('fonte importazione PDF');
            $t->integer('pasti_ata')->nullable();
        });
    }
    public function down() { Schema::dropIfExists('ordini_trasporto'); }
};
```

### `2024_01_01_000027_create_ordine_tappe_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('ordine_tappe', function (Blueprint $t) {
            $t->charset = 'utf8mb4'; $t->collation = 'utf8mb4_general_ci';
            $t->increments('id');
            $t->integer('id_ordine');
            $t->integer('numero_tappa')->default(1);
            $t->integer('id_autista')->nullable();
            $t->integer('id_mezzo')->nullable();
            $t->text('indirizzo_ritiro');
            $t->text('indirizzo_consegna');
            $t->text('note')->nullable();
            $t->enum('stato', ['attesa', 'in_corso', 'completato'])->default('attesa');
            $t->dateTime('completato_at')->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->useCurrent();
            $t->index('id_ordine', 'idx_ordine');
            $t->index('id_autista', 'idx_autista');
        });
    }
    public function down() { Schema::dropIfExists('ordine_tappe'); }
};
```

### `2024_01_01_000028_create_pedane_movimenti_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('pedane_movimenti', function (Blueprint $t) {
            $t->charset = 'utf8mb4'; $t->collation = 'utf8mb4_general_ci';
            $t->increments('id');
            $t->integer('id_azienda');
            $t->integer('id_cliente');
            $t->integer('id_ordine')->nullable();
            $t->enum('tipo', ['consegnata', 'ritirata', 'rettifica']);
            $t->integer('quantita')->default(0);
            $t->date('data');
            $t->text('note')->nullable();
            $t->integer('id_autista')->nullable();
            $t->integer('created_by')->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->useCurrent();
            $t->index('id_azienda', 'idx_azienda');
            $t->index('id_cliente', 'idx_cliente');
            $t->index('id_ordine', 'idx_ordine');
        });
    }
    public function down() { Schema::dropIfExists('pedane_movimenti'); }
};
```

### `2024_01_01_000029_create_planning_autisti_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('planning_autisti', function (Blueprint $t) {
            $t->charset = 'utf8mb4'; $t->collation = 'utf8mb4_general_ci';
            $t->increments('id');
            $t->integer('id_azienda');
            $t->integer('id_autista');
            $t->date('data');
            $t->enum('tipo', ['lavoro', 'riposo', 'ferie', 'malattia'])->default('lavoro');
            $t->time('ora_inizio')->nullable();
            $t->time('ora_fine')->nullable();
            $t->string('note')->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->useCurrent();
            $t->unique(['id_autista', 'data'], 'uk_autista_data');
        });
    }
    public function down() { Schema::dropIfExists('planning_autisti'); }
};
```

### `2024_01_01_000030_create_posizioni_live_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('posizioni_live', function (Blueprint $t) {
            $t->charset = 'utf8mb4'; $t->collation = 'utf8mb4_unicode_ci';
            $t->bigIncrements('id');
            $t->unsignedBigInteger('id_dispositivo')->unique('unique_dispositivo');
            $t->unsignedBigInteger('id_azienda');
            $t->unsignedBigInteger('id_mezzo')->nullable();
            $t->decimal('lat', 10, 8);
            $t->decimal('lng', 11, 8);
            $t->decimal('speed', 6, 2)->default(0);
            $t->smallInteger('heading')->default(0);
            $t->decimal('accuracy', 8, 2)->nullable();
            $t->decimal('altitude', 10, 2)->nullable();
            $t->boolean('is_moving')->default(0);
            $t->tinyInteger('battery_level')->nullable();
            $t->string('indirizzo')->nullable();
            $t->timestamp('recorded_at')->useCurrent();
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->useCurrent();
            $t->index('id_azienda', 'idx_azienda');
        });
    }
    public function down() { Schema::dropIfExists('posizioni_live'); }
};
```

### `2024_01_01_000031_create_posizioni_storico_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('posizioni_storico', function (Blueprint $t) {
            $t->charset = 'utf8mb4'; $t->collation = 'utf8mb4_unicode_ci';
            $t->bigIncrements('id');
            $t->unsignedBigInteger('id_dispositivo');
            $t->unsignedBigInteger('id_azienda');
            $t->unsignedBigInteger('id_mezzo')->nullable();
            $t->decimal('lat', 10, 8);
            $t->decimal('lng', 11, 8);
            $t->decimal('speed', 6, 2)->default(0);
            $t->smallInteger('heading')->default(0);
            $t->decimal('altitude', 10, 2)->nullable();
            $t->boolean('is_moving')->default(0);
            $t->timestamp('recorded_at')->useCurrent();
            $t->index(['id_dispositivo', 'recorded_at'], 'idx_dispositivo_data');
            $t->index(['id_azienda', 'recorded_at'], 'idx_azienda_data');
            $t->index(['id_mezzo', 'recorded_at'], 'idx_mezzo_data');
        });
    }
    public function down() { Schema::dropIfExists('posizioni_storico'); }
};
```

### `2024_01_01_000032_create_presenze_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('presenze', function (Blueprint $t) {
            $t->charset = 'utf8mb4'; $t->collation = 'utf8mb4_general_ci';
            $t->increments('id');
            $t->integer('id_dipendente');
            $t->date('data');
            $t->time('ora_inizio');
            $t->time('ora_fine')->nullable();
            $t->integer('id_cantiere');
            $t->integer('id_attivita');
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->useCurrent();
            $t->integer('id_azienda')->nullable();
            $t->string('lat_inizio', 50)->nullable();
            $t->string('long_inizio', 50)->nullable();
            $t->string('lat_fine', 50)->nullable();
            $t->string('long_fine', 50)->nullable();
            $t->text('note')->nullable();
            $t->enum('tipo_registrazione', ['automatica', 'manuale'])->default('automatica');
            $t->integer('registrata_da')->nullable();
            $t->integer('modificata_da')->nullable();
        });
    }
    public function down() { Schema::dropIfExists('presenze'); }
};
```

### `2024_01_01_000033_create_regole_lavoro_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('regole_lavoro', function (Blueprint $t) {
            $t->charset = 'utf8mb4'; $t->collation = 'utf8mb4_general_ci';
            $t->increments('id');
            $t->integer('id_azienda');
            $t->integer('max_giorni_consecutivi')->default(5);
            $t->decimal('ore_max_giornaliere', 4, 1)->default(9.0);
            $t->decimal('ore_riposo_minime', 4, 1)->default(11.0);
            $t->decimal('ore_max_settimanali', 5, 1)->default(48.0);
            $t->integer('giorni_riposo_obbligatori')->default(1);
            $t->text('ruoli_ids')->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->useCurrent();
            $t->unique('id_azienda', 'uk_azienda');
        });
    }
    public function down() { Schema::dropIfExists('regole_lavoro'); }
};
```

### `2024_01_01_000034_create_rifornimenti_carburante_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('rifornimenti_carburante', function (Blueprint $t) {
            $t->charset = 'utf8mb4'; $t->collation = 'utf8mb4_unicode_ci';
            $t->bigIncrements('id');
            $t->unsignedBigInteger('id_mezzo');
            $t->unsignedBigInteger('id_azienda');
            $t->date('data_rifornimento');
            $t->unsignedInteger('km_rifornimento')->default(0);
            $t->decimal('litri', 10, 2)->default(0);
            $t->decimal('importo_totale', 10, 2)->default(0);
            $t->decimal('prezzo_litro', 6, 3)->nullable();
            $t->string('tipo_carburante', 50)->default('diesel');
            $t->string('stazione_servizio')->nullable();
            $t->boolean('pieno')->default(1)->comment('1=pieno completo, 0=parziale');
            $t->string('foto_scontrino', 500)->nullable();
            $t->text('note')->nullable();
            $t->decimal('consumo_calcolato', 6, 2)->nullable()->comment('km/l calcolato automaticamente');
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->useCurrent();
            $t->index('id_mezzo', 'idx_mezzo');
            $t->index('id_azienda', 'idx_azienda');
            $t->index('data_rifornimento', 'idx_data');
            $t->index(['id_mezzo', 'data_rifornimento'], 'idx_mezzo_data');
        });
    }
    public function down() { Schema::dropIfExists('rifornimenti_carburante'); }
};
```

### `2024_01_01_000035_create_ruoli_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('ruoli', function (Blueprint $t) {
            $t->charset = 'utf8mb4'; $t->collation = 'utf8mb4_general_ci';
            $t->increments('id');
            $t->string('titolo', 50)->default('0');
            $t->integer('id_azienda')->nullable();
        });
    }
    public function down() { Schema::dropIfExists('ruoli'); }
};
```

### `2024_01_01_000036_create_segnalazioni_guasti_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('segnalazioni_guasti', function (Blueprint $t) {
            $t->charset = 'utf8mb4'; $t->collation = 'utf8mb4_general_ci';
            $t->increments('id');
            $t->integer('id_azienda');
            $t->integer('id_autista');
            $t->integer('id_mezzo')->nullable();
            $t->enum('tipo_guasto', ['meccanico', 'elettrico', 'pneumatico', 'carrozzeria', 'altro'])->default('altro');
            $t->text('descrizione');
            $t->enum('urgenza', ['bassa', 'media', 'alta', 'critica'])->default('media');
            $t->decimal('latitudine', 10, 8)->nullable();
            $t->decimal('longitudine', 11, 8)->nullable();
            $t->string('indirizzo', 500)->nullable();
            $t->enum('stato', ['segnalato', 'preso_in_carico', 'in_riparazione', 'risolto'])->default('segnalato');
            $t->text('note_risoluzione')->nullable();
            $t->integer('risolto_da')->nullable();
            $t->dateTime('data_risoluzione')->nullable();
            $t->boolean('email_inviata')->default(0);
            $t->string('email_destinatario')->nullable();
            $t->string('foto')->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->useCurrent();
            $t->index('id_azienda', 'idx_azienda');
            $t->index('id_autista', 'idx_autista');
            $t->index('id_mezzo', 'idx_mezzo');
            $t->index('stato', 'idx_stato');
            $t->index('created_at', 'idx_created');
        });
    }
    public function down() { Schema::dropIfExists('segnalazioni_guasti'); }
};
```

### `2024_01_01_000037_create_tariffari_clienti_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('tariffari_clienti', function (Blueprint $t) {
            $t->charset = 'utf8mb4'; $t->collation = 'utf8mb4_general_ci';
            $t->increments('id');
            $t->integer('id_cliente');
            $t->string('nome_tariffa', 100);
            $t->enum('tipo_calcolo', ['fisso', 'km', 'peso', 'volume', 'tempo'])->default('km');
            $t->decimal('prezzo_base', 8, 2)->default(0);
            $t->decimal('prezzo_per_km', 6, 3)->nullable()->comment('Euro per chilometro');
            $t->decimal('prezzo_per_kg', 6, 3)->nullable()->comment('Euro per chilogrammo');
            $t->decimal('prezzo_per_ora', 6, 2)->nullable()->comment('Euro per ora');
            $t->integer('km_minimi')->default(0)->comment('Chilometri minimi fatturabili');
            $t->decimal('peso_minimo', 8, 2)->default(0)->comment('Peso minimo fatturabile');
            $t->decimal('maggiorazione_urgente', 5, 2)->default(0)->comment('Percentuale maggiorazione');
            $t->decimal('maggiorazione_festivo', 5, 2)->default(0)->comment('Percentuale maggiorazione festivi');
            $t->decimal('maggiorazione_notturno', 5, 2)->default(0)->comment('Percentuale maggiorazione notturno');
            $t->decimal('sconto_fedelta', 5, 2)->default(0)->comment('Percentuale sconto');
            $t->date('valido_dal');
            $t->date('valido_fino')->nullable();
            $t->boolean('attivo')->default(1);
            $t->integer('id_azienda');
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->useCurrent();
        });
    }
    public function down() { Schema::dropIfExists('tariffari_clienti'); }
};
```

### `2024_01_01_000038_create_utenti_ruoli_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('utenti_ruoli', function (Blueprint $t) {
            $t->charset = 'utf8mb4'; $t->collation = 'utf8mb4_general_ci';
            $t->increments('id');
            $t->integer('id_utente');
            $t->integer('id_ruolo');
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('updated_at')->useCurrent();
            $t->unique(['id_utente', 'id_ruolo'], 'unique_utente_ruolo');
        });
    }
    public function down() { Schema::dropIfExists('utenti_ruoli'); }
};
```

> Le tabelle `migrations` e `users`/`password_resets`/`failed_jobs` di Laravel sono auto-create dalla `migrate:install` e dalle migration di stub Laravel: lasciale come default. La piattaforma non le usa (non c'è auth Laravel).

> **Tabelle "ERP legacy"** (bandi, preventivi, fatture, contratti, fornitori, agenti, leads, formazione_40, commesse, distinta_base, fasi_di_lavorazione, ODL, dipendenti separati): NON esistono nel DB attuale. Le viste in `resources/views/default/` ne fanno riferimento ma le route in `web.php` non le invocano: sono codice morto/parzialmente cablato. Se in futuro vorrai attivarle, andranno create separatamente.

---

## 6. Routes — `routes/web.php`

Sostituisci interamente `routes/web.php` con:

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrackingDashboardController;
use App\Http\Controllers\AutistaController;
use App\Http\Controllers\TrasportiController;

// Calcolo km Google Maps
Route::post('/azienda/ordine/calcola-km', 'TrasportiController@calcolaKmGoogle');

// Stampa PDF DDT
Route::get('/azienda/ddt/{id}/pdf', [App\Http\Controllers\TrasportiController::class, 'stampaDDT']);
Route::post('/azienda/ddt/salva-firma', [TrasportiController::class, 'salvaFirmaDDT']);
Route::post('/azienda/ddt/rimuovi-firma', [TrasportiController::class, 'rimuoviFirmaDDT']);
Route::post('/azienda/genera-ddt/{id}', [App\Http\Controllers\TrasportiController::class, 'generaDDT']);

// Ordini trasporto (alias non-azienda)
Route::get('/ordini-trasporto', 'AziendaController@ordiniTrasporto');
Route::post('/ordini-trasporto', 'AziendaController@ordiniTrasporto');
Route::get('/ordine-trasporto/{id}', 'AziendaController@dettaglioOrdine');
Route::post('/ordine-trasporto/cambia-stato', 'AziendaController@cambiaStatoOrdine');

// Admin
Route::any('', 'HomeController@index');
Route::any('admin/login', 'AdminController@login');
Route::any('admin/index', 'AdminController@index');
Route::any('admin/aziende', 'AdminController@aziende');
Route::any('admin/moduli', 'AdminController@moduli');
Route::any('admin/utenti', 'AdminController@utenti');
Route::any('admin/logout', 'AdminController@logout');
Route::any('admin/effettua_login', 'AdminController@effettua_login')->name('effettua_login');
Route::get('/admin/cerca-piva', 'AdminController@cercaPiva');

// Azienda — generale
Route::any('azienda/index', 'AziendaController@index');
Route::any('azienda/cantieri', 'AziendaController@cantieri');
Route::any('azienda/logout', 'AziendaController@logout');
Route::any('/azienda/cantiere/{id}', 'AziendaController@dettaglioCantiere')->name('azienda.cantiere.dettaglio');
Route::any('/azienda/cantieri/save', 'AziendaController@saveCantiere')->name('azienda.cantieri.save');
Route::any('azienda/utenti', 'AziendaController@utenti');
Route::any('azienda/ruoli', 'AziendaController@ruoli')->name('azienda.ruoli');
Route::get('/get-attivita-dipendente/{id}', 'AziendaController@getAttivitaDipendente');
Route::get('/get-dipendenti-attivita/{id}', 'AziendaController@getDipendentiAttivita');
Route::post('/salva-dipendenti-attivita', 'AziendaController@salvaDipendentiAttivita');
Route::any('/attivita/aggiorna-dipendenti', 'AziendaController@aggiornaDipendenti');

// Azienda — Mezzi
Route::any('azienda/mezzi', 'AziendaController@anagraficaMezzi')->name('anagrafica_mezzi');
Route::any('/azienda/mezzo/{id}', 'AziendaController@dettaglioMezzo')->name('dettaglio_mezzo');
Route::post('/azienda/mezzo/{id}/modifica-stato', 'AziendaController@modificaStatoMezzo')->name('modifica_stato_mezzo');
Route::post('/azienda/mezzo/{id}/aggiorna-km', 'AziendaController@aggiornaKmMezzo')->name('aggiorna_km_mezzo');
Route::post('/azienda/mezzo/{id}/sostituisci-gomma', 'AziendaController@sostituisciGomma')->name('sostituisci_gomma');
Route::post('/azienda/mezzo/{id}/impostazioni', 'AziendaController@impostazioniMezzo')->name('impostazioni_mezzo');
Route::post('/azienda/mezzo/{id}/registra-tagliando', 'AziendaController@registraTagliando');
Route::post('/azienda/mezzo/{id}/aggiorna-km', 'AziendaController@aggiornaKm');
Route::post('/azienda/mezzo/{id}/manutenzione', 'AziendaController@aggiungiManutenzione');
Route::post('/azienda/mezzo/{id}/manutenzione/modifica', 'AziendaController@modificaManutenzione');
Route::post('/azienda/mezzo/{id}/manutenzione/elimina', 'AziendaController@eliminaManutenzione');
Route::post('/azienda/mezzo/{id}/rifornimento', 'AziendaController@aggiungiRifornimento');
Route::post('/azienda/mezzo/{id}/rifornimento/elimina', 'AziendaController@eliminaRifornimento');
Route::post('/azienda/mezzo/{id}/rifornimento/upload-scontrino', 'AziendaController@uploadScontrino');

// Azienda — Utenti / responsabili
Route::post('/azienda/utente/update-vista-operaio', 'AziendaController@updateVistaOperaio')->name('update_vista_operaio');
Route::post('/azienda/update-stato', 'AziendaController@updateStato')->name('cantiere.updateStato');
Route::post('/azienda/utente/update-responsabile', 'AziendaController@updateResponsabile')->name('update_responsabile');
Route::get('/azienda/utente/get-permessi/{id}', 'AziendaController@getPermessiUtente')->name('get_permessi_utente');
Route::post('/azienda/utente/aggiorna-permessi', 'AziendaController@aggiornaPermessi')->name('aggiorna_permessi');
Route::any('azienda/responsabili', 'AziendaController@responsabili');
Route::get('/azienda/cantiere/{id}/responsabili', 'AziendaController@getResponsabiliCantiere')->name('cantiere_responsabili');
Route::post('/azienda/controlla-conflitti-dipendenti', 'AziendaController@controllaConflittiDipendenti');

// Azienda — Magazzino
Route::any('azienda/materiali', 'AziendaController@materiali');
Route::any('azienda/strumenti', 'AziendaController@strumenti');
Route::post('azienda/gestisci', 'AziendaController@gestisciArticolo')->name('magazzino.gestisci');
Route::post('/azienda/magazzino/impegna', 'AziendaController@impegnaArticolo')->name('magazzino.impegna');
Route::get('/azienda/magazzino/rimuovi', 'AziendaController@rimuoviImpegno')->name('magazzino.rimuovi');
Route::any('/magazzino/movimento', 'AziendaController@movimento')->name('magazzino.movimento');
Route::get('/azienda/movimenti', 'AziendaController@movimenti')->name('magazzino.movimenti');
Route::get('/azienda/magazzino/scarica', 'AziendaController@scaricaArticolo')->name('magazzino.scarica');
Route::get('/azienda/recuperaQuantita', 'AziendaController@recuperaArticolo');
Route::post('/azienda/aggiorna-soglia', 'AziendaController@aggiornaSoglia')->name('magazzino.aggiorna-soglia');
Route::post('/azienda/aggiorna-soglie-massive', 'AziendaController@aggiornaSoglieMassive')->name('magazzino.aggiorna-soglie-massive');

// Azienda — Cantieri / Pagamenti / Allegati
Route::any('/azienda/vista_cantiere', 'AziendaController@vistaOperaio');
Route::any('/azienda/cantieri/gestisci-pagamento', 'AziendaController@gestisciPagamento')->name('cantieri.gestisciPagamento');
Route::any('/cantiere/upload-allegato', 'AziendaController@uploadAllegato')->name('cantiere.upload.allegato');
Route::any('/cantiere/salva-foto', 'AziendaController@salvaFoto')->name('cantiere.salva.foto');
Route::any('/cantiere/elimina-allegato', 'AziendaController@eliminaAllegato')->name('cantiere.elimina.allegato');
Route::get('/azienda/cantiere/{id}/allegati', 'AziendaController@getAllegatiCantiere')->name('cantiere.get.allegati');
Route::post('/azienda/cantieri/{id}/assegna-dipendenti', 'AziendaController@assegnaDipendenti')->name('cantieri.assegnaDipendenti');
Route::delete('/azienda/cantieri/rimuovi-dipendente/{id}', 'AziendaController@rimuoviDipendente')->name('cantieri.rimuoviDipendente');

// Azienda — Dipendenti / Calendario
Route::get('/azienda/dipendenti/visualizza', 'AziendaController@visualizzaDipendenti')->name('azienda.dipendenti.visualizza');
Route::post('/azienda/salva-assegnazione-giorni', 'AziendaController@salvaAssegnazioneGiorni')->name('salva.assegnazione.giorni');
Route::get('/azienda/cantiere/{id}/assegnazioni', 'AziendaController@getAssegnazioniCantiere')->name('get.assegnazioni.cantiere');
Route::get('/azienda/cantiere/{cantiereId}/dipendente/{dipendenteId}/giorni', 'AziendaController@getGiorniDipendente')->name('get.giorni.dipendente');
Route::post('/azienda/rimuovi-assegnazione-dipendente', 'AziendaController@rimuoviAssegnazioneDipendente')->name('rimuovi.assegnazione.dipendente');

// Azienda — Report responsabili
Route::any('/azienda/responsabili/report/pdf', 'AziendaController@reportResponsabiliPDF');
Route::any('/azienda/responsabili/report/pdf/attivi', 'AziendaController@reportResponsabiliPDFAttivi');
Route::any('/azienda/responsabili/report/pdf/singolo/{id}', 'AziendaController@reportResponsabiliPDFSingolo');
Route::any('/azienda/responsabili/report/excel', 'AziendaController@reportResponsabiliExcel');
Route::any('/azienda/responsabili/report/excel/attivi', 'AziendaController@reportResponsabiliExcelAttivi');
Route::any('/azienda/responsabili/report/excel/singolo/{id}', 'AziendaController@reportResponsabiliExcelSingolo');
Route::any('/azienda/profilo', 'AziendaController@profilo');

// Azienda — Flotta in Cloud
Route::get('azienda/flotta', 'FlottaController@index')->name('azienda.flotta');
Route::get('azienda/flotta/live-positions', 'FlottaController@livePositions')->name('azienda.flotta.live');
Route::get('azienda/flotta/test-connection', 'FlottaController@testConnection')->name('azienda.flotta.test');
Route::get('azienda/flotta/debug', 'FlottaController@debugApi')->name('azienda.flotta.debug');
Route::get('azienda/flotta/dispositivi', 'FlottaController@getDispositivi')->name('azienda.flotta.dispositivi');
Route::get('azienda/flotta/storico/{veicoloId}', 'FlottaController@storicoVeicolo')->name('azienda.flotta.storico');
Route::get('azienda/flotta/esporta', 'FlottaController@esportaDati')->name('azienda.flotta.esporta');
Route::get('azienda/sincronizza-mezzi', 'AziendaController@sincronizzaMezzi');
Route::get('azienda/aggiorna-km', 'AziendaController@aggiornaKmMezzi');
Route::get('/migra-dipendenti', 'AziendaController@migraDipendenti');

// API JSON cantieri (cross-origin)
Route::post('/cantiere/crea', 'ApiController@creaCantiere');
Route::options('/cantiere/crea', 'ApiController@creaCantiere');

// TMS — Ordini trasporto
Route::any('/azienda/ordini-trasporto', 'TrasportiController@ordiniTrasporto');
Route::get('/azienda/ordine-trasporto/{id}', 'TrasportiController@dettaglioOrdine');
Route::post('/azienda/ordine-trasporto/cambia-stato', 'TrasportiController@cambiaStatoOrdine');
Route::get('/azienda/ordine/{id}/tappe', 'TrasportiController@getTappe');
Route::any('/azienda/clienti', 'TrasportiController@clienti');
Route::any('/azienda/tariffari', 'TrasportiController@tariffari');
Route::post('/azienda/calcola-costo-trasporto', 'TrasportiController@calcolaCostoTrasporto');
Route::any('/azienda/documenti-trasporto', 'TrasportiController@documenti');
Route::get('/azienda/documenti-trasporto/{idOrdine}', 'TrasportiController@documenti');
Route::get('/azienda/get-tariffa-cliente/{id_cliente}', 'TrasportiController@getTariffaCliente');
Route::get('/azienda/prossimo-numero-ddt', 'TrasportiController@prossimoNumeroDdt');
Route::post('/azienda/documenti-trasporto/segna-consegnato', 'TrasportiController@segnaConsegnato');
Route::get('/azienda/get-dati-ordine/{id}', 'TrasportiController@getDatiOrdine');
Route::get('/azienda/centro-operativo', 'TrasportiController@centroOperativo');
Route::get('/azienda/centro-operativo/live', 'TrasportiController@centroOperativoLive');
Route::any('/azienda/pedane', 'TrasportiController@pedane');
Route::post('/azienda/ordine/calcola-km', 'TrasportiController@calcolaKm');

// Analytics
Route::get('/azienda/analytics/dashboard', 'AnalyticsController@dashboardKPI');
Route::get('/azienda/analytics/predittivi', 'AnalyticsController@reportPredittivi');
Route::get('/azienda/analytics/export/excel', 'AnalyticsController@exportExcel');
Route::get('/azienda/analytics/export/pdf', 'AnalyticsController@exportPDF');
Route::get('/azienda/analytics/predittivi/export/excel', 'AnalyticsController@exportPredittiviExcel');
Route::get('/azienda/analytics/predittivi/export/pdf', 'AnalyticsController@exportPredittiviPDF');

// Reports TMS
Route::get('/azienda/reports-tms', 'AziendaController@reportTMS')->name('reports.tms');
Route::post('/azienda/report-tms/genera', 'AziendaController@generaReportTMS')->name('reports.tms.genera');
Route::post('/azienda/report-tms/operativo', 'AziendaController@generaReportOperativo')->name('reports.tms.operativo');
Route::post('/azienda/report-tms/finanziario', 'AziendaController@generaReportFinanziario')->name('reports.tms.finanziario');
Route::post('/azienda/report-tms/performance', 'AziendaController@generaReportPerformance')->name('reports.tms.performance');
Route::get('/azienda/report-tms/export/{tipo}/{formato}', 'AziendaController@exportReport')->name('reports.tms.export');
Route::post('/azienda/api/reports-tms', 'AziendaController@apiReportsTms');
Route::post('/azienda/api/reports-tms/export', 'AziendaController@apiReportsTmsExport');

// Tracking dashboard ufficio
Route::get('/azienda/tracking', 'TrackingDashboardController@index');
Route::get('/azienda/tracking/live-positions', 'TrackingDashboardController@livePositions');
Route::get('/azienda/tracking/report-km', 'TrackingDashboardController@reportKm');
Route::get('/azienda/tracking/dispositivi', 'TrackingDashboardController@listaDispositivi');
Route::post('/azienda/tracking/dispositivi/crea', 'TrackingDashboardController@creaDispositivo');
Route::post('/azienda/tracking/dispositivi/{id}/associa-mezzo', 'TrackingDashboardController@associaMezzo');
Route::delete('/azienda/tracking/dispositivi/{id}', 'TrackingDashboardController@eliminaDispositivo');

// Tracking API (chiamate dal tablet, esposte anche su /api in api.php)
Route::post('/api/tracking/position', 'TrackingApiController@receivePosition');
Route::post('/api/tracking/setup', 'TrackingApiController@setupIniziale');
Route::post('/api/tracking/status', 'TrackingApiController@checkStatus');
Route::post('/api/tracking/batch', 'TrackingApiController@receiveBatch');

// Planning autisti
Route::get('/azienda/planning-autisti', 'PlanningController@index');
Route::post('/azienda/planning-autisti/salva-giorno', 'PlanningController@salvaGiorno');
Route::post('/azienda/planning-autisti/salva-regole', 'PlanningController@salvaRegole');
Route::get('/azienda/planning-autisti/storico/{id}', 'PlanningController@storicoAutista');

// === AREA AUTISTA ===
Route::prefix('autista')->group(function () {
    Route::get('/dashboard', 'AutistaController@dashboard');
    Route::get('/tracking', 'AutistaController@tracking');
    Route::get('/consegne', 'AutistaController@consegne');
    Route::get('/debug-consegne', 'AutistaController@debugConsegne');
    Route::post('/consegna/{id}/inizia', 'AutistaController@iniziaConsegna');
    Route::post('/consegna/{id}/completa', 'AutistaController@completaConsegna');
    Route::post('/consegna/{id}/annulla', 'AutistaController@annullaConsegna');
    Route::post('/consegna/{id}/rinvia', 'AutistaController@rinviaConsegna');
    Route::get('/navigatore', 'AutistaController@navigatore');
    Route::get('/navigatore/{id}', 'AutistaController@navigatoreSingolo');
    Route::get('/profilo', 'AutistaController@profilo');
    Route::get('/api/stats', 'AutistaController@apiStats');
    Route::get('/api/storico', 'AutistaController@apiStorico');
    Route::get('/notifiche', 'AutistaController@notifiche');
    Route::post('/notifiche/segna-tutte-lette', 'AutistaController@segnaTutteLette');
    Route::get('/notifiche/nuove', 'AutistaController@notificheNuove');
    Route::get('/notifiche/lista', 'AutistaController@notificheLista');
    Route::post('/notifiche/{id}/letta', 'AutistaController@notificheSegnaLetta');
    Route::post('/consegna/{id}/upload-foto', 'AutistaController@uploadFotoConsegna');
    Route::delete('/consegna/foto/{idFoto}', 'AutistaController@eliminaFotoConsegna');
    Route::get('/storico', 'AutistaController@storicoOrdini');
    Route::post('/consegna/{id}/completa-avanzato', 'AutistaController@completaConsegnaAvanzato');
    Route::get('/percorso-consegne', 'AutistaController@percorsoConsegne');
    Route::post('/consegna-ordine/{id}/completa', 'AutistaController@completaConsegnaOrdine');
    Route::post('/salva-ordine-percorso', 'AutistaController@salvaOrdinePercorso');
    Route::get('/piano-giornaliero', 'AutistaController@pianoGiornaliero');
});

// Autista — Rifornimenti / DDT / Guasti / Email DDT pubblico
Route::get('/autista/rifornimenti', [AutistaController::class, 'rifornimenti']);
Route::post('/autista/rifornimenti/salva', [AutistaController::class, 'salvaRifornimento']);
Route::get('/autista/rifornimenti/{id}', [AutistaController::class, 'dettaglioRifornimento']);
Route::post('/autista/rifornimenti/{id}/elimina', [AutistaController::class, 'eliminaRifornimento']);

Route::get('/autista/ordine/{id}/completa', [AutistaController::class, 'completaOrdineView']);
Route::post('/autista/ddt/salva-firma', [AutistaController::class, 'salvaFirmaDDTAutista']);
Route::post('/autista/ddt/rimuovi-firma', [AutistaController::class, 'rimuoviFirmaDDTAutista']);
Route::post('/autista/ordine/completa', [AutistaController::class, 'completaOrdine']);
Route::get('/autista/ordine/{id}/completato', [AutistaController::class, 'ordineCompletato']);
Route::get('/autista/ddt/{id}/pdf', [AutistaController::class, 'ddtPdf']);
Route::post('/autista/ddt/invia-email', [AutistaController::class, 'inviaDdtEmail']);
Route::get('/ddt/download/{token}', [AutistaController::class, 'ddtPdfPubblico']);

// Segnalazione guasti
Route::get('/autista/segnala-guasto', 'AutistaController@segnalaGuastoForm');
Route::post('/autista/segnala-guasto', 'AutistaController@segnalaGuastoSalva');
Route::get('/autista/segnala-guasto', 'AutistaController@segnalaGuasto');
```

---

## 7. Routes — `routes/api.php`

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrackingApiController;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('tracking')->group(function () {
    Route::post('/status', 'TrackingApiController@checkStatus');
    Route::post('/setup', 'TrackingApiController@setupIniziale');
    Route::post('/position', 'TrackingApiController@receivePosition');
    Route::post('/batch', 'TrackingApiController@receiveBatch');
});
```

> Le 4 route `/api/tracking/*` esistono **anche** in `web.php` (replica originale) perché il tablet le punta storicamente lì. Lascia entrambe.

---

## 8. Authentication flow (replica identica)

### Pattern

1. **Login** (`AdminController@login`, route `admin/login`):
   - `DB::select('SELECT * FROM utenti WHERE email = "..." AND password = "..."')` — **password in chiaro**, SQL concatenato con `htmlentities` come unica protezione.
   - On match: `session(['utente' => $utente]); session()->save();` — `$utente` è uno **stdClass** (riga DB), non un Eloquent.
   - Routing post-login:
     - se esiste `dispositivi_tracking` con `id_utente = $utente->id` e `is_active=1` → `Redirect::to('autista/dashboard')`.
     - else if `$utente->admin_azienda IN (1,2)` → `Redirect::to('azienda/index')`.
     - else → `Redirect::to('admin/index')`.

2. **Guard per controller** — uno dei due pattern:
   - **Per metodo**: prima riga di ogni action `$this->is_loggato();` dove
     ```php
     public function is_loggato() {
         if (!session()->has('utente')) {
             return Redirect::to('admin/login')->send();
         }
     }
     ```
     (presente in: `AdminController`, `AziendaController`, `TrasportiController`, `PlanningController`, `AnalyticsController`, `AjaxController`, `StampaController`).
   - **In costruttore via middleware closure** (in `AutistaController` e `FlottaController`):
     ```php
     public function __construct() {
         $this->middleware(function ($request, $next) {
             if (!session()->has('utente')) return redirect('/admin/login');
             return $next($request);
         });
     }
     ```

3. **Logout**: `session()->flush(); return Redirect::to('admin/login');` (sia `AdminController@logout` che `AziendaController@logout`).

4. **"Effettua login" come altro utente** (`AdminController@effettua_login`): usato dal super-admin per impersonare un utente azienda. Setta `session('utente')` con la riga del nuovo utente e mette `torna_super_admin = $idAdminCorrente` come stdClass property; un secondo branch `torna_super_admin` ripristina la sessione del super-admin originale.

5. **CSRF**: `App\Http\Middleware\VerifyCsrfToken::class` è **commentato** nel gruppo `web` di `App\Http\Kernel`. Tutti i form post non includono `@csrf` e funzionano. Mantieni questa scelta nel nuovo progetto.

### `App\Http\Kernel.php`

```php
<?php
namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middleware = [
        \App\Http\Middleware\TrustProxies::class,
        \Fruitcake\Cors\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            // \App\Http\Middleware\VerifyCsrfToken::class,   // INTENZIONALMENTE OFF
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
        'api' => [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ];
}
```

---

## 9. Controller — inventario completo

> **Importante**: i controller non vanno riscritti, vanno **copiati interi** dal progetto sorgente al nuovo. Questa sezione serve a Claude Code per capire la responsabilità di ognuno e i punti di accoppiamento. I percorsi originali sono `app/Http/Controllers/*.php`.

### `HomeController.php` (24 righe)
- `index()` → `Redirect::to('admin/login')`. Unico ingresso.

### `AdminController.php` (354 righe) — area super-admin tenant
| Metodo | Route | Cosa fa |
|---|---|---|
| `login(Request)` | `admin/login` | Login plaintext, redirect dinamico per autista/azienda/admin (vedi §8). |
| `index()` | `admin/index` | Dashboard super-admin. |
| `effettua_login(Request)` | `admin/effettua_login` | Impersonate user / torna a super-admin. |
| `aziende(Request)` | `admin/aziende` | CRUD aziende + creazione utente admin azienda. Form flags: `aggiungi`, `modifica`, `elimina`. Campi `partita_iva`, `ragione_sociale`, `comune`, `regione`, `cap`, `pec`, `codice_sdi`, `regime_fiscale`, `nazione`, ecc. |
| `utenti(Request)` | `admin/utenti` | CRUD utenti globali. |
| `logout()` | `admin/logout` | flush session. |
| `is_loggato()` | — | guard. |
| `cercaPiva(Request)` | GET `admin/cerca-piva` | Proxy verso `https://company.openapi.com/IT-advanced/{piva}` con Bearer token (vedi §12) per popolare automaticamente i campi azienda. |

### `AziendaController.php` (5836 righe) — controller monolitico tenant
**Pattern generale**: ogni metodo principale gestisce sia GET (rendering view) che POST (CRUD interno via flags `crea_*/modifica_*/elimina_*` come hidden form input). Tutti i query sono manualmente filtrati per `id_azienda`.

Gruppi funzionali:

**Ordini di trasporto (alias non-azienda + smistamento legacy)**
- `creaOrdine($dati,$utente)` private — `INSERT ordini_trasporto` con `numero_ordine` generato.
- `modificaOrdine($dati,$utente)` private.
- `eliminaOrdine($dati,$utente)` private.
- `cambiaStatoOrdine(Request)` — POST endpoint AJAX/form, aggiorna `stato`.
- `generaNumeroOrdine($idAzienda)` private — formato sequenziale per anno.
- `ordiniTrasporto(Request)` (riga 5166) — variante alias di `TrasportiController@ordiniTrasporto`, supporta filtri per stato.
- `dettaglioOrdine($id)` — view dettaglio.

**Clienti TMS**
- `clienti(Request)` — CRUD `clienti` (flags `aggiungi/modifica/elimina_cliente`).

**Utenti azienda / permessi / ruoli**
- `utenti()` — lista + flags CRUD.
- `getPermessiUtente($id)` JSON — restituisce flag boolean per UI checkbox.
- `aggiornaPermessi(Request)` — `gestione_cantieri/mezzi/magazzino/utenti/trasporti/visualizza_costi/solo_lettura`.
- `hasPermesso($permesso)` helper di sessione.
- `creaUtente/modificaUtente/eliminaUtente($data)` private.
- `updateResponsabile(Request)` — toggle `is_responsabile`.
- `responsabili()` — lista + percentuali.
- `getResponsabiliCantiere($cantiereId)` JSON.
- `ruoli(Request)` — CRUD ruoli + tabella pivot `utenti_ruoli`.

**Cantieri**
- `cantieri()` — lista cantieri filtrati per azienda; calcola `costo_effettivo` aggregato.
- `dettaglioCantiere($id)` — vista completa cantiere con allegati, dipendenti, attività, pagamenti, impegni magazzino.
- `saveCantiere(Request)` — upsert cantiere.
- `updateStato(Request)` — cambio stato cantiere.
- `assegnaDipendenti(Request, $cantiereId)` — assegnazione/movimento dipendenti per giorni.
- `rimuoviDipendente($id)` DELETE.
- `salvaAssegnazioneGiorni(Request)` — POST batch per `cantieri_operai_giorni` (UNIQUE su `id_cantiere+id_dipendente+data_lavoro`).
- `getAssegnazioniCantiere($cantiereId)` JSON.
- `getGiorniDipendente($cantiereId, $dipendenteId)` JSON.
- `rimuoviAssegnazioneDipendente(Request)` POST.
- `getAttivitaDipendente/getDipendentiAttivita/salvaDipendentiAttivita/aggiornaDipendenti` — gestione `cantieri_attivita_dipendenti`.
- `gestisciPagamento(Request)` + privati `aggiungiPagamento/modificaPagamento/eliminaPagamento/segnaPagato` — su `cantieri_pagamenti`.
- `uploadAllegato/eliminaAllegato/getAllegatiCantiere/salvaFoto` — upload su `public/allegati_cantieri/{azienda_slug}/{cantiere_slug}/`, `Str::random(20)`, salvati in `cantieri_allegati`.

**Costi cantiere (privati helper)**
- `calcolaCostoEffettivoCantiere($cantiereId)` — somma manodopera + impegni magazzino.
- `calcolaCostoEffettivoCantiereSemplice($cantiereId)`.
- `aggiornaCostoEffettivoCantiere($cantiereId)`.
- `getDettaglioCostiCantiere($cantiereId, $idAzienda)`.
- `verificaConflittiDipendente($dipendenteId, $dataInizio, $dataFine, $cantiereEscluso = null)`.

**Magazzino**
- `materiali()` — lista articoli filtrati per azienda; flag CRUD.
- `strumenti()` — variante per articoli `tipologia=strumento`.
- `gestisciArticolo(Request)` — flags `aggiungi_articolo/modifica_articolo/elimina_articolo`.
- `movimento(Request)` + `movimenti(Request)` — gestione `mgmov` (carico/scarico/reso).
- `scaricaArticolo()` — POST scarico con causale.
- `recuperaArticolo(Request)` — JSON ricerca articoli.
- `impegnaArticolo(Request)` POST — inserisce in `impegni_magazzino`, decrementa `quantita_impegnata` di `articoli`.
- `rimuoviImpegno(Request)` GET.
- `aggiornaSoglia(Request)` / `aggiornaSoglieMassive(Request)`.
- `inviaNotificaOneSignal($messaggio)` private — tentativo push notif (vedi §12).

**Mezzi / Flotta**
- `anagraficaMezzi(Request)` — lista + CRUD mezzi.
- `dettaglioMezzo(Request, $id)` — vista con storico manutenzioni/rifornimenti/gomme/km.
- `modificaStatoMezzo(Request, $id)`.
- `aggiornaKmMezzo(Request, $id)` / `aggiornaKm(Request, $id)`.
- `sostituisciGomma(Request, $id)` — su `mezzi_gomme` + crea anche record `mezzi_manutenzioni` con `riferimento_gomma_id`.
- `traduciPosizione($posizione)` private — enum→stringa leggibile.
- `impostazioniMezzo(Request, $id)` — `km_warning/km_danger`.
- `calcolaStatiGomme($mezzo, $sostituzioni_gomme)` private — aggrega per posizione.
- `registraTagliando(Request, $id)`.
- `aggiungiManutenzione/modificaManutenzione/eliminaManutenzione(Request, $id)`.
- `aggiungiRifornimento(Request, $id)` — calcola `consumo_calcolato` km/l usando rifornimento precedente con `pieno=1`.
- `eliminaRifornimento(Request, $id)`.
- `uploadScontrino(Request, $id)` — salva foto in `public/allegati_scontrini/`.
- `sincronizzaMezzi()` — chiamata GET FlottaInCloud → upsert `mezzi` per riga remoto (vedi §12).
- `aggiornaKmMezzi()` — refresh km da FlottaInCloud.
- `chiamataFlottaAPI($endpoint)` private — wrapper.
- `migraDipendenti()` — script one-shot di migrazione dati storici.
- `determinaTipoMezzo($nome)` private.

**Vista operaio / Timesheet**
- `vistaOperaio(Request)` — UI dipendente: clock-in/out su `cantieri_attivita_operaio` + `presenze`.
- `updateVistaOperaio(Request)` — toggle `utenti.vista_operaio`.

**Index / Calendario**
- `index(Request)` (riga 478) — dashboard azienda con KPI, calendario eventi.
- `getEventiCalendario($utente)` private.
- `getColorePerCantiere($cantiere)` private.
- `getTipoLavoro($descrizione)` private.
- `getSpecialRows($giorni)` private.

**Profilo / logout / report responsabili**
- `profilo(Request)` — UI profilo utente con upload immagine, change password.
- `logout()`.
- `is_loggato()`.
- `reportResponsabiliPDF/Excel(...)` (e `*Attivi`, `*Singolo($id)`) — output PDF mPDF / Excel Maatwebsite.
- `generaHTMLReportResponsabili($responsabili, $tipo, $utente)` private — HTML per mPDF.
- `visualizzaDipendenti(Request, $isDashboard=false)` — vista riepilogo.

**Reports TMS**
- `reportTMS()` — view shell.
- `generaReportTMS/Operativo/Finanziario/Performance/Compliance/Predittivo(Request)`.
- `apiReportsTms(Request)` JSON / `apiReportsTmsExport(Request)`.
- Helpers privati: `getOrdiniGiornalieri`, `getUtilizzoMezzi`, `getPerformanceAutisti`, `getStatoSpedizioni`, `getRotteTop`, `getRicaviMensili`, `getCostiCarburante`, `getMarginalitaClienti`, `getROIMezzi`, `getBudgetVsActual`, `getOnTimeDelivery`, `getEfficienzaRotte`, `getCustomerSatisfaction`, `getTempoMedioConsegna`, `getKmVuotoVsCarico`, `getTempiGuida`, `getScadenzeDocumenti`, `getTrasportiADR`, `getViolazioni`, `getPrevisioniDomanda`, `getManutenzioniPredittive`, `getOttimizzazioneFlotta`, `getTrendCosti`.

**Tipo file / utility**
- `determinaTipoFile($estensione)` private — restituisce 'image'/'document'/'pdf'.
- `formatBytes($size, $precision=2)` private.

### `TrasportiController.php` (2190 righe) — TMS
| Metodo | Route | Cosa fa |
|---|---|---|
| `is_loggato()` | — | guard. |
| `ordiniTrasporto(Request)` | `/azienda/ordini-trasporto` GET/POST | Lista filtrabile + flags `crea_ordine/modifica_ordine/elimina_ordine`. JOIN su `clienti`, `mezzi`, `utenti` (autista), `documenti_trasporto` (per DDT). Su POST verifica blocco con `PlanningController::isAutistaBloccato($idAutista, $dataRitiro, $idAzienda)`. |
| `creaOrdine/modificaOrdine/eliminaOrdine` | private | + tappe (`ordine_tappe`), pedane, calcolo importo automatico da tariffario. |
| `calcolaImportoDaTariffa($tariffa, $dati)` | private | Applica `tipo_calcolo` (fisso/km/peso/volume/tempo), maggiorazioni e sconto. |
| `cambiaStatoOrdine(Request)` | POST | Aggiorna `ordini_trasporto.stato` + crea notifica autista via `AutistaController::creaNotifica(...)`. |
| `dettaglioOrdine($id)` | GET | View con tappe, DDT, mezzo, cliente. |
| `stampaDDT($idOrdine)` | GET `azienda/ddt/{id}/pdf` | mPDF dell'HTML generato. |
| `generaHTMLDDT($ddt, $ordine, $azienda)` | private | Template HTML DDT (firme base64, indirizzi, merce). |
| `generaDDT($idOrdine)` | POST | Crea record `documenti_trasporto` se non esiste; `tipo_documento='ddt'`. |
| `salvaFirmaDDT/rimuoviFirmaDDT(Request)` | POST | Salva base64 in `firma_mittente/vettore/destinatario`. |
| `getTariffaCliente($idCliente)` | JSON | Restituisce tariffa attiva oggi per il cliente. |
| `prossimoNumeroDdt()` | JSON | Calcola prossimo `numero_documento` `DDT-{YYYY}-{nnnn}`. |
| `getTappe($id)` | JSON | Tappe ordinate per `numero_tappa`. |
| `getDatiOrdine($id)` | JSON | Dati completi per UI modale. |
| `clienti(Request)` | `/azienda/clienti` | CRUD su `clienti`. |
| `tariffari(Request)` | `/azienda/tariffari` | CRUD su `tariffari_clienti`. |
| `documenti(Request, $idOrdine=null)` | `/azienda/documenti-trasporto[/{id}]` | Lista documenti, filtro per ordine. |
| `segnaConsegnato(Request)` | POST | Setta `data_consegna`, `consegnato_a`, eventualmente firma. |
| `centroOperativo()` | GET | Dashboard live per operatore: ordini in corso + posizioni live mezzi. |
| `centroOperativoLive()` | JSON | Polling per dashboard. |
| `pedane(Request)` | `/azienda/pedane` | CRUD `pedane_movimenti` per cliente. |
| `calcolaCostoTrasporto(Request)` | POST JSON | Calcola costo da km, peso, tempo, tipo mezzo, tariffa. |
| `calcolaDistanzaGoogleMaps($p, $a)` | private | Chiama Google Distance Matrix API. |
| `calcoloApprossimativo($p, $a)` | private | Fallback con tabella distanze città italiane. |
| `getDistanzeCittaItaliane()` | private | Hardcoded distance table. |
| `estraiCitta($indirizzo)` | private | Regex per ricavare nome città. |
| `calcolaCostoDettagliato($distanzaKm, $tempoMinuti, $peso, $tipoMezzo, $tariffa, $urgente)` | private. |
| `getCostiStandardMezzo($tipoMezzo)` | private. |
| `formattaTempo($minuti)` | private | "h m". |
| `calcolaKm(Request)` / `calcolaKmGoogle` | POST | Restituisce JSON con `km_totali`, `ore_stimate`. |
| `generaNumeroOrdine($idAzienda)` | private. |
| `generaNumeroDocumento($tipoDocumento, $idAzienda)` | private. |

### `AutistaController.php` (2645 righe) — area mobile autista
| Metodo | Route | Cosa fa |
|---|---|---|
| `__construct()` | — | middleware closure (vedi §8). |
| `dashboard()` | `autista/dashboard` | KPI giorno: km oggi, km settimana, consegne completate. |
| `tracking()` | `autista/tracking` | View live GPS con dispositivo associato. |
| `consegne()` | `autista/consegne` | Lista ordini assegnati per oggi. |
| `debugConsegne()` | `autista/debug-consegne` | Diagnostica. |
| `autistaPuoAccedere($idOrdine, $idAutista)` | private bool | Permission check. |
| `iniziaConsegna($id)` | POST | `stato=in_corso`. |
| `completaConsegna(Request, $id)` | POST | `stato=completato`, `data_completamento`. |
| `annullaConsegna/rinviaConsegna(Request, $id)` | POST. |
| `storico(Request)` / `storicoOrdini(Request)` | GET. |
| `apiStorico(Request)` / `apiStats()` | JSON. |
| `profilo()` | GET. |
| `navigatore()` / `navigatoreSingolo($id)` | GET — vista mappa. |
| `getDispositivoUtente($utenteId)` | private. |
| `ordiniTrasporto(Request)` (riga 619) | GET/POST. |
| `cambiaStatoOrdine(Request)` | POST. |
| `dettaglioOrdine($id)` | GET. |
| `is_loggato()` | guard. |
| `completaOrdineView($id)` | GET — form di completamento avanzato. |
| `salvaFirmaDDTAutista/rimuoviFirmaDDTAutista(Request)` | POST. |
| `completaOrdine(Request)` | POST | Variante completa per ordine intero (DDT + firme + foto). |
| `ordineCompletato($id)` | GET — view post-completamento. |
| `ddtPdf($id)` | GET — mPDF privato. |
| `ddtPdfPubblico($token)` | GET — pubblico via token. |
| `generaPdfDDT($ddt, $azienda)` / `generaHTMLDDT($ddt, $ordine, $azienda)` | private. |
| `inviaDdtEmail(Request)` | POST | Genera link `/ddt/download/{token}` (token `Str::random(64)`), invia email PHPMailer con SMTP `aziende.email_smtp/password_smtp`. |
| `segnalaGuastoForm()` GET / `segnalaGuastoSalva(Request)` POST / `segnalaGuasto()` GET — su `segnalazioni_guasti` + email. |
| `inviaEmailGuasto($azienda, $emailDest, $utente, $mezzo, $dati, $fotoPath, $idGuasto)` | private — PHPMailer con SMTP azienda. |
| `rifornimenti/salvaRifornimento/dettaglioRifornimento/eliminaRifornimento` | autista versione di rifornimenti su `rifornimenti_carburante`. |
| `notifiche()` GET / `segnaNotificaLetta($id)` / `segnaTutteLette()` / `notificheNuove()` JSON / `notificheLista()` JSON / `notificheSegnaLetta($id)` POST. |
| `creaNotifica($idAutista, $idAzienda, $tipo, $titolo, $messaggio, $idOrdine)` | static — chiamato anche da `TrasportiController`. |
| `uploadFotoConsegna(Request, $id)` | POST | Salva in `public/allegati_consegne/`, registra in `foto_consegna`. |
| `eliminaFotoConsegna($idFoto)` | DELETE. |
| `completaConsegnaAvanzato(Request, $id)` | POST | Firma + foto + note + nome firmatario. |
| `percorsoConsegne(Request)` | GET — pianificazione percorso giorno. |
| `completaConsegnaOrdine($id)` | POST. |
| `salvaOrdinePercorso(Request)` | POST | Salva sequenza tappe per giorno. |
| `pianoGiornaliero(Request)` | GET. |

### `AnalyticsController.php` (604 righe)
| Metodo | Route | Cosa fa |
|---|---|---|
| `dashboardKPI(Request)` | `azienda/analytics/dashboard` | KPI: ricavi, costi, km totali, consegne, top clienti, top mezzi, trend mensili. |
| `calcolaKPIPrincipali` private — aggregazioni. |
| `calcolaCostiOperativi` (carburante + manutenzioni). |
| `getPerformanceClienti` / `getPerformanceMezzi`. |
| `getTrendMensili`. |
| `getRedditivitaRotte` (raggruppa per coppie città). |
| `estraiCitta($indirizzo)` private. |
| `getTassoCompletamento`. |
| `reportPredittivi(Request)` | `azienda/analytics/predittivi`. |
| `calcolaPrevisioniDomanda` (regressione lineare semplice su storico ordini). |
| `getForecastCarburante`. |
| `getAnalisiStagionalita` (per mese). |
| `exportExcel/exportPDF(Request)` — Maatwebsite + mPDF (template `views/azienda/analytics_export_pdf.blade.php`). |
| `exportPredittiviExcel/exportPredittiviPDF(Request)`. |

### `PlanningController.php` (449 righe)
| Metodo | Route | Cosa fa |
|---|---|---|
| `is_loggato()` | — guard. |
| `index(Request)` | `azienda/planning-autisti` | Calendario autisti per settimana/mese, basato su `planning_autisti` + `regole_lavoro`. |
| `salvaGiorno(Request)` | POST `salva-giorno` | Upsert su `planning_autisti` (UNIQUE `id_autista+data`). |
| `salvaRegole(Request)` | POST `salva-regole` | Upsert su `regole_lavoro` (UNIQUE `id_azienda`). |
| `getRegole($idAzienda)` private. |
| `calcolaGiorniConsecutivi($idAutista, $data)` private. |
| `calcolaOreSettimana($idAutista, $dataLunedi)` private. |
| `isAutistaBloccato($idAutista, $data, $idAzienda)` **public static** — chiamato da `TrasportiController` e `AutistaController` per warning all'assegnazione. |
| `storicoAutista(Request, $idAutista)` GET. |

### `FlottaController.php` (251 righe)
| Metodo | Route | Cosa fa |
|---|---|---|
| `__construct()` | middleware closure. |
| `hasFlottaInCloudEnabled()` private — check `aziende.flotta_abilitato`. |
| `getFlottaCredentials()` private — `aziende.flotta_email/flotta_token`. |
| `makeAuthenticatedRequest($endpoint)` private — wrapper Guzzle. |
| `index()` | `azienda/flotta` | View dashboard FlottaInCloud. |
| `livePositions()` | JSON | Lista veicoli + posizioni. |
| `testConnection()` JSON. |
| `calcolaStatistiche($dispositivi)` private. |

> **Endpoint base**: `https://api.flottaincloud.it/external_api/v1/`. Vedi §12.

### `TrackingDashboardController.php` (282 righe)
| Metodo | Route | Cosa fa |
|---|---|---|
| `__construct()` | middleware closure. |
| `index()` | `azienda/tracking` | Mappa con tutti i mezzi. |
| `livePositions()` | JSON | Posizioni live filtrate per azienda. |
| `reportKm(Request)` | `azienda/tracking/report-km` | Aggregati `km_giornalieri` per range. |
| `listaDispositivi()` | GET. |
| `creaDispositivo(Request)` | POST | Genera `device_token = Str::random(64)`. |
| `associaMezzo(Request, $id)` | POST. |
| `eliminaDispositivo($id)` | DELETE. |

### `TrackingApiController.php` (375 righe) — endpoint per il tablet/app autista
| Metodo | Route | Cosa fa |
|---|---|---|
| `receivePosition(Request)` | POST `/api/tracking/position` | Validate `device_token` (size:64), `lat/lng` (decimal), `speed`, `heading`, `accuracy`, `altitude`, `battery`, `timestamp`. Verifica `dispositivo.is_active=1` e `configurato=1`. Calcola distanza Haversine dall'ultima posizione (filtra salti GPS > 2km). Aggiorna `posizioni_live` (UNIQUE per dispositivo), insert in `posizioni_storico`, upsert giornaliero in `km_giornalieri`. Aggiorna `mezzi.km_accumulati_gps` e `mezzi.ultima_lat/lng/velocita/ultimo_aggiornamento_gps`. Tutto in transazione. |
| `setupIniziale(Request)` | POST `/api/tracking/setup` | Salva `mezzi.km_iniziali_contachilometri`, setta `dispositivi_tracking.configurato=1`. |
| `checkStatus(Request)` | POST `/api/tracking/status` | Heartbeat + verifica configurazione. |
| `receiveBatch(Request)` | POST `/api/tracking/batch` | Bulk insert offline-recovery di posizioni. |
| `calcolaDistanzaKm($lat1, $lon1, $lat2, $lon2)` private — formula Haversine. |

### `ApiController.php` (77 righe)
- `creaCantiere(Request)` — endpoint POST cross-origin per creazione cantieri da app esterna. CORS handled da `Fruitcake/Cors` middleware globale.

### `StampaController.php` / `AjaxController.php` / `CronController.php` — sostanzialmente vuoti o con solo `is_loggato()`. Tienili come scheletri se ne hai bisogno.

---

## 10. View Blade — inventario per area

> Tutte le view vanno **copiate intere** in `resources/views/`. Non riscriverle: l'admin theme con sidebar/header/footer è cucito sui blade comuni in ogni area.

### `resources/views/admin/`
- `index.blade.php` — dashboard super-admin.
- `aziende.blade.php` — CRUD aziende (form con `aggiungi/modifica/elimina`, lookup P.IVA via OpenAPI).
- `moduli.blade.php` — moduli aziendali (placeholder).
- `utenti.blade.php` — utenti globali.
- `common/header.blade.php`, `sidebar.blade.php`, `footer.blade.php`, `riepilogo_documenti.blade.php` — partials.

### `resources/views/azienda/`
- `index.blade.php`, `index_special.blade.php` — dashboard tenant.
- `cantieri.blade.php`, `dettaglio_cantiere.blade.php` — gestione cantieri (allegati, dipendenti, attività, pagamenti, impegni).
- `anagrafica_mezzi.blade.php`, `dettaglio_mezzo.blade.php` — flotta + manutenzioni + gomme + rifornimenti.
- `materiali.blade.php`, `strumenti.blade.php`, `mgmov.blade.php` — magazzino.
- `clienti.blade.php`, `tariffari.blade.php` — TMS clienti.
- `ordini_trasporto.blade.php`, `dettaglio_ordine_trasporto.blade.php` — ordini TMS con tappe e DDT.
- `documenti_trasporto.blade.php` — DDT/CMR/fatture/bolle.
- `centro_operativo.blade.php` — dashboard live operatore.
- `pedane.blade.php` — gestione pedane.
- `flotta_tracking.blade.php` — FlottaInCloud.
- `tracking_dashboard.blade.php`, `tracking_dispositivi.blade.php`, `tracking_report_km.blade.php` — tracking proprietario.
- `planning_autisti.blade.php`, `storico_autista.blade.php` — planning.
- `analytics_dashboard.blade.php`, `analytics_predittivi.blade.php`, `analytics_export_pdf.blade.php`, `analytics_predittivi_export_pdf.blade.php` — analytics.
- `responsabili.blade.php`, `dipendenti_visualizza.blade.php`, `vista_operaio.blade.php` — risorse umane.
- `ruoli.blade.php`, `utenti.blade.php`, `profilo.blade.php` — gestione utenti.
- `report_tms.blade.php`, `reports_tms.blade.php` — reportistica TMS.
- `common/header.blade.php`, `sidebar.blade.php`, `footer.blade.php` — partials con logo `aziende.immagine`.

### `resources/views/autista/`
- `dashboard.blade.php`, `tracking.blade.php`, `consegne.blade.php`, `navigatore.blade.php`, `notifiche.blade.php`.
- `completa_ordine.blade.php`, `ordine_completato.blade.php`, `percorso_consegne.blade.php`, `piano_giornaliero.blade.php`.
- `profilo.blade.php`, `rifornimenti.blade.php`, `segnala_guasto.blade.php`, `storico.blade.php`.
- `common/layout.blade.php` — layout mobile-first dedicato.

### `resources/views/default/`
**Nota: queste view sono in larga parte ERP legacy (bandi, preventivi, fatture, contratti, fornitori, agenti, leads, formazione_40_*, commesse, articoli/distinta_base, ODL, gestione magazzini multipli) NON cablate dalle route attuali.** Copiale tutte ma sappi che molte non sono raggiungibili. Le uniche **attivamente usate** sono:
- `login.blade.php` — form di login (renderizzato da `AdminController@login`).

Le altre (~70 file) sono codice morto ma vanno copiate per non rompere `View::make` chiamate sparse: bandi, agenti, articoli, aziende, canoni, carico, cashflow, clienti, commesse_*, contratti, crea_documento, crea_ordine, dettaglio_*, dipendenti, distinta_base, documenti_di_trasporto, evadi_documento, fasi_di_lavorazione, fatture, formazione_40*, fornitori, gestione_documenti, gestione_magazzini, index, inventario, leads, magazzino_prodotto_finito, mg, modifica_*, moduli, odl, ordini, preferenze, preventivi, prodotti_finiti, progetti, riepilogo, riepilogo_documenti, scarico, task*, trasferimento_mg, utenti, utentiAdmin, ajax/.

### `resources/views/stampa/`
- `analytics_export_pdf.blade.php`, `analytics_predettivi_export_pdf.blade.php` — template mPDF.
- `corso.blade.php`, `report_progetti.blade.php` — template legacy.

---

## 11. Imports / Exports (Maatwebsite)

Copia interi i file in `app/Imports/` e `app/Exports/`.

### Imports — Excel → DB
- `ArticoliImport.php` — importa righe in `articoli` (id_azienda da sessione).
- `BOMImport.php` — Bill of Materials (legacy).
- `BPImport.php` — Business Partners (legacy).
- `MagazzinoImport.php` — quantità in `articoli`.
- `StoricoImport.php` — storico movimenti.
- `VenditeImport.php` — vendite (legacy).
- `TariffeImport.php` — citato da controller ma file non presente nel sorgente: aggiungilo solo se serve.

### Exports — DB → Excel
- `MassiveExport.php`, `MassiveViewExport.php`, `MassiveViewExport2.php`, `MassiveViewExportGTS.php` — export aggregati admin.
- `SearchResultExport.php` — export risultati di ricerca.
- `PreventivoExport.php` — preventivi (legacy).
- `DashboardBIExport.php` — export analytics.
- `PredittiviExport.php` — export report predittivi.

---

## 12. Integrazioni esterne (`.env` vars)

Aggiungi al `.env` le variabili sotto e leggi con `env('NOME_VAR')`. **Le chiavi originali NON sono incluse** (scelta 5a) — vanno richieste/ricreate.

```dotenv
# === Google Maps (Distance Matrix API) ===
# Usata in TrasportiController::calcolaDistanzaGoogleMaps() e calcolaKm()
# Endpoint: https://maps.googleapis.com/maps/api/distancematrix/json
GOOGLE_MAPS_API_KEY=

# === FlottaInCloud (GPS fleet provider per-tenant) ===
# Le credenziali sono per-azienda nelle colonne aziende.flotta_email / flotta_token / flotta_abilitato.
# Non servono variabili globali nel .env, ma l'endpoint base è hardcoded:
FLOTTA_API_BASE=https://api.flottaincloud.it/external_api/v1/
# Auth: header Authorization: Token token="{flotta_token}"

# === OpenAPI Italia — lookup P.IVA ===
# Usato in AdminController::cercaPiva() per popolare ragione sociale, indirizzo, ATECO da P.IVA.
# Endpoint: https://company.openapi.com/IT-advanced/{piva}
# Auth: Authorization: Bearer {token}
OPENAPI_PIVA_TOKEN=

# === SMTP per invio DDT/notifiche/guasti ===
# Lo SMTP "globale" viene da MAIL_* sopra (Mailtrap default).
# Lo SMTP "per-azienda" è in aziende.email_smtp / password_smtp e usato dalle email DDT (AutistaController::inviaDdtEmail) e guasti.

# === IMAP (webklex/laravel-imap) ===
# Per recupero email automatico se attivato (non usato nelle route attuali).
IMAP_HOST=
IMAP_USERNAME=
IMAP_PASSWORD=
IMAP_PORT=993
IMAP_ENCRYPTION=ssl

# === SFTP (phpseclib) ===
# Usato in alcuni import; le credenziali sono passate dai controller, non dal .env.
# Non è richiesta una variabile globale.

# === OneSignal push notifications ===
# Usato in AziendaController::inviaNotificaOneSignal() (solo template, da configurare).
ONESIGNAL_APP_ID=
ONESIGNAL_REST_API_KEY=

# === Mailchimp Marketing ===
# Pacchetto mailchimp/marketing presente in composer ma non utilizzato nelle route attive.
MAILCHIMP_API_KEY=
```

### Punti del codice da aggiornare per leggere dal .env (replica originale = chiavi inline)

> Nell'originale alcune chiavi sono **hardcoded** nel codice. Per il nuovo progetto, replica identica = lascia hardcoded; oppure (meglio) sostituiscile con `env('...')`. I punti:

- `AdminController::cercaPiva()` → header `Authorization: Bearer 68dc0564cc8194517e0ca866` (token OpenAPI). **Sostituiscilo con `env('OPENAPI_PIVA_TOKEN')`**.
- `TrasportiController::calcolaDistanzaGoogleMaps()` / `calcolaKm()` → API key Google in URL. **Sostituiscila con `env('GOOGLE_MAPS_API_KEY')`**.

---

## 13. File upload — directory map

Crea queste cartelle (vuote) sotto `public/`. I controller le riempiono runtime con `Str::random()` come nome file.

```
public/
  allegati_bandi/
  allegati_bandi_decreti/
  allegati_bandi_immagine_bando/
  allegati_cashflow/
  allegati_clienti/                      # struttura: {azienda_slug}/{settore_slug}/{file}
  allegati_fatture/
  allegati_preventivi/
  allegati_cantieri/                     # struttura: {azienda_slug}/{cantiere_slug}/{file}
  allegati_consegne/                     # foto consegna autista (foto_consegna.percorso_file)
  allegati_scontrini/                    # scontrini rifornimenti (rifornimenti_carburante.foto_scontrino)
  allegati_guasti/                       # foto segnalazione guasti (segnalazioni_guasti.foto)
  default/                               # tema admin (vedi §14)
```

> **Tutti gli allegati sono pubblicamente accessibili** perché stanno sotto `public/`. Replicato. Per i DDT è prevista una variante con token (`/ddt/download/{token}` → `documenti_trasporto.token_pubblico`), che però punta sempre a un file pubblico.

---

## 14. Tema admin & asset

Il tema admin di terze parti è in `public/default/` ed è **integralmente referenziato** dai blade comuni:

```blade
<link rel="stylesheet" href="/default/assets/css/bootstrap.min.css">
<link rel="stylesheet" href="/default/assets/css/icons.min.css">
<link rel="stylesheet" href="/default/assets/css/app.min.css">
<link rel="stylesheet" href="/default/assets/css/custom.min.css">
<script src="/default/assets/libs/jquery/jquery.min.js"></script>
... ecc
```

**Cosa fare**: copia integralmente la cartella `public/default/` dal progetto sorgente al nuovo. Non c'è uno script di download, è un tema acquistato/scaricato a parte (centinaia di file html/css/js già renderizzati). La struttura include:
- `public/default/assets/css/` — CSS bootstrap + custom.
- `public/default/assets/js/` — script + plugins.
- `public/default/assets/libs/` — librerie terze (jquery, swiper, sweetalert, ecc).
- `public/default/assets/images/` — placeholders, dummy avatars (`users/user-dummy-img.jpg` referenziato come default in `utenti.immagine`).
- `public/default/*.html` — pagine demo statiche (puoi anche eliminarle, non sono usate da Laravel).

`resources/css/app.css` e `resources/js/app.js` sono entry minimali per Mix; il vero rendering viene dal tema.

---

## 15. Checklist di replica

Quando dai questo MD a Claude Code su un progetto vuoto, falli seguire passo passo:

- [ ] **1.** `composer create-project laravel/laravel:^8.40 nusa_replica`.
- [ ] **2.** Sostituire `composer.json`, `package.json`, `webpack.mix.js` (§3) → `composer install`, `npm install`.
- [ ] **3.** Creare `.env` (§4) → `php artisan key:generate`.
- [ ] **4.** Creare DB MySQL `nusa.logistia.it` (CHARSET utf8mb4).
- [ ] **5.** Creare i 38 file migration in `database/migrations/` (§5).
- [ ] **6.** `php artisan migrate`.
- [ ] **7.** Sovrascrivere `routes/web.php` (§6) e `routes/api.php` (§7).
- [ ] **8.** Sovrascrivere `app/Http/Kernel.php` (§8) — **CSRF commentato**.
- [ ] **9.** Copiare interi i 12 controller in `app/Http/Controllers/` (§9).
- [ ] **10.** Copiare `app/Imports/` e `app/Exports/` (§11).
- [ ] **11.** Copiare `resources/views/` (4 sottocartelle: admin, azienda, autista, default, stampa) (§10).
- [ ] **12.** Copiare `resources/css/app.css` e `resources/js/app.js`.
- [ ] **13.** Copiare la cartella `public/default/` (tema admin) (§14).
- [ ] **14.** Creare le cartelle `public/allegati_*` vuote (§13).
- [ ] **15.** Copiare `public/.htaccess` (Laravel default).
- [ ] **16.** `npm run dev` per buildare gli asset di Mix.
- [ ] **17.** Configurare `.env` per le integrazioni esterne (§12) — Google Maps, OpenAPI P.IVA.
- [ ] **18.** Inserire manualmente in `aziende` la prima riga e in `utenti` il primo super-admin (con `admin_azienda=1`, password in chiaro).
- [ ] **19.** `php artisan serve` e visitare `/admin/login`.
- [ ] **20.** Test end-to-end: login admin → crea azienda → impersona admin azienda → crea utente autista → associa dispositivo tracking → login come autista.

---

## Note finali

- **Naming italiano**: tabelle/colonne/route/view sono in italiano. Non rinominare.
- **Multi-tenant**: ogni nuova action **deve** filtrare per `id_azienda` da sessione.
- **CSRF disattivo**: i form esistenti non hanno `@csrf`; non aggiungerlo senza patchare lato server.
- **Schema**: questo MD ricostruisce le **38 tabelle attivamente usate**. Le tabelle ERP legacy (bandi/preventivi/fatture/contratti/agenti/leads/formazione/commesse/ODL/distinta_base/fornitori/dipendenti/...) **non esistono** nel DB sorgente; le view che le usano sono codice morto. Se in futuro le riattivi, vanno create separatamente.
- **Auth e password**: replicate identiche all'originale (plaintext). Considera un hardening successivo (bcrypt + CSRF + parametric queries) come progetto a parte.
