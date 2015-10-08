<?php

namespace CeneoBundle\Command;

use CeneoBundle\Manager\ExportManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EnqueueCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ceneo:enqueue')
            ->setDescription('Enqueues shop processing');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $output->writeln('Getting idle shop identifiers');

        $em = $this->getContainer()->get('doctrine')->getManager();

        $epManager = new ExportManager($em);

        $shopList = $epManager->getRepository()->findIdleShopIds();

        $output->writeln(
            sprintf('Found %d shops', count($shopList))
        );

        $output->writeln('Marking shops as busy...');

        $epManager->markAllInProgress($shopList);

        $gearman = $this->getContainer()->get('gearman');

        $output->writeln('Scheduling shops...');

        foreach($shopList as $exportId){
            $gearman->doBackgroundJob('CeneoBundleWorkerGeneratorWorker~process', serialize(
                $exportId
            ));

            $output->writeln(
                sprintf('Shop ID#%d scheduled', $exportId)
            );
        }

        $output->writeln(
            sprintf('Scheduling finished, used %d bytes of peak memory', memory_get_peak_usage(true))
        );

    }
}
