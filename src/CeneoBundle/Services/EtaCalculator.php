<?php


namespace CeneoBundle\Services;

use Symfony\Component\Stopwatch\StopwatchEvent;
use Symfony\Component\Stopwatch\StopwatchPeriod;

class EtaCalculator
{

    /**
     * minimal time should be used for per product calculation
     */
    const MIN_TIME_MS = 10;

    /**
     * @var int
     */
    private $samples;

    public function __construct($samples = 20){
        $this->samples = $samples;
    }

    public function getEtaSeconds(StopwatchEvent $event, $count){
        $samples = $event->getPeriods();
        /**
         * @var $ending StopwatchPeriod[]
         */
        $ending = array_slice($samples, -$this->samples);

        if(!isset($ending[0])){
            return 0;
        }

        $avg = 0;
        foreach($ending as $e){
            $duration = $e->getDuration();
            if($duration<self::MIN_TIME_MS){
                $duration = self::MIN_TIME_MS;
            }
            $avg += $duration;
        }

        $avg = $avg/count($ending);

        $time = (int)(($avg*$count)/1000);

        return $time;

    }

    public static function formatSeconds($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds - ($hours * 3600)) / 60);
        $seconds = $seconds - ($hours * 3600) - ($minutes * 60);

        if ($hours   < 10) {
            $hours   = "0".$hours;
        }
        if ($minutes < 10) {
            $minutes = "0".$minutes;
        }
        if ($seconds < 10) {
            $seconds = "0".$seconds;
        }
        return sprintf('%s:%s:%s', $hours, $minutes, $seconds);

    }



}