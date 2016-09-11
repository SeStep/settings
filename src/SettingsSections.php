<?php

namespace Thoronir42\Settings;

use Thoronir42\Model\BaseRepository;

class SettingsSections extends BaseRepository
{
    protected $entity_class = SettingsSection::class;

    public function findAllOrdered()
    {
        return $this->findBy([], ['domain' => 'ASC']);
    }

    public function findByDomain($domain)
    {
        return $this->findOneBy(['domain' => $domain]);
    }
}
