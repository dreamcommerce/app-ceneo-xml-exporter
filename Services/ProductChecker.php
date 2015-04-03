<?php
/**
 * Created by PhpStorm.
 * User: eRIZ
 * Date: 2015-04-01
 * Time: 14:14
 */

namespace CeneoBundle\Services;


use CeneoBundle\Manager\ExcludedProductManager;
use DreamCommerce\Client;
use DreamCommerce\Resource\Product;
use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;
use DreamCommerce\ShopAppstoreBundle\Utils\CollectionWrapper;
use DreamCommerce\ShopAppstoreBundle\Utils\Fetcher;

class ProductChecker {

    /**
     * @var ExcludedProductManager
     */
    protected $excludedProductManager;
    /**
     * @var Client
     */
    protected $client;

    public function __construct(ExcludedProductManager $excludedProductManager, Client $client){
        $this->excludedProductManager = $excludedProductManager;
        $this->client = $client;
    }

    public function getNotExcluded($ids, ShopInterface $shop){

        $resource = new Product($this->client);

        $fetcher = new Fetcher($resource);

        $resource
            ->order('translation.pl_PL.name ASC')
            ->filters(array(
                'product_id'=>array(
                    'in'=>$ids
                )
            ));

        $products = $fetcher->fetchAll();

        $productsCollection = new CollectionWrapper($products);

        $chosenProducts = $productsCollection->getListOfField('product_id');
        $alreadyExcluded = $this->excludedProductManager->getRepository()->findIdsByProductAndShop($chosenProducts, $shop);

        $result = array();
        foreach($products as $p){
            if(in_array($p->product_id, $alreadyExcluded)){
                continue;
            }
            $result[$p->product_id] = $p;
        }

        return new \ArrayObject($result, \ArrayObject::ARRAY_AS_PROPS);


    }

    public function getExcluded(ShopInterface $shop){

        $ids = $this->excludedProductManager->getRepository()->findIdsByShop($shop);

        $resource = new Product($this->client);
        $fetcher = new Fetcher($resource);

        $resource
            ->filters(array(
                'product_id'=>array(
                    'in'=>$ids
                )
            ))->order('translation.pl_PL.name ASC');

        $result = $fetcher->fetchAll();

        $wrapper = new CollectionWrapper($result);
        $foundIds = $wrapper->getListOfField('product_id');

        if($ids!=$foundIds){
            $this->excludedProductManager->purgeNonExistingProducts($ids, $foundIds, $shop);
        }

        return $result;

    }

}