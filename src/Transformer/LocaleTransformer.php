<?php

namespace AdminBundle\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Intl\Locales;

class LocaleTransformer implements DataTransformerInterface
{
    /**
     * @var string[]
     */
    private $locales;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->locales = Locales::getLocales();
    }

    /**
     * @param string $value
     *
     * @return array
     */
    public function transform($value)
    {
        if (null === $value) {
            return [];
        }

        $value   = json_decode($value, true);
        $locales = array_flip($this->locales);

        foreach ($value as $i => $locale) {
            $value[$i] = $locales[$locale];
        }

        return $value;
    }

    /**
     * @param array $value
     *
     * @return string
     */
    public function reverseTransform($value)
    {
        if (null === $value) {
            $value = [];
        }

        foreach ($value as $i => $locale) {
            $value[$i] = $this->locales[$locale];
        }

        return json_encode($value);
    }
}