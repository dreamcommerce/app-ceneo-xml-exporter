<?php

namespace CeneoBundle\Command;

use CeneoBundle\Manager\ExcludedProductManager;
use CeneoBundle\Services\Generator;
use DreamCommerce\Client;
use DreamCommerce\ShopAppstoreBundle\EntityManager\ShopManager;
use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

class GenerateCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ceneo:generate')
            ->setDescription('Generate XMLs');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $em = $this->getContainer()->get('doctrine')->getManager();
        $shopManager = new ShopManager($em, 'BillingBundle\Entity\Shop');

        $shops = $shopManager->findByApplication('ceneo');

        $epManager = new ExcludedProductManager($em);

        $timer = new Stopwatch();

        /**
         * @var $shop ShopInterface
         */
        foreach($shops as $shop){
            try {
                $timer->start('shop');

                $client = $this->getClientByShop($shop);

                // todo: configurable path
                $path = sprintf('%s/web/ceneo/xml/%s.xml', dirname($this->getContainer()->getParameter('kernel.root_dir')), $shop->getName());

                $generator = new Generator($path, $client, $epManager, $shop);
                $generator->setStopwatch($timer);

                $timer->start('export');
                $count = $generator->export($shop);
                $timer->stop('export');

                $this->getContainer()->get('ceneo.export_checker')->setStatus($count, $shop);

                $output->writeln(sprintf('Shop %s, export: DONE, products: %d', $shop->getName(), $count));

                $timer->stop('shop');
            }catch(\Exception $ex){
                $output->writeln(sprintf('Shop %s: exception with message "%s"', $shop->getName(), $ex->getMessage()));
            }
        }

        $shops = $timer->getEvent('shop');
        $exports = $timer->getEvent('export');

        $this->printStats('Shop export time', $shops, $output);
        $this->printStats('Products export time', $exports, $output);

    }

    protected function printStats($name, StopwatchEvent $e, OutputInterface $o){
        $o->writeln('Stats: '.$name);
        $o->writeln(sprintf('Max memory consumed: %s bytes', number_format($e->getMemory(), 0, '.', ' ')));

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

        $o->writeln(sprintf('Timings, AVG: %.2fms, MIN: %.2fms, MAX: %.2fms', $avg, $min, $max));

    }

    protected function getClientByShop(ShopInterface $shop){
        $tokens = $shop->getToken();

        $config =
            $this->getContainer()->getParameter('dream_commerce_shop_appstore.applications');

        $config = $config['ceneo'];

        $client = new Client($shop->getShopUrl(), $config['app_id'], $config['app_secret']);
        $client->setAccessToken($tokens->getAccessToken());

        return $client;
    }
}
