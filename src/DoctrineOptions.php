<?php

namespace SeStep\SettingsDoctrine;

use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use RuntimeException;
use SeStep\Model\BaseDoctrineService;
use SeStep\SettingsDoctrine\Options\AOption;
use SeStep\SettingsDoctrine\Options\OptionsSection;
use SeStep\SettingsInterface\DomainLookup;
use SeStep\SettingsInterface\Options\IOption;
use SeStep\SettingsInterface\Options\IOptions;
use SeStep\SettingsInterface\Options\IOptionsSection;
use SeStep\SettingsInterface\Options\ReadOnlyOption;

class DoctrineOptions extends BaseDoctrineService implements IOptions
{
    use DomainLookup;

    /** @var EntityRepository */
    protected $sections;

    public function __construct($class, EntityManager $em)
    {
        parent::__construct($class, $em);
        $this->sections = $em->getRepository(OptionsSection::class);
    }

    /**
     * @return AOption[]
     */
    public function findAllOrdered()
    {
        $qb = $this->getQueryBuilder('opt');
        $qb->addSelect('sect')
            ->innerJoin(OptionsSection::class, 'sect', 'ON', 'opt.section = sect.id')
            ->orderBy(['sect.domain' => 'ASC', 'opt.name' => 'ASC']);

        return $qb->getQuery()->getResult();
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
        foreach ($this->findAllOrdered() as $option) {
            $return[$option->getSection() . '.' . $option->getName()] = new ReadOnlyOption($option);
        }
    }

    /**
     * @param string $name
     * @param string $domain
     * @return AOption
     */
    private function getOption($name, $domain = '')
    {
        $parts = $this->splitLocator($name, $domain);

        $section = $parts['domain'] ? $this->getSection($parts['domain']) : null;

        $option = $this->repository->findOneBy(['section' => $section, 'name' => $parts['name']]);
        if (!$option) {
            throw new RuntimeException("Option $parts[name] was not found.");
        }

        return $option;
    }

    /**
     * @param IOption|string $option
     * @return void
     * @throws RuntimeException in case the requested option does not exist
     */
    public function setValue($value, $name, $domain = '')
    {
        $option = $this->getOption($name, $domain);
        $option->setValue($value);

        $this->em->persist($option);
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
        return $this->sections->findBy([], ['domain' => 'ASC', ''])
    }

    /**
     * @param string $domain
     * @return IOptionsSection
     * @throws RuntimeException Occurs when subsection of defined domain does not exist.
     */
    public function getSection($domain)
    {
        $section = $this->sections->findOneBy(['domain' => $domain]);
        if (!$section) {
            throw new RuntimeException("OptionsSection $domain was not found.");
        }
    }
}
