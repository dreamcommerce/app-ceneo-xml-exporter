<?php
/**
 * Created by PhpStorm.
 * User: eRIZ
 * Date: 2015-03-31
 * Time: 17:32
 */

namespace CeneoBundle\Manager;


use CeneoBundle\Entity\AttributeGroupMapping;
use CeneoBundle\Entity\AttributeGroupMappingRepository;
use CeneoBundle\Entity\AttributeMapping;
use CeneoBundle\Entity\AttributeMappingRepository;
use Doctrine\ORM\EntityManager;
use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;

class AttributeMappingManager {

    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var AttributeGroupMappingRepository
     */
    protected $repository;

    public function __construct(EntityManager $em){
        $this->em = $em;
        $this->repository = $em->getRepository('CeneoBundle:AttributeMapping');
    }

    /**
     * @return AttributeMappingRepository
     */
    public function getRepository(){
        return $this->repository;
    }

    public function saveMapping(AttributeGroupMapping $groupMapping, $mappings, ShopInterface $shop){
        $attributes = $this->repository->findAllByAttributeGroupMapping($groupMapping);

        /**
         * @var $a AttributeMapping
         */
        foreach($attributes as $a){
            $attributeName = $a->getCeneoField();

            if(empty($mappings[$attributeName])){
                $this->em->remove($a);
            }else if($a->getShopAttributeId()!=$mappings[$attributeName]){
                $a->setShopAttributeId($mappings[$attributeName]);
                $this->em->persist($a);
            }

            unset($mappings[$attributeName]);
        }

        foreach($mappings as $k=>$v){
            if(empty($v)){
                continue;
            }

            $attribute = new AttributeMapping();
            $attribute->setAttributeGroup($groupMapping);
            $attribute->setCeneoField($k);
            $attribute->setShopAttributeId($v);
            $attribute->setShop($shop);

            $this->em->persist($attribute);
        }

        $this->em->flush();
    }

    public function deleteByAttributeId($id, ShopInterface $shop)
    {
        $id = (array)$id;
        $q = $this->em->createQueryBuilder();
        $q->delete('CeneoBundle:AttributeMapping', 'am')
            ->where('am.shopAttributeId in(:attribute_id)')
            ->andWhere('am.shop = :shop')
            ->setParameter('attribute_id', $id)
            ->setParameter('shop', $shop);

        $q->getQuery()->execute();
    }

}