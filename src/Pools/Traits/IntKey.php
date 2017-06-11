<?php

namespace SeStep\SettingsDoctrine\Pools\Traits;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\InvalidArgumentException;

trait IntKey
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $key;

    public function setKey($key)
    {
        if (!is_int($key)) {
            throw new InvalidArgumentException('Key has to be an integer value');
        }

        $this->key = $key;
    }

    public function getKey()
    {
        return $this->key;
    }
}
