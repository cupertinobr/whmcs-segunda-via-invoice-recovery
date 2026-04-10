<?php
function ir_send_whatsapp($numero, $mensagem, $config) {

    $url = "https://api.z-api.io/instances/{$config['whatsapp_instance']}/token/{$config['whatsapp_token']}/send-text";

    $data = [
        "phone" => preg_replace('/\D/', '', $numero),
        "message" => $mensagem
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_exec($ch);
    curl_close($ch);
}
