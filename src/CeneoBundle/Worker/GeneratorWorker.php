<?php


namespace CeneoBundle\Worker;


use CeneoBundle\Manager\AttributeGroupMappingManager;
use CeneoBundle\Manager\ExcludedProductManager;
use CeneoBundle\Services\ExportStatus;
use Doctrine\ORM\EntityManager;
use DreamCommerce\ShopAppstoreBundle\EntityManager\ShopManager;
use DreamCommerce\ShopAppstoreBundle\Handler\Application;
use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;
use Mmoreram\GearmanBundle\Command\Util\GearmanOutputAwareInterface;
use Mmoreram\GearmanBundle\Driver\Gearman;
use Symfony\Component\Console\Output\OutputInterface;
use CeneoBundle\Services\Generator;

/**
 * product export worker
 * spawn me how many you want!
 *
 * @Gearman\Work(
 *     service="ceneo.generator_worker"
 * )
 */
class GeneratorWorker implements GearmanOutputAwareInterface
{
    /**
     * @var ExcludedProductManager
     */
    protected $epManager;
    /**
     * @var AttributeGroupMappingManager
     */
    protected $attributeGroupMappingManager;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ShopManager
     */
    protected $shopManager;

    /**
     * processed shops count
     * @var int
     */
    protected $processed = 0;

    /**
     * processed products count
     * @var int
     */
    protected $processedProducts = 0;

    /**
     * @var Application
     */
    protected $application;
    /**
     * @var string
     */
    protected $xmlDir;
    /**
     * @var Generator
     */
    private $generator;
    /**
     * @var ExportStatus
     */
    private $exportStatus;

    /**
     * @param string $xmlDir xml output directory
     * @param Application $application
     * @param Generator $generator
     * @param ExportStatus $exportStatus
     * @param EntityManager $em
     */
    public function __construct(
        $xmlDir,
        Application $application,
        Generator $generator,
        ExportStatus $exportStatus,
        EntityManager $em
    )
    {
        $this->em = $em;

        $this->application = $application;
        $this->generator = $generator;
        $this->exportStatus = $exportStatus;
        $this->xmlDir = $xmlDir;

        $this->init();
    }

    /**
     * initialize some internal stuff
     */
    protected function init(){
        $this->shopManager = new ShopManager($this->em, 'BillingBundle\Entity\Shop');
        $this->epManager = new ExcludedProductManager($this->em);
        $this->attributeGroupMappingManager = new AttributeGroupMappingManager($this->em);
    }

    /**
     * @param \GearmanJob $job
     * @Gearman\Job()
     * @return string
     */
    public function process(\GearmanJob $job)
    {
        $data = unserialize($job->workload());

        $this->output->writeln(
            sprintf('Incoming shop #%d', $data)
        );

        try {

            $this->em->getConnection()->refresh();

            $shop = $this->shopManager->findById($data);
            if (!$shop) {
                throw new \RuntimeException(sprintf('Shop #%d doesn\'t exist', $data));
            }

            $this->output->writeln('Shop found, starting export...');

            $this->generateForShop($shop);
            $this->processed++;

            $job->sendComplete('ok');
            $this->output->writeln('Shop exported');

        }catch(\Exception $ex){
            $job->sendException($ex->getMessage());
            $this->output->writeln($ex->getMessage());
        }
    }

    /**
     * cleanup
     */
    public function __destruct()
    {
        $this->summary();

        $this->attributeGroupMappingManager = null;
        $this->shopManager = null;
        $this->epManager = null;
        $this->em = null;
    }

    /**
     * output summary about memory, timings, etc
     */
    protected function summary(){
        $stopwatch = $this->generator->getStopwatch();
        $stats = $this->exportStatus->getExportStats($stopwatch);

        foreach($stats as $group=>$stat){
            $this->output->writeln(
                sprintf('%s: %s', $group, $stat)
            );
        }

        $this->output->writeln(
            sprintf('overall products processed: %d', $this->processedProducts)
        );

        $this->output->writeln(
            sprintf('shops processed: %d', $this->processed)
        );
    }

    /**
     * perform export for particular shop
     * @param ShopInterface $shop
     */
    protected function generateForShop(ShopInterface $shop){
        $stopwatch = $this->generator->getStopwatch();

        $client = $this->application->getClient($shop);

        $this->output->writeln(
            sprintf('Shop: %s, date: %s', $shop->getShopUrl(), date('Y-m-d H:i:s'))
        );

        $path = sprintf('%s/%s.xml', $this->xmlDir, $shop->getName());

        $count = $this->generator->export($client, $shop, $path);

        $this->output->writeln(
            sprintf('Shop done, exported products: %d', $count)
        );
        $stats = $this->exportStatus->getLastExportStats($stopwatch);
        $this->output->writeln(sprintf('export stats: %s', $stats));
    }

    /**
     * Set the output handler
     *
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }
}