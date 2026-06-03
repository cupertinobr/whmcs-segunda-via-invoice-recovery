<?php
/**
 * Debug utility to check Invoice Recovery logs and test localAPI SSO generation.
 * Security: Requires an active WHMCS admin session.
 */

define("CLIENTAREA", true); // To load WHMCS bootstrap
require_once __DIR__ . '/init.php';

use WHMCS\Database\Capsule;

// Security check: Must be logged in as admin
if (!isset($_SESSION['adminid']) || empty($_SESSION['adminid'])) {
    http_response_code(403);
    die("Access Denied. Please log in as an administrator in WHMCS first.");
}

$action = $_GET['action'] ?? '';

// Fetch Addon settings
$addonConfig = Capsule::table('tbladdonmodules')->where('module', 'invoice_recovery')->pluck('value', 'setting');
$adminUser = trim($addonConfig['admin_user'] ?? '');
if (empty($adminUser)) {
    $adminUser = Capsule::table('tbladmins')->where('disabled', 0)->value('username') ?? '';
}

echo "<html><head><title>Invoice Recovery Debug Tool</title>";
echo "<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css'>";
echo "</head><body class='container my-5'>";
echo "<h2>Invoice Recovery Debug Tool</h2>";
echo "<div class='card my-3'><div class='card-body'>";
echo "<h5>Configuration</h5>";
echo "<ul>";
echo "<li><strong>Configured Admin User:</strong> " . htmlspecialchars($adminUser) . "</li>";
echo "<li><strong>Disable SSO setting:</strong> " . (($addonConfig['disable_sso'] ?? '') === 'on' ? 'Yes' : 'No') . "</li>";
echo "</ul>";
echo "</div></div>";

if ($action === 'test_sso') {
    $clientId = (int)($_GET['client_id'] ?? 0);
    if (!$clientId) {
        $clientId = (int)Capsule::table('tblclients')->value('id');
    }

    echo "<h3>Testing CreateSsoToken for Client ID: {$clientId}</h3>";
    if (!$clientId) {
        echo "<div class='alert alert-danger'>No clients found in the database.</div>";
    } else {
        $results = localAPI('CreateSsoToken', [
            'client_id' => $clientId,
            'destination' => 'sso:custom_redirect',
            'sso_redirect_path' => 'clientarea.php'
        ], $adminUser);

        echo "<h4>API Results:</h4>";
        echo "<pre>" . htmlspecialchars(print_r($results, true)) . "</pre>";

        if (isset($results['result']) && $results['result'] === 'success') {
            echo "<div class='alert alert-success'>Successfully created SSO token! URL: <a href='" . htmlspecialchars($results['redirect_url']) . "' target='_blank'>" . htmlspecialchars($results['redirect_url']) . "</a></div>";
        } else {
            echo "<div class='alert alert-danger'>Failed to create SSO token. Check if the admin user has correct permissions.</div>";
        }
    }
}

// Display last 20 logs from mod_invoice_recovery_logs
echo "<h3>Recent Logs (Last 20)</h3>";
if (Capsule::schema()->hasTable('mod_invoice_recovery_logs')) {
    $logs = Capsule::table('mod_invoice_recovery_logs')
        ->orderBy('id', 'desc')
        ->limit(20)
        ->get();

    if ($logs->isEmpty()) {
        echo "<p>No logs found.</p>";
    } else {
        echo "<table class='table table-striped table-bordered'>";
        echo "<thead><tr><th>ID</th><th>Date</th><th>IP</th><th>Client ID</th><th>Action</th><th>Details</th></tr></thead>";
        echo "<tbody>";
        foreach ($logs as $log) {
            echo "<tr>";
            echo "<td>{$log->id}</td>";
            echo "<td>{$log->created_at}</td>";
            echo "<td>" . htmlspecialchars($log->ip) . "</td>";
            echo "<td>" . ($log->client_id ?: 'Guest') . "</td>";
            echo "<td><span class='badge badge-info'>" . htmlspecialchars($log->action) . "</span></td>";
            echo "<td>" . htmlspecialchars($log->details) . "</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    }
} else {
    echo "<div class='alert alert-warning'>Table 'mod_invoice_recovery_logs' does not exist in the database. Make sure the addon is activated.</div>";
}

echo "<div class='my-4'>";
echo "<a href='debug_sso.php?action=test_sso' class='btn btn-primary'>Test CreateSsoToken on first client</a> ";
echo "<a href='debug_sso.php' class='btn btn-secondary'>Refresh logs</a>";
echo "</div>";

echo "</body></html>";
