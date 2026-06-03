<?php

use branix\WhmcsInvoiceRecovery\RateLimiter;
use branix\WhmcsInvoiceRecovery\LoggerService;

require_once dirname(__DIR__) . '/lib/RateLimiter.php';
require_once dirname(__DIR__) . '/lib/LoggerService.php';

add_hook('DailyCronJob', 1, function ($vars) {
    try {
        // Clear old IP attempts (older than 48 hours)
        RateLimiter::pruneOldAttempts(48);

        // Clear old logs (older than 30 days)
        LoggerService::pruneOldLogs(30);
    } catch (\Exception $e) {
        // Silent failure so it does not break the main WHMCS cron
    }
});
