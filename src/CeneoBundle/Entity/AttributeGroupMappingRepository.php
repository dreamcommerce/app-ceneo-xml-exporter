<?php


namespace CeneoBundle\Entity;


use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;

class AttributeGroupMappingRepository extends RepositoryAbstract{

    /**
     * @param ShopInterface $shop
     * @param $attributeGroupId
     * @return null|AttributeGroupMapping
     */
    public function findByShopAndAttributeGroupId(ShopInterface $shop, $attributeGroupId){

        return $this->findOneBy(array(
            'shop'=>$shop,
            'shopAttributeGroupId'=>$attributeGroupId
        ));

    }

    public function findFormGroups(){

        $data = $this->findAll();

        $result = array();

        /**
         * @var $a AttributeGroupMapping
         */
        foreach($data as $a){
            $result[$a->getShopAttributeGroupId()] = array(
                'group'=>$a->getCeneoGroup()
            );
        }

        return $result;

    }

    public function findByShop(ShopInterface $shop){
        return $this->findOneBy(array(
            'shop'=>$shop
        ));
    }

    public function findAllByShop(ShopInterface $shop){
        return $this->findBy(array(
            'shop'=>$shop
        ));
    }

}