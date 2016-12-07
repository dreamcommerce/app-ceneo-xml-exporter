<?php

namespace CeneoBundle\Command;

use CeneoBundle\Manager\ExportManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
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
            ->addArgument('id', InputArgument::OPTIONAL, 'Desired shop identifier')
            ->setDescription('Enqueues shop processing');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $id = $input->getArgument('id');
        if($id){
            return $this->executeSingle($input, $output, $id);
        }

        $output->writeln('Getting idle shop identifiers');

        $em = $this->getContainer()->get('doctrine')->getManager();

        $epManager = new ExportManager($em);

        $shopList = $epManager->getRepository()->findIdleShopIds();

        $output->writeln(
            sprintf('Found %d shops', count($shopList))
        );

        $queueManager = $this->getContainer()->get('ceneo.queue_manager');

        $output->writeln('Scheduling shops...');

        foreach($shopList as $exportId){

            $queueManager->enqueue($exportId);

            $output->writeln(
                sprintf('Shop ID#%d scheduled', $exportId)
            );
        }

        $output->writeln(
            sprintf('Scheduling finished, used %d bytes of peak memory', memory_get_peak_usage(true))
        );

    }

    protected function executeSingle(InputInterface $input, OutputInterface $output, $id){

        $output->writeln(sprintf('Shop #%d - enqueueing', $id));

        $em = $this->getContainer()->get('doctrine')->getManager();
        $shop = $em->getRepository('CeneoBundle:Shop')->find($id);
        if(!$shop){
            $output->writeln('Shop not found');
            return;
        }

        $queueManager = $this->getContainer()->get('ceneo.queue_manager');
        $queueManager->enqueue($shop);

        $output->writeln('Shop export scheduled');
    }
}
