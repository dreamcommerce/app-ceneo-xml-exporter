<?php
/**
 * Created by PhpStorm.
 * User: eRIZ
 * Date: 2015-04-01
 * Time: 16:38
 */

namespace CeneoBundle\Entity;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class RepositoryAbstract extends EntityRepository{

    protected function getColumnValues(Query $query){

        $records = $query->getArrayResult();

        $records = array_map(function($v){
            return $v['product_id'];
        }, $records);

        return $records;
    }

}