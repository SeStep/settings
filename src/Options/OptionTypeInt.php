<?php

namespace SeStep\SettingsDoctrine\Options;

use Doctrine\ORM\Mapping as ORM;
use Nette\InvalidArgumentException;
use SeStep\SettingsInterface\Options\IOptions;

/**
 * @ORM\Entity
 */
class OptionTypeInt extends AOption
{
	/** @ORM\Column(type="integer")  */
	protected $int_val;

	/**
	 * @return int
	 */
	public function getValue()
	{
		return $this->int_val;
	}

	/**
	 * @param int $int
	 */
	public function setValue($int)
	{
		if(!is_integer($int)){
			throw new InvalidArgumentException('Int option must not receive a ' . gettype($int) .' value');
		}
		$this->int_val = $int;
	}


    /**
     * @return string
     */
    public function getType()
    {
        return IOptions::TYPE_INT;
    }
}
