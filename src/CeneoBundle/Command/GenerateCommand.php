<?php

namespace CeneoBundle\Command;

use CeneoBundle\Entity\ShopManager;
use CeneoBundle\Manager\AttributeGroupMappingManager;
use CeneoBundle\Manager\ExcludedProductManager;
use CeneoBundle\Services\Generator;
use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;
use DreamCommerce\ShopAppstoreLib\Resource\Exception\CommunicationException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

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

        $attributeGroupMappingManager = new AttributeGroupMappingManager($em);

        $timer = new Stopwatch();

        $hasShops = false;
        /**
         * @var $shop ShopInterface
         */
        foreach($shops as $shop){

            try {
                $client = $this->getContainer()->get('dream_commerce_shop_appstore.ceneo')->getClient($shop);

                $path = sprintf('%s/%s.xml', $this->getContainer()->getParameter('xml_dir'), $shop->getName());

                $generator = new Generator(
                    $this->getContainer()->getParameter('kernel.cache_dir'),
                    $this->getContainer()->get('ceneo.orphans_purger'),
                    $epManager->getRepository(),
                    $attributeGroupMappingManager->getRepository(),
                    $this->getContainer()->get('ceneo.export_status')
                );
                $generator->setStopwatch($timer);
                $count = $generator->export($client, $shop, $path);

                $this->getContainer()->get('ceneo.export_status')->markDone($shop, $count);

                $output->writeln(sprintf('Shop %s, export: DONE, products: %d', $shop->getName(), $count));

            }catch(\Exception $ex){
                $output->writeln(sprintf('Shop %s: exception with message "%s"', $shop->getName(), $ex->getMessage()));
            }

            if(!$hasShops){
                $hasShops = true;
            }
        }

        if($hasShops) {
            $exportStatus = $this->getContainer()->get('ceneo.export_status');
            $data = $exportStatus->getExportStats($timer);
            foreach($data as $type=>$stats){
                $output->writeln(sprintf('%s: %s', $type, $stats));
            }
        }

    }

}
