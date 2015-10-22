<?php
/**
 * Created by PhpStorm.
 * User: eRIZ
 * Date: 2015-04-03
 * Time: 19:06
 */

namespace CeneoBundle\Entity;


use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;

class AttributeMapping {

    protected $id;
    protected $shopAttributeId;
    protected $attributeGroup;
    protected $ceneoField;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getShopAttributeId()
    {
        return $this->shopAttributeId;
    }

    /**
     * @param mixed $shopAttributeId
     */
    public function setShopAttributeId($shopAttributeId)
    {
        $this->shopAttributeId = $shopAttributeId;
    }

    /**
     * @return mixed
     */
    public function getCeneoField()
    {
        return $this->ceneoField;
    }

    /**
     * @param mixed $ceneoField
     */
    public function setCeneoField($ceneoField)
    {
        $this->ceneoField = $ceneoField;
    }

    /**
     * @return mixed
     */
    public function getAttributeGroup()
    {
        return $this->attributeGroup;
    }

    /**
     * @param mixed $attributeGroup
     */
    public function setAttributeGroup($attributeGroup)
    {
        $this->attributeGroup = $attributeGroup;
    }


}