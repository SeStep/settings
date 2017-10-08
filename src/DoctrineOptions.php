<?php

namespace SeStep\SettingsDoctrine;

use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Kdyby\Doctrine\InvalidArgumentException;
use Kdyby\Doctrine\UnexpectedValueException;
use LogicException;
use Nette\Utils\Strings;
use RuntimeException;
use SeStep\Model\BaseDoctrineService;
use SeStep\SettingsDoctrine\Options\AOption;
use SeStep\SettingsDoctrine\Options\OptionsSection;
use SeStep\SettingsDoctrine\Options\OptionTypeBool;
use SeStep\SettingsDoctrine\Options\OptionTypeInt;
use SeStep\SettingsDoctrine\Options\OptionTypeString;
use SeStep\SettingsInterface\DomainLocator;
use SeStep\SettingsInterface\Exceptions\NotFoundException;
use SeStep\SettingsInterface\Options\IEditableOptionsSection;
use SeStep\SettingsInterface\Options\IOption;
use SeStep\SettingsInterface\Options\IOptions;
use SeStep\SettingsInterface\Options\IOptionsSection;
use SeStep\SettingsInterface\Options\ReadOnlyOption;

class DoctrineOptions extends BaseDoctrineService implements IOptions, IEditableOptionsSection
{
    private static $typeMap = [
        IOptions::TYPE_STRING => OptionTypeString::class,
        IOptions::TYPE_INT    => OptionTypeInt::class,
        IOptions::TYPE_BOOL   => OptionTypeBool::class,
    ];
    /** @var EntityRepository */
    protected $sections;
    private $maxSubsectionLevel = 4;

    public function __construct(EntityManager $em)
    {
        parent::__construct(AOption::class, $em);
        $this->sections = $em->getRepository(OptionsSection::class);
    }

    /**
     * @param OptionsSection $section
     * @return AOption[]
     */
    public function findBySection(OptionsSection $section)
    {
        return $this->repository->findBy(['section' => $section]);
    }

    /** @return ReadOnlyOption[] */
    public function getOptions()
    {
        $return = [];
        /** @var AOption $option */
        foreach ($this->repository->findBy(['section' => null], ['name' => 'ASC']) as $option) {
            $return[$option->getFQN()] = new ReadOnlyOption($option);
        }

        return $return;
    }

    /**
     * @param string|IOption         $name
     * @param string|IOptionsSection $domain
     * @return AOption
     * @throws NotFoundException
     */
    public function getOption($name, $domain = '')
    {
        $option = $this->findOption($name, $domain);
        if (!$option) {
            throw NotFoundException::option($name, $domain);
        }

        return $option;
    }

    /**
     * @param string|IOption         $name
     * @param string|IOptionsSection $domain
     * @return AOption
     */
    private function findOption($name, $domain = null)
    {
        if ($name instanceof IOption) {
            $name = $name->getName();
        }

        $locator = DomainLocator::create($name, $domain);

        $section = $this->getSection($locator->domain);

        $locator->name = AOption::sanitizeName($locator->name);

        $option = $this->repository->findOneBy(['section' => $section, 'name' => $locator->name]);

        return $option;
    }

    /**
     * @param mixed          $value
     * @param IOption|string $option
     * @param string         $domain
     * @return void
     *
     * @throws NotFoundException
     * @throws \Exception
     */
    public function setValue($value, $option, $domain = '')
    {
        if (!($option instanceof AOption)) {
            if (!is_string($option)) {
                throw new UnexpectedValueException('Argument option expected to be string or instance ' .
                    'of ' . AOption::class . ', ' . gettype($option) . ' given');
            }
            $option = $this->getOption($option, $domain);
        }
        $option->setValue($value);

        $this->em->persist($option);
        $this->em->flush();
    }

    /**
     * @param IOption|string $name
     * @param string         $domain
     * @return mixed
     * @throws RuntimeException in case the requested option does not exist
     */
    public function getValue($name, $domain = '')
    {
        $option = $this->getOption($name, $domain);

        return $option->getValue();
    }

    /**
     * @return IOptionsSection[]
     */
    public function getSections()
    {

        $sections = $this->sections->findBy(['parentSection' => null], ['domain' => 'ASC']);

        return $sections;
    }

    /**
     * @param string                 $name
     * @param IOptionsSection|string $domain
     * @return IOptionsSection|null
     * @throws NotFoundException
     */
    public function getSection($name = '', $domain = null)
    {
        $locator = DomainLocator::create($name, $domain);

        if (!$locator->name) {
            return null;
        }

        $args = ['name' => $locator->name];
        if ($locator->domain) {
            $args['domain'] = $locator->domain;
        }

        $section = $this->sections->findOneBy($args);
        if (!$section) {
            throw NotFoundException::section($locator);
        }

        return $section;
    }

    /**
     * @param IOption|string $option
     * @param string         $domain
     */
    public function removeOption($option, $domain = '')
    {
        $option = $this->getOption($option, $domain);

        $this->em->remove($option);
    }

    public function createOption($type, $name, $value, $caption, $section = null)
    {
        if (Strings::contains($name, IOptionsSection::DOMAIN_DELIMITER)) {
            throw new InvalidArgumentException('Option name must not contain section delimiter (' .
                IOptionsSection::DOMAIN_DELIMITER . "), $name given.");
        }

        if (!isset(self::$typeMap[$type])) {
            throw new InvalidArgumentException("Option type $type is not supported");
        }

        $option = $this->findOption($name, $section);
        if ($option) {
            return $option;
        }


        /** @var AOption $option */
        $option = new self::$typeMap[$type]($name);

        $option->setCaption($caption);
        $option->setValue($value);
        $option->setSection($section);

        return $option;
    }

    /**
     * @param string              $name
     * @param string              $caption
     * @param OptionsSection|null $parent
     *
     * @return OptionsSection
     */
    public function findOrCreateSection($name, $caption = '', OptionsSection $parent = null)
    {
        $locator = DomainLocator::create($name, $parent);

        if ($locator->depth > $this->maxSubsectionLevel) {
            throw new LogicException("Section domain $locator->fqn would result in subsection level of " .
                "$locator->depth, maximum of $this->maxSubsectionLevel is allowed.");
        }
        if (!$parent && $locator->domain) {
            $parent = $this->findOrCreateSection($locator->domain);
        }

        $section = $this->sections->findOneBy(['name' => $locator->name, 'domain' => $locator->domain]);
        if (!$section) {
            $section = new OptionsSection($locator->name, $parent);
        }
        $section->setCaption($caption);
        $this->save($section);

        return $section;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return '';
    }

    /**
     * Returns fully qualified name. That is in most cases concatenated getDomain() and getName().
     * @return mixed
     */
    public function getFQN()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return '';
    }

    /** @return string */
    public function getCaption()
    {
        return 'Option stuffs';
    }
}
