<?php

namespace SeStep\SettingsDoctrine\Pools;

use Kdyby\Doctrine\Entities\Attributes\Identifier;
use SeStep\SettingsInterface\Pools\IPoolItem;

/**
 * Class APoolItem
 * @package SeStep\SettingsDoctrine\Pools
 *
 * @ORM\Entity
 * @ORM\Table(name="options__pool_item")
 *
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn("type", columnDefinition="ENUM('string', 'int')")
 * @ORM\DiscriminatorMap({"string" = "OptionTypeString", "bool" = "OptionTypeBool", "int" = "OptionTypeInt"})
 */
abstract class APoolItem implements IPoolItem
{
    use Identifier;

    public function getCaption()
    {
        if($val = $this->getValue()){
            return $val;
        }

        return $this->getKey();
    }
}
