<?php

namespace SeStep\SettingsDoctrine\Options;

use Doctrine\ORM\Mapping as ORM;
use SeStep\SettingsInterface\Options\IOption;
use SeStep\SettingsInterface\Options\IOptions;

/**
 * @ORM\Entity
 *
 * @param   bool $value
 */
class OptionTypeBool extends OptionTypeInt
{
    /** @return boolean */
    public function getValue()
    {
        return (boolean)$this->int_val;
    }

    /** @param boolean $bool */
    public function setValue($bool)
    {
        $this->int_val = (int)((boolean)$bool);
    }

    public function hasValues()
    {
        return true;
    }

    public function getValues()
    {
        return [
            true => 'Yes',
            false => 'No',
        ];
    }

    /**
     * @return string
     */
    public function getType()
    {
        return IOptions::TYPE_BOOL;
    }
}
