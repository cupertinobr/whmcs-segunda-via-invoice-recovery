<?php

namespace branix\WhmcsInvoiceRecovery;

use WHMCS\Database\Capsule;

class LoggerService
{
    /**
     * Logs an activity to the audit trail log.
     */
    public static function log(string $ip, ?int $clientId, string $action, string $details = '', float $amount = 0.0): void
    {
        try {
            Capsule::table('mod_invoice_recovery_logs')->insert([
                'ip' => substr($ip, 0, 45),
                'client_id' => $clientId,
                'action' => substr($action, 0, 50),
                'details' => $details,
                'amount' => $amount,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            // Silently ignore failures so it doesn't disrupt the user's flow
        }
    }

    /**
     * Returns usage statistics for the admin dashboard.
     */
    public static function getMetrics(): array
    {
        $metrics = [
            'total_amount' => 0.0,
            'total_clicks' => 0,
            'successful_searches' => 0,
            'failed_searches' => 0,
            'ips_blocked' => 0,
        ];

        try {
            if (Capsule::schema()->hasTable('mod_invoice_recovery_logs')) {
                $metrics['total_amount'] = (float)Capsule::table('mod_invoice_recovery_logs')
                    ->where('action', 'payment_initiated')
                    ->sum('amount');

                $metrics['total_clicks'] = Capsule::table('mod_invoice_recovery_logs')
                    ->where('action', 'payment_initiated')
                    ->count();

                $metrics['successful_searches'] = Capsule::table('mod_invoice_recovery_logs')
                    ->where('action', 'search_success')
                    ->count();

                $metrics['failed_searches'] = Capsule::table('mod_invoice_recovery_logs')
                    ->where('action', 'search_failed')
                    ->count();
            }

            if (Capsule::schema()->hasTable('mod_invoice_recovery_attempts')) {
                // Get lockout attempt limit
                $limitAttempts = (int)Capsule::table('tbladdonmodules')
                    ->where('module', 'invoice_recovery')
                    ->where('setting', 'limit_attempts')
                    ->value('value');

                if ($limitAttempts <= 0) {
                    $limitAttempts = 5;
                }

                $metrics['ips_blocked'] = Capsule::table('mod_invoice_recovery_attempts')
                    ->where('attempts', '>=', $limitAttempts)
                    ->count();
            }
        } catch (\Exception $e) {
            // Silent failure
        }

        return $metrics;
    }

    /**
     * Cleans up old logs to prevent excessive table size.
     */
    public static function pruneOldLogs(int $days = 30): void
    {
        try {
            if (Capsule::schema()->hasTable('mod_invoice_recovery_logs')) {
                $cutoff = date('Y-m-d H:i:s', time() - ($days * 24 * 3600));
                Capsule::table('mod_invoice_recovery_logs')
                    ->where('created_at', '<', $cutoff)
                    ->delete();
            }
        } catch (\Exception $e) {
            // Silent failure
        }
    }
}
