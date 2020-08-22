<?php
//
//[UPI-Payment] is a Payment Module to Integrate
//UPI - [ Unified Payments Interface ] A Service From NPCI - [ National Payments Corporation Of India ]
//as a Payment Gateway Method in OPENCART - 3.0.2.0 - 3.0.3.0 - 3.0.3.1 - 3.0.3.2 - 3.0.3.3
//
//Developed By:
//Mr. TARAKESHWAR GAJAM
//ABCD PAYMENT SERVICES, #4-1-71/416, Sai Durga Gardens, Nacharam Main Road, Medchal-Malkajgiri District, Telangana State - 500076, India.
//URL: https://www.abcd-pay.com , E-Mail : admin@abcd-pay.com, Mobile : 0091-0-8106877688.
//
//File Path = "catalog/controller/mail/upipaymentqr.php"
//

class ControllerMailUpiPaymentQr extends Controller {
	public function index(&$route, &$args) {
		if (isset($args[0])) {
			$order_id = trim($args[0]);
		} else {
			$order_id = 0;
		}

		if (isset($args[1])) {
			$order_status_id = $args[1];
		} else {
			$order_status_id = 0;
		}	

		if (isset($args[2])) {
                        $comment = $this->language->get('text_email_attachment').$args[2];
		} else {
			$comment = '';
		}

                // We need to grab the old order status ID
		$order_info = $this->model_checkout_order->getOrder($order_id);
		
                $upiqrimage = DIR_IMAGE."upiqrimage/".$order_id.".png";

                if (($order_info) && (trim($order_info['payment_code']) === "upipayment") && (file_exists($upiqrimage))) {
			// If order status is 0 then becomes greater than 0 send main html email
			if (!$order_info['order_status_id'] && $order_status_id) {

                            $this->load->model('setting/setting');

                            $from = $this->model_setting_setting->getSettingValue('config_email', $order_info['store_id']);

                            if (!$from) {
                                    $from = $this->config->get('config_email');
                            }

                            $email_subject = $order_info['store_name']." - Order ".$order_id." - UPI-Payment-QR";
                            $email_body_text = "To view your order click on the link below :";
                            $order_link = $this->url->link('account/order/info&order_id='.$order_id,'','SSL');
                            $email_body = html_entity_decode(("<pre>".$email_body_text."<br/><br/><a href=".$order_link.">".$order_link."</a><br/><br/>".$comment."<br/>"."</pre>"),ENT_QUOTES,'UTF-8');
                            
                            $mail = new Mail($this->config->get('config_mail_engine'));
                            $mail->parameter = $this->config->get('config_mail_parameter');
                            $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                            $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                            $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                            $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                            $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
                            
                            $mail->setTo($order_info['email']);
                            $mail->setFrom($from);
                            $mail->setSender(html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'));
                            $mail->setSubject(html_entity_decode($email_subject), ENT_QUOTES, 'UTF-8');
                            $mail->setHtml($email_body, ENT_QUOTES, 'UTF-8');
                            // UPI QR IMAGE ATTACHMENT
                            $mail->addAttachment($upiqrimage);
                            $mail->send();
                            if(file_exists($upiqrimage)){
                                $imagearchivedir = DIR_IMAGE."upiqrimage/archive";
                                if(!file_exists($imagearchivedir)){
                                    mkdir($imagearchivedir, 0755, true);
                                }
                                $imagepath = $imagearchivedir.'/'. basename($upiqrimage);
                                rename($upiqrimage, $imagepath);
                            }
                            // UPI QR IMAGE ATTACHMENT
                    }
		}
	}
}