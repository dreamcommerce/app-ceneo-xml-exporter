<?php
namespace BillingBundle\Entity;


use DreamCommerce\ShopAppstoreBundle\Doctrine\Shop as ShopBase;

class Shop extends ShopBase{

    protected $id;

    public function getId(){
        return $this->id;
    }

}