<?php

namespace SeStep\SettingsDoctrine;


use SeStep\Model\BaseDoctrineService;
use SeStep\SettingsDoctrine\Pools\Pool;
use SeStep\SettingsInterface\Exceptions\NotFoundException;
use SeStep\SettingsInterface\Pools\IPool;
use SeStep\SettingsInterface\Pools\IPools;

class DoctrinePools extends BaseDoctrineService implements IPools
{

    /**
     * @return IPool[]
     */
    public function findAll()
    {
        return $this->repository->findAll();
    }

    /**
     * @param string $name
     * @return IPool|null
     */
    public function find($name)
    {
        return $this->repository->findOneBy(['name' => $name]);
    }

    public function create($name, $type)
    {
        if($this->find($name)){
            throw new \Exception("Pool $name already exists");
        }
        $pool = new Pool($name, $type);
    }

    /**
     * @param string $name
     * @throws NotFoundException
     * @return IPool
     */
    public function get($name)
    {
        $pool = $this->find($name);
        if (!$pool) {
            throw NotFoundException::pool($name);
        }

        return $pool;
    }

    /**
     * @param string $name
     * @param mixed $key
     * @throws NotFoundException
     * @return mixed
     */
    public function getValue($name, $key)
    {
        $pool = $this->get($name);

        return $pool->get($key);
    }

    /**
     * @param string $name
     * @param mixed $key
     * @param mixed $value
     * @return mixed
     */
    public function setValue($name, $key, $value)
    {
        $pool = $this->get($name);

        return $pool->set($key, $value);
    }
}
