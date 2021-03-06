<?php


namespace CeneoBundle\Services\Fetchers;


use DreamCommerce\ShopAppstoreLib\Resource\CategoriesTree;
use DreamCommerce\ShopAppstoreLib\Resource\Category;
use DreamCommerce\ShopAppstoreBundle\Utils\CollectionWrapper;
use DreamCommerce\ShopAppstoreBundle\Utils\Fetcher;

class Categories extends FetcherAbstract
{

    protected $categories;
    protected $tree;

    protected function fetch(){

        $categoriesTreeResource = new CategoriesTree($this->client);
        $categoriesResource = new Category($this->client);

        $this->tree = $categoriesTreeResource->get();

        $fetcher = new Fetcher($categoriesResource);
        $categories = $fetcher->fetchAll();
        $wrapper = new CollectionWrapper($categories);

        $this->categories = $wrapper->getArray('category_id');

    }

    public function getCategoryTree($id){
        $targetPath = array();

        $iterator = function($node, $path = array()) use (&$targetPath, $id, &$iterator){

            foreach($node as $i){
                if($i['id']==$id){
                    $path[] = $id;
                    $targetPath = $path;
                    return;
                }else if(!empty($i['children'])){
                    $path[] = $i['id'];
                    $iterator($i['children'], $path);
                    array_pop($path);
                }
            }

        };

        $iterator($this->tree);

        foreach($targetPath as &$n){
            $n = $this->categories[$n]->translations->pl_PL->name;
        }

        $stringPath = implode('/', $targetPath);
        $cache[$id] = $stringPath;

        return $stringPath;
    }



}