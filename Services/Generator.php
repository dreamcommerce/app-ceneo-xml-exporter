<?php
/**
 * Created by PhpStorm.
 * User: eRIZ
 * Date: 2015-04-03
 * Time: 11:21
 */

namespace CeneoBundle\Services;


use CeneoBundle\Manager\ExcludedProductManager;
use DreamCommerce\Client;
use DreamCommerce\Resource\Attribute;
use DreamCommerce\Resource\CategoriesTree;
use DreamCommerce\Resource\Category;
use DreamCommerce\Resource\Product;
use DreamCommerce\Resource\ProductImage;
use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;
use DreamCommerce\ShopAppstoreBundle\Utils\CollectionWrapper;
use DreamCommerce\ShopAppstoreBundle\Utils\Fetcher;
use Symfony\Component\Stopwatch\Stopwatch;

class Generator {

    protected $output;

    /**
     * @var \XMLWriter
     */
    protected $resource;
    /**
     * @var ExcludedProductManager
     */
    protected $excludedProductManager;
    /**
     * @var Client
     */
    protected $client;

    protected $count = 0;

    protected $categories;
    protected $categoriesTree;
    protected $shop;
    protected $stopwatch = false;
    protected $attributes;

    function __construct($output, Client $client, ExcludedProductManager $excludedProductManager, ShopInterface $shop)
    {
        $this->output = $output;

        $this->resource = new \XMLWriter();
        $this->resource->openUri($output);

        $this->excludedProductManager = $excludedProductManager;
        $this->client = $client;
        $this->shop = $shop;
    }

    public function setStopwatch(Stopwatch $s){
        $this->stopwatch = $s;
    }

    public function export(ShopInterface $shop){

        $excluded = $this->excludedProductManager->getRepository()->findIdsByShop($shop);

        $productResource = new Product($this->client);

        if($excluded) {
            $productResource->filters(array('product_id' => array('not in' => $excluded)));
        }

        $w = $this->resource;

        $fetcher = new Fetcher($productResource);

        $this->loadCategories();
        $this->loadAttributes();

        $w->startDocument();
            $w->startElementNs('xsi', 'offers', 'http://www.w3.org/2001/XMLSchema');
            $w->writeAttribute('version', 1);
                $w->startElement('group');
                    $w->writeAttribute('name', 'other');
                    $fetcher->walk(function($row){
                        $this->appendProduct($row);
                    });
                $w->endElement();
            $w->endElement();
        $w->endDocument();

        // may something go wrong, so not directly from collection
        return $this->count;
    }


    public function appendProduct($row){

        $this->count++;

        if($this->stopwatch){
            $this->stopwatch->lap('export');
        }

        $categoryPath = $this->getCategoryPath($row->category_id);

        $images = $this->getProductImages($row->product_id);
        $attributes = $this->getAttributes($row->attributes);

        $w = $this->resource;
        $w->startElement('o');
            $w->writeAttribute('id', $row->product_id);
            $w->writeAttribute('price', $row->stock->price);
            $w->writeAttribute('stock', $row->stock->stock);
            $w->startElement('name');
                $w->writeCdata($row->translations->pl_PL->name);
            $w->endElement();
            $w->startElement('cat');
                $w->writeCdata($categoryPath);
            $w->endElement();

            if($images){
                $w->startElement('imgs');
                if($images['main']){
                    $w->startElement('main');
                        $w->writeAttribute('url', $images['main']);
                    $w->endElement();
                }
                foreach($images['images'] as $i){
                    $w->startElement('i');
                        $w->writeAttribute('url', $i);
                    $w->endElement();
                }
                $w->endElement();
            }

            if($attributes){
                $w->startElement('attrs');
                    foreach($attributes as $k=>$v){
                        $w->startElement('a');
                            $w->writeAttribute('name', $k);
                            $w->writeCdata($v);
                        $w->endElement();
                    }
                $w->endElement();
            }

            if($row->translations->pl_PL->short_description){
                $w->startElement('desc');
                    $w->writeCdata($row->translations->pl_PL->short_description);
                $w->endElement();
            }

        $w->endElement();
    }

    protected function loadCategories(){
        $categoriesTreeResource = new CategoriesTree($this->client);
        $categoriesResource = new Category($this->client);

        $this->categoriesTree = $categoriesTreeResource->get();

        $fetcher = new Fetcher($categoriesResource);
        $categories = $fetcher->fetchAll();
        $wrapper = new CollectionWrapper($categories);

        $this->categories = $wrapper->getArray('category_id');
    }

    protected function getCategoryPath($id){

        static $cache;

        if(isset($cache[$id])){
            return $cache[$id];
        }

        $targetPath = array();

        $iterator = function($node, $path = array()) use (&$targetPath, $id, &$iterator){

            foreach($node as $i){
                if($i['id']==$id){
                    $path[] = $id;
                    $targetPath = $path;
                    return;
                }else if(!empty($i['children'])){
                    $path[] = $i['id'];
                    $iterator($i['children'], $path);
                    array_pop($path);
                }
            }

        };

        $iterator($this->categoriesTree);

        foreach($targetPath as &$n){
            $n = $this->categories[$n]->translations->pl_PL->name;
        }

        $stringPath = implode('/', $targetPath);
        $cache[$id] = $stringPath;

        return $stringPath;

    }

    public function getProductImages($productId){
        static $shopUrlBase;

        if(!$shopUrlBase){
            $shopUrlBase = $this->shop->getShopUrl();
            if(substr($shopUrlBase, -1)!='/'){
                $shopUrlBase .= '/';
            }
        }

        $imageResource = new ProductImage($this->client);
        $images = $imageResource->filters(array('product_id'=>$productId))->get();

        $result = array(
            'main'=>false,
            'images'=>array()
        );

        $count = 0;
        foreach($images as $i){
            $count++;

            $url = $shopUrlBase.'environment/cache/images/'.$i->unic_name;

            if($i->main){
                $result['main'] = $url;
            }else {
                $result['images'][] = $url;
            }
        }

        if(!$count){
            return array();
        }

        return $result;
    }

    public function loadAttributes(){
        $resource = new Attribute($this->client);
        $fetcher = new Fetcher($resource);

        $list = $fetcher->fetchAll();

        $wrapper = new CollectionWrapper($list);

        $this->attributes = $wrapper->getArray('attribute_id');
    }

    public function getAttributes($attributes){

        $result = array();

        $counter = 0;
        foreach($attributes as $group){
            foreach($group as $attr=>$v){
                $name = $this->attributes[$attr]->name;

                $result[$name] = $v;

                $counter++;

                if($counter>=10){
                    break;
                }
            }
        }

        return $result;

    }
}