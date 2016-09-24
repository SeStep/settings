<?php

namespace SeStep\SettingsDoctrine\Options;

use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use SeStep\SettingsInterface\Options\IOptions;

/**
 * @ORM\Entity
 */
class OptionTypeString extends AOption
{
	/** @ORM\Column(type="string", length=512)  */
	protected $string_val;

	/** @return string */
	public function getValue()
	{
		return $this->string_val;
	}

	/** @param string $string */
	public function setValue($string)
	{
		if(!is_string($string)){
			throw new InvalidArgumentException('String option must not receive a ' . gettype($string) .' value');
		}
		$this->string_val = $string;
	}

    /**
     * @return string
     */
    public function getType()
    {
        return IOptions::TYPE_STRING;
    }
}
