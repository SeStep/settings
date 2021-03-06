<?php

namespace SeStep\SettingsDoctrine\Options;


use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Nette\Utils\Strings;
use SeStep\Model\BaseEntity;
use SeStep\SettingsInterface\DomainLocator;
use SeStep\SettingsInterface\Options\IOption;

/**
 * @ORM\Entity
 * @ORM\Table(name="options__value")
 *
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn("option_type", columnDefinition="ENUM('string', 'bool', 'int')")
 * @ORM\DiscriminatorMap({"string" = "OptionTypeString", "bool" = "OptionTypeBool", "int" = "OptionTypeInt"})
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
     * @var OptionsSection|null
     * @ORM\ManyToOne(targetEntity="OptionsSection")
     * @ORM\JoinColumn(name="parent_section_id", referencedColumnName="id")
     */
    protected $section;

    /**
     * AOption constructor.
     * @param string $name
     */
    public function __construct($name)
    {
        $this->setName($name);
    }

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
        $this->name = self::sanitizeName($name);
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
    public function setSection(OptionsSection $section = null)
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
        if (!is_string($caption)) {
            throw new InvalidArgumentException('Caption must be a string, ' . gettype($caption) . ' given');
        }

        $this->caption = $caption;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->section ? $this->section->getFQN() : '';
    }

    /**
     * Returns fully qualified name. That is in most cases concatenated getDomain() and getName().
     * @return mixed
     */
    public function getFQN()
    {
        return DomainLocator::concatFQN($this->getName(), $this->getDomain());
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

    public static function sanitizeName($name)
    {
        return Strings::replace($name, '%[-\.]%', '_');
    }
}
