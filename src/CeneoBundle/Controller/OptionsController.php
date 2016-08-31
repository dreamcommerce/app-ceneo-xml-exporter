<?php

namespace CeneoBundle\Controller;

use CeneoBundle\Entity\ExcludedProductRepository;
use CeneoBundle\Services\ExportStatus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OptionsController extends ControllerAbstract
{

    public function indexAction(Request $request){


        $shopId = $this->shop->getName();

        /**
         * @var $exportStatus ExportStatus
         */
        $exportStatus = $this->get('ceneo.export_status');

        if($exportStatus->exportExists($this->shop)) {
            $xmlLink = $this->get('router')->generate('ceneo_xml', array(
                'shopId' => $shopId
            ), UrlGeneratorInterface::ABSOLUTE_URL);
        }else{
            $xmlLink = false;
        }

        $status = $exportStatus->getStatus($this->shop);

        $enqueueLink = $this->generateAppUrl('ceneo_enqueue');
        $statusLink = $this->generateAppUrl('ceneo_status_check');
        $excludeAllLink = $this->generateAppUrl('ceneo_exclude_all');

        /**
         * @var $excludedProducts ExcludedProductRepository
         */
        $excludedProducts = $this->getDoctrine()->getRepository('CeneoBundle:ExcludedProduct');

        $count = $excludedProducts->getProductsCountByShop($this->shop);

        $stockLink = $this->shop->getShopUrl().'/admin/stock';

        $services = $this->get('ceneo.shop_version_checker');
        $upgradeNeeded = !$services->arePicturesSupported($this->shop);

        // add current IP for whitelist
        $request->getSession()->set('ip', $_SERVER['REMOTE_ADDR']);

        return $this->render('CeneoBundle::options/index.html.twig', array(
            'xml_link'=>$xmlLink,
            'enqueue_link'=>$enqueueLink,
            'status_link'=>$statusLink,
            'excluded_count'=>$count,
            'stock_link'=> $stockLink,
            'export_status'=>$status,
            'exclude_all_link'=>$excludeAllLink,
            'upgrade_needed'=>$upgradeNeeded
        ));
    }

}
