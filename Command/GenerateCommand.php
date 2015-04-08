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

        /**
         * @var $shop ShopInterface
         */
        foreach($shops as $shop){
            try {
                $client = $this->getClientByShop($shop);

                // todo: configurable path
                $path = sprintf('%s/web/ceneo/xml/%s.xml', dirname($this->getContainer()->getParameter('kernel.root_dir')), $shop->getName());

                $generator = new Generator($path, $client, $epManager);
                $count = $generator->export($shop);

                $this->getContainer()->get('ceneo.export_checker')->setStatus($count, $shop);

                $output->writeln(sprintf('Shop %s, export: DONE, products: %d', $shop->getName(), $count));

            }catch(\Exception $ex){
                $output->writeln(sprintf('Shop %s: exception with message "%s"', $shop->getName(), $ex->getMessage()));
            }
        }

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
