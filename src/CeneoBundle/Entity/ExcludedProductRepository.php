<?php
namespace CeneoBundle\Entity;


use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;

class ExcludedProductRepository extends RepositoryAbstract{

    const RECORDS_PER_PAGE = 40;

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

    public function countAllByShop(ShopInterface $shop)
    {
        $q = $this->createQueryBuilder('ep')
            ->select('count(ep.id)')
            ->where('ep.shop=:shop')
            ->setParameter('shop', $shop);

        $count = $q->getQuery()->getSingleScalarResult();
        return $count;
    }

    public function findAllByShopPaged(ShopInterface $shop, $page = 1)
    {
        $count = $this->countAllByShop($shop);

        $start = ($page-1)*self::RECORDS_PER_PAGE;

        if($start>=$count){
            throw new \Exception('Page not found');
        }

        return $this->findBy([
            'shop'=>$shop
        ], [
            'title'=>'ASC'
        ], self::RECORDS_PER_PAGE, $start);
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

        $records = $this->getColumnValues($q->getQuery(), 'product_id');
        return $records;
    }

    public function findIdsByShop(ShopInterface $s){
        // todo: refactor copy-paste
        $q = $this->createQueryBuilder('ep');

        $q->select('ep.product_id')
            ->where('ep.shop = :shop_id')
            ->setParameter('shop_id', $s);
        $records = $this->getColumnValues($q->getQuery(), 'product_id');
        return $records;
    }

}