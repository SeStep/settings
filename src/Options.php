<?php

namespace SeStep\Settings;

use SeStep\Model\BaseDoctrineService;
use SeStep\Settings\Options\AOption;

class Options extends BaseDoctrineService
{
    protected $entity_class = AOption::class;

	public function findAllOrdered(){
		return $this->repository->findBy([], ['domain' => 'ASC']);
	}

	public function findByDomain($domain)
	{
		return $this->repository->findOneBy(['domain' => $domain]);
	}
}
