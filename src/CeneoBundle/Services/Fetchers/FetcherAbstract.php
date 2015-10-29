<?php


namespace CeneoBundle\Services\Fetchers;


use CeneoBundle\Services\OrphansPurger;
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
     * @var OrphansPurger
     */
    protected $orphansPurger;


    public function __construct(OrphansPurger $orphansPurger = null)
    {
        $this->orphansPurger = $orphansPurger;
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