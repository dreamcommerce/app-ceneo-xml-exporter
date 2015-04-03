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
use DreamCommerce\Resource\Product;
use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;
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
        $productResource->filters(array('product_id'=>array('not in'=>$excluded)));

        $w = $this->resource;

        $fetcher = new Fetcher($productResource);

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

    }


    public function appendProduct($row){
        $w = $this->resource;
        $w->startElement('o');
            $w->writeAttribute('id', $row->product_id);
            $w->startElement('name');
                $w->writeCdata($row->translations->pl_PL->name);
            $w->endElement();
        $w->endElement();
    }
}