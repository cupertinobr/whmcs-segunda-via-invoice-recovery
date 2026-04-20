<?php
require __DIR__ . '/../init.php';
use WHMCS\Database\Capsule;

$fields = Capsule::table('tblcustomfields')
    ->where('type', 'client')
    ->get()
    ->map(function($f) {
        return ['id' => $f->id, 'fieldname' => $f->fieldname];
    });

echo json_encode($fields, JSON_PRETTY_PRINT);
