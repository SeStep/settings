<?php

namespace SeStep\Settings\Options;

use Doctrine\ORM\Mapping as ORM;

/**
 * @param	bool	$value
 *
 * @ORM\Entity
 */
class OptionBool extends AOption
{
	/** @ORM\Column(type="boolean")  */
	protected $bool;

	/**
	 * @return boolean
	 */
	public function getValue()
	{
		return $this->bool;
	}

	/**
	 * @param boolean $bool
	 */
	public function setValue($bool)
	{
		$this->bool = !!$bool;
	}

	public function getValues(){
		return [
			true => 'Yes',
			false => 'No',
		];
	}
}
