<?php
/**
 * Created by PhpStorm.
 * User: eRIZ
 * Date: 2015-03-27
 * Time: 18:00
 */

namespace BillingBundle\Entity;


use DreamCommerce\ShopAppstoreBundle\Model\Shop as ShopBase;

class Shop extends ShopBase{

    protected $id;

    public function getId(){
        return $this->id;
    }

}