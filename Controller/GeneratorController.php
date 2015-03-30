<?php
/**
 * Created by PhpStorm.
 * User: eRIZ
 * Date: 2015-03-28
 * Time: 16:43
 */

namespace CeneoBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class GeneratorController extends Controller{

    public function downloadAction($shopId){

        // todo: refactor
        $repository = $this->getDoctrine()->getRepository('BillingBundle\Entity\Shop');
        $shop = $repository->findOneBy(array('name'=>$shopId));

        if(!$shop){
            throw new NotFoundHttpException();
        }

        $webDir = $this->get('kernel')->getRootDir() . '/../web';

        $path = $webDir . '/exports/' . $shop->getName() . '.xml';
        if(!file_exists($path)){
            throw new ServiceUnavailableHttpException();
        }

        return new Response('', 200, array('X-Accel-Redirect' => $path, 'Content-Type'=>'text/xml'));

    }

}