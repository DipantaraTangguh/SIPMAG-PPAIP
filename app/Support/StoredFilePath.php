<?php

namespace App\Support;

final class StoredFilePath
{
    public static function resolve(string $root, ?string $storedPath): ?string
    {
        if ($storedPath === null || $storedPath === '' || str_contains($storedPath, "\0")) {
            return null;
        }

        $normalizedPath = str_replace('\\', '/', $storedPath);
        if (
            str_starts_with($normalizedPath, '/')
            || preg_match('/^[A-Za-z]:\//', $normalizedPath) === 1
            || in_array('..', explode('/', $normalizedPath), true)
        ) {
            return null;
        }

        $resolvedRoot = realpath($root);
        $resolvedPath = realpath($root.DIRECTORY_SEPARATOR.$normalizedPath);

        if ($resolvedRoot === false || $resolvedPath === false || ! is_file($resolvedPath)) {
            return null;
        }

        $rootPrefix = rtrim($resolvedRoot, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        return str_starts_with($resolvedPath, $rootPrefix) ? $resolvedPath : null;
    }
}
