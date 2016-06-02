<?php
namespace CeneoBundle\Manager;


use CeneoBundle\Entity\ExcludedProduct;
use CeneoBundle\Entity\ExcludedProductRepository;
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

    public function addProducts($products, ShopInterface $shop){

        foreach($products as $id=>$title){
            $obj = new ExcludedProduct();
            $obj->setShop($shop);
            $obj->setProductId($id);
            $obj->setTitle($title);
            $obj->setLink('http://example.org');
            $this->em->persist($obj);
        }

        $this->em->flush();

    }

    public function clearByShop(ShopInterface $shop)
    {
        $b = $this->em->createQuery('DELETE CeneoBundle\Entity\ExcludedProduct e WHERE e.shop=:shop');
        $b->setParameter('shop', $shop);
        $b->execute();
        $this->em->flush();
    }

    public function addProductsByIdentifiers($ids, ShopInterface $shop)
    {

        $counter = 0;

        foreach($ids as $id){
            $ep = new ExcludedProduct();
            $ep->setProductId($id);
            $ep->setShop($shop);
            $ep->setLink('http://example.org');
            $ep->setTitle('');
            $this->em->persist($ep);

            if($counter++ % 50 == 0){
                $this->em->flush();
            }
        }

        $this->em->flush();

    }

    public function purgeNonExistingProducts($existing, $found, ShopInterface $shop){
        $idsToDelete = array_diff($existing, $found);
        $q = $this->em->createQueryBuilder();
        $q->delete('CeneoBundle:ExcludedProduct', 'ep')
            ->where('ep.product_id in(:product_id)')
            ->andWhere('ep.shop = :shop')
            ->setParameter('product_id', $idsToDelete)
            ->setParameter('shop', $shop);

        $q->getQuery()->execute();
    }

    public function deleteByProductId($id, ShopInterface $shop){
        $ids = (array)$id;
        $this->purgeNonExistingProducts($ids, array(), $shop);
    }

}