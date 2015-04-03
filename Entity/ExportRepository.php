<?php
/**
 * Created by PhpStorm.
 * User: eRIZ
 * Date: 2015-04-03
 * Time: 14:25
 */

namespace CeneoBundle\Entity;


use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;

class ExportRepository extends RepositoryAbstract{

    function findByShop(ShopInterface $shopInterface){
        return $this->findOneBy(array(
            'shop'=>$shopInterface
        ));
    }

}