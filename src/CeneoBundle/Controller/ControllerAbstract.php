<?php
/**
 * Created by PhpStorm.
 * User: eRIZ
 * Date: 2015-03-31
 * Time: 17:09
 */

namespace CeneoBundle\Controller;


use DreamCommerce\ShopAppstoreBundle\Controller\ApplicationController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class ControllerAbstract extends ApplicationController{

    protected function addError($error){
        /**
         * @var $session Session
         */
        $session = $this->get('session');
        $session->getFlashBag()->add('error', $error);
    }

    protected function addNotice($notice){
        /**
         * @var $session Session
         */
        $session = $this->get('session');
        $session->getFlashBag()->add('notice', $notice);
    }

    /**
     * @param Request $request
     * @throws \HttpMalformedHeadersException
     */
    protected function filterAjaxCall(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new \HttpMalformedHeadersException();
        }
    }



}