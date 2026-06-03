<?php

namespace branix\WhmcsInvoiceRecovery;

class DocumentValidator
{
    /**
     * Cleans a document formatting, keeping only digits.
     */
    public static function clean(string $doc): string
    {
        return preg_replace('/[^0-9]/', '', $doc) ?? '';
    }

    /**
     * Validates if a string is a valid CPF (Brazilian Individual Taxpayer ID).
     */
    public static function validateCpf(string $cpf): bool
    {
        $cpf = self::clean($cpf);

        if (strlen($cpf) !== 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validates if a string is a valid CNPJ (Brazilian Corporate Taxpayer ID).
     */
    public static function validateCnpj(string $cnpj): bool
    {
        $cnpj = self::clean($cnpj);

        if (strlen($cnpj) !== 14 || preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j === 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto)) {
            return false;
        }

        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j === 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;

        return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
    }
}
