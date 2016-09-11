<?php

namespace Thoronir42\Settings;

/**
 * Class SettingsLookup
 * @package App\Model\Settings
 * @internal
 */
trait SettingsLookup
{
    protected function explodeDomain($domain)
    {
        $parts = explode('.', $domain);
        array_pop($parts);
        $section_domain = implode('.', $parts);

        return $section_domain;
    }
}
