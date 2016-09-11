<?php

namespace Thoronir42\Settings;


use Nette\InvalidArgumentException;
use Nette\InvalidStateException;

/**
 * @property        SettingsSection $general
 * @property        SettingsSection $quotes
 */
class Settings
{
    use SettingsLookup;

    /** @var SettingsSection[] */
    private $section_entities;


    /** @var Options */
    private $options;
    /** @var SettingsSections */
    private $sections;

    public function __construct(Options $options, SettingsSections $sections)
    {
        $this->sections = $sections;
        $this->options = $options;
    }

    public function fetchAll()
    {
        return $this->options->findAll();
    }

    /**
     * @param string $domain
     * @return SettingsSection
     */
    public function getSection($domain)
    {
        $section = $this->sections->findByDomain($domain);
        if (!$section) {
            throw new InvalidStateException("Section $domain could not be found");
        }

        return $section;
    }

    /**
     * @param string $domain
     * @return AOption
     */
    public function getOption($domain)
    {
        $option = $this->options->findByDomain($domain);
        if (!$option) {
            throw new InvalidArgumentException("Option $domain does not exist.");
        }

        return $option;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        $option = $this->getOption($name);
        $value = $option->getValue();

        return $value;

    }

    public function set($name, $value)
    {
        $option = $this->getOption($name);
        $option->setValue($value);
        $this->options->save($option);
    }
}
