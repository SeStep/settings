<?php

namespace SeStep\SettingsDoctrine\Options;

use Doctrine\ORM\Mapping as ORM;

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
        return (boolean)$this->int;
    }

    /** @param boolean $bool */
    public function setValue($bool)
    {
        $this->bool = (int)((boolean)$bool);
    }

    public function getValues()
    {
        return [
            true => 'Yes',
            false => 'No',
        ];
    }
}
