<?php
namespace CeneoBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;

class AttributeGroupMapping {

    protected $id;
    protected $shop;
    protected $shopAttributeGroupId;
    protected $attributes;
    protected $ceneoGroup;

    public function __construct(){
        $this->attributes = new ArrayCollection();
    }

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
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * @param mixed $shop
     */
    public function setShop(ShopInterface $shop)
    {
        $this->shop = $shop;
    }

    /**
     * @return mixed
     */
    public function getShopAttributeGroupId()
    {
        return $this->shopAttributeGroupId;
    }

    /**
     * @param mixed $shopAttributeGroupId
     */
    public function setShopAttributeGroupId($shopAttributeGroupId)
    {
        $this->shopAttributeGroupId = $shopAttributeGroupId;
    }

    /**
     * @return mixed
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    public function addAttribute(AttributeMapping $attributeMapping){
        $this->attributes[] = $attributeMapping;
    }

    /**
     * @return mixed
     */
    public function getCeneoGroup()
    {
        return $this->ceneoGroup;
    }

    /**
     * @param mixed $ceneoGroup
     */
    public function setCeneoGroup($ceneoGroup)
    {
        $this->ceneoGroup = $ceneoGroup;
    }


}