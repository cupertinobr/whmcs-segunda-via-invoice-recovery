<?php

namespace branix\WhmcsInvoiceRecovery;

use WHMCS\Database\Capsule;

class RateLimiter
{
    /**
     * Checks if the IP is temporarily blocked due to too many failed attempts.
     * Returns the number of minutes remaining if blocked, or 0 if allowed.
     */
    public static function checkLockout(string $ip, int $maxAttempts, int $lockoutMinutes): int
    {
        if ($maxAttempts <= 0) {
            return 0;
        }

        $record = Capsule::table('mod_invoice_recovery_attempts')->where('ip', $ip)->first();

        if ($record) {
            $lastAttempt = strtotime($record->last_attempt);
            $diffMinutes = (time() - $lastAttempt) / 60;

            if ($record->attempts >= $maxAttempts && $diffMinutes < $lockoutMinutes) {
                return (int)ceil($lockoutMinutes - $diffMinutes);
            }

            // If the lockout period has expired, reset the attempt counter
            if ($diffMinutes >= $lockoutMinutes) {
                self::resetAttempts($ip);
            }
        }

        return 0;
    }

    /**
     * Increments the number of failed attempts for a given IP.
     */
    public static function incrementAttempts(string $ip): void
    {
        $exists = Capsule::table('mod_invoice_recovery_attempts')->where('ip', $ip)->exists();
        if ($exists) {
            Capsule::table('mod_invoice_recovery_attempts')->where('ip', $ip)->update([
                'attempts' => Capsule::raw('attempts + 1'),
                'last_attempt' => date('Y-m-d H:i:s')
            ]);
        } else {
            Capsule::table('mod_invoice_recovery_attempts')->insert([
                'ip' => $ip,
                'attempts' => 1,
                'last_attempt' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Resets the attempt counter for a given IP.
     */
    public static function resetAttempts(string $ip): void
    {
        Capsule::table('mod_invoice_recovery_attempts')->where('ip', $ip)->update(['attempts' => 0]);
    }

    /**
     * Cleans up old attempts (useful for cron jobs).
     */
    public static function pruneOldAttempts(int $hours = 48): void
    {
        $cutoff = date('Y-m-d H:i:s', time() - ($hours * 3600));
        Capsule::table('mod_invoice_recovery_attempts')
            ->where('last_attempt', '<', $cutoff)
            ->delete();
    }
}
