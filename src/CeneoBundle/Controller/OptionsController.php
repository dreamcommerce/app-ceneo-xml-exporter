<?php

namespace CeneoBundle\Controller;

use CeneoBundle\Entity\ExcludedProductRepository;
use CeneoBundle\Services\ExportChecker;

class OptionsController extends ControllerAbstract
{

    public function indexAction(){


        $shopId = $this->shop->getName();

        $xmlLink = $this->get('router')->generate('ceneo_xml', array(
            'shopId' => $shopId
        ), true);

        $xmlForceLink = $this->get('router')->generate('ceneo_xml_force', array(
            'shopId' => $shopId
        ), true);

        /**
         * @var $excludedProducts ExcludedProductRepository
         */
        $excludedProducts = $this->getDoctrine()->getRepository('CeneoBundle:ExcludedProduct');
        $count = $excludedProducts->getProductsCountByShop($this->shop);

        $stockLink = $this->shop->getShopUrl().'/admin/stock';

        /**
         * @var $exportChecker ExportChecker
         */
        $exportChecker = $this->get('ceneo.export_checker');
        $status = $exportChecker->getStatus($this->shop);

        return $this->render('CeneoBundle::options/index.html.twig', array(
            'xml_link'=>$xmlLink,
            'xml_force_link'=>$xmlForceLink,
            'excluded_count'=>$count,
            'stock_link'=> $stockLink,
            'export_status'=>$status
        ));
    }

}
