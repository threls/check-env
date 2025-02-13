<?php

if (!function_exists('getEnvKeyFromFile')) {
    function getEnvKeyFromFile($fileName, $key): ?string
    {
        $filePath = base_path($fileName);
        if (!file_exists($filePath)) {
            return null;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (str_starts_with($line, $key . '=')) {
                return trim(str_replace($key . '=', '', $line), '"');
            }
        }

        return null;
    }
}
