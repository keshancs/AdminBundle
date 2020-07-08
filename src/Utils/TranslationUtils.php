<?php

namespace AdminBundle\Utils;

final class TranslationUtils
{
    /**
     * @param string $string
     *
     * @return string
     */
    public static function camelCaseToSnakeCase(string $string)
    {
        preg_match_all('/([A-Z])?([a-z]+)/', $string, $matches);

        $parts = array_map('strtolower', $matches[0]);

        return implode('_', $parts);
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public static function snakeCaseToCamelCase(string $string)
    {
        $parts     = explode('_', $string);
        $firstPart = array_shift($parts);
        $parts     = array_map('ucfirst', $parts);

        return lcfirst($firstPart) . implode('', $parts);
    }
}
