<?php

namespace SeStep\SettingsDoctrine\Options;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\InvalidArgumentException;
use SeStep\Model\BaseEntity;
use SeStep\SettingsInterface\DomainLocator;
use SeStep\SettingsInterface\Exceptions\NotFoundException;
use SeStep\SettingsInterface\Options\IOption;
use SeStep\SettingsInterface\Options\IOptionsSection;
use SeStep\SettingsInterface\Options\ReadOnlyOption;

/**
 * Class SettingsSection
 * @package App\Model\Settings
 *
 * @ORM\Entity
 * @ORM\Table("options__section")
 */
class OptionsSection extends BaseEntity implements IOptionsSection
{
    use Identifier;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $caption;
    /**
     * @var string
     * @ORM\Column(type="string", length=320, nullable=true)
     */
    protected $domain;
    /**
     * @var string
     * @ORM\Column(type="string", length=48)
     */
    protected $name;
    /**
     * @var OptionsSection
     * @ORM\ManyToOne(targetEntity="OptionsSection")
     * @ORM\JoinColumn(name="parent_section_id", referencedColumnName="id")
     */
    protected $parentSection;
    /**
     * @var OptionsSection[]|Collection
     * @ORM\OneToMany(targetEntity="OptionsSection", mappedBy="parentSection")
     */
    protected $subsections;
    /**
     * @var AOption[]|Collection
     * @ORM\OneToMany(targetEntity="AOption", mappedBy="section")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $options;

    public function __construct($name, OptionsSection $parent = null)
    {
        $this->options = new ArrayCollection();
        $this->subsections = new ArrayCollection();

        $this->setName($name);
        $this->setParentSection($parent);
    }

    /** @return ReadOnlyOption[] */
    public function getOptions()
    {
        $return = [];
        foreach ($this->options as $option) {
            $return[$option->name] = new ReadOnlyOption($option);
        }

        return $return;
    }

    /**
     * @param string $name
     * @param string $domain
     * @param bool $adjust_locator
     * @throws NotFoundException
     * @return AOption
     */
    public function getOption($name, $domain = '', $adjust_locator = true)
    {
        if ($adjust_locator) {
            $locator = DomainLocator::create($name, $domain);
            $name = $locator->name;
            $domain = $locator->domain;
        }

        if ($domain) {
            $domainLocator = DomainLocator::create($domain);
            $section = $this->getSection($this->domain . $domainLocator->domainStart);

            return $section->getOption($name, $domainLocator->domainRest, false);
        }

        foreach ($this->options as $option) {
            if ($option->getName() == $name) {
                return $option;
            }
        }

        throw NotFoundException::option($name, $this->domain);
    }

    /**
     * @param IOption|string $name
     * @param string $domain
     * @return mixed
     */
    public function getValue($name, $domain = '')
    {
        $option = $this->getOption($name, $domain);

        return $option->getValues();
    }

    /**
     * @param string|IOption $option
     * @param string $domain
     * @return bool
     */
    public function removeOption($option, $domain = '')
    {
        $name = is_string($option) ? $option : $option->getName();
        foreach ($this->options as $opt) {
            if ($opt->getName() == $name) {
                $this->options->removeElement($opt);

                return true;
            }
        }

        return false;
    }

    /**
     * @return IOptionsSection[]
     */
    public function getSections()
    {
        return $this->subsections->toArray();
    }

    /**
     * @param string $domain
     * @return OptionsSection
     * @throws NotFoundException Occurs when subsection of defined domain does not exist.
     */
    public function getSection($domain)
    {
        foreach ($this->subsections as $section) {
            if ($section->getDomain() == $domain) {
                return $section;
            }
        }
        throw NotFoundException::section($domain, $this->caption . "($this->domain)");
    }

    /** @return string */
    public function getCaption()
    {
        return $this->caption;
    }

    /** @param string $caption */
    public function setCaption($caption)
    {
        $this->caption = $caption;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    /** @param string $domain */
    private function setDomain($domain = null)
    {
        if ($domain && !is_string($domain)) {
            throw new InvalidArgumentException("Domain must be of type string.");
        }
        $this->domain = $domain;
    }

    /** @return string */
    public function getName()
    {
        return $this->name;
    }

    /** @param string $name */
    public function setName($name)
    {
        $this->name = $name;
    }


    public function getParentSection()
    {
        return $this->parentSection;
    }

    public function setParentSection(OptionsSection $parent = null)
    {
        $this->parentSection = $parent;
        if ($parent) {
            $this->setDomain(DomainLocator::concatFQN($parent->getFQN()));
        } else {
            $this->setDomain(null);
        }
    }

    public function addSubsection(OptionsSection $section)
    {
        if (!$this->subsections->contains($section)) {
            $this->subsections->add($section);
        }
        $section->setParentSection($this);
    }

    public function removeSubsection(OptionsSection $section)
    {
        $this->subsections->removeElement($section);
        $section->setParentSection(null);
    }

    /**
     * Returns fully qualified name. That is in most cases concatenated getDomain() and getName().
     * @return string
     */
    public function getFQN()
    {
        return DomainLocator::concatFQN($this->getName(), $this->getDomain());
    }
}
