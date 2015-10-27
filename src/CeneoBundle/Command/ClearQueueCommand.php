<?php

namespace CeneoBundle\Command;

use CeneoBundle\Manager\ExportManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearQueueCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ceneo:clear_queue')
            ->addArgument('id',
                InputArgument::OPTIONAL,
                'Shop to mark idle - its appstore uniq identifier')
            ->setDescription('Clears inProgress flag for all shops or particular one');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $em = $this->getContainer()->get('doctrine')->getManager();
        $epManager = new ExportManager($em);

        $name = $input->getArgument('id');
        $shops = [];

        if($name){

            $shop = $em->getRepository('BillingBundle:Shop')->findOneBy([
                'name'=>$name
            ]);
            
            if(!$shop){
                $output->writeln('Shop not found');
            }else {
                $shops[] = $shop;
            }

        }else{
            $shops = $em->getRepository('BillingBundle:Shop')->findAll();
        }

        $epManager->markAllInProgress($shops, false);
    }
}
