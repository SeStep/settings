<?php

namespace SeStep\SettingsDoctrine\Pools\Traits;


use Kdyby\Doctrine\InvalidArgumentException;

trait StringValue
{
    /**
     * @var string|null
     * @ORM\Column(type=
     */
    protected $value;

    /**
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        if(!is_string($value)){
            throw new InvalidArgumentException("Value must be a string");
        }
        $prev = $this->getValue();
        $this->value = $value;

        return $prev;

    }
}
