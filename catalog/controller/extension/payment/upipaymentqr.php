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
//File Path = "catalog/controller/extension/payment/upipaymentqr.php"
//

require (DIR_SYSTEM."library/upiqr/upiqrcode.php");

class ControllerExtensionPaymentUpiPaymentQr extends Controller {
    
    public function index(&$route, &$args) {
        
        if (isset($args[0])) {
                $order_id = trim($args[0]);
        } else {
                $order_id = 0;
        }

        if (isset($args[1])) {
                $order_status_id = trim($args[1]);
        } else {
                $order_status_id = 0;
        }

        $order_success_status_id = trim($this->config->get('payment_upipayment_order_status_id'));

        $order_info = $this->model_checkout_order->getOrder($order_id);
        
        if
            (
                ($order_info)
                &&
                (trim($order_info['payment_code']) === "upipayment")
                &&
                (!$order_info['order_status_id'])
                &&
                ($order_status_id)
                &&
                ($order_status_id == $order_success_status_id)
            )
            {
                $upiid = trim($this->config->get('payment_upipayment_upi_id'));
                $upipayee = trim($this->config->get('payment_upipayment_upi_reg_name'));
                $amount = trim(number_format($order_info['total'],2,'.',''));
                if
                    (
                        (!empty($upiid))
                        &&
                        (!empty($upipayee))
                        &&
                        ($amount > 0)
                    )
                    {
			$remarks = $this->config->get('payment_upipayment_remarks_prefix').$order_id;
                        $addlabeltext = filter_var($this->config->get('payment_upipayment_add_label_text'), FILTER_VALIDATE_BOOLEAN);
                        $imagepath = getupiqrcode($upiid, $upipayee, $amount, $remarks, $order_id, $order_id, $addlabeltext);
                        unset($imagepath);
                    }
            }
    }
}