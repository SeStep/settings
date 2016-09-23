<?php

namespace SeStep\SettingsDoctrine\Options;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\NotImplementedException;
use RuntimeException;
use SeStep\Model\BaseEntity;
use SeStep\SettingsInterface\DomainLookup;
use SeStep\SettingsInterface\Options\IOption;
use SeStep\SettingsInterface\Options\IOptionsSection;
use SeStep\SettingsInterface\Options\ReadOnlyOption;

/**
 * Class SettingsSection
 * @package App\Model\Settings
 *
 * @ORM\Entity
 * @ORM\Table("options__section")
 *
 * @property        string $caption
 * @property        string $domain
 *
 * @property        OptionsSection $parentSection
 * @property        Collection|OptionsSection[] $subsections
 * @property        Collection|AOption[] $options
 */
class OptionsSection extends BaseEntity implements IOptionsSection
{
    use Identifier;
    use DomainLookup;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $caption;
    /**
     * @var string
     * @ORM\Column(type="string", length=320)
     */
    protected $domain;
    /**
     * @var OptionsSection
     * @ORM\ManyToOne(targetEntity="OptionsSection")
     * @ORM\JoinColumn(name="parent_section_id", referencedColumnName="id")
     */
    protected $parentSection;
    /**
     * @var OptionsSection[]|Collection
     * @ORM\OneToMany(targetEntity="SettingsSection", mappedBy="parent_section")
     */
    protected $subsections;
    /**
     * @var AOption[]|Collection
     * @ORM\OneToMany(targetEntity="SeStep\SettingsDoctrine\Options\AOption", mappedBy="section")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $options;

    public function __construct()
    {
        $this->options = new ArrayCollection();
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
     * @return AOption
     */
    protected function getOption($name, $domain = '', $adjust_locator = true)
    {
        if ($adjust_locator) {
            list($name, $domain) = $this->splitLocator($name, $domain);
        }

        if ($domain) {
            list($next, $rest) = $this->splitDomain($domain);
            $section = $this->getSection($this->domain . $next);

            return $section->getOption($name, $rest, false);
        }

        foreach ($this->options as $option) {
            if($option->getName() == $name){
                return $option;
            }
        }

        throw new RuntimeException("Option $name not found in section $this->domain");
    }


    /**
     * @param mixed $value
     * @param IOption|string $option
     * @param $domain
     * @return void
     */
    public function setValue($value, $option, $domain = '')
    {
        throw new NotImplementedException("To set option value use DoctrineOptions->setValue");
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
     * @throws RuntimeException Occurs when subsection of defined domain does not exist.
     */
    public function getSection($domain)
    {
        foreach ($this->subsections as $section) {
            if ($section->getDomain() == $domain) {
                return $section;
            }
        }
        throw new RuntimeException(sprintf('Section %s does not contain %s subsection',
            $this->title . "($this->domain)", $domain));
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }
}
