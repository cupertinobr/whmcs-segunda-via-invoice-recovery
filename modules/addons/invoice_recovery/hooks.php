<?php
/**
 * Hook Loader for Invoice Recovery Module
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

// Carregar todos os arquivos de hook da pasta /hooks/
foreach (glob(__DIR__ . "/hooks/*.php") as $filename) {
    include_once $filename;
}
