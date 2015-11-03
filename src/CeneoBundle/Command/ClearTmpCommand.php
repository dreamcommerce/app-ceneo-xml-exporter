<?php

namespace CeneoBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearTmpCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ceneo:clear_tmp')
            ->setDescription('Clear old unfinished job files');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $d = new \DirectoryIterator($this->getContainer()->getParameter('xml_dir'));

        $threshold = $this->getContainer()->getParameter('temporary_ttl');

        $output->writeln('Purging old temporary files');

        foreach($d as $i){

            if($i->getExtension()!='tmp'){
                continue;
            }

            $mtime = $i->getMTime();
            if(time()-$mtime>$threshold){
                $output->writeln(sprintf('Deleting: %s', $i->getPathname()));
                @unlink($i->getPathname());
            }

        }

        $output->writeln('Done');
    }
}
