<?php
require_once __DIR__ . '/init.php';
use WHMCS\Database\Capsule;

$email = $_POST['email'] ?? '';

$clientes = Capsule::table('tblclients')->where('email', $email)->get();

foreach($clientes as $c){
 $faturas = Capsule::table('tblinvoices')
   ->where('userid', $c->id)
   ->where('status','Unpaid')->get();

 foreach($faturas as $f){
  echo "<div>Fatura #{$f->id} - R$ {$f->total}
  <a href='viewinvoice.php?id={$f->id}&gateway=pix'>PIX</a>
  </div>";
 }
}
