<?php
/**
 * Created by PhpStorm.
 * User: eRIZ
 * Date: 2015-03-30
 * Time: 13:45
 */

namespace CeneoBundle\Entity;


use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;

class ExcludedProduct {

    /**
     * @var integer
     */
    protected $id;
    /**
     * @var ShopInterface
     */
    protected $shop;
    /**
     * @var integer
     */
    protected $product_id;

    /**
     * @var string
     */
    protected $link;
    /**
     * @var string
     */
    protected $title;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ShopInterface
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * @param ShopInterface $shop
     */
    public function setShop($shop)
    {
        $this->shop = $shop;
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->product_id;
    }

    /**
     * @param int $productId
     */
    public function setProductId($productId)
    {
        $this->product_id = $productId;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }


}