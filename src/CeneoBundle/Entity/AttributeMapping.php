<?php
namespace CeneoBundle\Entity;


use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;

class AttributeMapping {

    protected $id;
    protected $shopAttributeId;
    protected $attributeGroup;
    protected $ceneoField;
    protected $shop;

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

    /**
     * @return ShopInterface
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * @param ShopInterface $shop
     */
    public function setShop($shop)
    {
        $this->shop = $shop;
    }


}