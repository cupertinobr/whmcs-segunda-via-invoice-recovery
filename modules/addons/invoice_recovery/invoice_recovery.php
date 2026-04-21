<?php
use WHMCS\Database\Capsule;

function invoice_recovery_get_lang() {
    global $_ADDONLANG;
    if (isset($_ADDONLANG) && is_array($_ADDONLANG) && isset($_ADDONLANG['config_enabled'])) {
        return $_ADDONLANG;
    }

    $addon_path = dirname(__FILE__);
    $lang = 'portuguese-br'; // Default

    // Try to get from session or config
    if (isset($_SESSION['adminlang']) && $_SESSION['adminlang']) {
        $lang = $_SESSION['adminlang'];
    } else {
        try {
            $lang = Capsule::table('tblconfiguration')->where('setting', 'Language')->value('value');
        } catch (\Exception $e) {}
    }

    $lang = strtolower($lang);
    $lang_file = $addon_path . '/lang/' . $lang . '.php';
    if (!file_exists($lang_file)) {
        $lang_file = $addon_path . '/lang/english.php';
    }

    if (file_exists($lang_file)) {
        include $lang_file;
    }

    return $_ADDONLANG;
}


if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function invoice_recovery_config() {
    $lang = invoice_recovery_get_lang();
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
        "name" => "Invoice Recovery (2ª via)",
        "description" => $lang['addon_description'] ?? "Portal de 2ª via",
        "version" => "1.0",
        "author" => "BRANIX Solutions",
        "fields" => [
            "enabled" => ["FriendlyName"=>$lang['config_enabled'] ?? "Ativar Portal","Type"=>"yesno","Default"=>"on"],
            "enable_pix" => ["FriendlyName"=>$lang['config_pix'] ?? "PIX","Type"=>"yesno","Default"=>"on"],
            "enable_boleto" => ["FriendlyName"=>$lang['config_boleto'] ?? "Boleto","Type"=>"yesno","Default"=>"on"],
            "enable_cartao" => ["FriendlyName"=>$lang['config_cartao'] ?? "Cartão","Type"=>"yesno","Default"=>"on"],
            "cpf_field_id" => [
                "FriendlyName" => $lang['config_cpf_field'] ?? "Campo CPF/CNPJ",
                "Type" => "dropdown",
                "Options" => $customFields,
                "Description" => $lang['config_cpf_field_desc'] ?? "Selecione o campo personalizado que contém o CPF ou CNPJ do cliente.",
            ],
            "block_field_id" => [
                "FriendlyName" => $lang['config_block_field'] ?? "Campo Bloqueio",
                "Type" => "dropdown",
                "Options" => $customFields,
                "Description" => ($lang['config_block_field_desc'] ?? "Selecione o campo (Sim/Não) que bloqueia o acesso deste cliente à 2ª via.") . "<br><a href=\"addonmodules.php?module=invoice_recovery&action=create_block_field\">" . ($lang['config_block_field_create'] ?? "Clique aqui para criar o campo automaticamente") . "</a>",
            ],
            "pix_gateway_id" => [
                "FriendlyName" => $lang['config_pix_gateway'] ?? "Gateway PIX",
                "Type" => "dropdown",
                "Options" => $gateways,
                "Description" => $lang['config_pix_gateway_desc'] ?? "Selecione o gateway que será usado para o botão PIX.",
            ],
            "boleto_gateway_id" => [
                "FriendlyName" => $lang['config_boleto_gateway'] ?? "Gateway Boleto",
                "Type" => "dropdown",
                "Options" => $gateways,
                "Description" => $lang['config_boleto_gateway_desc'] ?? "Selecione o gateway que será usado para o botão Boleto.",
            ],
            "cc_gateway_id" => [
                "FriendlyName" => $lang['config_cc_gateway'] ?? "Gateway Cartão",
                "Type" => "dropdown",
                "Options" => $gateways,
                "Description" => $lang['config_cc_gateway_desc'] ?? "Selecione o gateway que será usado para o botão Cartão.",
            ],
            "api_identifier" => ["FriendlyName"=>"API Identifier","Type"=>"text", "Description" => "System Settings > API & Security > <a href=\"configapicredentials.php\" target=\"_blank\">API Credentials</a>"],
            "api_secret" => ["FriendlyName"=>"API Secret","Type"=>"password", "Description" => "System Settings > API & Security > <a href=\"configapicredentials.php\" target=\"_blank\">API Credentials</a>"],
            "disable_sso" => ["FriendlyName" => $lang['config_disable_sso'] ?? "Desativar Login Automático", "Type" => "yesno", "Description" => $lang['config_disable_sso_desc'] ?? "Se marcado, o cliente não será logado automaticamente ao acessar ou pagar faturas."],
            "limit_attempts" => ["FriendlyName" => $lang['config_limit_attempts'] ?? "Limite de Tentativas", "Type" => "text", "Size" => "3", "Default" => "5", "Description" => $lang['config_limit_attempts_desc'] ?? "Número máximo de consultas mal-sucedidas permitidas antes do bloqueio."],
            "lockout_time" => ["FriendlyName" => $lang['config_lockout_time'] ?? "Tempo de Bloqueio (min)", "Type" => "text", "Size" => "3", "Default" => "15", "Description" => $lang['config_lockout_time_desc'] ?? "Tempo em minutos que o visitante ficará bloqueado após atingir o limite."],
        ]
    ];
}



function invoice_recovery_activate()
{
    $lang = invoice_recovery_get_lang();
    try {
        if (!Capsule::schema()->hasTable('mod_invoice_recovery_attempts')) {
            Capsule::schema()->create('mod_invoice_recovery_attempts', function ($table) {
                $table->string('ip', 45)->primary();
                $table->integer('attempts')->default(0);
                $table->timestamp('last_attempt')->useCurrent();
            });
        }
        return ['status' => 'success', 'description' => $lang['activate_success'] ?? 'Módulo ativado com sucesso.'];
    } catch (\Exception $e) {
        return ['status' => "error", 'description' => ($lang['activate_error'] ?? 'Erro ao criar tabela: ') . $e->getMessage()];
    }
}




function invoice_recovery_output($vars) {
    $lang = $vars['_lang'];
    $action = $_GET['action'] ?? '';

    if ($action === 'create_block_field') {
        try {
            $fieldName = $lang['restricted_access'] ?? 'Desativar 2ª Via';
            $exists = Capsule::table('tblcustomfields')
                ->where('type', 'client')
                ->where('fieldname', $fieldName)
                ->exists();

            if (!$exists) {
                Capsule::table('tblcustomfields')->insert([
                    'type' => 'client',
                    'relid' => 0,
                    'fieldname' => $fieldName,
                    'fieldtype' => 'tickbox',
                    'description' => $lang['config_block_field_desc'] ?? 'Marque para bloquear o acesso deste cliente ao portal de 2ª via.',
                    'adminonly' => 'on',
                    'required' => '',
                    'showorder' => 'on',
                    'showinvoice' => '',
                    'sortorder' => 0
                ]);
                echo '<div class="alert alert-success">' . str_replace(':field', $fieldName, $lang['field_created_success']) . ' <a href="configaddonmods.php">' . $lang['back_to_config'] . '</a></div>';
            } else {
                echo '<div class="alert alert-info">' . str_replace(':field', $fieldName, $lang['field_already_exists']) . ' <a href="configaddonmods.php">' . $lang['back_to_config'] . '</a></div>';
            }
        } catch (\Exception $e) {
            echo '<div class="alert alert-danger">' . $lang['field_create_error'] . $e->getMessage() . '</div>';
        }
    }

    echo "<h2>" . $lang['dashboard_title'] . "</h2>";


    // $total = Capsule::table('mod_invoice_recovery_logs')->sum('amount');
    // $count = Capsule::table('mod_invoice_recovery_logs')->count();

    // echo "<div style='display:flex;gap:20px'>
    //     <div class='card p-3'>💰 R$ {$total}</div>
    //     <div class='card p-3'>📊 {$count} ações</div>
    // </div>";

    echo "<hr><p>" . $lang['dashboard_active'] . "</p>";
}

