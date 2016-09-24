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
use SeStep\SettingsInterface\Exceptions\OptionNotFoundException;
use SeStep\SettingsInterface\Exceptions\OptionsSectionNotFoundException;
use SeStep\SettingsInterface\Options\IOption;
use SeStep\SettingsInterface\Options\IOptions;
use SeStep\SettingsInterface\Options\IOptionsSection;
use SeStep\SettingsInterface\Options\ReadOnlyOption;

class DoctrineOptions extends BaseDoctrineService implements IOptions
{
    private static $typeMap = [
        IOptions::TYPE_STRING => OptionTypeString::class,
        IOptions::TYPE_INT => OptionTypeInt::class,
        IOptions::TYPE_BOOL => OptionTypeBool::class,
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
        foreach ($this->repository->findBy(['section' => null], ['name' => 'ASC']) as $option) {
            $return[$option->getFQN()] = new ReadOnlyOption($option);
        }

        return $return;
    }

    /**
     * @param string $name
     * @param string|IOptionsSection $domain
     * @return AOption
     * @throws OptionNotFoundException
     */
    public function getOption($name, $domain = '')
    {
        $option = $this->findOption($name, $domain);
        if (!$option) {
            throw new OptionNotFoundException($name, $domain);
        }

        return $option;
    }

    /**
     * @param string $name
     * @param string|IOptionsSection $domain
     * @return AOption
     */
    private function findOption($name, $domain = null)
    {
        $locator = DomainLocator::create($name, $domain);

        $section = $this->getSection($locator->domain);

        $locator->name = AOption::sanitizeName($locator->name);

        $option = $this->repository->findOneBy(['section' => $section, 'name' => $locator->name]);

        return $option;
    }

    /**
     * @param mixed $value
     * @param IOption|string $option
     * @return void
     * @throws RuntimeException in case the requested option does not exist
     */
    public function setValue($option, $value, $domain = '')
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
     * @param string $domain
     * @return mixed
     * @throws RuntimeException in case the requested option does not exist
     */
    public function getValue($name, $domain = '')
    {
        $option = $this->getOption($name, $domain);

        return $option->getValue();
    }

    /**
     * @param IOption|string $option
     * @return void
     */
    public function removeOption($option, $domain = '')
    {
        $option = $this->getOption($option, $domain);

        $this->em->remove($option);
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
     * @param string $name
     * @param IOptionsSection|string $domain
     * @return IOptionsSection|null
     * @throws OptionsSectionNotFoundException
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
            throw new OptionsSectionNotFoundException($locator);
        }

        return $section;
    }

    public function createOption($type, $name, $value, $caption, OptionsSection $section = null)
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

    public function createSection($name, $caption = '', OptionsSection $parent = null)
    {
        $locator = DomainLocator::create($name, $parent);

        if ($locator->depth > $this->maxSubsectionLevel) {
            throw new LogicException("Section domain $locator->fqn would result in subsection level of " .
                "$locator->depth, maximum of $this->maxSubsectionLevel is allowed.");
        }

        $section = $this->sections->findOneBy(['name' => $locator->name, 'domain' => $locator->domain]);
        if ($section) {
            return $section;
        }

        $return = $section = new OptionsSection($locator->name);
        $section->setCaption($caption);
        $save = [$section];

        while ($locator->domain) {
            $locator = DomainLocator::create($locator->domain);

            $ancestor = new OptionsSection($locator->name);
            $ancestor->addSubsection($section);

            $save[] = $ancestor;

            $section = $ancestor;
        }

        if ($parent) {
            $parent->addSubsection($section);
            $save[] = $parent;
        }

        $this->save($save);

        return $return;
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
