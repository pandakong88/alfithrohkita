<?php

if (!function_exists('format_role_name')) {
    function format_role_name(string $name): string
    {
        // Hapus angka di akhir (_27 atau @27)
        $name = preg_replace('/([_@]\d+)$/', '', $name);

        // Ganti underscore / @ jadi spasi
        $name = str_replace(['_', '@'], ' ', $name);

        return ucwords(trim($name));
    }
}

// cara panggil nya gini {{ format_role_name($role->name) }}