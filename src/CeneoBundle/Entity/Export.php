<?php
/**
 * Created by PhpStorm.
 * User: eRIZ
 * Date: 2015-04-03
 * Time: 14:13
 */

namespace CeneoBundle\Entity;


use DreamCommerce\ShopAppstoreBundle\Model\ShopInterface;

class Export {

    protected $id;
    /**
     * @var ShopInterface
     */
    protected $shop;
    /**
     * @var \DateTime
     */
    protected $date;
    /**
     * @var integer
     */
    protected $productsCount = 0;
    /**
     * @var boolean
     */
    protected $inProgress = false;
    /**
     * @var int
     */
    protected $seconds = 0;

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
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return int
     */
    public function getProductsCount()
    {
        return $this->productsCount;
    }

    /**
     * @param int $productsCount
     */
    public function setProductsCount($productsCount)
    {
        $this->productsCount = $productsCount;
    }

    /**
     * @return boolean
     */
    public function isInProgress()
    {
        return $this->inProgress;
    }

    /**
     * @param boolean $inProgress
     */
    public function setInProgress($inProgress)
    {
        $this->inProgress = $inProgress;
    }

    /**
     * @return int
     */
    public function getSeconds()
    {
        return $this->seconds;
    }

    /**
     * @param int $seconds
     */
    public function setSeconds($seconds)
    {
        $this->seconds = $seconds;
    }

}