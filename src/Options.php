<?php

namespace Thoronir42\Settings;


use Thoronir42\Model\BaseRepository;
use Thoronir42\Settings\Options\AOption;

class Options extends BaseRepository
{
    protected $entity_class = AOption::class;

	public function findAllOrdered(){
		return $this->findBy([], ['domain' => 'ASC']);
	}

	public function findByDomain($domain)
	{
		return $this->findOneBy(['domain' => $domain]);
	}
}
