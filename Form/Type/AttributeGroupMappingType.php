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
                'choices'=>array_combine($choices, $choices)
            ))
            ->add('save', 'submit');
    }

    public function getName()
    {
        return 'attribute_mapping_'.$this->groupId;
    }
}