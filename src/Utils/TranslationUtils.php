<?php

final class TranslationUtils
{
    /**
     * @param string $propertyPath
     *
     * @return string
     */
    public static function getLabel(string $propertyPath)
    {
        preg_match_all('/([A-Z])?([a-z]+)/', $propertyPath, $matches);

        $parts = array_map('strtolower', $matches[0]);

        return implode('_', $parts);
    }
}