<?php

if (! function_exists('bdt')) {
    /** Format a number as Bangladeshi Taka, e.g. ৳18,655 or ৳217,591.92 */
    function bdt(float|int|string|null $amount, int $decimals = 0): string
    {
        return '৳'.number_format((float) $amount, $decimals);
    }
}

if (! function_exists('signed_bdt')) {
    /** ▲ +৳180 / ▼ −৳90 / — ৳0 */
    function signed_bdt(float|int $change, int $decimals = 0): string
    {
        if (round($change, $decimals) == 0) {
            return '— ৳0';
        }

        return ($change > 0 ? '▲ +' : '▼ −').'৳'.number_format(abs($change), $decimals);
    }
}
