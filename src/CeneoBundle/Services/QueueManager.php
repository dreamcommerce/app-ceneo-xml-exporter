<?php


namespace CeneoBundle\Services;


use CeneoBundle\Entity\Shop;
use Mmoreram\GearmanBundle\Service\GearmanClient;

class QueueManager
{

    const WORKER_METHOD_NAME = 'CeneoBundleWorkerGeneratorWorker~process';

    /**
     * @var GearmanClient
     */
    private $gearman;

    public function __construct(GearmanClient $gearman)
    {
        $this->gearman = $gearman;
    }

    /**
     * enqueue shop for export
     * @param int|Shop $shop
     * @param bool|false $highPriority
     */
    public function enqueue($shop, $highPriority = false){

        $param = null;

        if($shop instanceof Shop){
            $param = $shop->getId();
        }else{
            $param = $shop;
        }

        $param = serialize($param);

        if($highPriority){
            $this->gearman->doHighBackgroundJob(self::WORKER_METHOD_NAME, $param);
        }else{
            $this->gearman->doBackgroundJob(self::WORKER_METHOD_NAME, $param);
        }

    }

}