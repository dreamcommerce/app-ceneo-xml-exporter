<?php

namespace CeneoBundle\Controller;

use CeneoBundle\Entity\ExcludedProductRepository;
use CeneoBundle\Services\ExportStatus;

class OptionsController extends ControllerAbstract
{

    public function indexAction(){


        $shopId = $this->shop->getName();

        /**
         * @var $exportStatus ExportStatus
         */
        $exportStatus = $this->get('ceneo.export_status');

        if($exportStatus->exportExists($this->shop)) {
            $xmlLink = $this->get('router')->generate('ceneo_xml', array(
                'shopId' => $shopId
            ), true);
        }else{
            $xmlLink = false;
        }

        $status = $exportStatus->getStatus($this->shop);

        $enqueueLink = $this->generateAppUrl('ceneo_enqueue');
        $statusLink = $this->generateAppUrl('ceneo_status_check');

        /**
         * @var $excludedProducts ExcludedProductRepository
         */
        $excludedProducts = $this->getDoctrine()->getRepository('CeneoBundle:ExcludedProduct');

        $ids = $excludedProducts->findIdsByShop($this->shop);
        $this->get('ceneo.orphans_purger')->purgeExcluded($ids, $this->client, $this->shop);

        $count = $excludedProducts->getProductsCountByShop($this->shop);

        $stockLink = $this->shop->getShopUrl().'/admin/stock';

        return $this->render('CeneoBundle::options/index.html.twig', array(
            'xml_link'=>$xmlLink,
            'enqueue_link'=>$enqueueLink,
            'status_link'=>$statusLink,
            'excluded_count'=>$count,
            'stock_link'=> $stockLink,
            'export_status'=>$status
        ));
    }

}
