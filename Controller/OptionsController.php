<?php

namespace CeneoBundle\Controller;

use CeneoBundle\Entity\ExcludedProductRepository;
use DreamCommerce\Client;
use DreamCommerce\Resource\Attribute;
use DreamCommerce\ShopAppstoreBundle\Controller\ApplicationController;
use DreamCommerce\ShopAppstoreBundle\Controller\ShopInterface;
use DreamCommerce\ShopAppstoreBundle\Model\ShopManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class OptionsController extends ApplicationController
{

    public function indexAction(){


        $shopId = $this->shop->getName();

        $xmlLink = $this->get('router')->generate('ceneo_xml', array(
            'shopId' => $shopId
        ), true);

        /**
         * @var $excludedProducts ExcludedProductRepository
         */
        $excludedProducts = $this->getDoctrine()->getRepository('CeneoBundle:ExcludedProduct');
        $count = $excludedProducts->getProductsCountByShop($this->shop);

        $stockLink = $this->shop->getShopUrl().'admin/stock';

        return $this->render('CeneoBundle::options/index.html.twig', array(
            'xml_link'=>$xmlLink,
            'excluded_count'=>$count,
            'stock_link'=> $stockLink
        ));
    }

}
