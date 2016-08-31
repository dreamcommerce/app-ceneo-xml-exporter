<?php


namespace CeneoBundle\Services;


use Doctrine\Common\Cache\Cache;
use DreamCommerce\ShopAppstoreBundle\Handler\Application;
use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;
use DreamCommerce\ShopAppstoreLib\Resource\ApplicationVersion;
use DreamCommerce\ShopAppstoreLib\Resource\Exception\ResourceException;

class ShopVersionChecker
{

    const CACHE_TTL = 600;

    /**
     * @var Cache
     */
    protected $cache;
    /**
     * @var Application
     */
    protected $application;

    public function __construct(Cache $cache, Application $application)
    {
        $this->cache = $cache;
        $this->application = $application;
    }

    public function getCacheKey(ShopInterface $shop, $object)
    {
        return sprintf('%s-%s', $shop->getName(), $object);
    }

    public function arePicturesSupported(ShopInterface $shop)
    {

        $cacheKey = $this->getCacheKey($shop, 'pictures_supported');

        if($this->cache->contains($cacheKey)){
            return $this->cache->fetch($cacheKey);
        }

        try{
            $res = new ApplicationVersion(
                $this->application->getClient($shop)
            );

            $data = $res->get();

            $result = version_compare($data->version, '5.7.7', '>=');
        }catch(ResourceException $ex){
            $result = false;
        }

        $this->cache->save($cacheKey, $result, self::CACHE_TTL);

        return $result;


    }

}