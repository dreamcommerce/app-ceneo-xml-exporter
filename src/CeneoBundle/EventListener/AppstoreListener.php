<?php
namespace CeneoBundle\EventListener;


use Doctrine\ORM\EntityManager;
use DreamCommerce\ShopAppstoreBundle\Event\Appstore\UninstallEvent;
use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;

class AppstoreListener {

    protected $xmlDir;
    /**
     * @var EntityManager
     */
    protected $manager;

    public function __construct($xmlDir, EntityManager $manager){
        $this->xmlDir = $xmlDir;
        $this->manager = $manager;
    }

    public function onUninstall(UninstallEvent $event){

        $params = $event->getPayload();
        $shopHash = $params['shop'];

        $this->removeGeneratedXml($shopHash);
        $this->removePurgedEntities($shopHash);

    }

    protected function removePurgedEntities($hash){

        /**
         * @var $shopInstance ShopInterface
         */
        $shopInstance =
            $this->manager->getRepository('BillingBundle\Entity\Shop')
                ->findOneBy([
                    'name'=>$hash
                ]);

        $this->removeCollectionByShop('CeneoBundle\Entity\Export', $shopInstance);
        $this->removeCollectionByShop('CeneoBundle\Entity\ExcludedProduct', $shopInstance);
        $this->removeCollectionByShop('CeneoBundle\Entity\AttributeGroupMapping', $shopInstance);

        $this->manager->flush();
    }

    protected function removeCollectionByShop($entityPath, ShopInterface $shop){
        $collection = $this->manager->getRepository($entityPath)
            ->findBy([
                'shop'=>$shop
            ]);

        foreach($collection as $entity){
            $this->manager->remove($entity);
        }
    }

    /**
     * @param $shopHash
     */
    protected function removeGeneratedXml($shopHash)
    {
        $path = sprintf('%s/%s.xml', $this->xmlDir, basename($shopHash));
        @unlink($path);
    }

}