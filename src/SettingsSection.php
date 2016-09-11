<?php

namespace Thoronir42\Settings;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Thoronir42\Model\BaseEntity;

/**
 * Class SettingsSection
 * @package App\Model\Settings
 *
 * @ORM\Entity
 * @ORM\Table("settings_section")
 *
 * @property		string $title
 * @property		string $domain
 *
 * @property		SettingsSection $parent_section
 * @property		ArrayCollection $subsections
 * @property		ArrayCollection $options
 */
class SettingsSection extends BaseEntity
{
	use Identifier;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $title;

	/**
	 * @ORM\Column(type="string", length=320)
	 */
	protected $domain;

	/**
	 * @ORM\ManyToOne(targetEntity="SettingsSection")
	 * @ORM\JoinColumn(name="parent_section_id", referencedColumnName="id")
	 */
	protected $parent_section;

	/**
	 * @ORM\OneToMany(targetEntity="SettingsSection", mappedBy="parent_section")
	 */
	protected $subsections;

	/**
	 * @ORM\OneToMany(targetEntity="Thoronir42\Settings\Options\AOption", mappedBy="section")
	 */
	protected $options;
}
