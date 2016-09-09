<?php


namespace CeneoBundle\EventListener;


use CeneoBundle\Manager\ExcludedProductManager;
use Doctrine\ORM\EntityManager;
use DreamCommerce\ShopAppstoreBundle\Event\Webhook\ProductDeleteEvent;

class ProductDeleteListener
{

    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var ExcludedProductManager
     */
    protected $epm;

    public function __construct(EntityManager $em, ExcludedProductManager $epm)
    {
        $this->em = $em;
        $this->epm = $epm;
    }

    public function onProductDelete(ProductDeleteEvent $event)
    {
        $shop = $event->getShop();
        $payload = $event->getPayload();

        $this->epm->deleteByProductId($payload->product_id, $shop);
    }
    
}