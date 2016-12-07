<?php
namespace CeneoBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use DreamCommerce\ShopAppstoreBundle\Doctrine\Shop as ShopBase;

class Shop extends ShopBase{

    protected $id;
    /**
     * @var Export
     */
    protected $export;
    /**
     * @var ExcludedProduct[]
     */
    protected $excludedProducts;
    /**
     * @var AttributeGroupMapping[]
     */
    protected $attributeGroupMappings;

    public function __construct()
    {
        parent::__construct();
        $this->excludedProducts = new ArrayCollection();
        $this->attributeGroupMappings = new ArrayCollection();
    }


    public function getId(){
        return $this->id;
    }

    /**
     * @return Export
     */
    public function getExport()
    {
        return $this->export;
    }

    /**
     * @param Export $export
     */
    public function setExport($export)
    {
        $this->export = $export;
    }

    /**
     * @return ExcludedProduct[]
     */
    public function getExcludedProducts()
    {
        return $this->excludedProducts;
    }

    /**
     * @param ExcludedProduct $excludedProduct
     */
    public function addExcludedProduct($excludedProduct)
    {
        $this->excludedProducts[] = $excludedProduct;
    }

    /**
     * @return AttributeGroupMapping[]
     */
    public function getAttributeGroupMappings()
    {
        return $this->attributeGroupMappings;
    }

    /**
     * @param AttributeGroupMapping $attributeGroupMapping
     */
    public function addAttributeGroupMapping($attributeGroupMapping)
    {
        $this->attributeGroupMappings[] = $attributeGroupMapping;
    }

}