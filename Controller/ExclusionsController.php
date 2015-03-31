<?php
/**
 * Created by PhpStorm.
 * User: eRIZ
 * Date: 2015-03-31
 * Time: 13:33
 */

namespace CeneoBundle\Controller;


use CeneoBundle\Entity\ExcludedProductRepository;
use DreamCommerce\ShopAppstoreBundle\Controller\ApplicationController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

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

    public function deleteAction($id = null){

        /**
         * @var $excludedProducts ExcludedProductRepository
         */
        $excludedProducts = $this->getDoctrine()->getRepository('CeneoBundle:ExcludedProduct');
        $em = $this->getDoctrine()->getManager();

        $product = $excludedProducts->findByProductAndShop($id, $this->shop);

        /**
         * @var $session Session
         */
        $session = $this->get('session');

        if($product){
            $em->remove($product);
            $em->flush();

            $session->getFlashBag()->add('notice', 'Produkt został skasowany');
        }else{
            $session->getFlashBag()->all('error', 'Nie znaleziono produktu');
        }


        return $this->redirect(
            $this->generateAppUrl('ceneo_exclusions')
        );

    }

}