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
use CeneoBundle\Entity\ExcludedProduct;
use CeneoBundle\Entity\ExcludedProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;

class AttributeGroupMappingManager {

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
        $this->repository = $em->getRepository('CeneoBundle:AttributeGroupMapping');
    }

    /**
     * @return AttributeGroupMappingRepository
     */
    public function getRepository(){
        return $this->repository;
    }

    public function saveMapping(ShopInterface $shop, $attributeGroupId, $ceneoGroup){
        $groupMapping = $this->getRepository()->findByShopAndAttributeGroupId($shop, $attributeGroupId);

        // "others" doesn't need any mappings in database
        if($groupMapping && $ceneoGroup=='other'){
            $this->em->remove($groupMapping);
            $this->em->flush();
            return;
        }

        if(!$groupMapping){
            $groupMapping = new AttributeGroupMapping();
            $groupMapping->setShop($shop);
        }else{
            // nothing changed
            if($groupMapping->getCeneoGroup()==$ceneoGroup){
                return $groupMapping->getId();
            }

            $attributes = $groupMapping->getAttributes();
            foreach($attributes as $a) {
                $this->em->remove($a);
            }
        }

        $groupMapping->setShopAttributeGroupId($attributeGroupId);
        $groupMapping->setCeneoGroup($ceneoGroup);

        $this->em->persist($groupMapping);
        $this->em->flush();

        return $groupMapping->getId();

    }

}