<?php
namespace CeneoBundle\EventListener;


use DreamCommerce\ShopAppstoreBundle\Event\Appstore\UninstallEvent;

class AppstoreListener {

    protected $xmlDir;

    public function __construct($xmlDir){
        $this->xmlDir = $xmlDir;
    }

    public function onUninstall(UninstallEvent $event){

        $params = $event->getPayload();

        $path = sprintf('%s/%s.xml', $this->xmlDir, basename($params['shop']));

        @unlink($path);

    }

}