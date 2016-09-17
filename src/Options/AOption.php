<?php

namespace SeStep\Settings\Options;


use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use SeStep\Model\BaseEntity;
use SeStep\Settings\SettingsSection;

/**
 * @ORM\Entity
 * @ORM\Table(name="option")
 *
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn("option_type", columnDefinition="ENUM('string', 'bool', 'int')")
 * @ORM\DiscriminatorMap({"string" = "OptionString", "bool" = "OptionBool", "int" = "OptionInt"})
 *
 * @property		int $id
 * @property		string $domain
 * @property		string $title
 * @property		SettingsSection $section
 */
abstract class AOption extends BaseEntity
{
	const TYPE_STRING = 'string';
	const TYPE_BOOL = 'bool';
	const TYPE_INT = 'int';

	use Identifier;

	/** @ORM\Column(type="string") */
	protected $domain;

	/** @ORM\Column(type="string") */
	protected $title;

	/**
	 * @ORM\ManyToOne(targetEntity="Thoronir42\Settings\SettingsSection")
	 * @ORM\JoinColumn(name="section_id", referencedColumnName="id")
	 */
	protected $section;

	/**
	 * @return string
	 */
	public function getDomain()
	{
		return $this->domain;
	}

	/**
	 * @param string $domain
	 */
	public function setDomain($domain)
	{
		$this->domain = $domain;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * @return mixed
	 */
	public function getSection()
	{
		return $this->section;
	}

	/**
	 * @param mixed $section
	 */
	public function setSection($section)
	{
		$this->section = $section;
	}

	

	public abstract function getValue();

	public abstract function setValue($value);


}
