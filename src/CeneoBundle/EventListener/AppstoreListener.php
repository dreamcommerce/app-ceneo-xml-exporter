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

    /**
     * @param $shopHash
     */
    protected function removeGeneratedXml($shopHash)
    {
        $path = sprintf('%s/%s.xml', $this->xmlDir, basename($shopHash));
        $gzPath = $path.'.gz';
        file_exists($path) && unlink($path);
        file_exists($gzPath) && unlink($gzPath);
    }

}