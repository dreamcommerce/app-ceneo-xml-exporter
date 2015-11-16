<?php
namespace BillingBundle\Entity;

use DreamCommerce\ShopAppstoreBundle\Doctrine\Subscription as SubscriptionBase;

class Subscription extends SubscriptionBase
{
    protected $id;

    public function getId(){
        return $this->id;
    }


}