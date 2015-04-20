<?php


namespace CeneoBundle\Form\Type;


use CeneoBundle\Model\CeneoGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class AttributeGroupMappingType extends AbstractType{

    protected $groupId;

    public function __construct($groupId){
        $this->groupId = $groupId;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $choices = array_keys(CeneoGroup::$groups);

        $builder
            ->add('group', 'choice', array(
                'choices'=>array_combine($choices, $choices),
                'label'=>false
            ))
            ->add('save', 'submit', array(
                'label'=>'Mapuj'
            ));
    }

    public function getName()
    {
        return 'attribute_mapping_'.$this->groupId;
    }
}