<?php
/**
 * Created by PhpStorm.
 * User: eRIZ
 * Date: 2015-04-03
 * Time: 11:21
 */

namespace CeneoBundle\Services;


use CeneoBundle\Entity\AttributeGroupMappingRepository;
use CeneoBundle\Entity\ExcludedProductRepository;
use CeneoBundle\Model\CeneoGroup;
use CeneoBundle\Services\Fetchers\Attributes;
use CeneoBundle\Services\Fetchers\Categories;
use CeneoBundle\Services\Fetchers\Deliveries;
use CeneoBundle\Services\Fetchers\ProductImages;
use CeneoBundle\Services\Fetchers\Products;
use Doctrine\Common\Cache\Cache;
use DreamCommerce\Client;
use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;
use DreamCommerce\ShopAppstoreBundle\Utils\Fetcher;
use Symfony\Component\Stopwatch\Stopwatch;

class Generator {

    /**
     * directory where files are being prepared
     * @var string
     */
    protected $tempDirectory;

    /**
     * excluded products repository
     * @var ExcludedProductRepository
     */
    protected $excludedProductRepository;

    /**
     * @var Client
     */
    protected $client;

    /**
     * processed products count
     * @var int
     */
    protected $count = 0;

    /**
     * stopwatch component for statistics
     * @var bool|Stopwatch
     */
    protected $stopwatch = false;

    /**
     * product groups stats
     * @var array
     */
    protected $counters = [];

    /**
     * array group=>path to particular file
     * @var array
     */
    protected $paths = [];
    /**
     * array with \XmlWriter for groups
     * @var array
     */
    protected $writers = [];
    /**
     * @var AttributeGroupMappingRepository
     */
    protected $attributeGroupMappingRepository;
    /**
     * cache for shop objects
     * @var Cache
     */
    protected $cache;

    /**
     * @param $tempDirectory
     * @param Client $client
     * @param Cache $cache
     * @param ExcludedProductRepository $excludedProductRepository
     * @param AttributeGroupMappingRepository $attributeGroupMappingRepository
     */
    function __construct(
        $tempDirectory,
        Client $client,
        Cache $cache,
        ExcludedProductRepository $excludedProductRepository,
        AttributeGroupMappingRepository $attributeGroupMappingRepository
    )
    {
        $this->tempDirectory = $tempDirectory;

        $this->client = $client;
        $this->excludedProductRepository = $excludedProductRepository;
        $this->attributeGroupMappingRepository = $attributeGroupMappingRepository;

        $this->cache = $cache;
    }

    /**
     * clear mappings groups files
     */
    protected function clearTemporary(){
        foreach($this->paths as $group=>$path){
            /**
             * @var $item \XmlWriter
             */
            $this->writers[$group] = null;
            unset($this->writers[$group]);
            unlink($path);
        }

        $this->paths = [];
        $this->counters = [];
    }

    /**
     * initialize XmlWriter array and fill up paths
     */
    protected function initializeWriters(){
        $groups = array_keys(CeneoGroup::$groups);

        $uniq = uniqid('', true);

        foreach($groups as $g){
            $path = sprintf('%s/%s_%s', $this->tempDirectory, $uniq, $g);

            $writer = new \XMLWriter();
            $writer->openUri($path);
            $writer->setIndent(true);
            $writer->startDocument();
                $writer->startElement('group');
                $writer->writeAttribute('name', $g);

            $this->writers[$g] = $writer;
            $this->paths[$g] = $path;
            $this->counters[$g] = 0;
        }

    }

    /**
     * take care of writers markup ending
     */
    protected function endWriters(){
        /**
         * @var $w \XmlWriter
         */
        foreach($this->writers as $w){
            $w->endElement();
            $w->endDocument();
        }
    }

    /**
     * merge groups files into destination one
     * @param string $output
     */
    protected function mergeFiles($output){

        $dst = fopen($output, 'w');

        fwrite($dst, '<'.'?xml version="1.0" encoding="UTF-8"?'.'><offers>');

        foreach($this->paths as $group=>$path){

            if($this->counters[$group]==0){
                continue;
            }

            $src = new \SplFileObject($path);
            $skip = true;

            foreach($src as $r){
                // skip xml prologue
                if($skip){
                    $skip = false;
                    continue;
                }

                fwrite($dst, $r);
            }

            $src = null;
        }

        fwrite($dst, '</offers>');

        fclose($dst);

    }

    /**
     * determine group for product using fetcher
     * @param $product
     * @param ShopInterface $shop
     * @return string
     */
    protected function determineGroupForProduct($product, ShopInterface $shop){
        static $fetcher;
        if(!$fetcher){
            // todo: hardcoded 100
            $fetcher = new Attributes(100, $this->cache);
            // todo: cache
            $fetcher->setMappings($this->attributeGroupMappingRepository, $shop);
            $fetcher->init($this->client, $shop);
        }

        return $fetcher->determineGroupForProduct($product);
    }

    /**
     * enable stopwatch
     * @param Stopwatch $s
     */
    public function setStopwatch(Stopwatch $s){
        $this->stopwatch = $s;
    }

    /**
     * fetch products from shop
     * @param ShopInterface $shop
     * @return Fetcher\ResourceListIterator
     */
    protected function fetchProducts(ShopInterface $shop){

        // todo: 100 is hardcoded
        $fetcher = new Products(100, $this->cache);
        $fetcher->init($this->client, $shop);

        $products = $fetcher->getWithoutExcluded(
            $shop, $this->excludedProductRepository
        );

        return $products;
    }

    /**
     * do proper export
     * @param ShopInterface $shop
     * @param $output
     * @return int
     */
    public function export(ShopInterface $shop, $output){

        $this->initializeWriters();

        $products = $this->fetchProducts($shop);

        foreach($products as $product){
            $group = $this->determineGroupForProduct($product, $shop);
            $this->counters[$group]++;
            $this->appendProduct($this->writers[$group], $product, $shop);
        }

        $this->endWriters();

        $this->mergeFiles($output);

        $this->clearTemporary();

        // may something go wrong, so not directly from collection
        return $this->count;
    }

    /**
     * append particular product to specified writer
     * @param \XmlWriter $writer
     * @param $row
     * @param ShopInterface $shop
     */
    protected function appendProduct(\XmlWriter $writer, $row, ShopInterface $shop){

        $this->count++;

        if($this->stopwatch){
            $this->stopwatch->lap('export');
        }

        $categoryPath = $this->getCategoryPath($row->category_id, $shop);

        $images = $this->getProductImages($row->product_id, $shop);
        $attributes = $this->getAttributes($row->attributes, $shop);

        $w = $writer;
        $w->startElement('o');
            $w->writeAttribute('id', $row->product_id);
            $w->writeAttribute('price', $row->stock->price);
            $w->writeAttribute('stock', $row->stock->stock);
            $w->writeAttribute('url', $row->translations->pl_PL->permalink);
            $w->writeAttribute('weight', $row->stock->weight);
            $w->writeAttribute('avail', $this->getDaysForDeliveryId($row->stock->delivery_id, $shop));
            $w->writeAttribute('set', 0);

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

            if($row->translations->pl_PL->description){
                $w->startElement('desc');
                    $w->writeCdata($row->translations->pl_PL->description);
                $w->endElement();
            }

        $w->endElement();
    }

    // todo: fetcher factory?
    protected function getCategoryPath($id, ShopInterface $shop){
        static $fetcher;
        if(!$fetcher){
            //todo: hardcoded ttl
            $fetcher = new Categories(100, $this->cache);
            $fetcher->init($this->client, $shop);
        }

        return $fetcher->getCategoryTree($id);
    }

    protected function getProductImages($productId, ShopInterface $shop){
        //todo: ttl
        $fetcher = new ProductImages(100, $this->cache);
        $fetcher->init($this->client, $shop);

        return $fetcher->getByProductId($productId);
    }

    protected function getDaysForDeliveryId($deliveryId, ShopInterface $shop){
        static $fetcher;
        if(!$fetcher){
            //todo: hardcoded 100
            $fetcher = new Deliveries(100, $this->cache);
            $fetcher->init($this->client, $shop);
        }

        return $fetcher->getDaysForDeliveryId($deliveryId);
    }

    public function getAttributes($attributes, ShopInterface $shop){
        static $fetcher;
        if(!$fetcher){
            // todo: hardcoded 100
            $fetcher = new Attributes(100, $this->cache);
            // todo caching
            $fetcher->setMappings($this->attributeGroupMappingRepository, $shop);
            $fetcher->init($this->client, $shop);
        }

        return $fetcher->getAttributes($attributes);
    }

}