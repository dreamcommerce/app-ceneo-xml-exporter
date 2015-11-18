<?php
namespace CeneoBundle\Controller;


use BillingBundle\Entity\Shop;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GeneratorController extends ControllerAbstract{

    public function enqueueAction(Request $request){

        $this->filterAjaxCall($request);

        /**
         * @var $shop Shop
         */
        $shop = $this->shop;

        if(!$shop){
            throw new NotFoundHttpException();
        }

        $response = [
            'ok'=>false
        ];

        $status = $this->get('ceneo.export_status')->getStatus($shop)->isInProgress();
        if($status){
            $response['error'] = 'in_progress';
        }else{
            $manager = $this->get('ceneo.queue_manager');
            $manager->enqueue($shop);

            $this->get('ceneo.export_status')->markInProgress($this->shop, 0);

            $response['ok'] = true;
        }

        return new JsonResponse($response);
    }

    public function checkStatusAction(Request $request){

        $this->filterAjaxCall($request);

        $export = $this->get('ceneo.export_status')->getStatus($this->shop);
        $status = $export->isInProgress();

        $response = [
            'inProgress'=>$status,
            'productsCount'=>$export->getProductsCount(),
            'exported'=>$export->getExported(),
            'eta'=>$export->getEta()
        ];

        if(!$status) {
            $response['url'] = $this->get('router')->generate('ceneo_xml', array(
                'shopId' => $this->shop->getName()
            ), true);
        }

        return new JsonResponse($response);

    }

    public function dummyAction(){
        throw new NotFoundHttpException();
    }



}