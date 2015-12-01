<?php

namespace CeneoBundle\Command;

use CeneoBundle\Entity\ExcludedProduct;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use DreamCommerce\Client;
use DreamCommerce\Resource\Product;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateTitlesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ceneo:update_titles')
            ->setDescription('Hello PhpStorm');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        /**
         * @var $em EntityManager
         */
        $em = $doctrine->getManager();

        $shopRepo = $doctrine->getRepository('BillingBundle:Shop');
        $epRepo = $doctrine->getRepository('CeneoBundle:ExcludedProduct');

        $shops = $shopRepo->findAll();

        $app = $this->getContainer()->get('dream_commerce_shop_appstore.app.ceneo');

        /**
         * @var $query Query
         */
        $query =
            $em->createQuery('update CeneoBundle:ExcludedProduct ep set ep.title=:title where ep.shop=:shop and ep.product_id=:product_id');


        $output->writeln('Initiating excluded products title update...');

        $output->writeln(sprintf('found %d shops', count($shops)));

        foreach($shops as $shop){
            $page = 1;

            $output->writeln(sprintf('shop#%d, URL: %s', $shop->getId(), $shop->getShopUrl()));

            $processed = 0;

            $count = $epRepo->countAllByShop($shop);

            $output->writeln(sprintf('to go: %d', $count));

            try {
                do {
                    /**
                     * @var $epList ExcludedProduct[]
                     */
                    try {
                        $epList = $epRepo->findAllByShopPaged($shop, $page);
                        $found = count($epList);
                    }catch(\Exception $ex){
                        $found = 0;
                    }

                    if(!$found){
                        continue;
                    }

                    $ids = [];
                    foreach ($epList as $e) {
                        $ids[] = $e->getProductId();
                    }

                    $client = $app->getClient($shop);
                    $res = new Product($client);
                    $res->filters([
                        'product_id' => [
                            'in' => $ids
                        ]
                    ]);

                    $products = $res->get();

                    foreach ($products as $p){
                        if(!isset($p->translations->pl_PL->name)){
                            continue;
                        }

                        $output->write('.');

                        $query->setParameter('title', $p->translations->pl_PL->name);
                        $query->setParameter('shop', $shop);
                        $query->setParameter('product_id', $p->product_id);

                        $query->execute();
                    }

                    $processed += $found;

                    $output->write($processed);

                    $page++;
                } while ($found >= 50);
            }catch(\Exception $ex){
                throw $ex;
            }
        }

        $output->writeln('');
    }
}
