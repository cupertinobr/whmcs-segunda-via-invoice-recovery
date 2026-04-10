<?php
use WHMCS\Database\Capsule;

add_hook('ClientAreaInit', 1, function() {
    if(isset($_GET['gateway'], $_GET['id'])) {
        Capsule::table('tblinvoices')
            ->where('id', (int)$_GET['id'])
            ->update(['paymentmethod' => $_GET['gateway']]);
    }
});
