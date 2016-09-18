<?php

namespace SeStep\SettingsDoctrine\Options;

use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/**
 * @ORM\Entity
 */
class OptionTypeString extends AOption
{
	/** @ORM\Column(type="string", length=512)  */
	protected $string;

	/**
	 * @return int
	 */
	public function getValue()
	{
		return $this->string;
	}

	/**
	 * @param string $string
	 */
	public function setValue($string)
	{
		if(!is_string($string)){
			throw new InvalidArgumentException('String option must not receive a ' . gettype($string) .' value');
		}
		$this->string = $string;
	}
}
