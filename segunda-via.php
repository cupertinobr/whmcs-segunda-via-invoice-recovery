<?php
define("CLIENTAREA", true);
require_once __DIR__ . '/init.php';

$ca = new WHMCS\ClientArea();
$ca->setPageTitle("2ª Via de Pagamento");
$ca->addToBreadCrumb('index.php', 'Portal');
$ca->addToBreadCrumb('segunda-via.php', '2ª Via de Pagamento');
$ca->initPage();

// O WHMCS busca o template relativo à pasta do tema ativo.
// Usamos o caminho partindo da raiz para garantir compatibilidade.
$ca->setTemplate('/modules/addons/invoice_recovery/templates/clientarea.tpl');

$ca->output();
