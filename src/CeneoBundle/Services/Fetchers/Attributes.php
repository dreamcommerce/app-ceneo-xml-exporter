<?php


namespace CeneoBundle\Services\Fetchers;


use CeneoBundle\Entity\AttributeGroupMapping;
use CeneoBundle\Entity\AttributeGroupMappingRepository;
use CeneoBundle\Entity\AttributeMapping;
use DreamCommerce\Resource\Attribute;
use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;
use DreamCommerce\ShopAppstoreBundle\Utils\CollectionWrapper;
use DreamCommerce\ShopAppstoreBundle\Utils\Fetcher;

class Attributes extends FetcherAbstract
{

    protected $attributes = [];
    protected $mappings = [];

    public function setMappings(AttributeGroupMappingRepository $attributeGroupMappingRepository, ShopInterface $shop){
        $mappings = $attributeGroupMappingRepository->findAllByShop($shop);
        /**
         * @var $m AttributeGroupMapping
         */
        foreach($mappings as $m){
            $this->mappings[$m->getShopAttributeGroupId()] = $m;
        }
    }

    /**
     * fetching data
     * @return mixed
     */
    protected function fetch()
    {
        $resource = new Attribute($this->client);
        $fetcher = new Fetcher($resource);

        $list = $fetcher->fetchAll();

        $wrapper = new CollectionWrapper($list);

        $attributes = $wrapper->getArray('attribute_id');

        $this->attributes = $attributes;

    }

    public function determineGroupForProduct($product){
        if(empty($product->attributes)){
            return 'other';
        }

        foreach($product->attributes as $group=>$attributes){
            if(isset($this->mappings[$group])){
                /**
                 * @var $mapping AttributeGroupMapping
                 */
                $mapping = $this->mappings[$group];
                return $mapping->getCeneoGroup();
            }
        }

        return 'other';
    }

    public function getAttributes($productAttributes){

        $result = array();

        $counter = 0;
        foreach($productAttributes as $id=>$group){

            if(count($group)==0){
                continue;
            }

            if(isset($this->mappings[$id])){
                $data = $this->mappings[$id];

                /**
                 * @var $data AttributeGroupMapping
                 */
                foreach($data->getAttributes() as $i){
                    /**
                     * @var $i AttributeMapping
                     */
                    $result[$i->getCeneoField()] = $group[$i->getShopAttributeId()];
                }

                break;

            }else {
                foreach ($group as $attr => $v) {
                    $name = $this->attributes[$attr]->name;

                    $result[$name] = $v;

                    $counter++;

                    if ($counter >= 10) {
                        break;
                    }
                }
            }
        }

        return $result;

    }
}