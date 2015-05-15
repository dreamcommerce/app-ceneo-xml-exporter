<?php


namespace CeneoBundle\Form\Type;


use CeneoBundle\Model\CeneoGroup;
use DreamCommerce\ShopAppstoreBundle\Form\CollectionChoiceList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class CeneoType extends AbstractType{

    protected $attributes;
    protected $group;

    public function __construct(\ArrayObject $attributes, $group){
        $this->attributes = $attributes;
        $this->group = $group;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $items = CeneoGroup::$groups[$this->group][1];

        $keyResolver = function($record){
            return $record->attribute_id;
        };

        $valueResolver = function($record){
            return $record->name;
        };

        foreach($items as $i){
            $builder->add($i, 'choice', array(
                'empty_value'=>'',
                'choice_list'=>new CollectionChoiceList($this->attributes, $keyResolver, $valueResolver),
                'required'=>false
            ));
        }

        $builder->add('save', 'submit', array(
            'label'=>'zapisz'
        ));
    }

    public function getName()
    {
        return 'ceneo_'.$this->group;
    }
}