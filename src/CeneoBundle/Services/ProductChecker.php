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
use DreamCommerce\ClientInterface;
use DreamCommerce\Resource\Product;
use DreamCommerce\ResourceList;
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
    /**
     * @var OrphansPurger
     */
    protected $orphansPurger;

    public function __construct(ExcludedProductManager $excludedProductManager, ClientInterface $client, OrphansPurger $orphansPurger){
        $this->excludedProductManager = $excludedProductManager;
        $this->client = $client;
        $this->orphansPurger = $orphansPurger;
    }

    public function getNotExcluded($ids, ShopInterface $shop){

        if(empty($ids)){
            return new ResourceList();
        }

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

        return new ResourceList($result, \ArrayObject::ARRAY_AS_PROPS);


    }

    public function getExcluded(ShopInterface $shop){

        $ids = $this->excludedProductManager->getRepository()->findIdsByShop($shop);

        $result = $this->orphansPurger->purgeExcluded($ids, $this->client, $shop);

        if(count($result)==0){
            return new ResourceList();
        }

        $res = new Product($this->client);
        $res->filters([
            'product_id'=>[
                'in'=>$result
            ]
        ]);

        $fetcher = new Fetcher($res);
        $result = $fetcher->fetchAll();

        return $result;

    }

}