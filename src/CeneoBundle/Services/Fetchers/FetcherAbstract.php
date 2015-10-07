<?php


namespace CeneoBundle\Services\Fetchers;


use Doctrine\Common\Cache\Cache;
use DreamCommerce\Client;
use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;

abstract class FetcherAbstract
{

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ShopInterface
     */
    protected $shop;

    /**
     * @var Cache
     */
    protected $cache;
    protected $ttl;

    public function __construct($ttl, Cache $cache)
    {
        $this->cache = $cache;
        $this->ttl = $ttl;
    }

    protected function getCacheKey($key){
        return sprintf('%s_%s', $this->shop->getName(), $key);
    }

    /**
     * fetching data
     * @return mixed
     */
    abstract protected function fetch();

    /**
     * @param mixed $client
     * @param ShopInterface $shop
     */
    public function init($client, ShopInterface $shop)
    {
        $this->client = $client;
        $this->shop = $shop;

        $this->fetch();
    }
}