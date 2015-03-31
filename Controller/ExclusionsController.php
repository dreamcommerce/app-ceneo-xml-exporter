<?php
/**
 * Created by PhpStorm.
 * User: eRIZ
 * Date: 2015-03-31
 * Time: 13:33
 */

namespace CeneoBundle\Controller;


use CeneoBundle\Entity\ExcludedProductRepository;
use CeneoBundle\Manager\ExcludedProductManager;
use DreamCommerce\ShopAppstoreBundle\Controller\ApplicationController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class ExclusionsController extends ControllerAbstract{

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

    public function deleteAction($id = null){

        $manager = new ExcludedProductManager(
            $this->getDoctrine()->getManager()
        );

        $products = $manager->getRepository()->findByProductAndShop($id, $this->shop);

        if($products){
            $manager->delete($products);
            $this->addNotice('Produkty zostały skasowane');
        }else{
            $this->addError('Nie znaleziono produktów');
        }

        return $this->redirect(
            $this->generateAppUrl('ceneo_exclusions')
        );

    }

}