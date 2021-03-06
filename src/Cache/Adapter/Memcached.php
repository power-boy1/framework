<?php
/**
 * Bluz Framework Component
 *
 * @copyright Bluz PHP Team
 * @link https://github.com/bluzphp/framework
 */

/**
 * @namespace
 */
namespace Bluz\Cache\Adapter;

use Bluz\Cache\Cache;
use Bluz\Cache\CacheException;

/**
 * Class Memcached
 * @package Bluz\Cache\Adapter
 */
class Memcached extends AbstractAdapter
{
    /**
     * Instance of memcached
     * @var \Memcached
     */
    protected $memcached = null;

    /**
     * Check and setup memcached servers
     * @param array $settings
     * @throws \Bluz\Cache\CacheException
     */
    public function __construct($settings = array())
    {
        // check Memcached extension
        if (!extension_loaded('memcached')) {
            throw new CacheException(
                "Memcached extension not installed/enabled.
                Install and/or enable memcached extension. See phpinfo() for more information"
            );
        }

        // check Memcached settings
        if (!is_array($settings) or empty($settings) or !isset($settings['servers'])) {
            throw new CacheException(
                "Memcached configuration is missed.
                Please check 'cache' configuration section"
            );
        }

        parent::__construct($settings);
    }

    /**
     * Get Mamcached Handler
     *
     * @return \Memcached
     */
    public function getHandler()
    {
        if (!$this->memcached) {

            $persistentId = isset($this->settings['persistent']) ? $this->settings['persistent'] : null;

            $this->memcached = new \Memcached($persistentId);

            if (!$this->memcached->getServerList()) {
                $this->memcached->addServers($this->settings['servers']);
            }

            if (isset($this->settings['options'])) {
                $this->memcached->setOptions($this->settings['options']);
            }
        }
        return $this->memcached;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $id
     * @return mixed
     */
    protected function doGet($id)
    {
        return $this->getHandler()->get($id);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $id
     * @param mixed $data
     * @param int $ttl
     * @return bool
     */
    protected function doAdd($id, $data, $ttl = Cache::TTL_NO_EXPIRY)
    {
        return $this->getHandler()->add($id, $data, $ttl);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $id
     * @param mixed $data
     * @param int $ttl
     * @return bool
     */
    protected function doSet($id, $data, $ttl = Cache::TTL_NO_EXPIRY)
    {
        return $this->getHandler()->set($id, $data, $ttl);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $id
     * @return bool
     */
    protected function doContains($id)
    {
        $this->getHandler()->get($id);
        return $this->getHandler()->getResultCode() !== \Memcached::RES_NOTFOUND;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $id
     * @return bool
     */
    protected function doDelete($id)
    {
        return $this->getHandler()->delete($id);
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    protected function doFlush()
    {
        return $this->getHandler()->flush();
    }
}
