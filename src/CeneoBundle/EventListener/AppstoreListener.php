<?php
namespace CeneoBundle\EventListener;


use CeneoBundle\Services\ExportStatus;
use Doctrine\ORM\EntityManager;
use DreamCommerce\ShopAppstoreBundle\Event\Appstore\InstallEvent;
use DreamCommerce\ShopAppstoreBundle\Event\Appstore\UninstallEvent;
use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;

class AppstoreListener {

    protected $xmlDir;
    /**
     * @var EntityManager
     */
    protected $manager;
    /**
     * @var ExportStatus
     */
    private $exportStatus;

    public function __construct($xmlDir, EntityManager $manager, ExportStatus $exportStatus){
        $this->xmlDir = $xmlDir;
        $this->manager = $manager;
        $this->exportStatus = $exportStatus;
    }

    public function onUninstall(UninstallEvent $event){

        $params = $event->getPayload();
        $shopHash = $params['shop'];

        $this->removeGeneratedXml($shopHash);
        $this->removePurgedEntities($shopHash);

    }

    public function onInstall(InstallEvent $event){
        $params = $event->getPayload();
        $shopHash = $params['shop'];

        /**
         * @var $shop ShopInterface
         */
        $shop = $this->manager->getRepository('BillingBundle:Shop')->findOneBy(['name'=>$shopHash]);

        $this->exportStatus->initialize($shop);
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

        if(!$shopInstance){
            return;
        }

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