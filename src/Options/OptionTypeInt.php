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
	 * @param int $value
	 */
	public function setValue($value)
	{
		if(!is_numeric($value)){
			throw new InvalidArgumentException(sprintf('Int option must not receive value \'%s\' of type %s', gettype($value), $value));
		}
		$this->int_val = (int)$value;
	}


    /**
     * @return string
     */
    public function getType()
    {
        return IOptions::TYPE_INT;
    }
}
