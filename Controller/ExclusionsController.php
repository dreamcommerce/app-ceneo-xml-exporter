<?php
/**
 * Created by PhpStorm.
 * User: eRIZ
 * Date: 2015-03-31
 * Time: 13:33
 */

namespace CeneoBundle\Controller;


use CeneoBundle\Manager\ExcludedProductManager;
use CeneoBundle\Services\ProductChecker;
use DreamCommerce\ShopAppstoreBundle\Form\CollectionChoiceList;
use DreamCommerce\ShopAppstoreBundle\Utils\CollectionWrapper;
use DreamCommerce\ShopAppstoreBundle\Utils\InvalidRequestException;
use Symfony\Component\HttpFoundation\Request;

class ExclusionsController extends ControllerAbstract{

    public function indexAction(Request $request){

        $em = new ExcludedProductManager(
            $this->getDoctrine()->getManager()
        );

        $checker = new ProductChecker($em, $this->client);
        $products = $checker->getExcluded($this->shop);

        $valueResolver = function(\ArrayObject $row){
            return $row->translations->pl_PL->name;
        };

        $form = $this->createFormBuilder()
            ->add('products', 'choice', array(
                'choice_list'=>new CollectionChoiceList($products, $valueResolver),
                'multiple'=>true,
                'expanded'=>true
            ))
            ->add('delete', 'submit', array(
                'label'=>'Skasuj'
            ))->add('back', 'submit', array('label'=>'Powrót'))
            ->getForm();

        $form->handleRequest($request);

        if($form->isValid()){
            if($form->get('delete')->isClicked()){
                $em->deleteByProductId($form->getData()['products'], $this->shop);
                $this->addNotice('Produkty zostały usunięte z ignorowanych');
            }

            return $this->redirect(
                $this->generateAppUrl('ceneo_options')
            );
        }

        return $this->render('CeneoBundle::exclusions/index.html.twig', array(
            'products'=>$products,
            'form'=>$form->createView()
        ));
    }

    public function addAction(Request $request){

        $ids = $request->query->get('id');
        if(empty($ids) or !is_array($ids)){
            throw new InvalidRequestException();
        }

        $em = new ExcludedProductManager($this->getDoctrine()->getManager());

        $productChecker = new ProductChecker($em, $this->client);
        $products = $productChecker->getNotExcluded($ids, $this->shop);

        $wrapper = new CollectionWrapper($products);

        $data = array(
            'products'=>$wrapper->getListOfField('product_id')
        );

        $valueResolver = function(\ArrayObject $row){
            return $row->translations->pl_PL->name;
        };

        $form = $this->createFormBuilder($data)
            ->add('products', 'choice', array(
                'choice_list'=>new CollectionChoiceList($products, $valueResolver),
                'multiple'=>true,
                'expanded'=>true
            ))
            ->add('save', 'submit')
            ->getForm();
        $form->handleRequest($request);

        if($form->isValid()){
            $em->addByProductId($form->getData()['products'], $this->shop);
            $this->addNotice('Produkty zostały dodane do ignorowanych');
            return $this->redirect(
                $this->generateAppUrl('ceneo_exclusions')
            );
        }

        $viewProducts = $wrapper->getArray('product_id');

        return $this->render('CeneoBundle::exclusions/add.html.twig', array(
            'products'=>$viewProducts,
            'form'=>$form->createView()
        ));

    }
}