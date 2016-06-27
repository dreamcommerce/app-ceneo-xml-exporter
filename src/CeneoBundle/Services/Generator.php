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
use DreamCommerce\ShopAppstoreBundle\Utils\ShopChecker;
use DreamCommerce\ShopAppstoreBundle\Utils\TokenRefresher;
use DreamCommerce\ShopAppstoreLib\Client;
use DreamCommerce\ShopAppstoreLib\ClientInterface;
use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;
use DreamCommerce\ShopAppstoreBundle\Utils\Fetcher;
use DreamCommerce\ShopAppstoreLib\Resource\Exception\CommunicationException;
use DreamCommerce\ShopAppstoreLib\Resource\Exception\PermissionsException;
use DreamCommerce\ShopAppstoreLib\Resource\Exception\ResourceException;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchPeriod;

class Generator {

    const PROGRESS_RESOLUTION = 50;

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
     * @var null|ClientInterface
     */
    protected $client;
    /**
     * @var ExportStatus
     */
    protected $exportStatus;

    /**
     * products count to export
     * @var int
     */
    protected $productsCount = 0;

    /**
     * @var Categories
     */
    protected $categoriesFetcher;

    /**
     * @var ProductImages
     */
    protected $productImagesFetcher;
    /**
     * @var Deliveries
     */
    protected $deliveriesFetcher;
    /**
     * @var Attributes
     */
    protected $attributesFetcher;
    /**
     * @var OrphansPurger
     */
    protected $orphansPurger;
    /**
     * @var FileCompressor
     */
    protected $fileCompressor;
    /**
     * @var Products
     */
    protected $productsFetcher;
    /**
     * @var TokenRefresher
     */
    protected $tokenRefresher;
    /**
     * has shop a valid SSL support
     * @var boolean
     */
    protected $hasSsl;

    /**
     * @param $tempDirectory
     * @param OrphansPurger $orphansPurger
     * @param ExcludedProductRepository $excludedProductRepository
     * @param AttributeGroupMappingRepository $attributeGroupMappingRepository
     * @param ExportStatus $exportStatus
     * @param TokenRefresher $tokenRefresher
     */
    function __construct(
        $tempDirectory,
        OrphansPurger $orphansPurger,
        ExcludedProductRepository $excludedProductRepository,
        AttributeGroupMappingRepository $attributeGroupMappingRepository,
        ExportStatus $exportStatus,
        TokenRefresher $tokenRefresher
    )
    {
        $this->tempDirectory = $tempDirectory;

        $this->excludedProductRepository = $excludedProductRepository;
        $this->attributeGroupMappingRepository = $attributeGroupMappingRepository;

        $this->exportStatus = $exportStatus;
        $this->orphansPurger = $orphansPurger;
        $this->tokenRefresher = $tokenRefresher;
    }

    public function setFileCompressor(FileCompressor $compressor = null)
    {
        $this->fileCompressor = $compressor;
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
            $path = sprintf('%s/%s_%s.tmp', $this->tempDirectory, $uniq, $g);

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

        fwrite($dst, '<'.'?xml version="1.0" encoding="UTF-8"?'.'><offers xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" version="1">');

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
     * enable stopwatch
     * @param Stopwatch $s
     */
    public function setStopwatch(Stopwatch $s){
        $this->stopwatch = $s;
    }

    /**
     * get stopwatch instance
     * @return bool|Stopwatch
     */
    public function getStopwatch()
    {
        return $this->stopwatch;
    }

    /**
     * fetch products from shop
     * @param ShopInterface $shop
     * @return Fetcher\ResourceListIterator
     */
    protected function fetchProducts(ShopInterface $shop){

        $this->productsFetcher = $fetcher = new Products($this->orphansPurger);
        $fetcher->init($this->client, $shop);

        $products = $fetcher->getWithoutExcluded(
            $shop, $this->excludedProductRepository
        );

        return $products;
    }

    /**
     * do proper export
     * @param ClientInterface $client
     * @param ShopInterface $shop
     * @param $output
     * @return int
     */
    public function export(ClientInterface $client, ShopInterface $shop, $output){

        if($this->stopwatch){
            $this->stopwatch->start('shop');
            $this->stopwatch->start('export');
        }

        $shopChecker = new ShopChecker();
        $this->hasSsl = $shopChecker->verifySsl($shop);

        $this->initializeWriters();

        $this->client = $client;

        // initialize fetchers and make sure we exchange application tokens if it's necessary
        try {
            $this->initializeFetchers($shop);
        }catch(PermissionsException $ex){
            $this->tokenRefresher->setClient($this->client);
            $token = $this->tokenRefresher->refresh($shop);
            $shop->setToken($token);
            $this->initializeFetchers($shop);
        }catch(\Exception $ex){
            $this->clearTemporary();
        }

        $success = false;
        $counter = 0;

        try {

            $products = $this->fetchProducts($shop);
            $this->productsCount = count($products);

            $this->exportStatus->markInProgress($shop, 0, $this->productsCount);

            $calculator = new EtaCalculator(100);

            foreach ($products as $product) {

                $counter++;

                // we support only pl_PL locale for export (intentionally)
                if (!isset($product->translations->pl_PL)) {
                    continue;
                }

                if (!$this->productsFetcher->isIgnored($product->product_id)) {
                    $group = $this->determineGroupForProduct($product, $shop);
                    $this->counters[$group]++;
                    $this->appendProduct($this->writers[$group], $product, $shop);
                }

                if ($counter % self::PROGRESS_RESOLUTION == 0) {
                    if ($this->stopwatch) {
                        $eta = $calculator->getEtaSeconds($this->stopwatch->getEvent('export'), $this->productsCount - $counter);
                    } else {
                        $eta = 0;
                    }
                    $this->exportStatus->markInProgress($shop, $counter, $this->productsCount, $eta);
                }
            }


            $success = true;

        }catch (\Exception $ex){
            // let the process continue
        }

        // close handles
        $this->endWriters();

        if($success) {
            $this->mergeFiles($output);

            if($this->fileCompressor){
                $this->fileCompressor->compressAsync($output);
            }

            $orphans = $this->productsFetcher->getNotExistingIgnores();
            $this->orphansPurger->purgeExcludedIds($orphans, $shop);

        }

        // clear what's should not mess up
        $this->clearTemporary();

        $seconds = 0;

        if($this->stopwatch){
            $this->stopwatch->stop('shop');
            $this->stopwatch->stop('export');
            $seconds = $this->getSecondsForLastShop($this->stopwatch);
        }

        $this->exportStatus->markDone($shop, $seconds);

        if(!empty($ex) && $ex instanceof CommunicationException){
            throw $ex;
        }

        // may something go wrong, so not directly from collection
        return $counter;
    }

    /**
     * get seconds the shop has consumed to perform an export
     * @param Stopwatch $stopwatch
     * @return int
     */
    protected function getSecondsForLastShop(Stopwatch $stopwatch){
        $e = $stopwatch->getEvent('shop');
        $periods = $e->getPeriods();
        /**
         * @var $last StopwatchPeriod
         */
        $last = end($periods);
        return intval($last->getDuration()/1000);
    }

    /**
     * append particular product to specified writer
     * @param \XmlWriter $writer
     * @param $row
     * @param bool $appendAdditionalFields
     */
    protected function appendProduct(\XmlWriter $writer, $row, $appendAdditionalFields = false){

        if($this->stopwatch){
            $this->stopwatch->lap('export');
        }

        $categoryPath = $this->getCategoryPath($row->category_id);

        $images = $this->getProductImages($row->ProductImage);

        if($appendAdditionalFields) {
            $attributes = $this->getAdditionalAttributes($row);
        }else{
            $attributes = [];
        }

        $attributes = array_merge($attributes, $this->getAttributes($row));

        $permalink = $row->translations->pl_PL->permalink;
        $permalink = strtr($permalink, ['http://'=>'', 'https://'=>'']);

        $permalink = 'http://'.$permalink;

        $stock = round($row->stock->stock);

        $w = $writer;
        $w->startElement('o');
            $w->writeAttribute('id', $row->product_id);
            $w->writeAttribute('price', $row->stock->comp_promo_price);
            $w->writeAttribute('stock', $stock);
            $w->writeAttribute('url', $permalink);
            $w->writeAttribute('weight', $row->stock->weight);
            $w->writeAttribute('avail', $this->getDaysForDeliveryId($row->stock->delivery_id));
            $w->writeAttribute('set', 0);

            $w->startElement('name');
                $name = $this->utf8ify($row->translations->pl_PL->name);
                $w->writeCdata($name);
            $w->endElement();
            $w->startElement('cat');
                $w->writeCdata($this->utf8ify($categoryPath));
            $w->endElement();

            if($images){

                $w->startElement('imgs');
                if(!empty($images['main'])){
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
                    $counter = 0;
                    foreach($attributes as $k=>$v){
                        // ceneo limits attributes count to 10
                        if($counter>=10){
                            break;
                        }

                        $k = $this->utf8ify($k);
                        $v = $this->utf8ify($v);

                        $w->startElement('a');
                            $w->writeAttribute('name', $k);
                            $w->writeCdata($v);
                        $w->endElement();
                        $counter++;
                    }
                $w->endElement();
            }

            if($row->translations->pl_PL->description){
                $w->startElement('desc');
                    //sometimes, someone put an old-fashioned JS with CDATA onto description...
                    $description = $row->translations->pl_PL->description;

                    $description = $this->utf8ify($description);

                    $description = str_replace(']]>', ']]]]><![CDATA[>', $description);
                    $w->writeCdata($description);
                $w->endElement();
            }

        $w->endElement();
    }

    protected function initializeFetchers(ShopInterface $shop){
        $this->categoriesFetcher = new Categories();
        $this->categoriesFetcher->init($this->client, $shop);

        $this->productImagesFetcher = new ProductImages();
        $this->productImagesFetcher->init($this->client, $shop, $this->hasSsl);

        $this->deliveriesFetcher = new Deliveries();
        $this->deliveriesFetcher->init($this->client, $shop);

        $this->attributesFetcher = new Attributes($this->orphansPurger);
        $this->attributesFetcher->init($this->client, $shop);
        $this->attributesFetcher->setMappings($this->attributeGroupMappingRepository, $shop);
    }

    /**
     * determine group for product using fetcher
     * @param $product
     * @param ShopInterface $shop
     * @return string
     */
    protected function determineGroupForProduct($product, ShopInterface $shop){
        return $this->attributesFetcher->determineGroupForProduct($product);
    }

    protected function getCategoryPath($id){
        return $this->categoriesFetcher->getCategoryTree($id);
    }

    protected function getProductImages($images){
        return $this->productImagesFetcher->getImages($images);
    }

    protected function getDaysForDeliveryId($deliveryId){
        return $this->deliveriesFetcher->getDaysForDeliveryId($deliveryId);
    }

    public function getAttributes($product){
        return $this->attributesFetcher->getAttributes($product);
    }

    /**
     * @return int
     */
    public function getProductsCount()
    {
        return $this->productsCount;
    }

    protected function getEta(Stopwatch $stopwatch = null){
        if(!$stopwatch){
            return null;
        }


    }

    /**
     * @param $row
     * @return array
     */
    protected function getAdditionalAttributes($row)
    {
        $attributes = [];

        foreach (['isbn', 'bloz7', 'bloz12', 'code', 'kgo', 'producer'] as $name) {
            $key = 'additional_' . $name;
            if (isset($row->$key) && !empty($row->$key)) {
                if ($name == 'bloz7') {
                    $attributeKey = 'bloz_7';
                } else if ($name == 'bloz12') {
                    $attributeKey = 'bloz_12';
                } else {
                    $attributeKey = $name;
                }
                $attributes[strtoupper($attributeKey)] = $row->$key;
            }
        }

        return $attributes;
    }

    protected function utf8ify($str)
    {
        return iconv(mb_detect_encoding($str, mb_detect_order(), true), "UTF-8", $str);
    }

}