<?php

namespace Crm\Storage\Redis;

use App\Models\User;
use Illuminate\Redis\Connections\PredisConnection;

class Tenants
{
    /**
     * @var PredisConnection
     */
    private $redis;

    /**
     * Tenants constructor.
     *
     * @param PredisConnection $redis
     */
    public function __construct(PredisConnection $redis)
    {
        $this->redis = $redis;
    }

    /**
     * @param User $user
     * @param int $tenant_id
     * @param string $key
     */
    public function set(User $user, int $tenant_id, string $key = '')
    {
        $key = $key === '' ? $this->getName($user) : $key;
        $this->redis->set($key, $tenant_id);
    }

    /**
     * @param User $user
     */
    public function remove(User $user)
    {
        $this->redis->del($this->getName($user));
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function isSet(User $user) : bool
    {
        return $this->get($user) ? true : false;
    }

    /**
     * @param User $user
     *
     * @param string $searchKey
     *
     * @return int|null
     */
    public function get(User $user, string $searchKey = '')
    {
        $key = $searchKey === '' ? $this->getName($user) : $searchKey;
        return $this->redis->get($key);
    }

    /**
     * @param User $user
     *
     * @return string
     */
    private function getName(User $user) : string
    {
        return sprintf('user:%s' . 'tenant:%s', ($user->is_admin ? 'admin:' : ''), $user->id);
    }
}
