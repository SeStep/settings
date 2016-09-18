<?php

namespace SeStep\SettingsDoctrine\Options;

use Doctrine\ORM\Mapping as ORM;
use Nette\InvalidArgumentException;

/**
 * @property	int		$value
 *
 * @ORM\Entity
 */
class OptionTypeInt extends AOption
{
	/** @ORM\Column(type="integer")  */
	protected $int;

	/**
	 * @return int
	 */
	public function getValue()
	{
		return $this->int;
	}

	/**
	 * @param int $int
	 */
	public function setValue($int)
	{
		if(!is_integer($int)){
			throw new InvalidArgumentException('Int option must not receive a ' . gettype($int) .' value');
		}
		$this->int = $int;
	}


}
