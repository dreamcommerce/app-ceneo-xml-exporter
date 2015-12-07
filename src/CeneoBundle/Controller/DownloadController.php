<?php

namespace CeneoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DownloadController extends Controller
{


    public function downloadAction(Request $req, $shopId){

        $file = sprintf('%s/%s.xml', $this->container->getParameter('xml_dir'), basename($shopId));

        if(!$this->isIpAllowed($req, $_SERVER['REMOTE_ADDR'])){
            throw new NotFoundHttpException();
        }

        if(!file_exists($file)){
            throw new NotFoundHttpException();
        }

        // x-sendfile
        return new BinaryFileResponse(
            new \SplFileInfo($file)
        );

    }

    protected function isIpAllowed(Request $request, $ip){

        $localIp = $request->getSession()->get('ip');
        if($localIp && $localIp==$ip){
            return true;
        }

        // http://stackoverflow.com/a/594134
        $checker = function($ip, $cidrOrIp){
            $parts = explode('/', $cidrOrIp);

            if(!isset($parts[1])){
                return $ip==$cidrOrIp;
            }

            list ($subnet, $bits) = $parts;

            $ip = ip2long($ip);
            $subnet = ip2long($subnet);
            $mask = -1 << (32 - $bits);
            $subnet &= $mask; # nb: in case the supplied subnet wasn't correctly aligned
            return ($ip & $mask) == $subnet;
        };

        foreach($this->container->getParameter('ip_whitelist') as $cidrOrIp){
            if($checker($ip, $cidrOrIp)){
                return true;
            }
        }

        return false;

    }
}
