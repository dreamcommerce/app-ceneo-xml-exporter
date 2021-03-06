<?php
namespace CeneoBundle\Controller;


use CeneoBundle\Entity\ExcludedProduct;
use CeneoBundle\Entity\ExcludedProductRepository;
use CeneoBundle\Manager\ExcludedProductManager;
use CeneoBundle\Services\ProductChecker;
use CeneoBundle\Services\ProductResolver;
use DreamCommerce\ShopAppstoreBundle\Utils\Fetcher;
use DreamCommerce\ShopAppstoreLib\Resource\Product;
use DreamCommerce\ShopAppstoreBundle\Form\CollectionChoiceListLoader;
use DreamCommerce\ShopAppstoreBundle\Utils\CollectionWrapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
        $resource->limit($perPage);
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
                'choices_as_values'=>true,
                'choice_loader'=>new CollectionChoiceListLoader($products, $keyResolver, $valueResolver),
                'multiple'=>true,
                'expanded'=>true
            ))
            ->add('submit', SubmitType::class, array(
                'label'=>'button.delete'
            ))->add('back', SubmitType::class, array('label'=>'button.back'))
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

    public function excludeAllAction(Request $request)
    {

        $resource = new Product($this->client);

        $form = $this->createFormBuilder()
            ->add('back', SubmitType::class, ['label'=>'button.back'])
            ->add('exclude', SubmitType::class, ['label'=>'button.exclude'])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted()){

            if($form->get('back')->isClicked()){
                return new RedirectResponse(
                    $this->generateAppUrl('ceneo_options')
                );
            }

            $em = new ExcludedProductManager($this->get('doctrine')->getManager());
            $em->clearByShop($this->shop);

            $fetcher = new Fetcher($resource);
            $data = $fetcher->fetchAll();

            $wrapper = new CollectionWrapper($data);
            $ids = $wrapper->getListOfField('product_id');
            $em->addProductsByIdentifiers($ids, $this->shop);

            $this->get('session')->getFlashBag()->add('notice', $this->get('translator')->trans('excluded.done'));

            return new RedirectResponse(
                $this->generateAppUrl('ceneo_options')
            );

        }else{
            $collection = $resource->get();
            $count = $collection->count;

            return $this->render('CeneoBundle:exclusions:all.html.twig', [
                'products_count'=>$count,
                'form'=>$form->createView()
            ]);
        }

    }
}