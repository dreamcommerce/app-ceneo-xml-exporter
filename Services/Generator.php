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
use DreamCommerce\Resource\CategoriesTree;
use DreamCommerce\Resource\Category;
use DreamCommerce\Resource\Product;
use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;
use DreamCommerce\ShopAppstoreBundle\Utils\CollectionWrapper;
use DreamCommerce\ShopAppstoreBundle\Utils\Fetcher;

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

    function __construct($output, Client $client, ExcludedProductManager $excludedProductManager)
    {
        $this->output = $output;

        $this->resource = new \XMLWriter();
        $this->resource->openUri($output);

        $this->excludedProductManager = $excludedProductManager;
        $this->client = $client;
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

        $categoryPath = $this->getCategoryPath($row->category_id);

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

        $iterator = function($node, $path) use (&$targetPath, $id, &$iterator){

            foreach($node as $i){
                if($i['id']==$id){
                    $path[] = $id;
                    $targetPath = $path;
                    return;
                }else{
                    $path[] = $i['id'];
                    $iterator($i['children'], $path);
                }
            }

        };

        $iterator($this->categoriesTree, array());

        foreach($targetPath as &$n){
            $n = $this->categories[$n]->translations->pl_PL->name;
        }

        $stringPath = implode('/', $targetPath);
        $cache[$id] = $stringPath;

        return $stringPath;

    }
}