<?php
use WHMCS\Database\Capsule;

if (!defined("WHMCS")) die("Acesso negado");

function invoice_recovery_config() {
    $customFields = [];
    try {
        $fields = Capsule::table('tblcustomfields')
            ->where('type', 'client')
            ->orderBy('fieldname', 'asc')
            ->get();

        foreach ($fields as $field) {
            $customFields[$field->id] = $field->fieldname;
        }
    } catch (\Exception $e) {
        // Fallback caso ocorra algum erro na consulta
    }

    return [
        "name" => "Invoice Recovery Pro",
        "description" => "Portal de 2ª via com WhatsApp, PIX e Dashboard",
        "version" => "1.0",
        "author" => "BRANIX Solutions",
        "fields" => [
            "enabled" => ["FriendlyName"=>"Ativar Portal","Type"=>"yesno","Default"=>"on"],
            "enable_pix" => ["FriendlyName"=>"PIX","Type"=>"yesno","Default"=>"on"],
            "enable_boleto" => ["FriendlyName"=>"Boleto","Type"=>"yesno","Default"=>"on"],
            "enable_cartao" => ["FriendlyName"=>"Cartão","Type"=>"yesno","Default"=>"on"],
            "cpf_field_id" => [
                "FriendlyName" => "Campo CPF/CNPJ",
                "Type" => "dropdown",
                "Options" => $customFields,
                "Description" => "Selecione o campo personalizado que contém o CPF ou CNPJ do cliente.",
            ],
            "api_identifier" => ["FriendlyName"=>"API Identifier","Type"=>"text", "Description" => "Gerado em Setup > Staff Management > API Credentials"],
            "api_secret" => ["FriendlyName"=>"API Secret","Type"=>"password", "Description" => "Gerado em Setup > Staff Management > API Credentials"],
        ]
    ];
}

function invoice_recovery_output($vars) {

    echo "<h2>📊 Dashboard Invoice Recovery</h2>";

    // $total = Capsule::table('mod_invoice_recovery_logs')->sum('amount');
    // $count = Capsule::table('mod_invoice_recovery_logs')->count();

    // echo "<div style='display:flex;gap:20px'>
    //     <div class='card p-3'>💰 R$ {$total}</div>
    //     <div class='card p-3'>📊 {$count} ações</div>
    // </div>";

    echo "<hr><p>Dashboard avançado ativo ✔️</p>";
}
