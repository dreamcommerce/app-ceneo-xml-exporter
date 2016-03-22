<?php
namespace CeneoBundle\Controller;


use CeneoBundle\Entity\AttributeGroupMapping;
use CeneoBundle\Form\Type\AttributeGroupMappingType;
use CeneoBundle\Form\Type\CeneoType;
use CeneoBundle\Manager\AttributeGroupMappingManager;
use CeneoBundle\Manager\AttributeMappingManager;
use CeneoBundle\Model\CeneoGroup;
use DreamCommerce\ShopAppstoreLib\Resource\Attribute;
use DreamCommerce\ShopAppstoreLib\Resource\AttributeGroup;
use DreamCommerce\ShopAppstoreBundle\Utils\CollectionWrapper;
use DreamCommerce\ShopAppstoreBundle\Utils\Fetcher;
use Symfony\Component\HttpFoundation\Request;

class MappingsController extends ControllerAbstract{

    public function indexAction(Request $request){

        //todo:refactor

        $attributeGroupsResource = new AttributeGroup($this->client);
        $attributeGroupsResource->order('name ASC');

        $fetcher = new Fetcher($attributeGroupsResource);
        $wrapper = new CollectionWrapper($fetcher->fetchAll());
        $list = $wrapper->getArray('attribute_group_id');

        // purge old ones
        $orphansPurger = $this->get('ceneo.orphans_purger');
        $orphansPurger->purgeAttributeGroups(array_keys($list), $this->client, $this->shop);

        $groups = CeneoGroup::$groups;

        $mapper = new AttributeGroupMappingManager($this->getDoctrine()->getManager());

        $groupsList = $mapper->getRepository()->findFormGroups($this->shop);

        $forms = $this->createFormBuilder($groupsList);
        foreach($list as $l){
            $groupId = $l->attribute_group_id;

            if(!isset($groupsList[$groupId])){
                $data = $forms->getData();
                $data[$groupId] = ['group'=>'other'];
                $forms->setData($data);
            }

            $options = ['group' => $groupId, 'label' => false];

            $forms->add($groupId, AttributeGroupMappingType::class, $options);
        }

        $form = $forms->getForm();
        $form->handleRequest($request);

        $button = $form->getClickedButton();
        if($button){
            $form = $button->getParent();
            $data = $form->getData();
            $group = $form->getConfig()->getOptions()['group'];

            $mapping = $mapper->saveMapping($this->shop, $group, $data['group']);

            if($mapping){
                return $this->redirect(
                    $this->generateAppUrl('ceneo_mappings_group', array('group'=>$mapping))
                );
            }else{
                $this->addNotice('Grupa została przypisana');
                return $this->redirect(
                    $this->generateAppUrl('ceneo_mappings')
                );
            }
        }

        return $this->render('CeneoBundle:mappings:index.html.twig', array(
            'groups'=>$list,
            'forms'=>$form->createView(),
            'ceneo_groups'=>$groups
        ));
    }

    public function groupAction(Request $request, $group){


        /**
         * @var $attributeGroup AttributeGroupMapping
         */
        $attributeGroupManager = new AttributeGroupMappingManager($this->getDoctrine()->getManager());
        $attributeGroup = $attributeGroupManager->getRepository()->findOneBy(array('id'=>$group));

        if(!$attributeGroup){
            throw new \HttpInvalidParamException();
        }

        $attributeResource = new Attribute($this->client);
        $attributes = $attributeResource->filters(array('attribute_group_id'=>$attributeGroup->getShopAttributeGroupId()))->order('name')->get();

        $wrapper = new CollectionWrapper($attributes);
        $list = $wrapper->getListOfField('attribute_id');

        $this->get('ceneo.orphans_purger')->purgeAttributes($list, $this->client, $this->shop);

        $attributeGroupResource = new AttributeGroup($this->client);
        $result = $attributeGroupResource->get($attributeGroup->getShopAttributeGroupId());

        $title = $result['name'];

        $attributeMappingManager = new AttributeMappingManager($this->getDoctrine()->getManager());
        $data = $attributeMappingManager->getRepository()->findForForm($attributeGroup);

        $form = $this->createForm(CeneoType::class, $data, [
            'attributes'=>$attributes,
            'group'=>$attributeGroup->getCeneoGroup()
        ]);
        $form->handleRequest($request);

        if($form->isValid()){
            $mappings = $form->getData();

            $attributeMappingManager->saveMapping($attributeGroup, $mappings, $this->shop);

            $this->addNotice('Mapowanie atrybutów zostało zapisane');

            return $this->redirect(
                $this->generateAppUrl('ceneo_mappings')
            );
        }

        return $this->render('CeneoBundle:mappings:group.html.twig', array(
            'form'=>$form->createView(),
            'title'=>$title
        ));
    }

}