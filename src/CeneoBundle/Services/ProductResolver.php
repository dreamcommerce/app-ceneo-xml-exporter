<?php


namespace CeneoBundle\Services;


use DreamCommerce\ShopAppstoreLib\Resource\Product;
use DreamCommerce\ShopAppstoreLib\Resource\ProductStock;
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

    public function getProductListForExclusion($ids, $translations = 'pl_PL')
    {
        $result = [];

        $res = new Product($this->client);
        $res->filters([
            'product_id'=>[
                'in'=>$ids
            ]
        ]);

        $fetcher = new Fetcher($res);
        $items = $fetcher->fetchAll();

        foreach($items as $i){
            $result[$i->product_id] = $i->translations->$translations->name;
        }

        return $result;
    }

}