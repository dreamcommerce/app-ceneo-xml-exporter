<?php


namespace CeneoBundle\Services;


use DreamCommerce\Resource\ProductStock;
use DreamCommerce\ShopAppstoreBundle\Utils\CollectionWrapper;
use DreamCommerce\ShopAppstoreBundle\Utils\Fetcher;

class ProductResolver {

    private $client;

    public function __construct($client){
        $this->client = $client;
    }

    public function getProductIdFromStock($id){
        $id = (array)$id;

        $stockResource = new ProductStock($this->client);
        $stockResource->filters(array('stock_id'=>array('IN'=>$id)));
        $fetcher = new Fetcher($stockResource);

        $result = $fetcher->fetchAll();

        $wrapper = new CollectionWrapper($result);
        $toReturn = $wrapper->getListOfField('product_id');

        return $toReturn;
    }

}