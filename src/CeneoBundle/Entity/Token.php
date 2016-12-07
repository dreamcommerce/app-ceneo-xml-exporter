<?php
namespace CeneoBundle\Entity;


use DreamCommerce\ShopAppstoreBundle\Doctrine\Token as TokenBase;

class Token extends TokenBase{

    protected $id;

    public function getId(){
        return $this->id;
    }

}