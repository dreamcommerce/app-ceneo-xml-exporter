<?php
/**
 * Created by PhpStorm.
 * User: eRIZ
 * Date: 2015-03-30
 * Time: 14:08
 */

namespace CeneoBundle\Entity;


use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;

class ExcludedProductRepository extends RepositoryAbstract{

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
     * @return array
     */
    public function findByProductAndShop($id, ShopInterface $s){
        return $this->findBy(array(
            'shop'=>$s,
            'id'=>$id
        ));
    }

    public function findIdsByProductAndShop($id, ShopInterface $s){
        $q = $this->createQueryBuilder('ep');

        $q->select('ep.product_id')
            ->where('ep.shop = :shop_id and ep.product_id in(:product_id)')
            ->setParameter('shop_id', $s)
            ->setParameter('product_id', $id);

        $records = $this->getColumnValues($q->getQuery());
        return $records;
    }

}