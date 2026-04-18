<?php
require_once __DIR__ . '/init.php';
use WHMCS\Database\Capsule;

$email = $_POST['email'] ?? '';

if (empty($email)) {
    die('<div class="alert alert-warning">Por favor, informe seu e-mail.</div>');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('<div class="alert alert-danger">E-mail inválido.</div>');
}

// Buscar o cliente apenas pelo e-mail
$cliente = Capsule::table('tblclients')
    ->where('email', $email)
    ->select('id', 'firstname', 'lastname')
    ->first();

if (!$cliente) {
    die('<div class="alert alert-danger">Nenhuma conta encontrada com este e-mail.</div>');
}

// Buscar faturas do cliente
$faturas = Capsule::table('tblinvoices')
    ->where('userid', $cliente->id)
    ->where('status', 'Unpaid')
    ->orderBy('id', 'desc')
    ->get();

if ($faturas->isEmpty()) {
    echo '<div class="alert alert-info text-center">
            <i class="fas fa-check-circle fa-2x mb-3 d-block"></i>
            Parabéns, <strong>' . $cliente->firstname . '</strong>! Não encontramos faturas pendentes.
          </div>';
    exit;
}

echo '<h5 class="mb-3 mt-2">Olá, ' . $cliente->firstname . '. Encontramos as seguintes faturas:</h5>';
echo '<div class="list-group shadow-sm">';

foreach ($faturas as $f) {
    $date = date('d/m/Y', strtotime($f->date));
    $duedate = date('d/m/Y', strtotime($f->duedate));
    $total = number_format($f->total, 2, ',', '.');
    
    echo "
    <div class='list-group-item list-group-item-action flex-column align-items-start py-3'>
        <div class='d-flex w-100 justify-content-between align-items-center'>
            <h6 class='mb-1 text-primary font-weight-bold'>Fatura #{$f->id}</h6>
            <span class='badge badge-warning p-2'>Pendente</span>
        </div>
        <p class='mb-2 text-muted small'>Vencimento: {$duedate} | Total: <strong>R$ {$total}</strong></p>
        <div class='d-flex gap-2 mt-2'>
            <a href='viewinvoice.php?id={$f->id}' target='_blank' class='btn btn-sm btn-outline-secondary mr-2'>
                <i class='fas fa-eye'></i> Ver Fatura
            </a>
            <a href='viewinvoice.php?id={$f->id}&gateway=pix' target='_blank' class='btn btn-sm btn-success px-4 font-weight-bold'>
                <i class='fas fa-qrcode'></i> Pagar com PIX
            </a>
        </div>
    </div>";
}

echo '</div>';
?>
