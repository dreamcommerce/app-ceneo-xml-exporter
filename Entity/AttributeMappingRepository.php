<?php


namespace CeneoBundle\Entity;


class AttributeMappingRepository extends RepositoryAbstract{

    public function findAllByAttributeGroupMapping(AttributeGroupMapping $groupMapping){
        return $this->findBy(array(
            'attributeGroup'=>$groupMapping
        ));
    }

    public function findForForm(AttributeGroupMapping $groupMapping){

        $data = $this->findAllByAttributeGroupMapping($groupMapping);

        $result = array();
        /**
         * @var $a AttributeMapping
         */
        foreach($data as $a){
            $result[$a->getCeneoField()] = $a->getShopAttributeId();
        }

        return $result;

    }

}