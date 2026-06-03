<?php
define("CLIENTAREA", true);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/init.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
// Autoloader PSR-4 de fallback para as classes do Addon
spl_autoload_register(function ($class) {
    $prefix = 'branix\\WhmcsInvoiceRecovery\\';
    $base_dir = __DIR__ . '/modules/addons/invoice_recovery/lib/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

use branix\WhmcsInvoiceRecovery\Security;
use branix\WhmcsInvoiceRecovery\DocumentValidator;
use branix\WhmcsInvoiceRecovery\RateLimiter;
use branix\WhmcsInvoiceRecovery\LoggerService;
use branix\WhmcsInvoiceRecovery\InvoiceSearchService;
use branix\WhmcsInvoiceRecovery\PaymentRedirectService;
use WHMCS\Database\Capsule;

try {
// Define explicit UTF-8 charset for AJAX
header('Content-Type: text/html; charset=utf-8');

// Load Addon Language
$language = (isset($_SESSION['Language']) && $_SESSION['Language']) ? $_SESSION['Language'] : 'portuguese-br';
$language = strtolower($language);
$langFile = __DIR__ . '/modules/addons/invoice_recovery/lang/' . $language . '.php';
if (!file_exists($langFile)) {
    $langFile = __DIR__ . '/modules/addons/invoice_recovery/lang/english.php';
}
require_once $langFile;

$action = $_REQUEST['action'] ?? '';
$documento = $_POST['documento'] ?? '';

// Addon Settings
$addonConfig = Capsule::table('tbladdonmodules')->where('module', 'invoice_recovery')->pluck('value', 'setting');
$portalEnabled = ($addonConfig['enabled'] ?? '') === 'on';
$disableSso = ($addonConfig['disable_sso'] ?? '') === 'on';
$disableCdn = ($addonConfig['disable_cdn'] ?? '') === 'on';
$enablePix = ($addonConfig['enable_pix'] ?? '') === 'on';
$enableBoleto = ($addonConfig['enable_boleto'] ?? '') === 'on';
$enableCartao = ($addonConfig['enable_cartao'] ?? '') === 'on';
$limitAttempts = (int)($addonConfig['limit_attempts'] ?? 5);
$lockoutTime = (int)($addonConfig['lockout_time'] ?? 15);
$blockFieldId = (int)($addonConfig['block_field_id'] ?? 0);
$cpfFieldId = (int)($addonConfig['cpf_field_id'] ?? 0);

// Identify configured admin user or use the first active as fallback
$adminUser = trim($addonConfig['admin_user'] ?? '');
if (empty($adminUser)) {
    $adminUser = Capsule::table('tbladmins')->where('disabled', 0)->value('username') ?? '';
}

$userIp = $_SERVER['REMOTE_ADDR'] ?? '';

function irSecurityAlert(string $type, string $title, string $desc = ''): void
{
    $icon = $type === 'warning' ? 'fa-user-lock' : 'fa-exclamation-circle';
    $descHtml = $desc !== ''
        ? '<p class="mb-0 small">' . htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') . '</p>'
        : '';

    die('<div class="alert alert-' . htmlspecialchars($type, ENT_QUOTES, 'UTF-8') . ' shadow-sm border-0 py-3 animate-fade-in text-center">
            <i class="fas ' . $icon . ' fa-2x mb-3 d-block"></i>
            <h6 class="font-weight-bold">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h6>
            ' . $descHtml . '
         </div>');
}

function irRenderPayForm(int $invoiceId, string $gateway, string $recoveryToken, string $whmcsToken, string $label, string $icon): string
{
    $invoiceId = (int) $invoiceId;
    $gateway = htmlspecialchars($gateway, ENT_QUOTES, 'UTF-8');
    $recoveryToken = htmlspecialchars($recoveryToken, ENT_QUOTES, 'UTF-8');
    $whmcsToken = htmlspecialchars($whmcsToken, ENT_QUOTES, 'UTF-8');
    $label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    $icon = htmlspecialchars($icon, ENT_QUOTES, 'UTF-8');

    return "<form method='post' action='segunda-via.php' target='_blank' class='pay-form-inline'>
                <input type='hidden' name='action' value='pay'>
                <input type='hidden' name='invoiceid' value='{$invoiceId}'>
                <input type='hidden' name='gateway' value='{$gateway}'>
                <input type='hidden' name='recovery_token' value='{$recoveryToken}'>
                <input type='hidden' name='token' value='{$whmcsToken}'>
                <button type='submit' class='btn-act btn-act-primary btn-pay'><i class='fas {$icon}'></i> {$label}</button>
            </form>";
}

function irRenderViewForm(int $invoiceId, string $recoveryToken, string $whmcsToken, string $label, string $icon): string
{
    $invoiceId = (int) $invoiceId;
    $recoveryToken = htmlspecialchars($recoveryToken, ENT_QUOTES, 'UTF-8');
    $whmcsToken = htmlspecialchars($whmcsToken, ENT_QUOTES, 'UTF-8');
    $label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    $icon = htmlspecialchars($icon, ENT_QUOTES, 'UTF-8');

    return "<form method='post' action='segunda-via.php' target='_blank' class='pay-form-inline'>
                <input type='hidden' name='action' value='view'>
                <input type='hidden' name='invoiceid' value='{$invoiceId}'>
                <input type='hidden' name='recovery_token' value='{$recoveryToken}'>
                <input type='hidden' name='token' value='{$whmcsToken}'>
                <button type='submit' class='btn-act btn-act-light btn-pay'><i class='fas {$icon}'></i> {$label}</button>
            </form>";
}

if (!$portalEnabled && (!empty($documento) || in_array($action, ['pay', 'view']))) {
    irSecurityAlert('warning', $_ADDONLANG['portal_disabled'], '');
}

/**
 * Payment Action (UpdateInvoice + On-Demand SSO Redirect)
 */
if ($action === 'pay') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        irSecurityAlert('danger', $_ADDONLANG['method_not_allowed'], $_ADDONLANG['method_not_allowed_desc']);
    }

    $lockoutMinutes = RateLimiter::checkLockout($userIp, $limitAttempts, $lockoutTime);
    if ($lockoutMinutes > 0) {
        $msg = str_replace(':minutes', $lockoutMinutes, $_ADDONLANG['too_many_attempts_desc']);
        irSecurityAlert('danger', $_ADDONLANG['too_many_attempts'], $msg);
    }

    if (!Security::validateCsrfToken($_POST['recovery_token'] ?? '')) {
        RateLimiter::incrementAttempts($userIp);
        irSecurityAlert('danger', $_ADDONLANG['invalid_token'], $_ADDONLANG['invalid_token_desc']);
    }

    $session = Security::getSession();
    if (!$session) {
        irSecurityAlert('warning', $_ADDONLANG['session_expired'], $_ADDONLANG['session_expired_desc']);
    }

    $invoiceId = (int)($_POST['invoiceid'] ?? 0);
    $gateway = Security::sanitizeGateway($_POST['gateway'] ?? '');

    if (!$invoiceId || !Security::isInvoiceAuthorized($invoiceId)) {
        RateLimiter::incrementAttempts($userIp);
        irSecurityAlert('danger', $_ADDONLANG['unauthorized_invoice'], $_ADDONLANG['unauthorized_invoice_desc']);
    }

    if (!Security::isGatewayAllowed($gateway, $addonConfig)) {
        RateLimiter::incrementAttempts($userIp);
        irSecurityAlert('danger', $_ADDONLANG['invalid_gateway'], $_ADDONLANG['invalid_gateway_desc']);
    }

    try {
        $redirectUrl = PaymentRedirectService::processPaymentRedirect(
            $invoiceId,
            $gateway,
            $addonConfig,
            $session,
            $disableSso,
            $adminUser
        );
        header('Location: ' . $redirectUrl);
        exit;
    } catch (\Exception $e) {
        Security::clearSession();
        RateLimiter::incrementAttempts($userIp);
        irSecurityAlert('danger', $_ADDONLANG['invoice_not_found'], $_ADDONLANG['unauthorized_invoice_desc']);
    }
}

/**
 * Invoice View Action (On-Demand SSO Redirect)
 */
if ($action === 'view') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        irSecurityAlert('danger', $_ADDONLANG['method_not_allowed'], $_ADDONLANG['method_not_allowed_desc']);
    }

    $lockoutMinutes = RateLimiter::checkLockout($userIp, $limitAttempts, $lockoutTime);
    if ($lockoutMinutes > 0) {
        $msg = str_replace(':minutes', $lockoutMinutes, $_ADDONLANG['too_many_attempts_desc']);
        irSecurityAlert('danger', $_ADDONLANG['too_many_attempts'], $msg);
    }

    if (!Security::validateCsrfToken($_POST['recovery_token'] ?? '')) {
        RateLimiter::incrementAttempts($userIp);
        irSecurityAlert('danger', $_ADDONLANG['invalid_token'], $_ADDONLANG['invalid_token_desc']);
    }

    $session = Security::getSession();
    if (!$session) {
        irSecurityAlert('warning', $_ADDONLANG['session_expired'], $_ADDONLANG['session_expired_desc']);
    }

    $invoiceId = (int)($_POST['invoiceid'] ?? 0);

    if (!$invoiceId || !Security::isInvoiceAuthorized($invoiceId)) {
        RateLimiter::incrementAttempts($userIp);
        irSecurityAlert('danger', $_ADDONLANG['unauthorized_invoice'], $_ADDONLANG['unauthorized_invoice_desc']);
    }

    try {
        $redirectUrl = PaymentRedirectService::processViewRedirect(
            $invoiceId,
            $session,
            $disableSso,
            $adminUser
        );
        header('Location: ' . $redirectUrl);
        exit;
    } catch (\Exception $e) {
        Security::clearSession();
        RateLimiter::incrementAttempts($userIp);
        irSecurityAlert('danger', $_ADDONLANG['invoice_not_found'], $_ADDONLANG['unauthorized_invoice_desc']);
    }
}

/**
 * Invoice Search Logic (via POST)
 */
if (!empty($documento)) {
    $lockoutMinutes = RateLimiter::checkLockout($userIp, $limitAttempts, $lockoutTime);
    if ($lockoutMinutes > 0) {
        $msg = str_replace(':minutes', $lockoutMinutes, $_ADDONLANG['too_many_attempts_desc']);
        irSecurityAlert('danger', $_ADDONLANG['too_many_attempts'], $msg);
    }

    if (!Security::validateCsrfToken($_POST['recovery_token'] ?? '')) {
        RateLimiter::incrementAttempts($userIp);
        irSecurityAlert('danger', $_ADDONLANG['invalid_token'], $_ADDONLANG['invalid_token_desc']);
    }

    $cliente = InvoiceSearchService::findClient($documento, $cpfFieldId);

    if (!$cliente) {
        RateLimiter::incrementAttempts($userIp);
        LoggerService::log($userIp, null, 'search_failed', "Busca falhou para o documento/e-mail informado.");
        die('<div class="alert alert-danger shadow-sm border-0 py-3 animate-fade-in text-center">
                <i class="fas fa-exclamation-circle fa-2x mb-3 d-block text-danger"></i>
                <h6 class="font-weight-bold">' . $_ADDONLANG['invalid_data'] . '</h6>
                <p class="mb-0 small">' . $_ADDONLANG['invalid_data_desc'] . '</p>
             </div>');
    }

    // Reset attempts after successful lookup
    RateLimiter::resetAttempts($userIp);

    $isBlocked = InvoiceSearchService::isClientBlocked((int)$cliente->id, $blockFieldId);
    if ($isBlocked) {
        LoggerService::log($userIp, (int)$cliente->id, 'search_blocked', "Tentativa de acesso de cliente bloqueado.");
        die('<div class="alert alert-warning shadow-sm border-0 py-3 animate-fade-in text-center">
                <i class="fas fa-user-lock fa-2x mb-3 d-block text-warning"></i>
                <h6 class="font-weight-bold">' . $_ADDONLANG['restricted_access'] . '</h6>
                <p class="mb-0 small">' . $_ADDONLANG['restricted_access_desc'] . '</p>
             </div>');
    }

    $faturas = InvoiceSearchService::getPendingInvoices((int)$cliente->id);

    if (empty($faturas)) {
        Security::clearSession();
        LoggerService::log($userIp, (int)$cliente->id, 'search_empty', "Nenhuma fatura em aberto localizada.");
        echo '<div class="alert alert-info text-center">' . $_ADDONLANG['no_pending_invoices'] . '</div>';
        exit;
    }

    $invoiceIds = array_map(function($f) { return (int)$f->id; }, $faturas);
    Security::establishSession((int)$cliente->id, $invoiceIds);
    $csrfToken = Security::generateCsrfToken();

    LoggerService::log($userIp, (int)$cliente->id, 'search_success', "Localizadas " . count($faturas) . " faturas.");

    echo '<h5 class="mb-3 mt-2">' . $_ADDONLANG['unpaid_invoices'] . '</h5>';
    echo '<div class="list-group shadow-sm">';

    $pixGateway = $addonConfig['pix_gateway_id'] ?? '';
    $ccGateway = $addonConfig['cc_gateway_id'] ?? '';
    $boletoGateway = $addonConfig['boleto_gateway_id'] ?? '';

    foreach ($faturas as $f) {
        $duedate = date($_ADDONLANG['date_format'], strtotime($f->duedate));
        $totalFormatted = InvoiceSearchService::formatCurrency((float)$f->total, (int)$cliente->currency, $_ADDONLANG);

        echo "
        <div class='invoice-card-custom animate-fade-in'>
            <div class='invoice-meta'>
                <h5>{$_ADDONLANG['invoice_num']}{$f->id}</h5>
                <p>{$_ADDONLANG['due_date']} <strong>{$duedate}</strong> | {$_ADDONLANG['total']} <strong>{$totalFormatted}</strong></p>
            </div>
            <div class='invoice-btns'>";
        
        $whmcsToken = $_SESSION['tkval'] ?? '';
        if (empty($whmcsToken) && function_exists('generate_token')) {
            $whmcsToken = generate_token();
        }

        // On-demand View Button (View SSO)
        echo irRenderViewForm((int)$f->id, $csrfToken, $whmcsToken, $_ADDONLANG['view'], 'fa-eye');

        if ($enablePix && $pixGateway) {
            echo irRenderPayForm((int)$f->id, $pixGateway, $csrfToken, $whmcsToken, $_ADDONLANG['pix'], 'fa-qrcode');
        }

        if ($enableBoleto && $boletoGateway) {
            echo irRenderPayForm((int)$f->id, $boletoGateway, $csrfToken, $whmcsToken, $_ADDONLANG['boleto'], 'fa-barcode');
        }

        if ($enableCartao && $ccGateway) {
            echo irRenderPayForm((int)$f->id, $ccGateway, $csrfToken, $whmcsToken, $_ADDONLANG['credit_card'], 'fa-credit-card');
        }

        echo '</div></div>';
    }

    echo '</div>';
    exit;
}

$ca = new WHMCS\ClientArea();
$ca->setPageTitle($_ADDONLANG['page_title']);
$ca->addToBreadCrumb('index.php', $_ADDONLANG['breadcrumb_portal']);
$ca->addToBreadCrumb('segunda-via.php', $_ADDONLANG['page_title']);
$ca->initPage();
$ca->assign('RECOVERY_LANG', $_ADDONLANG);
$ca->assign('csrf_token', Security::generateCsrfToken());
$whmcsToken = $_SESSION['tkval'] ?? '';
if (empty($whmcsToken) && function_exists('generate_token')) {
    $whmcsToken = generate_token();
}
$ca->assign('token', $whmcsToken);
$ca->assign('user_ip', $userIp);
$ca->assign('disable_cdn', $disableCdn);

$ca->setTemplate('/modules/addons/invoice_recovery/templates/clientarea.tpl');

$ca->output();
} catch (\Throwable $e) {
    http_response_code(500);
    echo "<h1>Debug Error: " . htmlspecialchars($e->getMessage()) . "</h1>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    exit;
}
