<?php

namespace SeStep\SettingsDoctrine\Options;


use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use SeStep\Model\BaseEntity;
use SeStep\SettingsInterface\Options\IOption;

/**
 * @ORM\Entity
 * @ORM\Table(name="options_value")
 *
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn("option_type", columnDefinition="ENUM('string', 'bool', 'int')")
 * @ORM\DiscriminatorMap({"string" = "OptionTypeString", "bool" = "OptionTypeBool", "int" = "OptionTypeInt"})
 *
 * @property-read    int $id
 * @property        string $name
 * @property        OptionsSection $section
 */
abstract class AOption extends BaseEntity implements IOption
{
    use Identifier;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $caption;
    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $name;
    /**
     * @var OptionsSection
     * @ORM\ManyToOne(targetEntity="OptionsSection")
     * @ORM\JoinColumn(name="parent_section_id", referencedColumnName="id")
     */
    protected $section;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return OptionsSection
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * @param OptionsSection $section
     */
    public function setSection($section)
    {
        $this->section = $section;
    }


    public abstract function getValue();

    public abstract function setValue($value);

    /** @return string */
    public function getCaption()
    {
        return $this->caption;
    }

    public function setCaption($caption)
    {
        $caption = $caption ?: '';
        if (!is_string($caption)) {
            throw new InvalidArgumentException('Caption must be a string, ' . gettype($caption) . ' given');
        }
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->section->getDomain();
    }

    /**
     * Returns fully qualified name. That is in most cases concatenated getDomain() and getName().
     * @return mixed
     */
    public function getFQN()
    {
        return $this->getDomain() . '.' . $this->getName();
    }

    /**
     * @return string
     */
    public abstract function getType();

    /**
     * @return boolean
     */
    public function hasValues()
    {
        return false;
    }

    /**
     * @return string[]|int[]
     */
    public function getValues()
    {
        return [];
    }
}
