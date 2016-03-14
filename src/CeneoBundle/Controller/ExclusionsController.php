<?php
namespace CeneoBundle\Controller;


use CeneoBundle\Entity\ExcludedProduct;
use CeneoBundle\Entity\ExcludedProductRepository;
use CeneoBundle\Manager\ExcludedProductManager;
use CeneoBundle\Services\ProductChecker;
use CeneoBundle\Services\ProductResolver;
use DreamCommerce\ShopAppstoreLib\Resource\Product;
use DreamCommerce\ShopAppstoreBundle\Form\CollectionChoiceListLoader;
use DreamCommerce\ShopAppstoreBundle\Utils\CollectionWrapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class ExclusionsController extends ControllerAbstract{

    public function indexAction(Request $request){

        $em = new ExcludedProductManager(
            $this->getDoctrine()->getManager()
        );

        $page = $request->attributes->get('page');

        $repo = $this->getDoctrine()->getRepository('CeneoBundle:ExcludedProduct');
        $count = $repo->countAllByShop($this->shop);
        $productsIds = $repo->findAllByShopPaged($this->shop, $page);

        $perPage = ExcludedProductRepository::RECORDS_PER_PAGE;

        $ids = [];
        /**
         * @var $productsIds ExcludedProduct[]
         */
        foreach($productsIds as $i){
            $ids[] = $i->getProductId();
        }

        $resource = new Product($this->client);
        $resource->filters([
            'product_id'=>[
                'in'=>$ids
            ]
        ]);

        $products = $resource->get();

        $valueResolver = function(\ArrayObject $row){
            if(empty($row->translations->pl_PL)){
                return false;
            }
            return $row->translations->pl_PL->name;
        };

        $keyResolver = function(\ArrayObject $row){
            return $row->product_id;
        };

        $form = $this->createFormBuilder()
            ->add('products', ChoiceType::class, array(
                'choice_loader'=>new CollectionChoiceListLoader($products, $keyResolver, $valueResolver),
                'multiple'=>true,
                'expanded'=>true
            ))
            ->add('submit', SubmitType::class, array(
                'label'=>'Skasuj'
            ))->add('back', 'submit', array('label'=>'Powrót'))
            ->getForm();

        $form->handleRequest($request);

        if($form->isValid()){
            if($form->get('submit')->isClicked()){
                $em->deleteByProductId($form->getData()['products'], $this->shop);
                $this->addNotice('Produkty zostały usunięte z ignorowanych');
            }

            return $this->redirect(
                $this->generateAppUrl('ceneo_options')
            );
        }

        $pages = ceil($count/$perPage);

        return $this->render('CeneoBundle::exclusions/index.html.twig', array(
            'products'=>$products,
            'form'=>$form->createView(),
            'page'=>$page,
            'count'=>$count,
            'perPage'=>$perPage,
            'pages'=>$pages
        ));
    }

    public function addAction(Request $request){

        $ids = (array)$request->query->get('id');
        if(empty($ids) or !is_array($ids)){
            throw new \Exception();
        }

        $translations = $request->query->get('translations', 'pl_PL');

        $em = new ExcludedProductManager($this->getDoctrine()->getManager());

        $productResolver = new ProductResolver($this->client);
        $productsIdentifiers = $productResolver->getProductIdFromStock($ids);

        $productChecker = new ProductChecker($em, $this->client, $this->get('ceneo.orphans_purger'));
        $products = $productChecker->getNotExcluded($productsIdentifiers, $this->shop);

        if(!count($products)){
            $this->addNotice('Wszystkie wybrane produkty zostały już zignorowane');
            return $this->redirect($this->generateAppUrl('ceneo_options'));
        }

        $wrapper = new CollectionWrapper($products);

        $data = array(
            'products'=>$wrapper->getListOfField('product_id')
        );

        $valueResolver = function(\ArrayObject $row) use ($translations){
            if(empty($row->translations->$translations)){
                return false;
            }
            return $row->translations->$translations->name;
        };

        $keyResolver = function(\ArrayObject $row){
            return $row->product_id;
        };

        $form = $this->createFormBuilder($data)
            ->add('products', ChoiceType::class, array(
                'choice_loader'=>new CollectionChoiceListLoader($products, $keyResolver, $valueResolver),
                'multiple'=>true,
                'expanded'=>true
            ))
            ->add('submit', SubmitType::class, array(
                'label'=>'action.exclude'
            ))
            ->getForm();
        $form->handleRequest($request);

        if($form->isValid()){
            $productsList = $productResolver->getProductListForExclusion($form->getData()['products']);

            $em->addProducts($productsList, $this->shop);
            $this->addNotice('Produkty zostały dodane do ignorowanych');
            return $this->redirect(
                $this->generateAppUrl('ceneo_options')
            );
        }

        $viewProducts = $wrapper->getArray('product_id');

        return $this->render('CeneoBundle::exclusions/add.html.twig', array(
            'products'=>$viewProducts,
            'form'=>$form->createView()
        ));

    }
}