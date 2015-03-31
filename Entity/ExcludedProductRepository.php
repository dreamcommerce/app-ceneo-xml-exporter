<?php
/**
 * Created by PhpStorm.
 * User: eRIZ
 * Date: 2015-03-30
 * Time: 14:08
 */

namespace CeneoBundle\Entity;


use Doctrine\ORM\EntityRepository;
use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;

class ExcludedProductRepository extends EntityRepository{

    public function getProductsCountByShop(ShopInterface $shop){
        $q = $this->createQueryBuilder('ep');
        $q->select('count(ep.product_id)')
            ->where('ep.shop = :shop_id')
            ->setParameter('shop_id', $shop);

        return $q->getQuery()->getSingleScalarResult();
    }

    public function findAllByShop(ShopInterface $shop){
        return $this->findBy(array(
            'shop'=>$shop
        ));
    }

    /**
     * @param $id
     * @param ShopInterface $s
     * @return ExcludedProduct
     */
    public function findByProductAndShop($id, ShopInterface $s){
        $result = $this->findBy(array(
            'shop'=>$s,
            'id'=>$id
        ));

        if($result){
            $result = $result[0];
        }

        return $result;
    }

}