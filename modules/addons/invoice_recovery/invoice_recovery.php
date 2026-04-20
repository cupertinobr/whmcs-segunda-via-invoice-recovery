<?php
use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function invoice_recovery_config() {
    $customFields = [];
    $gateways = [];
    try {
        // Buscar campos personalizados
        $fields = Capsule::table('tblcustomfields')
            ->where('type', 'client')
            ->orderBy('fieldname', 'asc')
            ->get();
        foreach ($fields as $field) {
            $customFields[$field->id] = $field->fieldname;
        }

        // Buscar gateways ativos
        $gatewayRows = Capsule::table('tblpaymentgateways')
            ->where('setting', 'name')
            ->get();
        foreach ($gatewayRows as $row) {
            $gateways[$row->gateway] = $row->value;
        }
    } catch (\Exception $e) { }

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
            "pix_gateway_id" => [
                "FriendlyName" => "Gateway PIX",
                "Type" => "dropdown",
                "Options" => $gateways,
                "Description" => "Selecione o gateway que será usado para o botão PIX.",
            ],
            "boleto_gateway_id" => [
                "FriendlyName" => "Gateway Boleto",
                "Type" => "dropdown",
                "Options" => $gateways,
                "Description" => "Selecione o gateway que será usado para o botão Boleto.",
            ],
            "cc_gateway_id" => [
                "FriendlyName" => "Gateway Cartão",
                "Type" => "dropdown",
                "Options" => $gateways,
                "Description" => "Selecione o gateway que será usado para o botão Cartão.",
            ],
            "api_identifier" => ["FriendlyName"=>"API Identifier","Type"=>"text", "Description" => "System Settings > API & Security > <a href=\"configapicredentials.php\" target=\"_blank\">API Credentials</a>"],
            "api_secret" => ["FriendlyName"=>"API Secret","Type"=>"password", "Description" => "System Settings > API & Security > <a href=\"configapicredentials.php\" target=\"_blank\">API Credentials</a>"],
            "disable_sso" => ["FriendlyName" => "Desativar Login Automático", "Type" => "yesno", "Description" => "Se marcado, o cliente não será logado automaticamente ao acessar ou pagar faturas."],
        ]
    ];
}


function invoice_recovery_activate()
{
    // Create custom tables and schema required by your module
    try {
        // Capsule::schema()
        //     ->create(
        //         'mod_addonexample',
        //         function ($table) {
        //             /** @var \Illuminate\Database\Schema\Blueprint $table */
        //             $table->increments('id');
        //             $table->text('demo');
        //         }
        //     );

        // return [
        //     // Supported values here include: success, error or info
        //     'status' => 'success',
        //     'description' => 'This is a demo module only. '
        //         . 'In a real module you might report a success or instruct a '
        //             . 'user how to get started with it here.',
        // ];
    } catch (\Exception $e) {
        return [
            // Supported values here include: success, error or info
            'status' => "error",
            'description' => 'Unable to create mod_addonexample: ' . $e->getMessage(),
        ];
    }
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
