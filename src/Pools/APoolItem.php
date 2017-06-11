<?php

namespace SeStep\SettingsDoctrine\Pools;

use Kdyby\Doctrine\Entities\Attributes\Identifier;
use SeStep\SettingsInterface\Pools\IPoolItem;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class APoolItem
 * @package SeStep\SettingsDoctrine\Pools
 *
 * @ORM\Entity
 * @ORM\Table(name="options__pool_item")
 *
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn("type", columnDefinition="ENUM('string', 'int')")
 * @ORM\DiscriminatorMap({
 *     "string" = "SeStep\SettingsDoctrine\Pools\PoolItemString"
 * })
 */
abstract class APoolItem implements IPoolItem
{
    use Identifier;

    /**
     * @var Pool
     * @ORM\ManyToOne(targetEntity="Pool", inversedBy="items")
     */
    protected $pool;

    public function getCaption()
    {
        if($val = $this->getValue()){
            return $val;
        }

        return $this->getKey();
    }
}
