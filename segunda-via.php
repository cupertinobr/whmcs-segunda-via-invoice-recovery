<?php
define("CLIENTAREA", true);
require_once __DIR__ . '/init.php';

use WHMCS\Database\Capsule;

// Handle AJAX / Form actions
$action = $_REQUEST['action'] ?? '';
$documento = $_POST['documento'] ?? '';

/**
 * Validações de Documentos (CPF/CNPJ)
 */
function validarCPF($cpf) {
    if (empty($cpf)) return false;
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) return false;
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) return false;
    }
    return true;
}

function validarCNPJ($cnpj) {
    if (empty($cnpj)) return false;
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
    if (strlen($cnpj) != 14) return false;
    for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
        $soma += $cnpj[$i] * $j;
        $j = ($j == 2) ? 9 : $j - 1;
    }
    $resto = $soma % 11;
    if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto)) return false;
    for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
        $soma += $cnpj[$i] * $j;
        $j = ($j == 2) ? 9 : $j - 1;
    }
    $resto = $soma % 11;
    return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
}

// Configurações do Addon
$addonConfig = Capsule::table('tbladdonmodules')->where('module', 'invoice_recovery')->pluck('value', 'setting');
$portalEnabled = ($addonConfig['enabled'] ?? '') === 'on';
$disableSso = ($addonConfig['disable_sso'] ?? '') === 'on';
$enablePix = ($addonConfig['enable_pix'] ?? '') === 'on';
$enableBoleto = ($addonConfig['enable_boleto'] ?? '') === 'on';
$enableCartao = ($addonConfig['enable_cartao'] ?? '') === 'on';

if (!$portalEnabled && !empty($documento)) {
    die('<div class="alert alert-warning text-center">O portal de 2ª via está temporariamente desativado.</div>');
}

/**
 * Ação de Pagamento (UpdateInvoice + Redirect)
 */
if ($action === 'pay') {
    $invoiceId = (int)$_GET['invoiceid'];
    $gateway = $_GET['gateway'] ?? 'pix';

    // 1. Buscar UserID
    $userid = Capsule::table('tblinvoices')->where('id', $invoiceId)->value('userid');
    if (!$userid) die('Fatura não encontrada.');

    // 2. Atualizar Gateway via Admin API
    $adminUser = Capsule::table('tbladmins')->where('disabled', 0)->value('username');
    localAPI('UpdateInvoice', ['invoiceid' => $invoiceId, 'paymentmethod' => $gateway], $adminUser);

    // 3. Gerar Login SSO para a fatura com flag de restrição (se não estiver desativado)
    if (!$disableSso) {
        $results = localAPI('CreateSsoToken', [
            'client_id' => $userid,
            'destination' => 'sso:custom_redirect',
            'sso_redirect_path' => 'viewinvoice.php?id=' . $invoiceId . '&restricted=1'
        ], $adminUser);

        if ($results['result'] == 'success') {
            header("Location: " . $results['redirect_url'] . '&restricted=1');
            exit;
        }
    }

    header("Location: viewinvoice.php?id=" . $invoiceId);
    exit;
}

/**
 * Lógica de Busca de Faturas (via POST)
 */
if (!empty($documento)) {
    $docLimpo = preg_replace('/[^0-9]/', '', $documento);
    
    // 1. Validar CPF ou CNPJ
    if (!validarCPF($docLimpo) && !validarCNPJ($docLimpo)) {
        die('<div class="alert alert-danger shadow-sm border-0 py-3 animate-fade-in text-center">
                <i class="fas fa-exclamation-circle fa-2x mb-3 d-block text-danger"></i>
                <h6 class="font-weight-bold">Documento Inválido</h6>
                <p class="mb-0 small">O CPF ou CNPJ informado não é válido. Verifique os números e tente novamente.</p>
             </div>');
    }

    // 2. Buscar o cliente pelo campo personalizado configurado
    $cpfFieldId = (int)($addonConfig['cpf_field_id'] ?? 0);
    
    if (!$cpfFieldId) {
        die('<div class="alert alert-danger text-center">Configuração incompleta: Campo de CPF não definido no módulo.</div>');
    }

    $cliente = Capsule::table('tblcustomfieldsvalues')
        ->join('tblclients', 'tblclients.id', '=', 'tblcustomfieldsvalues.relid')
        ->where('tblcustomfieldsvalues.fieldid', $cpfFieldId)
        ->where('tblcustomfieldsvalues.value', $docLimpo)
        ->select('tblclients.id', 'tblclients.firstname', 'tblclients.lastname', 'tblclients.email')
        ->first();

    if (!$cliente) {
        die('<div class="alert alert-danger shadow-sm border-0 py-3 animate-fade-in text-center">
                <i class="fas fa-user-times fa-2x mb-3 d-block text-danger"></i>
                <h6 class="font-weight-bold">Não Encontrado</h6>
                <p class="mb-0 small">Não encontramos nenhum cliente cadastrado com este documento.</p>
             </div>');
    }

    // 2. Verificar se a 2ª Via está desativada para este cliente
    $isDisabled = Capsule::table('tblcustomfieldsvalues')
        ->join('tblcustomfields', 'tblcustomfields.id', '=', 'tblcustomfieldsvalues.fieldid')
        ->where('tblcustomfields.type', 'client')
        ->where('tblcustomfields.fieldname', 'like', '%Desativar 2ª Via%')
        ->where('tblcustomfieldsvalues.relid', $cliente->id)
        ->value('value');

    if (strtolower($isDisabled) == 'yes' || strtolower($isDisabled) == 'on' || $isDisabled == '1') {
        die('<div class="alert alert-warning shadow-sm border-0 py-3 animate-fade-in text-center">
                <i class="fas fa-user-lock fa-2x mb-3 d-block text-warning"></i>
                <h6 class="font-weight-bold">Acesso Restrito</h6>
                <p class="mb-0 small">Identificamos que o acesso simplificado está desativado para sua conta.</p>
             </div>');
    }

    // Buscar faturas do cliente
    $faturas = Capsule::table('tblinvoices')
        ->where('userid', $cliente->id)
        ->where('status', 'Unpaid')
        ->orderBy('id', 'desc')
        ->get();

    if ($faturas->isEmpty()) {
        echo '<div class="alert alert-info text-center">Parabéns, não encontramos faturas pendentes.</div>';
        exit;
    }

    echo '<h5 class="mb-3 mt-2">Faturas não pagas:</h5>';
    echo '<div class="list-group shadow-sm">';

    foreach ($faturas as $f) {
        $duedate = date('d/m/Y', strtotime($f->duedate));
        $total = number_format($f->total, 2, ',', '.');
        
        // Gerar Link SSO com trava de restrição
        $adminUser = Capsule::table('tbladmins')->where('disabled', 0)->value('username');
        $viewLink = "viewinvoice.php?id=" . $f->id;
        
        if ($adminUser && !$disableSso) {
            $results = localAPI('CreateSsoToken', [
                'client_id' => $cliente->id,
                'destination' => 'sso:custom_redirect',
                'sso_redirect_path' => 'viewinvoice.php?id=' . $f->id . '&restricted=1'
            ], $adminUser);
            if ($results['result'] == 'success') {
                $viewLink = $results['redirect_url'] . '&restricted=1';
            }
        }

        $pixGateway = $addonConfig['pix_gateway_id'] ?? 'bancointer_pix';
        $ccGateway = $addonConfig['cc_gateway_id'] ?? 'gofasiugucartao';
        $boletoGateway = $addonConfig['boleto_gateway_id'] ?? '';

        $payPixLink = "segunda-via.php?action=pay&invoiceid={$f->id}&gateway={$pixGateway}";
        $payCreditCardLink = "segunda-via.php?action=pay&invoiceid={$f->id}&gateway={$ccGateway}";
        $payBoletoLink = "segunda-via.php?action=pay&invoiceid={$f->id}&gateway={$boletoGateway}";

        echo "
        <div class='invoice-card-custom animate-fade-in'>
            <div class='invoice-meta'>
                <h5>Fatura #{$f->id}</h5>
                <p>Vencimento: <strong>{$duedate}</strong> | Total: <strong>R$ {$total}</strong></p>
            </div>
            <div class='invoice-btns'>
                <a href='{$viewLink}' target='_blank' class='btn-act btn-act-light'><i class='fas fa-eye'></i> Ver</a>";
        
        if ($enablePix) {
            echo "<a href='{$payPixLink}' target='_blank' class='btn-act btn-act-primary btn-pay'><i class='fas fa-qrcode'></i> PIX</a>";
        }

        if ($enableBoleto && $boletoGateway) {
            echo "<a href='{$payBoletoLink}' target='_blank' class='btn-act btn-act-primary btn-pay'><i class='fas fa-barcode'></i> Boleto</a>";
        }
        
        if ($enableCartao) {
            echo "<a href='{$payCreditCardLink}' target='_blank' class='btn-act btn-act-primary btn-pay'><i class='fas fa-credit-card'></i> Cartão</a>";
        }
        
        echo "</div></div>";
    }

    echo '</div>';
    exit;
}

$ca = new WHMCS\ClientArea();
$ca->setPageTitle("2ª Via de Pagamento");
$ca->addToBreadCrumb('index.php', 'Portal');
$ca->addToBreadCrumb('segunda-via.php', '2ª Via de Pagamento');
$ca->initPage();

// O WHMCS busca o template relativo à pasta do tema ativo.
// Usamos o caminho partindo da raiz para garantir compatibilidade.
$ca->setTemplate('/modules/addons/invoice_recovery/templates/clientarea.tpl');

$ca->output();
