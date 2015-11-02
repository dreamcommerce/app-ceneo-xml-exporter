<?php


namespace CeneoBundle\Services\Fetchers;

class ProductImages extends FetcherAbstract
{

    protected $shopUrlBase = null;

    /**
     * fetching data
     * @return mixed
     */
    protected function fetch()
    {
        $this->shopUrlBase = null;
    }

    public function getImages($images){

        if(!$this->shopUrlBase){
            $this->shopUrlBase = $this->shop->getShopUrl();
            if(substr($this->shopUrlBase, -1)!='/'){
                $this->shopUrlBase .= '/';
            }
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

        return $result;
    }
}