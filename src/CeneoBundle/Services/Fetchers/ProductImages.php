<?php


namespace CeneoBundle\Services\Fetchers;

class ProductImages extends FetcherAbstract
{

    /**
     * fetching data
     * @return mixed
     */
    protected function fetch()
    {

    }

    public function getImages($images){
        static $shopUrlBase;

        if(!$shopUrlBase){
            $shopUrlBase = $this->shop->getShopUrl();
            if(substr($shopUrlBase, -1)!='/'){
                $shopUrlBase .= '/';
            }
        }

        $result = [
            'images'=>[]
        ];

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