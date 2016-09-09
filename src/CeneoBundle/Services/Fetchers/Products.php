<?php


namespace CeneoBundle\Services\Fetchers;


use CeneoBundle\Entity\ExcludedProductRepository;
use DreamCommerce\ShopAppstoreLib\Resource\Producer;
use DreamCommerce\ShopAppstoreLib\Resource\Product;
use DreamCommerce\ShopAppstoreLib\Resource\ProductImage;
use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;
use DreamCommerce\ShopAppstoreBundle\Utils\Fetcher;

class Products extends FetcherAbstract
{

    /**
     * ignored products hits
     * @var array
     */
    protected $ignored = [];

    /**
     * fetching data
     * @return mixed
     */
    protected function fetch(){}

    /**
     * @return Fetcher\ResourceListIterator
     */
    public function get()
    {
        return $this->fetchProducts();
    }

    /**
     * get products skipping excluded for that shop
     * @param ShopInterface $shop
     * @param ExcludedProductRepository $repository
     * @return Fetcher\ResourceListIterator
     */
    public function getWithoutExcluded(ShopInterface $shop, ExcludedProductRepository $repository){
        return $this->fetchProducts($shop, $repository);
    }

    public function getIgnoredCount()
    {
        return count($this->ignored);
    }

    /**
     * check if product is ignored
     * @param $id
     * @param bool|true $hitMark update counter adequately
     * @return bool
     */
    public function isIgnored($id, $hitMark = true)
    {
        $result = isset($this->ignored[$id]);
        if($result && $hitMark){
            $this->ignored[$id]++;
        }

        return $result;
    }

    /**
     * get products, if manager regarding exclusions
     * @param ShopInterface|null $shop
     * @param ExcludedProductRepository $repository
     * @return Fetcher\ResourceListIterator
     * @throws \DreamCommerce\ShopAppstoreLib\Resource\Exception\ResourceException
     */
    protected function fetchProducts(ShopInterface $shop = null, ExcludedProductRepository $repository = null){
        $productResource = new Product($this->client);

        if($repository) {

            $excluded = $repository->findIdsByShop($shop);

            if ($excluded) {
                $this->ignored = array_combine($excluded, array_fill(0, count($excluded), 0));
            }
        }

        $productResource->filters([
            'producer_id'=>[
                '!='=>null
            ],
            'translations.pl_PL.active'=>true
        ]);

        $fetcher = new Fetcher($productResource);

        $image = new ProductImage($this->client);
        $fetcher->connect(
            $image, 'product_id'
        );

        $producer = new Producer($this->client);
        $fetcher->connect(
            $producer, 'producer_id'
        );

        return $fetcher->fetchAll();
    }

}