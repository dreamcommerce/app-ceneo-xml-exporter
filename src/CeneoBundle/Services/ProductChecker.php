<?php
/**
 * Created by PhpStorm.
 * User: eRIZ
 * Date: 2015-04-01
 * Time: 14:14
 */

namespace CeneoBundle\Services;


use CeneoBundle\Manager\ExcludedProductManager;
use DreamCommerce\ShopAppstoreLib\Client;
use DreamCommerce\ShopAppstoreLib\ClientInterface;
use DreamCommerce\ShopAppstoreLib\Resource\Product;
use DreamCommerce\ShopAppstoreLib\ResourceList;
use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;
use DreamCommerce\ShopAppstoreBundle\Utils\CollectionWrapper;
use DreamCommerce\ShopAppstoreBundle\Utils\Fetcher;

class ProductChecker {

    const REQUEST_MAX_LENGTH = 8192;

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

        //$result = $this->orphansPurger->purgeExcluded($ids, $this->client, $shop);
        $result = $ids;

        if(count($result)==0){
            return new ResourceList();
        }

        $res = new Product($this->client);

        $wrapper = new CollectionWrapper(new \ArrayObject());
        $partitions = $this->partitionFilterArguments($result, self::REQUEST_MAX_LENGTH-512);

        foreach($partitions as $p) {
            $res->filters([
                'product_id'=>[
                    'in'=>$p
                ]
            ]);

            $fetcher = new Fetcher($res);
            $result = $fetcher->fetchAll();
            $wrapper->appendCollection($result);
        }

        return $wrapper->getCollection();

    }

    protected function partitionFilterArguments($arguments, $maxLength){

        $partitions = [];
        $bufferLength = 0;

        $buffer = [];
        while($element = array_shift($arguments)){
            // "",
            $length = strlen($element)+3;
            if($bufferLength+$length<$maxLength){
                $buffer[] = $element;
                $bufferLength += $length;
            }else{
                $partitions[] = $buffer;
                $buffer = [$element];
                $bufferLength = $length;
            }
        }

        $partitions[] = $buffer;

        return $partitions;

    }

}