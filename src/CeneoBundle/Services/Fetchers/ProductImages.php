<?php


namespace CeneoBundle\Services\Fetchers;

use CeneoBundle\Services\ShopVersionChecker;
use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;

class ProductImages extends FetcherAbstract
{

    protected $shopUrlBase = null;
    /**
     * @var ShopVersionChecker
     */
    protected $shopVersionChecker;

    /**
     * fetching data
     * @return mixed
     */
    protected function fetch()
    {
        $this->shopUrlBase = null;
    }

    public function init($client, ShopInterface $shop, $hasSsl = true)
    {
        parent::init($client, $shop);
        $urlBase = $shop->getShopUrl();
        $urlBase = strtr($urlBase, ['https://'=>'', 'http://'=>'']);
        $urlBase = 'http://'.$urlBase;

        $this->shopUrlBase = $urlBase;
    }

    public function setVersionChecker(ShopVersionChecker $shopVersionChecker)
    {
        $this->shopVersionChecker = $shopVersionChecker;
    }

    public function getImages($images){

        if($this->shopVersionChecker && !$this->shopVersionChecker->arePicturesSupported($this->shop)){
            return [];
        }

        if(!$this->shopUrlBase){
            $this->shopUrlBase = $this->shop->getShopUrl();
        }

        if(substr($this->shopUrlBase, -1)!='/'){
            $this->shopUrlBase .= '/';
        }

        $result = [
            'images'=>[]
        ];

        $count = 0;
        foreach($images as $i){
            $count++;

            $url = $this->shopUrlBase.'userdata/gfx/'.$i->unic_name.'.jpg';

            if($i->main){
                $result['main'] = $url;
            }else {
                $result['images'][] = $url;
            }
        }

        if(!$count){
            return array();
        }

        if(empty($result['main']) && isset($result['images'][0])){
            $result['main'] = $result['images'][0];
            unset($result['images'][0]);
        }

        return $result;
    }
}