<?php


namespace CeneoBundle\Services;


use CeneoBundle\Manager\AttributeGroupMappingManager;
use CeneoBundle\Manager\AttributeMappingManager;
use CeneoBundle\Manager\ExcludedProductManager;
use DreamCommerce\Client;
use DreamCommerce\Resource;
use DreamCommerce\Resource\Attribute;
use DreamCommerce\Resource\AttributeGroup;
use DreamCommerce\Resource\Product;
use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;
use DreamCommerce\ShopAppstoreBundle\Utils\CollectionWrapper;
use DreamCommerce\ShopAppstoreBundle\Utils\Fetcher;

class OrphansPurger
{

    /**
     * @var AttributeMappingManager
     */
    private $attributeMappingManager;
    /**
     * @var ExcludedProductManager
     */
    private $excludedProductManager;
    /**
     * @var AttributeGroupMappingManager
     */
    private $attributeGroupMappingManager;

    public function __construct(
        ExcludedProductManager $excludedProductManager,
        AttributeMappingManager $attributeMappingManager,
        AttributeGroupMappingManager $attributeGroupMappingManager
    ){

        $this->attributeMappingManager = $attributeMappingManager;
        $this->excludedProductManager = $excludedProductManager;
        $this->attributeGroupMappingManager = $attributeGroupMappingManager;
    }

    protected function purgeResource($found, Resource $resource, $field, Callable $callbackDelete){
        if(empty($found)){
            return new \ArrayObject();
        }

        $fetcher = new Fetcher($resource);

        $resource
            ->filters(array(
                $field=>array(
                    'in'=>$found
                )
            ));

        $result = $fetcher->fetchAll();

        $wrapper = new CollectionWrapper($result);
        $foundIds = $wrapper->getListOfField($field);

        $idsToDelete = array_diff($found, $foundIds);
        if($idsToDelete){
            $callbackDelete($idsToDelete);
        }

        return $foundIds;
    }

    public function purgeExcluded($found, Client $client, ShopInterface $shop)
    {
        $resource = new Product($client);
        $resource->order('translation.pl_PL.name ASC');

        return $this->purgeResource($found, $resource, 'product_id', function($idsToDelete) use ($shop){
            $this->excludedProductManager->deleteByProductId($idsToDelete, $shop);
        });
    }

    public function purgeAttributeGroups($found, Client $client, ShopInterface $shop)
    {
        $resource = new AttributeGroup($client);

        return $this->purgeResource($found, $resource, 'attribute_group_id', function($idsToDelete) use ($shop){
            $this->attributeGroupMappingManager->deleteByAttributeGroupId($idsToDelete, $shop);
        });
    }

    public function purgeAttributes($found, Client $client, ShopInterface $shop)
    {

        $resource = new Attribute($client);

        return $this->purgeResource($found, $resource, 'attribute_id', function($idsToDelete) use ($shop){
            $this->attributeMappingManager->deleteByAttributeId($idsToDelete);
        });
    }

}