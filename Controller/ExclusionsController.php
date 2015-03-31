<?php
/**
 * Created by PhpStorm.
 * User: eRIZ
 * Date: 2015-03-31
 * Time: 13:33
 */

namespace CeneoBundle\Controller;


use DreamCommerce\ShopAppstoreBundle\Controller\ApplicationController;

class ExclusionsController extends ApplicationController{

    public function indexAction(){
        /**
         * @var $excludedProducts ExcludedProductRepository
         */
        $excludedProducts = $this->getDoctrine()->getRepository('CeneoBundle:ExcludedProduct');
        $products = $excludedProducts->findAllByShop($this->shop);

        return $this->render('CeneoBundle::exclusions/index.html.twig', array(
            'products'=>$products
        ));
    }

}