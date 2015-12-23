<?php

namespace CeneoBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WorkerCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ceneo:worker')
            ->setDescription('Executes export worker');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $command = $this->getApplication()->find('gearman:worker:execute');
        $arguments = array(
            'command' => 'gearman:worker:execute',
            'worker'    => 'CeneoBundleWorkerGeneratorWorker',
            '-n'  => true,
        );

        $input = new ArrayInput($arguments);

        declare(ticks=1);
        $command->run($input, $output);
    }
}
