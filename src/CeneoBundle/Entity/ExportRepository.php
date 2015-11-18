<?php
namespace CeneoBundle\Entity;


use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;

class ExportRepository extends RepositoryAbstract{

    public function findByShop(ShopInterface $shopInterface){
        return $this->findOneBy(array(
            'shop'=>$shopInterface
        ));
    }

    public function findIdleShopIds(){
        $em = $this->getEntityManager();
        $result = $em->createQuery("SELECT IDENTITY(e.shop) FROM CeneoBundle:Export e WHERE e.inProgress=false")->getScalarResult();
        $ids = array_map('current', $result);
        return $ids;
    }

}