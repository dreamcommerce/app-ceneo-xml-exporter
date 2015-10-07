<?php


namespace CeneoBundle\Services\Fetchers;


use DreamCommerce\Resource\ProductImage;

class ProductImages extends FetcherAbstract
{

    /**
     * fetching data
     * @return mixed
     */
    protected function fetch()
    {

    }

    public function getByProductId($productId){
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

            $url = $shopUrlBase.'userdata/gfx/'.$i->unic_name.'.jpg';

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
}