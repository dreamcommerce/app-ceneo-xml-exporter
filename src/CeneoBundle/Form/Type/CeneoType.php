<?php


namespace CeneoBundle\Form\Type;


use CeneoBundle\Model\CeneoGroup;
use DreamCommerce\ShopAppstoreBundle\Form\CollectionChoiceListLoader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CeneoType extends AbstractType{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $items = CeneoGroup::$groups[$options['group']][1];

        $keyResolver = function($record){
            return $record->attribute_id;
        };

        $valueResolver = function($record){
            return $record->name;
        };

        foreach($items as $i){
            $builder->add($i, ChoiceType::class, array(
                'placeholder'=>'',
                'choices_as_values'=>true,
                'choice_loader'=>new CollectionChoiceListLoader($options['attributes'], $keyResolver, $valueResolver),
                'required'=>false
            ));
        }

        $builder->add('save', SubmitType::class, array(
            'label'=>'action.save'
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('attributes', []);
        $resolver->setDefault('group', null);
    }

}