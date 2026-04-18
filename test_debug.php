<?php
require_once __DIR__ . '/init.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Iniciando teste...<br>";
try {
    echo "Testando classe ClientArea...<br>";
    if (class_exists('WHMCS\ClientArea')) {
        $ca = new WHMCS\ClientArea();
        echo "Instanciação de ClientArea: OK<br>";
    } else {
        echo "Classe WHMCS\ClientArea NÃO encontrada.<br>";
    }

    include_once __DIR__ . '/modules/addons/invoice_recovery/invoice_recovery.php';
    echo "Inclusão do arquivo: OK<br>";
    
    if (function_exists('invoice_recovery_config')) {
        echo "Função config existe. Chamando...<br>";
        $config = invoice_recovery_config();
        echo "Configuração obtida com sucesso:<br>";
        print_r($config);
    } else {
        echo "Função config NÃO encontrada.<br>";
    }
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage();
}
