<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define("CLIENTAREA", true);

require_once __DIR__ . '/init.php';

$ca = new WHMCS\ClientArea();

$ca->setPageTitle("2ª Via de Pagamento");

$ca->addToBreadCrumb('index.php', 'Portal');
$ca->addToBreadCrumb('segunda-via.php', '2ª Via de Pagamento');

$ca->initPage();

// Para funcionar em qualquer tema (dinâmico), apontamos para o template dentro do módulo
// O WHMCS procura a partir da pasta do tema ativo, então usamos o caminho relativo para sair dela
$templatePath = '../../modules/addons/invoice_recovery/templates/clientarea.tpl';

// Verificamos se o arquivo existe (opcional, mas bom por segurança)
if (!file_exists(__DIR__ . '/modules/addons/invoice_recovery/templates/clientarea.tpl')) {
    die("Erro: Template file not found.");
}

$ca->setTemplate($templatePath);

$ca->output();

?>


