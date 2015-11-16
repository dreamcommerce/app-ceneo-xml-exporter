<?php
namespace BillingBundle\Entity;

use DreamCommerce\ShopAppstoreBundle\Doctrine\Billing as BillingBase;

class Billing extends BillingBase
{
    protected $id;

    public function getId(){
        return $this->id;
    }


}