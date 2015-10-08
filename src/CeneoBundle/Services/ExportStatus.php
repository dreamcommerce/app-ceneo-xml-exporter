<?php
/**
 * Created by PhpStorm.
 * User: eRIZ
 * Date: 2015-04-03
 * Time: 14:30
 */

namespace CeneoBundle\Services;


use CeneoBundle\Entity\Export;
use Doctrine\ORM\EntityManager;
use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;
use Symfony\Component\Stopwatch\StopwatchPeriod;

class ExportStatus {
    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * xml target directory
     * @var string
     */
    protected $xmlDir;

    /**
     * @param string $xmlDir
     * @param EntityManager $em
     */
    public function __construct($xmlDir, EntityManager $em){
        $this->em = $em;
        $this->xmlDir = $xmlDir;
    }

    /**
     * @param ShopInterface $shop
     * @return null|Export
     */
    public function getStatus(ShopInterface $shop){
        $result = $this->em->getRepository('CeneoBundle\Entity\Export')->findByShop($shop);
        if(is_array($result)){
            $result = $result[0];
        }

        if(!$result){
            throw new \RuntimeException(sprintf('Shop #%s status not found', $shop->getName()));
        }

        return $result;
    }

    public function initialize(ShopInterface $shop){
        $entity = new Export();
        $entity->setShop($shop);

        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }

    /**
     * marks export as being processed
     * @param ShopInterface $shop
     */
    public function markInProgress(ShopInterface $shop){
        $status = $this->getStatus($shop);

        $status->setInProgress(true);

        $this->em->persist($status);
        $this->em->flush();
    }

    /**
     * mark export as done and specify how much products have been exported
     * @param ShopInterface $shop
     * @param int $count
     * @return Export|null
     */
    public function markDone(ShopInterface $shop, $count = 0)
    {
        $status = $this->getStatus($shop);

        $status->setInProgress(false);
        $status->setProductsCount($count);
        $status->setDate(new \DateTime());

        $this->em->persist($status);
        $this->em->flush();

        return $status;
    }

    /**
     * formats export stopwatch to arrayized data
     * @param Stopwatch $stopwatch
     * @return array
     */
    public function getExportStats(Stopwatch $stopwatch){
        $shops = $stopwatch->getEvent('shop');
        $exports = $stopwatch->getEvent('export');

        return [
            'shop'=>$this->formatStatsSection($shops),
            'export'=>$this->formatStatsSection($exports)
        ];
    }

    public function getLastExportStats(Stopwatch $stopwatch){
        $shop = $stopwatch->getEvent('shop');
        $periods = $shop->getPeriods();
        /**
         * @var $period StopwatchPeriod
         */
        $period = end($periods);

        $mem = number_format($period->getMemory(), 0, '.', ' ');

        return sprintf('MEM: %sB, time: %dms', $mem, $period->getDuration());
    }

    /**
     * formats stopwatch event data to string
     * @todo may be use a wrapper class on stopwatch to skip scalar array keys
     * @param StopwatchEvent $e
     * @return string
     */
    protected function formatStatsSection(StopwatchEvent $e){
        $mem = number_format($e->getMemory(), 0, '.', ' ');

        $time = 0;
        $periods = $e->getPeriods();
        $min = getrandmax();
        $max = 0;
        foreach($periods as $p){
            $lap = $p->getDuration();
            $time += $lap;
            if($min>$lap){
                $min = $lap;
            }
            if($max<$lap){
                $max = $lap;
            }
        }

        if($periods) {
            $avg = $time / count($periods);
        }else{
            $avg = 0;
        }

        return sprintf('MEM: %sB, AVG: %.2fms, MIN: %.2fms, MAX: %.2fms', $mem, $avg, $min, $max);
    }

    /**
     * checks whether exported file is available for download
     * @param ShopInterface $shop
     * @return bool
     */
    public function exportExists(ShopInterface $shop){
        return file_exists(
            sprintf('%s/%s.xml', $this->xmlDir, $shop->getName())
        );
    }

}