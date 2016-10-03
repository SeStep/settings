<?php

namespace SeStep\SettingsDoctrine\Pools;

use SeStep\SettingsDoctrine\Pools\Traits\IntKey;
use SeStep\SettingsDoctrine\Pools\Traits\StringValue;


/**
 * Class PoolItemString
 * @package SeStep\SettingsDoctrine\Pools
 *
 * @ORM\Entity
 */
class PoolItemString extends APoolItem
{
    use IntKey;
    use StringValue;

    /**
     * PoolItemString constructor.
     * @param string $key
     * @param string|null $value
     */
    public function __construct($key, $value = null)
    {
        $this->setKey($key);
        $this->setValue($value);
    }

}
