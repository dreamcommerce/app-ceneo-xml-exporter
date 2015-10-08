<?php
/**
 * Created by PhpStorm.
 * User: eRIZ
 * Date: 2015-03-31
 * Time: 17:32
 */

namespace CeneoBundle\Manager;


use CeneoBundle\Entity\ExcludedProductRepository;
use Doctrine\ORM\EntityManager;

class ExportManager {

    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var ExcludedProductRepository
     */
    protected $repository;

    public function __construct(EntityManager $em){
        $this->em = $em;
        $this->repository = $em->getRepository('CeneoBundle:Export');
    }

    public function getRepository(){
        return $this->repository;
    }

    /**
     * @param array $products
     */
    public function delete($products){

        foreach($products as $p){
            $this->em->remove($p);
        }

        $this->em->flush();
    }

    /**
     * @param array $ids shop identifiers
     */
    public function markAllInProgress($ids){
        $q = $this->em
            ->createQuery('UPDATE CeneoBundle:Export e SET e.inProgress=true WHERE IDENTITY(e.shop) in (:ids)')
            ->setParameter('ids', $ids);
        $q->execute();
    }

}