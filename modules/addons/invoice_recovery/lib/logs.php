<?php
function ir_log($clientId, $action, $amount = 0, $method = null) {
    \WHMCS\Database\Capsule::table('mod_invoice_recovery_logs')->insert([
        'client_id' => $clientId,
        'action' => $action,
        'amount' => $amount,
        'method' => $method
    ]);
}
