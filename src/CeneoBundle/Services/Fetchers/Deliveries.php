<?php


namespace CeneoBundle\Services\Fetchers;


use DreamCommerce\Resource\Delivery;
use DreamCommerce\ShopAppstoreBundle\Utils\CollectionWrapper;

class Deliveries extends FetcherAbstract
{

    protected $deliveries = [];

    protected function fetch(){

        $deliveriesResource = new Delivery($this->client);
        $list = $deliveriesResource->get();

        $wrapper = new CollectionWrapper($list);
        $deliveries = $wrapper->getArray('delivery_id');

        $this->deliveries = $deliveries;
    }

    public function getDaysForDeliveryId($deliveryId){
        if(!isset($this->deliveries[$deliveryId])){
            return 99;
        }else{

            $delivery = $this->deliveries[$deliveryId];
            $days = $delivery->days;

            if($days>14){
                return 99;
            }

            if($days>7){
                return 14;
            }

            if($days>3){
                return 7;
            }

            if($days>1){
                return 3;
            }

            return 1;
        }
    }

}