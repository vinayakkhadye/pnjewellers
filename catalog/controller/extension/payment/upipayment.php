<?php

//
//[UPI-Payment] is a Payment Module to Integrate
//UPI - [ Unified Payments Interface ] A Service From NPCI - [ National Payments Corporation Of India ]
//as a Payment Gateway Method in OPENCART - 3.0.2.0 - 3.0.3.0 - 3.0.3.1  - 3.0.3.2 - 3.0.3.3
//
//Developed By:
//Mr. TARAKESHWAR GAJAM
//ABCD PAYMENT SERVICES, #4-1-71/416, Sai Durga Gardens, Nacharam Main Road, Medchal-Malkajgiri District, Telangana State - 500076, India.
//URL: https://www.abcd-pay.com , E-Mail : admin@abcd-pay.com, Mobile : 0091-0-8106877688.
//
//File Path = "catalog/controller/extension/payment/upipayment.php"
//

class ControllerExtensionPaymentUpiPayment extends Controller {
    
    public function index() {
        $data['orderid'] = trim($this->session->data['order_id']);
        return $this->load->view('extension/payment/upipayment',$data);
    }

    public function confirm() {
        $json = array();
        if($this->customer->isLogged()){
            $this->load->language('extension/payment/upipayment');
            $this->load->model('checkout/order');
            if (isset($_POST['orderid'])){
                $order_id = trim($_POST['orderid']);
                $data['orderid'] = $order_id;
                $order_success_status_id = $this->config->get('payment_upipayment_order_status_id');
                $order_cancel_status_id = $this->config->get('payment_upipayment_cancel_order_status_id');
                if (isset($this->session->data['payment_method']['code'])){
                    if(trim($this->session->data['payment_method']['code']) == 'upipayment'){
                        if (isset($this->session->data['order_id'])){
                            if
                                (
                                    (trim($this->session->data['order_id']) == $order_id)
                                    &&
                                    ($this->cart->hasProducts())
                                    &&
                                    ($this->cart->getTotal() > 0)
                                    &&
                                    ($this->cart->getTotal() >= $this->config->get('payment_upipayment_total'))
                                )
                                {
                                    $upiid = trim($this->config->get('payment_upipayment_upi_id'));
                                    $upipayee = trim($this->config->get('payment_upipayment_upi_reg_name'));

                                    $order_totals = $this->model_checkout_order->getOrder($order_id);
                                    $amount = trim(number_format($order_totals['total'],2,'.',''));

                                    if
                                        (
                                            (!empty($upiid))
                                            &&
                                            (!empty($upipayee))
                                            &&
                                            ($amount > 0)
                                        )
                                        {
                                            $comment = $this->language->get('text_upi_qr_info').$order_id.".png\n\n"
                                                        .$this->language->get('text_upi_payment')."\n\n"
                                                        .$this->language->get('text_upi_id')."\n".$upiid."\n\n"
                                                        .$this->language->get('text_upi_reg_name')."\n".$upipayee."\n\n"
                                                        .$this->language->get('text_upi_amount')."\n"."INR.".$amount."\n\n"
                                                        .$this->language->get('text_upi_remarks')."\n".$this->config->get('payment_upipayment_remarks_prefix').$order_id."\n\n"
                                                        .$this->language->get('text_payment_note')."\n\n"
                                                        .$this->language->get('text_payment_info')."\n\n--";
                                            $this->model_checkout_order->addOrderHistory($order_id, $order_success_status_id, $comment, true);
                                        } else {
                                            $data['upipaymentcomment'] = ": UPI-Payment Configuration Error :\n\n--";
                                            $this->model_checkout_order->addOrderHistory($order_id, $order_cancel_status_id, $data['upipaymentcomment'], true);
                                        }
                                    } else {
                                        $data['upipaymentcomment'] = ": Shopping Cart Error :\n\n-- ";
                                        $this->model_checkout_order->addOrderHistory($order_id, $order_cancel_status_id, $data['upipaymentcomment'], true);
                                    }
                        }
                    }
                } else {
                    $data['upipaymentcomment'] = ": Customer Activity Error :\n\n-- ";
                    $this->model_checkout_order->addOrderHistory($order_id, $order_cancel_status_id, $data['upipaymentcomment'], true);
                }
            }
        }
        $json['redirect'] = $this->url->link('checkout/upipayment');
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

}