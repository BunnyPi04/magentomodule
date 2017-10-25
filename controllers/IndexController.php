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
                'entity_id'=>$ob->getEntityId(),
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

    public function storeAction()
    {
        $allStores = Mage::app()->getStores();
        foreach ($allStores as $eachStoreId => $val)
        {
            $arr_store[]=array(
            'storeName' => Mage::app()->getStore($eachStoreId)->getName(),
            'storeId' => Mage::app()->getStore($eachStoreId)->getId());
        }
        return $this->getResponse()
            ->setHeader('Content-type', 'application/json')
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setBody(json_encode($arr_store));
    }

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
    public function quoteAction()
    {
        if (isset($_GET["productId"])&& isset($_GET["customerId"]) && isset($_GET["storeId"])) {
            $productId = $_GET["productId"];
            $quote = Mage::getModel('sales/quote');
            foreach($productId as $singleProduct) {
                $product = Mage::getModel('catalog/product')->load($singleProduct);
                $quote->addProduct($product, new Varien_Object(array('qty'=>1)));
            }
            $storeId = $_GET["storeId"];
            $quote->setStoreId($storeId);

            $id = $_GET["customerId"];
            $customer = Mage::getModel('customer/customer')->load($id);
            $roleId = $customer->getCustomerGroupId();
            $role = Mage::getSingleton('customer/group')->load($roleId)->getData('customer_group_code');
            $quote->assignCustomer($customer);

            $billingAddress = $customer->getDefaultBillingAddress();
            $quote->getBillingAddress()
                ->addData($billingAddress->getData());
//            $quote->collectTotals();
//            var_dump($quote->collectTotals()->getTotals());die;
            $quote->getPayment()->setMethod('cashondelivery');
            $quote->getPayment()->importData(array('method' => 'cashondelivery'));

//            $shippingAddress = $quote->getShippingAddress();
//            $shippingAddress->addData($billingAddress->getData())
//                ->setCollectShippingRates(true)
//                ->collectShippingRates();

//            var_dump($quote->getShippingMethod());die;
//            $rate = [];
//            foreach ($quote->getShippingAddress()->getAllSShippingRates() as $rates) {
//                $rate[]= $rates->getData();
//            }
            $quoteData= $quote->getData();
            $grandTotal=$quoteData['grand_total'];
            $result = $grandTotal;
            if (isset($_GET['ship'])) {
//
                if ($_GET['ship'] == 1) {
                    $quote->getShippingAddress()
                        ->setRecollect(true)
                        ->addData($customer->getDefaultShippingAddress()->getData())
                        ->setCollectShippingRates(true)
                        ->collectShippingRates();

                    $_rates = $quote->getShippingAddress()->getAllShippingRates();
                    $shippingRates = array();
                    foreach ($_rates as $_rate) {
                        $shippingRates[] = $_rate->getData();
                    }
                    $result = $shippingRates;
                } else {
                    $ship = $_GET['ship'];
                    $quote->getShippingAddress()
                        ->setShippingMethod('ups_GND')
                        ->setRecollect(true)
                        ->addData($customer->getDefaultShippingAddress()->getData())
                        ->setCollectShippingRates(true)
                        ->collectShippingRates();
                }
            }


//            $increment_id = $service->getOrder()->getRealOrderId();

            if (isset($_GET['action'])) {
//                $quote->getShippingAddress()
//                    ->setCollectShippingRates(true)
//                    ->collectShippingRates();
                $quote->collectTotals();
                $quote->save();
//                var_dump($quote->getShipping);die;
                $service = Mage::getModel('sales/service_quote', $quote);
                $order = $service->submit();
            }
            return $this->getResponse()
                ->setHeader('Content-type', 'application/json') //sends the http json header to the browser
                ->setHeader('Access-Control-Allow-Origin', '*') // Allow other page to get data
                ->setBody(json_encode($result));
        }

    }
}