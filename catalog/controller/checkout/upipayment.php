<?php

//
//[UPI-Payment] is a Payment Module to Integrate
//UPI - [ Unified Payments Interface ] A Service From NPCI - [ National Payments Corporation Of India ]
//as a Payment Gateway Method in OPENCART - 3.0.2.0 - 3.0.3.0 - 3.0.3.1  - 3.0.3.2
//
//Developed By:
//Mr. TARAKESHWAR GAJAM
//ABCD PAYMENT SERVICES, #4-1-71/416, Sai Durga Gardens, Nacharam Main Road, Medchal-Malkajgiri District, Telangana State - 500076, India.
//URL: https://www.abcd-pay.com , E-Mail : admin@abcd-pay.com, Mobile : 0091-0-8106877688.
//
//File Path = "catalog/controller/checkout/upipayment.php"
//

class ControllerCheckoutUpiPayment extends Controller {
    public function index() {
        
        if($this->customer->isLogged()){
            $this->document->setTitle($this->config->get('config_meta_title'));
            $this->document->setDescription($this->config->get('config_meta_description'));
            $this->document->setKeywords($this->config->get('config_meta_keyword'));
            if (isset($this->request->get['route'])) {
                $this->document->addLink($this->config->get('config_url'), 'canonical');
            }
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');
            $data['successpage'] = $this->url->link('checkout/success','','SSL');
            if
                (
                    (isset($this->session->data['payment_method']['code']))
                    &&
                    ($this->session->data['payment_method']['code'] == 'upipayment')
                    &&
                    (isset($this->session->data['order_id']))
                    &&
                    ($this->cart->hasProducts())
                    &&
                    ($this->cart->getTotal() > 0)
                    &&
                    ($this->cart->getTotal() >= $this->config->get('payment_upipayment_total'))
                )
                {
                    $this->load->language('extension/payment/upipayment');

                    $order_id = trim($this->session->data['order_id']);
                    $data['orderid'] = $order_id;
                    $data['payment_method_code'] = $this->session->data['payment_method']['code'];
                    $data['order_success_status_id'] = $this->config->get('payment_upipayment_order_status_id');
                    $data['order_cancel_status_id'] = $this->config->get('payment_upipayment_cancel_order_status_id');

                    if ($this->request->server['HTTPS']) {
                            $server = $this->config->get('config_ssl');
                    } else {
                            $server = $this->config->get('config_url');
                    }
                    if (isset($this->session->data['guest'])) {
                        $data['customeremail'] = trim($this->session->data['guest']['email']);
                    } else {
                        $data['customeremail'] = trim($this->customer->getEmail());
                    }

                    $this->cart->clear();

                    unset($this->session->data['shipping_method']);
                    unset($this->session->data['shipping_methods']);
                    unset($this->session->data['payment_method']);
                    unset($this->session->data['payment_methods']);
                    unset($this->session->data['guest']);
                    unset($this->session->data['comment']);
                    unset($this->session->data['order_id']);
                    unset($this->session->data['coupon']);
                    unset($this->session->data['reward']);
                    unset($this->session->data['voucher']);
                    unset($this->session->data['vouchers']);
                    unset($this->session->data['totals']);

                    $qrimage = DIR_IMAGE."upiqrimage/archive/".$order_id.".png";
                    if(file_exists($qrimage)){
                        $data['upipaymentcomment'] = "Order-ID : ".$order_id." : ";
                        $data['timeout'] = trim($this->config->get('payment_upipayment_qr_show_time'));
                        $data['upiqrimageurl'] = $server."image/upiqrimage/archive/".basename($qrimage);
                        $upitxninput = filter_var($this->config->get('payment_upipayment_transaction_input'), FILTER_VALIDATE_BOOLEAN);
                        if($upitxninput == TRUE){
                            $this->response->setOutput($this->load->view('checkout/upitransactioninput', $data));
                        } else if($upitxninput == FALSE){
                            $this->response->setOutput($this->load->view('checkout/upipaymentshow', $data));
                        }
                    } else {
                        $data['upipaymentcomment'] = ": UPI-Payment Configuration Error :\n\n-- ";
                        $this->response->setOutput($this->load->view('checkout/upipaymenterror', $data));
                    }
                } else {
                    $data['upipaymentcomment'] = ": Shopping Cart Error :\n\n-- ";
                    $this->response->setOutput($this->load->view('checkout/upipaymenterror', $data));
                }
        } else {
           $this->response->redirect($this->url->link('account/account','language='.$this->config->get('config_language'),'SSL'));
        }
    }

    public function upiqrpayupdate() {
            $json = array();
            if ($_POST['paymentmethodcode'] == 'upipayment') {
                $this->load->model('checkout/order');
                $this->model_checkout_order->addOrderHistory($_POST['updateorderid'], $_POST['order_success_status_id'], $_POST['upitxntext'], true);
                $json['redirect'] = $this->url->link('checkout/success');
            }
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
    }

    public function ordercancel() {
        $json = array();
        if ($_POST['paymentmethodcode'] == 'upipayment') {
            $this->load->model('checkout/order');
            $this->model_checkout_order->addOrderHistory($_POST['cancelorderid'], $_POST['order_cancel_status_id'], $_POST['ordercanceltext'], true);
            $json['redirect'] = $this->url->link('checkout/success');
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    public function orderstatus(){
        $json = array();
        $order_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$_POST['orderid'] . "' AND order_status_id > '0'");
        if (($order_query->num_rows) && (trim((int)($order_query->row['order_status_id'])) !== trim((int)$this->config->get('payment_upipayment_order_status_id')))) {
            $json['redirect'] = $this->url->link('checkout/success');
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

}