<?php

namespace AdminBundle\Twig;

use AdminBundle\Admin\SettingManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class SettingExtension extends AbstractExtension
{
    /**
     * @var SettingManager
     */
    private $settingManager;

    /**
     * @param SettingManager $settingManager
     */
    public function __construct(SettingManager $settingManager)
    {
        $this->settingManager = $settingManager;
    }

    /**
     * @return array|TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('get_admin_setting', [$this, 'getAdminSetting']),
        ];
    }

    /**
     * @param string $name
     *
     * @return array
     */
    public function getAdminSetting(string $name)
    {
        return $this->settingManager->get($name);
    }
}