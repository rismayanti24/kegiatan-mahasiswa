<?php

if (! function_exists('statusKuota')) {
    function statusKuota(int $kuota): string
    {
        if ($kuota >= 75) {
            return 'Besar';
        }

        return 'Terbatas';
    }
}