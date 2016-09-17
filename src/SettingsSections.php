<?php

namespace SeStep\Settings;

use SeStep\Model\BaseDoctrineService;

class SettingsSections extends BaseDoctrineService
{
    protected $entity_class = SettingsSection::class;

    public function findAllOrdered()
    {
        return $this->repository->findBy([], ['domain' => 'ASC']);
    }

    public function findByDomain($domain)
    {
        return $this->repository->findOneBy(['domain' => $domain]);
    }
}
