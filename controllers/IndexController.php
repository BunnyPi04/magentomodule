<?php

class Magebase_Hello_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * index action
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function customerAction()
    {
        $customer = Mage::getModel('customer/customer')
            ->getCollection()
            ->addAttributeToSelect('*')
            ->setOrder('entity_id', 'DESC')
            ->setPageSize(100)
            //get default bill setting
            ->joinAttribute('billing_street', 'customer_address/street', 'default_billing', null, 'left')
            ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('billing_fax', 'customer_address/fax', 'default_billing', null, 'left')
            ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_code', 'customer_address/country_id', 'default_billing', null, 'left')
            //get default ship setting
            ->joinAttribute('shipping_street', 'customer_address/street', 'default_shipping', null, 'left')
            ->joinAttribute('shipping_postcode', 'customer_address/postcode', 'default_shipping', null, 'left')
            ->joinAttribute('shipping_city', 'customer_address/city', 'default_shipping', null, 'left')
            ->joinAttribute('shipping_telephone', 'customer_address/telephone', 'default_shipping', null, 'left')
            ->joinAttribute('shipping_fax', 'customer_address/fax', 'default_shipping', null, 'left')
            ->joinAttribute('shipping_region', 'customer_address/region', 'default_shipping', null, 'left')
            ->joinAttribute('shipping_country_code', 'customer_address/country_id', 'default_shipping', null, 'left')
            ->joinAttribute('taxvat', 'customer/taxvat', 'entity_id', null, 'left');
        $arr_customers = array();
        foreach ($customer as $ob) {
            $arr_customers[] = $ob
                ->toArray(array());
        }
        return $this->getResponse()
            ->setHeader('Content-type', 'application/json')
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setBody(json_encode($arr_customers));
    }

    public function productAction()
    {
        $product = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('type_id', 'simple')//get only simple product
            ->setOrder('entity_id', 'DESC')
            ->setPageSize(100);
        $arr_products = array();
        foreach ($product as $ob) {
            $arr_products[] = array(
                'sku' => $ob->getSKU(),
                'name' => $ob->getName(),
                'type_id' => $ob->getType_id(),
                'description' => $ob->getDescription(),
                'color' => $ob->getColor(),
                'fit' => $ob->getFit(),
                'size' => $ob->getSize(),
                'price' => $ob->getPrice(),
                'weight' => $ob->getWeight(),
                'img' => Mage::getModel('catalog/product_media_config')->getMediaUrl($ob->getData("small_image"))
            );
        }
        return $this->getResponse()
            ->setHeader('Content-type', 'application/json')
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setBody(json_encode($arr_products));
    }

    public function paymentAction()
    {
        $payment = Mage::getModel('payment/config')->getAllMethods();

        foreach ($payment as $paymentCode => $paymentModel) {
            $paymentTitle = Mage::getStoreConfig('payment/' . $paymentCode . '/title');
            $paymentStatus = Mage::getStoreConfig('payment/' . $paymentCode . '/active');
            $methods[] = array(
                'title' => $paymentTitle,
                'code' => $paymentCode,
                'status' => $paymentStatus
            );
        }
        return $this->getResponse()
            ->setHeader('Content-type', 'application/json') //sends the http json header to the browser
            ->setHeader('Access-Control-Allow-Origin', '*') // Allow other page to get data
            ->setBody(json_encode($methods));
    }

    public function shippingAction()
    {
        $shipping = Mage::getModel('shipping/config')->getAllCarriers();
        $arr_shipping = array();

        foreach ($shipping as $shippingCode => $shippingModel) {
            $shippingTitle = Mage::getStoreConfig('carriers/' . $shippingCode . '/title'); //get name of shipping method
            $shippingStatus = Mage::getStoreConfig('carriers/' . $shippingCode . '/active');//get status of shipping method
            $methods[] = array(
                'title' => $shippingTitle,
                'code' => $shippingCode,
                'status' => $shippingStatus
            );
        }
        return $this->getResponse()
            ->setHeader('Content-type', 'application/json')
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setBody(json_encode($methods));
    }

    public function searchproductAction()
    {
        $search = $_GET["search"];//http://magento1.dev/index.php/hello/index/product //get search word
        $products = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToFilter('name', array('like' => "%$search%"))//select * where name like %$search%
            ->addAttributeToSelect('*')
//            ->addAttributeToSelect(array('sku','name','description','price','small_image','weight'))
            ->load();
        $arr_search = array();
        foreach ($products as $product) {
            $arr_search[] = array(
                'sku' => $product->getSKU(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'size' => $product->getSize(),
                'price' => $product->getPrice(),
                'weight' => $product->getWeight(),
                'img' => Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getData("small_image"))
            );
        }
        return $this->getResponse()
            ->setHeader('Content-type', 'application/json')
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setBody(json_encode($arr_search));
    }

//    public function stateAction()
//    {
//        $country = $_GET["country"];
//        $regionCollection = Mage::getModel('directory/region')
//            ->getResourceCollection()
//            ->addCountryFilter('country_id', $country)
//            ->load();
//        $arr_region = array();
//
//        foreach ($regionCollection as $region) {
//            $arr_region[] = $region
//                ->toArray(array());
//        }
//        return $this->getResponse()
//            ->setHeader('Content-type', 'application/json')
//            ->setHeader('Access-Control-Allow-Origin', '*')
//            ->setBody(json_encode($arr_region));
//    }

    public function countryAction()
    {
        $countryCollection = Mage::getModel('directory/country')
            ->getCollection();
        $arr_country = array();

        foreach ($countryCollection as $country) {
//            $arr_country[] = $country
//                ->toArray(array());
            $arr_country[] = array(
                'id' => $country->getCountryId(),
                'name' =>  $country->getName());
        }
        return $this->getResponse()
            ->setHeader('Content-type', 'application/json')
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setBody(json_encode($arr_country));
    }
}