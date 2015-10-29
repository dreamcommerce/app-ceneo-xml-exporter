<?php


namespace CeneoBundle\Services\Fetchers;


use CeneoBundle\Entity\ExcludedProductRepository;
use DreamCommerce\Resource\Product;
use DreamCommerce\Resource\ProductImage;
use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;
use DreamCommerce\ShopAppstoreBundle\Utils\Fetcher;

class Products extends FetcherAbstract
{

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

    /**
     * get products, if manager regarding exclusions
     * @param ShopInterface|null $shop
     * @param ExcludedProductRepository $repository
     * @return Fetcher\ResourceListIterator
     * @throws \DreamCommerce\Exception\ResourceException
     */
    protected function fetchProducts(ShopInterface $shop = null, ExcludedProductRepository $repository = null){
        $productResource = new Product($this->client);

        if($repository) {

            $excluded = $repository->findIdsByShop($shop);

            if ($excluded) {
                $this->orphansPurger->purgeExcluded($excluded, $this->client, $this->shop);
                $productResource->filters(array('product_id' => array('not in' => $excluded)));
            }
        }

        $fetcher = new Fetcher($productResource);

        $image = new ProductImage($this->client);
        $fetcher->connect(
            $image, 'product_id'
        );
        return $fetcher->fetchAll();
    }

}