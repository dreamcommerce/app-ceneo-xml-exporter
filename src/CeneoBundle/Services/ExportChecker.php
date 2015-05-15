<?php
/**
 * Created by PhpStorm.
 * User: eRIZ
 * Date: 2015-04-03
 * Time: 14:30
 */

namespace CeneoBundle\Services;


use CeneoBundle\Entity\Export;
use Doctrine\ORM\EntityManager;
use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;

class ExportChecker {
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em){
        $this->em = $em;
    }

    public function getStatus(ShopInterface $shop){
        $result = $this->em->getRepository('CeneoBundle\Entity\Export')->findByShop($shop);
        if(is_array($result)){
            $result = $result[0];
        }
        return $result;
    }

    public function setStatus($productsCount, ShopInterface $shop){
        $entity = $this->getStatus($shop);
        if(!$entity){
            $entity = new Export();
            $entity->setShop($shop);
        }

        $entity->setProductsCount($productsCount);
        $entity->setDate(new \DateTime());

        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }

}