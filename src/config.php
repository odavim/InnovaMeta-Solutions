<?php
// Carrega as configurações: variável de ambiente > config.local.php > padrão.
function config(string $chave, $padrao = '')
{
    static $local = null;
    if ($local === null) {
        $arquivo = __DIR__ . '/config.local.php';
        $local = file_exists($arquivo) ? (require $arquivo) : [];
    }
    $env = getenv(strtoupper($chave));
    if ($env !== false && $env !== '') {
        return $env;
    }
    return $local[$chave] ?? $padrao;
}
