<?php
$file = realpath(__DIR__ . '/../app/Http/Controllers/AutistaController.php');
if (function_exists('opcache_invalidate') && $file) {
    $ok = opcache_invalidate($file, true);
    echo $ok ? "✅ Cache AutistaController invalidata. Torna alla pagina consegne." : "⚠️ Invalidazione fallita.";
} else {
    echo "ℹ️ OpCache non attivo o file non trovato.";
}