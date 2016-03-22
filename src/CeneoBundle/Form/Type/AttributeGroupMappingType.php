<?php


namespace CeneoBundle\Form\Type;


use CeneoBundle\Model\CeneoGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttributeGroupMappingType extends AbstractType{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $choices = array_keys(CeneoGroup::$groups);

        sort($choices);

        $builder
            ->add('group', ChoiceType::class, array(
                'choices_as_values'=>true,
                'choices'=>array_combine($choices, $choices),
                'label'=>false
            ))
            ->add('save', SubmitType::class, array(
                'label'=>'action.map'
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('group');
    }

}