<?php
/**
 * Created by PhpStorm.
 * User: eRIZ
 * Date: 2015-03-31
 * Time: 17:32
 */

namespace CeneoBundle\Manager;


use CeneoBundle\Entity\ExcludedProduct;
use CeneoBundle\Entity\ExcludedProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;

class ExcludedProductManager {

    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var ExcludedProductRepository
     */
    protected $repository;

    public function __construct(EntityManager $em){
        $this->em = $em;
        $this->repository = $em->getRepository('CeneoBundle:ExcludedProduct');
    }

    public function getRepository(){
        return $this->repository;
    }

    /**
     * @param array $products
     */
    public function delete($products){

        foreach($products as $p){
            $this->em->remove($p);
        }

        $this->em->flush();
    }

    public function addByProductId($id, ShopInterface $shop){

        $ids = (array)$id;

        foreach($ids as $product){
            $obj = new ExcludedProduct();
            $obj->setShop($shop);
            $obj->setProductId($product);
            $obj->setTitle($product);
            $obj->setLink('http://example.org');
            $this->em->persist($obj);
        }

        $this->em->flush();

    }

}