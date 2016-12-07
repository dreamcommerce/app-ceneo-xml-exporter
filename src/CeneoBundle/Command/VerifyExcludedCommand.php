<?php

namespace CeneoBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VerifyExcludedCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ceneo:verify_excluded')
            ->setDescription('Verify if excluded products still exist in shops');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $output->writeln('Purging non-existing products ignores...');

        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine')->getManager();

        $shops = $em->getRepository('CeneoBundle:Shop')->findAll();

        $shopsCount = count($shops);
        $output->writeln(sprintf('Found %d shops', $shopsCount));

        $productsRepo = $em->getRepository('CeneoBundle:ExcludedProduct');
        $app = $this->getContainer()->get('dream_commerce_shop_appstore.app.ceneo');

        $purger = $this->getContainer()->get('ceneo.orphans_purger');

        $c = 0;
        foreach($shops as $shop){

            $c++;

            $output->writeln(sprintf('[%d/%d] Shop %s purging', $c, $shopsCount, $shop->getShopUrl()));

            $output->writeln('Deleting ignored duplicates...');
            $q = $em->getConnection()->executeUpdate(
                'delete from ExcludedProduct using ExcludedProduct, ExcludedProduct e1
              where ExcludedProduct.id > e1.id and ExcludedProduct.product_id = e1.product_id
              and ExcludedProduct.shop_id=:shop'
            , [
                'shop'=>$shop->getId()
            ]);

            $output->writeln(sprintf('Deleted %d duplicates.', $q));

            $output->writeln('Deleting non-existing references...');

            try {
                $client = $app->getClient($shop);
                $products = $productsRepo->findIdsByShop($shop);
                $products = array_map(function (&$v) {
                    return (string)$v;
                }, $products);

                $real = $purger->purgeExcluded($products, $client, $shop);

                $output->writeln(sprintf('Difference: %d', count($products)-count($real)));
            }catch(\Exception $ex){
                $output->writeln('An error occurred during shop communication: '.$ex->getMessage());
            }

        }

        $output->writeln('Done');



    }
}
