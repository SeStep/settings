<?php

namespace SeStep\SettingsDoctrine\Pools;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use SeStep\Model\BaseEntity;
use SeStep\SettingsInterface\Exceptions\NotFoundException;
use SeStep\SettingsInterface\OptionHelper;
use SeStep\SettingsInterface\Pools\IPool;
use Doctrine\ORM\Mapping as ORM;
use SeStep\SettingsInterface\Pools\IPoolItem;


/**
 * Class APool
 * @package SeStep\SettingsDoctrine\Pools
 *
 * @ORM\Entity
 * @ORM\Table(name="options__pool")
 */
class Pool extends BaseEntity implements IPool
{
    use Identifier;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $type;

    /**
     * @var APoolItem[]|Collection
     * @ORM\OneToMany(targetEntity="APoolItem", mappedBy="pool", indexBy="key")
     */
    protected $items;


    /**
     * Pool constructor.
     * @param string $name
     * @param string $type
     * @throws \InvalidArgumentException If the type is not valid
     */
    public function __construct($name, $type)
    {
        OptionHelper::validateType($type, true);

        $this->items = new ArrayCollection();
    }

    /**
     * @param mixed $key
     * @throws NotFoundException
     * @return mixed
     */
    public function get($key)
    {
        return $this->getItem($key)->getValue();
    }

    /**
     * @param mixed $key
     * @param mixed $value
     * @return mixed|null previously set value
     */
    public function set($key, $value)
    {
        return $this->getItem($key)->setValue($value);
    }

    /**
     * @param mixed[] $values key-value array
     * @return void
     */
    public function setMany($values)
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * @return mixed[] key-value array
     */
    public function getValues()
    {
        $values = [];
        foreach ($this->items as $item){
            $values[$item->getKey()] = $item->getValue();
        }
    }

    /**
     * @param mixed $key
     * @return mixed|null previously set value
     */
    public function remove($key)
    {
        /** @var IPoolItem $item */
        $item = $this->remove($key);

        return $item ? $item->getValue() : null;
    }

    /**
     * @return mixed[] key-value array
     */
    public function clear()
    {
        $values = $this->getValues();

        $this->items->clear();

        return $values;
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->items->count();
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->items->isEmpty();
    }

    /**
     * @param mixed $key
     * @return APoolItem
     * @throws NotFoundException
     */
    private function getItem($key)
    {
        $value = $this->items->get($key);
        if (!$value) {
            throw NotFoundException::poolValue($this->name, $key);
        }

        return $value;
    }
}
