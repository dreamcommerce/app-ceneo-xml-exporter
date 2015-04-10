<?php
/**
 * Created by PhpStorm.
 * User: eRIZ
 * Date: 2015-03-28
 * Time: 16:43
 */

namespace CeneoBundle\Controller;


use CeneoBundle\Manager\ExcludedProductManager;
use CeneoBundle\Services\Generator;
use DreamCommerce\Client;
use DreamCommerce\ShopAppstoreBundle\EntityManager\ShopManager;
use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GeneratorController extends Controller{

    public function downloadAction($shopId){

        $em = $this->get('doctrine')->getManager();
        $shopManager = new ShopManager($em, 'BillingBundle\Entity\Shop');
        $shop = $shopManager->findShopByNameAndApplication($shopId, 'ceneo');

        if(!$shop){
            throw new NotFoundHttpException();
        }

        $path = sprintf('%s/web/ceneo/xml/%s.xml', dirname($this->container->getParameter('kernel.root_dir')), $shopId);

        $urlPath = $this->generateUrl('ceneo_xml', array(
            'shopId'=>$shopId
        ));

        $config =
            $this->container->getParameter('dream_commerce_shop_appstore.applications');

        $config = $config['ceneo'];
        $client = new Client($shop->getShopUrl(), $config['app_id'], $config['app_secret']);
        $client->setAccessToken($shop->getToken()->getAccessToken());

        $excludedProductManager = new ExcludedProductManager($em);
        $generator = new Generator($path, $client, $excludedProductManager, $shop);

        set_time_limit(0);
        $count = $generator->export($shop);
        $this->get('ceneo.export_checker')->setStatus($count, $shop);

        return $this->redirect($urlPath);

    }

    public function dummyAction(){
        throw new NotFoundHttpException();
    }

}