<?php
/**
 * Created by PhpStorm.
 * User: eRIZ
 * Date: 2015-04-01
 * Time: 14:14
 */

namespace CeneoBundle\Services;


use CeneoBundle\Entity\ExcludedProductRepository;
use DreamCommerce\Client;
use DreamCommerce\Resource\Product;
use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;
use DreamCommerce\ShopAppstoreBundle\Utils\CollectionWrapper;
use DreamCommerce\ShopAppstoreBundle\Utils\Fetcher;

class ProductChecker {

    /**
     * @var ExcludedProductRepository
     */
    protected $excludedProductRepository;
    /**
     * @var Client
     */
    protected $client;

    public function __construct(ExcludedProductRepository $excludedProductRepository, Client $client){

        $this->excludedProductRepository = $excludedProductRepository;
        $this->client = $client;
    }

    public function getNotExcluded($ids, ShopInterface $shop){

        $resource = new Product($this->client);

        $fetcher = new Fetcher($resource);

        $resource->filters(array('product_id'=>$ids));
        $products = $fetcher->fetchAll();

        $productsCollection = new CollectionWrapper($products);

        $chosenProducts = $productsCollection->getListOfField('product_id');
        $alreadyExcluded = $this->excludedProductRepository->findIdsByProductAndShop($chosenProducts, $shop);

        $result = array();
        foreach($products as $p){
            if(in_array($p->product_id, $alreadyExcluded)){
                continue;
            }
            $result[$p->product_id] = $p;
        }

        return new \ArrayObject($result, \ArrayObject::ARRAY_AS_PROPS);


    }

}