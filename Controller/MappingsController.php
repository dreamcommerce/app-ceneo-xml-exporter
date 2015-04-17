<?php
/**
 * Created by PhpStorm.
 * User: eRIZ
 * Date: 2015-04-09
 * Time: 13:07
 */

namespace CeneoBundle\Controller;


use CeneoBundle\Entity\AttributeGroupMapping;
use CeneoBundle\Entity\AttributeGroupMappingRepository;
use CeneoBundle\Form\Type\AttributeGroupMappingType;
use CeneoBundle\Form\Type\CeneoType;
use CeneoBundle\Manager\AttributeGroupMappingManager;
use CeneoBundle\Manager\AttributeMappingManager;
use CeneoBundle\Model\CeneoGroup;
use DreamCommerce\Resource\Attribute;
use DreamCommerce\Resource\AttributeGroup;
use DreamCommerce\Resource\Category;
use DreamCommerce\ShopAppstoreBundle\Utils\CollectionWrapper;
use DreamCommerce\ShopAppstoreBundle\Utils\Fetcher;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MappingsController extends ControllerAbstract{

    public function indexAction(Request $request){

        //todo:purging
        //todo:refactor

        $attributeGroupsResource = new AttributeGroup($this->client);
        $attributeGroupsResource->order('name ASC');

        $fetcher = new Fetcher($attributeGroupsResource);
        $wrapper = new CollectionWrapper($fetcher->fetchAll());
        $list = $wrapper->getArray('attribute_group_id');

        $groups = CeneoGroup::$groups;

        $mapper = new AttributeGroupMappingManager($this->getDoctrine()->getManager());

        $groupsList = $mapper->getRepository()->findFormGroups();

        $forms = array();
        foreach($list as $l){
            $groupId = $l->attribute_group_id;
            $data = isset($groupsList[$groupId]) ? $groupsList[$groupId] : array();
            $forms[$groupId] = $this->createForm(new AttributeGroupMappingType($groupId), $data);
        }

        /**
         * @var $f Form
         */
        foreach($forms as $groupId=>$f){

            $f->handleRequest($request);

            if($f->isValid()){
                $mapping = $mapper->saveMapping($this->shop, $groupId, $f->getData()['group']);
                $this->addNotice('Grupa została przypisana');

                if($mapping){
                    return $this->redirect(
                        $this->generateAppUrl('ceneo_mappings_group', array('group'=>$mapping))
                    );
                }
            }
        }

        foreach($forms as $k=>$f){
            $forms[$k] = $f->createView();
        }

        return $this->render('CeneoBundle:mappings:index.html.twig', array(
            'groups'=>$list,
            'forms'=>$forms,
            'ceneo_groups'=>$groups
        ));
    }

    public function groupAction(Request $request, $group){

        //todo:group name

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

        $attributeMappingManager = new AttributeMappingManager($this->getDoctrine()->getManager());
        $data = $attributeMappingManager->getRepository()->findForForm($attributeGroup);

        $form = $this->createForm(new CeneoType($attributes, $attributeGroup->getCeneoGroup()), $data);
        $form->handleRequest($request);

        if($form->isValid()){
            $mappings = $form->getData();

            $attributeMappingManager->saveMapping($attributeGroup, $mappings);

            $this->addNotice('Attribute mapping saved');

            return $this->redirect(
                $this->generateAppUrl('ceneo_mappings')
            );
        }

        return $this->render('CeneoBundle:mappings:group.html.twig', array(
            'form'=>$form->createView()
        ));
    }

}