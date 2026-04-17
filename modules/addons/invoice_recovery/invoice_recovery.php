<?php
use WHMCS\Database\Capsule;

if (!defined("WHMCS")) die("Acesso negado");

function invoice_recovery_config() {
    return [
        "name" => "Invoice Recovery Pro",
        "description" => "Portal de 2ª via com WhatsApp, PIX e Dashboard",
        "version" => "1.0",
        "author" => "Rodolfo Cupertino - rodolfols@gmail.com",
        "fields" => [
            "enabled" => ["FriendlyName"=>"Ativar Portal","Type"=>"yesno","Default"=>"on"],
            "enable_pix" => ["FriendlyName"=>"PIX","Type"=>"yesno","Default"=>"on"],
            "enable_boleto" => ["FriendlyName"=>"Boleto","Type"=>"yesno","Default"=>"on"],
            "enable_cartao" => ["FriendlyName"=>"Cartão","Type"=>"yesno","Default"=>"on"],
            "cpf_field_id" => ["FriendlyName"=>"ID CPF/CNPJ","Type"=>"text"],
            "whatsapp_token" => ["FriendlyName"=>"Token Z-API","Type"=>"text"],
            "whatsapp_instance" => ["FriendlyName"=>"Instance ID","Type"=>"text"],
        ]
    ];
}

function invoice_recovery_output($vars) {

    echo "<h2>📊 Dashboard Invoice Recovery</h2>";

    $total = Capsule::table('mod_invoice_recovery_logs')->sum('amount');
    $count = Capsule::table('mod_invoice_recovery_logs')->count();

    echo "<div style='display:flex;gap:20px'>
        <div class='card p-3'>💰 R$ {$total}</div>
        <div class='card p-3'>📊 {$count} ações</div>
    </div>";

    echo "<hr><p>Dashboard avançado ativo ✔️</p>";
}
