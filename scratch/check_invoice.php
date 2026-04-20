<?php
require 'init.php';
use WHMCS\Database\Capsule;
$invoice = Capsule::table('tblinvoices')->where('id', 1232)->first();
if ($invoice) {
    echo "ID: " . $invoice->id . "\n";
    echo "Hash: [" . $invoice->hash . "]\n";
    echo "Fields: " . implode(', ', array_keys((array)$invoice)) . "\n";
} else {
    echo "Invoice not found\n";
}
