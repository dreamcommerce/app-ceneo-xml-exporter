<?php


namespace CeneoBundle\Services\Fetchers;


use CeneoBundle\Entity\AttributeGroupMapping;
use CeneoBundle\Entity\AttributeGroupMappingRepository;
use CeneoBundle\Entity\AttributeMapping;
use DreamCommerce\ShopAppstoreLib\Resource\Attribute;
use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;
use DreamCommerce\ShopAppstoreBundle\Utils\CollectionWrapper;
use DreamCommerce\ShopAppstoreBundle\Utils\Fetcher;

class Attributes extends FetcherAbstract
{

    protected $attributes = [];
    protected $mappings = [];

    public function setMappings(AttributeGroupMappingRepository $attributeGroupMappingRepository, ShopInterface $shop){

        $mappings = $attributeGroupMappingRepository->findAllByShop($shop);

        $ids = [];
        /**
         * @var $m AttributeGroupMapping
         */
        foreach($mappings as $m){
            $ids[] = $m->getShopAttributeGroupId();
        }

        $this->orphansPurger->purgeAttributeGroups($ids, $this->client, $this->shop);

        $mappedAttributes = [];
        foreach($mappings as $m){
            $this->mappings[$m->getShopAttributeGroupId()] = $m;
            $attributes = $m->getAttributes();
            /**
             * @var $a AttributeMapping
             */
            foreach($attributes as $a){
                $mappedAttributes[] = $a->getShopAttributeId();
            }
        }

        $this->orphansPurger->purgeAttributes($mappedAttributes, $this->client, $this->shop);
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

    public function getAttributes($product){

        $result = array();

        $productAttributes = $product->attributes;

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

        $result = $this->injectDefaultAttributes($product, $result);

        return $result;

    }

    protected function injectDefaultAttributes($product, $result){
        if(empty($result['Producent'])){
            if(isset($product->Producer) && isset($product->Producer[0])){
                $result['Producent'] = $product->Producer[0]->name;
            }
        }

        if(empty($result['Kod_producenta'])){
            $result['Kod_producenta'] = $product->code;
        }

        if(empty($result['EAN'])){
            if(!empty($product->ean)) {
                $result['EAN'] = $product->ean;
            }
        }

        return $result;
    }
}