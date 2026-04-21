<?php
/**
 * Sample addon module language file.
 * Language: English
 */
$_ADDONLANG['title_recovery'] = "INVOICE RECOVERY";
$_ADDONLANG['search_title'] = "Check your invoices";
$_ADDONLANG['search_desc'] = "Enter your Email or ID Number to search for your open invoices.";
$_ADDONLANG['input_placeholder'] = "Email or ID Number";
$_ADDONLANG['btn_search'] = "Search";
$_ADDONLANG['loading_find'] = "Locating your invoices...";
$_ADDONLANG['security_info'] = "Secure SSL Environment — Your IP (:ip) is recorded.";
$_ADDONLANG['searching'] = "Searching...";
$_ADDONLANG['error_internal'] = "Oops! An internal error occurred. Please try again later.";
$_ADDONLANG['redirecting_gateway'] = "Forwarding to payment gateway...";
$_ADDONLANG['too_many_attempts'] = "Too Many Attempts";
$_ADDONLANG['too_many_attempts_desc'] = "You have reached the limit of queries. Please try again in :minutes minutes.";

$_ADDONLANG['portal_disabled'] = "The invoice recovery portal is temporarily disabled.";
$_ADDONLANG['invoice_not_found'] = "Invoice not found.";
$_ADDONLANG['invalid_data'] = "Invalid Data";
$_ADDONLANG['invalid_data_desc'] = "The Email or ID Number provided is not valid. Please check and try again.";
$_ADDONLANG['config_incomplete'] = "Incomplete configuration: ID Number field not defined in the module.";
$_ADDONLANG['not_found'] = "Not Found";
$_ADDONLANG['not_found_desc'] = "We couldn't find any client with the provided details.";
$_ADDONLANG['restricted_access'] = "Restricted Access";
$_ADDONLANG['restricted_access_desc'] = "We identified that simplified access is disabled for your account.";
$_ADDONLANG['no_pending_invoices'] = "Congratulations, we found no pending invoices.";
$_ADDONLANG['unpaid_invoices'] = "Unpaid invoices:";
$_ADDONLANG['invoice_num'] = "Invoice #";
$_ADDONLANG['due_date'] = "Due Date:";
$_ADDONLANG['total'] = "Total:";
$_ADDONLANG['view'] = "View";
$_ADDONLANG['pix'] = "PIX";
$_ADDONLANG['boleto'] = "Boleto";
$_ADDONLANG['credit_card'] = "Credit Card";
$_ADDONLANG['page_title'] = "Invoice recovery";
$_ADDONLANG['breadcrumb_portal'] = "Portal";
$_ADDONLANG['date_format'] = "m/d/Y";
$_ADDONLANG['decimal_separator'] = ".";
$_ADDONLANG['thousands_separator'] = ",";
$_ADDONLANG['currency_prefix'] = "$";
$_ADDONLANG['currency_suffix'] = "";

// Admin & Config
$_ADDONLANG['addon_description'] = "Simplified invoice recovery portal for clients.";
$_ADDONLANG['config_enabled'] = "Enable Portal";
$_ADDONLANG['config_pix'] = "Enable PIX Button";
$_ADDONLANG['config_boleto'] = "Enable Boleto Button";
$_ADDONLANG['config_cartao'] = "Enable Card Button";
$_ADDONLANG['config_cpf_field'] = "ID Number Field";
$_ADDONLANG['config_cpf_field_desc'] = "Select the custom field that contains the client's CPF or CNPJ.";
$_ADDONLANG['config_block_field'] = "Block Field";
$_ADDONLANG['config_block_field_desc'] = "Select the field (Yes/No) that blocks this client's access to invoice recovery.";
$_ADDONLANG['config_block_field_create'] = "Click here to create the field automatically";
$_ADDONLANG['config_pix_gateway'] = "PIX Gateway";
$_ADDONLANG['config_pix_gateway_desc'] = "Select the gateway that will be used for the PIX button.";
$_ADDONLANG['config_boleto_gateway'] = "Boleto Gateway";
$_ADDONLANG['config_boleto_gateway_desc'] = "Select the gateway that will be used for the Boleto button.";
$_ADDONLANG['config_cc_gateway'] = "Card Gateway";
$_ADDONLANG['config_cc_gateway_desc'] = "Select the gateway that will be used for the Card button.";
$_ADDONLANG['config_disable_sso'] = "Disable Automatic Login";
$_ADDONLANG['config_disable_sso_desc'] = "If checked, the client will not be automatically logged in when accessing or paying invoices.";
$_ADDONLANG['config_limit_attempts'] = "Attempt Limit";
$_ADDONLANG['config_limit_attempts_desc'] = "Maximum number of unsuccessful queries allowed before blocking.";
$_ADDONLANG['config_lockout_time'] = "Lockout Time (min)";
$_ADDONLANG['config_lockout_time_desc'] = "Time in minutes that the visitor will be blocked after reaching the limit.";

$_ADDONLANG['activate_success'] = "Module activated successfully and limits table created.";
$_ADDONLANG['activate_error'] = "Error creating table: ";
$_ADDONLANG['field_created_success'] = "Custom field \":field\" created successfully as (Admin Only).";
$_ADDONLANG['field_already_exists'] = "The field \":field\" already exists in the system.";
$_ADDONLANG['field_create_error'] = "Error creating field: ";
$_ADDONLANG['dashboard_title'] = "📊 Invoice Recovery Dashboard";
$_ADDONLANG['dashboard_active'] = "Advanced dashboard active ✔️";
$_ADDONLANG['back_to_config'] = "Back to settings";