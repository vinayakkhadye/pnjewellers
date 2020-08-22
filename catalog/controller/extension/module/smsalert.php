<?php
class ControllerExtensionModuleSmsAlert extends Controller {
	private $error = array();
	
	public function index() {
		$this->load->model('account/customer');
		$this->load->model('extension/module/smsalert');
		if(!empty($this->request->get['otp_event']))
		{
			$otp_event=$this->request->get['otp_event'];
			$mobile = (!empty($this->session->data['guest']['telephone'])) ? $this->session->data['guest']['telephone'] : $this->customer->getTelephone();
			
			if($otp_event=='checkout')
			{
				$template = $this->getOtpTemplates('otp_for_checkout', $this->config->get('config_store_id'));
				if($template)
				{
					return $this->sendotpforcheckout($mobile,$template);
				}
			}
			elseif($otp_event=='verify')
			{
				if((!empty($this->request->post['code'])))
				{
					return $this->verifyotpforcheckout($mobile,$this->request->post['code']);
				}
			}
			
		}
		else
		{
			$this->load->language('extension/module/smsalert');
			$this->document->setTitle($this->language->get('heading_title'));
			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home')
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_account'),
				'href' => $this->url->link('extension/module/smsalert', '', true)
			);
            $data['heading_title'] = $this->language->get('heading_title');
			 
			$data['button_continue'] = $this->language->get('button_continue');
			$data['button_resend'] = $this->language->get('button_resend');
			
			$data['entry_otp'] = $this->language->get('entry_otp');

			$data['action'] = $this->url->link('extension/module/smsalert', '', true);
			$data['resend'] = $this->url->link('extension/module/smsalert', '', true);
			
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');
			if(isset($this->session->data['sa_register']) && $this->session->data['sa_register'])
			{
				if (isset($this->session->data['two_fa_r_sms'])) {
					$this->response->redirect($this->url->link('account/account', '', true));
				}
				if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
					
					$this->session->data['two_fa_r_sms'] = true;
					unset($this->session->data['sa_email']);
					unset($this->session->data['sa_password']);
					unset($this->session->data['sa_telphone']);
					unset($this->session->data['sa_firstname']);
					unset($this->session->data['sa_lastname']);
					unset($this->session->data['sa_confirm']);
					unset($this->session->data['sa_newsletter']);
					unset($this->session->data['sa_agree']);
					unset($this->session->data['sa_register']);
					$this->load->controller('account/register',$this->request->post);
				}

				if ($this->request->server['REQUEST_METHOD'] != 'POST') 
				{
					if (!isset($this->session->data['two_fa_smsalert_time']) || ($this->session->data['two_fa_smsalert_time'] + 30) <= time()) 
					{
						$template = $this->getOtpTemplates('otp_for_register', $this->config->get('config_store_id'));
						if($template)
						{
							$status = $this->resend($this->session->data['sa_telephone'],$template);
							$data['resend_timer']=$this->language->get('resend_timer');//in secs
							if (!$status) 
							{
								$this->session->data['two_fa_r_sms'] = true;
								$this->response->redirect($this->url->link('account/account', '', true));
							}
						}
					}
					else{
						unset($this->session->data['two_fa_smsalert_time']);
						$this->response->redirect($this->url->link('account/account', '', true));
					}
					
				}
				
				if(!empty($this->session->data['sa_email']) && !empty($this->session->data['sa_password']))
				{
					$data['data']['email']=$this->session->data['sa_email'];
					$data['data']['password']=$this->session->data['sa_password'];
					$data['data']['telephone']=$this->session->data['sa_telephone'];
					$data['data']['firstname']=$this->session->data['sa_firstname'];
					$data['data']['lastname']=$this->session->data['sa_lastname'];
					$data['data']['confirm']=$this->session->data['sa_confirm'];
					$data['data']['newsletter']=$this->session->data['sa_newsletter'];
					$data['data']['agree']=$this->session->data['sa_agree'];
				}
				if (isset($this->session->data['success'])) {
				$data['success'] = $this->session->data['success'];

				unset($this->session->data['success']);
				} else {
					$data['success'] = '';
				}
				
				if (isset($this->session->data['error_warning'])) {
					$data['error_warning'] = $this->session->data['error_warning'];

					unset($this->session->data['error_warning']);
				} else {
					$data['error_warning'] = '';
				}
			    $this->response->setOutput($this->load->view('extension/module/smsalert_otp', $data));
			}
			else{
				if (isset($this->session->data['two_fa_sms'])) {
					$this->response->redirect($this->url->link('account/account', '', true));
				}
				if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
					
					$this->session->data['two_fa_sms'] = true;
					unset($this->session->data['sa_email']);
					unset($this->session->data['sa_pwd']);
					unset($this->session->data['sa_phone']);
					$this->load->controller('account/login',$this->request->post);
				}

				if ($this->request->server['REQUEST_METHOD'] != 'POST') 
				{
					if (!isset($this->session->data['two_fa_smsalert_time']) || ($this->session->data['two_fa_smsalert_time'] + 30) <= time()) 
					{
						$template = $this->getOtpTemplates('otp_for_login', $this->config->get('config_store_id'));
						if($template)
						{
							$status = $this->resend($this->session->data['sa_phone'],$template);
							$data['resend_timer']=$this->language->get('resend_timer');//in secs
							if (!$status) 
							{
								$this->session->data['two_fa_sms'] = true;
								$this->response->redirect($this->url->link('account/account', '', true));
							}
						}
					}
					else{
						unset($this->session->data['two_fa_smsalert_time']); //when try to re-login again.
						$this->response->redirect($this->url->link('account/account', '', true));
					}
					
				}
				
				if(!empty($this->session->data['sa_email']) && !empty($this->session->data['sa_pwd']))
				{
					$data['data']['email']=$this->session->data['sa_email'];
					$data['data']['password']=$this->session->data['sa_pwd'];
					$data['data']['telephone']=$this->session->data['sa_phone'];
				}
				  if (isset($this->session->data['success'])) {
				$data['success'] = $this->session->data['success'];

				unset($this->session->data['success']);
				} else {
					$data['success'] = '';
				}
				
				if (isset($this->session->data['error_warning'])) {
					$data['error_warning'] = $this->session->data['error_warning'];

					unset($this->session->data['error_warning']);
				} else {
					$data['error_warning'] = '';
				}
				  $this->response->setOutput($this->load->view('extension/module/smsalert_otp', $data));
			}
		}
	}
	
	public function getOtpTemplates($type,$store_id)
	{
		$this->load->model('extension/module/smsalert');
		$templates = $this->model_extension_module_smsalert->getTemplates($type, $store_id);
		$template = array_shift($templates);
		if(!empty($template['message']) && $template['status']=='1')
		{
			return $template['message'];
		}
		return false;
	}
	
	protected function resend($receiver,$template) {
		$this->load->model('account/customer');
		$this->load->model('extension/module/smsalert');
		if (!$this->config->get('smsalert_auth_key') || !$this->config->get('smsalert_auth_secret')) {
			exit('No key is configured to send SMS. Please contact the administrator.');
		}
		$this->load->language('extension/module/smsalert');
		$this->session->data['two_fa_smsalert_time'] = time();
		
		$response = $this->model_extension_module_smsalert->sendOtp($receiver,$template);
		if(!empty($response['status']) && $response['status']=='error')
		{
			$this->session->data['error_warning'] = $this->language->get('error_invalid_number');
			return false;
		}
		
		$this->session->data['success'] = sprintf($this->language->get('text_success'), $receiver);
		return true;
	}
	
	private function validate() {
		
		if (empty($this->request->post['code'])) 
		{
			    $this->session->data['error_warning'] = $this->language->get('blank_otp');
		        return false;
		}
		else 
		{
			//call mverify for check the otp
			$receiver = $this->request->post['telephone'];
			$this->load->model('extension/module/smsalert');
			
			$response = $this->model_extension_module_smsalert->verifyOtp($receiver,$this->request->post['code']);
			if(!empty($response['description']['desc']) && $response['description']['desc']=='Code does not match.')
			{
				$this->session->data['error_warning'] = $response['description']['desc'];
		        return false;
			}
		} 
		return true;
	}
	
	/*add html code for checkout otp popup*/
	public function eventPostControllerCheckoutPaymentMethod($route, &$data) {
		$template = $this->getOtpTemplates('otp_for_checkout', $this->config->get('config_store_id'));
		$enabled_payment_methods=$this->config->get('smsalert_payment_method');
		
		if($template)
		{
			if ($this->cart->hasShipping()) {
				$data['text_checkout_payment_method'] = sprintf($this->language->get('text_checkout_payment_method'), 5);
				$data['text_checkout_confirm'] = sprintf($this->language->get('text_checkout_confirm'), 6);
			} else {
				$data['text_checkout_payment_method'] = sprintf($this->language->get('text_checkout_payment_method'), 3);
				$data['text_checkout_confirm'] = sprintf($this->language->get('text_checkout_confirm'), 4);	
			}
			$data['resend_timer']=30;//in secs
			$data['blank_otp'] = $this->language->get('blank_otp');
			$data['enabled_payment_methods'] = $enabled_payment_methods;
			echo $this->load->view('extension/module/smsalert_checkout_otp', $data);
		}
	}
	
	/*send OTP to Mobile Number from checkout page*/
	public function sendotpforcheckout($receiver,$template) {
		
		$this->load->model('extension/module/smsalert');
		$json=array();
		$json = $this->model_extension_module_smsalert->sendOtp($receiver,$template);
		$json['telephone']=$receiver;
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	/*send OTP to Mobile Number from checkout page*/
	public function verifyotpforcheckout($receiver,$code) {
		
		$this->load->model('extension/module/smsalert');
		
		$json=array();
		$json = $this->model_extension_module_smsalert->verifyOtp($receiver,$code);
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	
	
	
	
	public function eventPreControllerAccountLogin($route, &$data) {
		$this->load->model('account/customer');
		$this->load->model('extension/module/smsalert');
		$template = $this->getOtpTemplates('otp_for_login', $this->config->get('config_store_id'));
		if($template)
		{
			$email = (!empty($this->request->post['email'])) ? $this->request->post['email'] : '';
			$password = (!empty($this->request->post['password'])) ? $this->request->post['password'] : '';
			if($email!='' && $password != '')
			{
				$customer_info = $this->model_extension_module_smsalert->isCustomerExists($email,$password);
				if(empty($customer_info))
				{
					$this->session->data['error'] ='Invalid email or password.';
					$this->response->redirect($this->url->link('account/login', '', true));
				}
				else if (!isset($this->session->data['two_fa_sms']) && !empty($customer_info)) {
					
					$this->session->data['sa_email']=$email;
					$this->session->data['sa_pwd']=$password;
					$this->session->data['sa_phone']=$customer_info['telephone'];
					$this->response->redirect($this->url->link('extension/module/smsalert', '', true));
					//$this->load->controller('extension/module/smsalert',$this->request->post);
				}
			}
		}
	}
	
	public function eventPostControllerAccountLogout($route, &$data) {
		unset($this->session->data['two_fa_sms']);
		unset($this->session->data['two_fa_r_sms']);
	}
		
	public function eventPostModelAccountCustomerAdd($route, $args, $output) {
		$this->load->model('account/customer');
		$this->load->model('extension/module/smsalert');
		
		$customer_info = $this->model_account_customer->getCustomer($output);
		
		$replace = array(
			$customer_info['firstname'],
			$customer_info['lastname'],
			$customer_info['email'],
			$customer_info['telephone'],
			$customer_info['password']
		);
		$this->model_extension_module_smsalert->parseSMS('register', $this->config->get('config_store_id'), $customer_info['telephone'], $replace);
	}
	public function eventPreModelAccountCustomerAdd($route, &$data) {
		$this->load->model('extension/module/smsalert');
		$template = $this->getOtpTemplates('otp_for_register', $this->config->get('config_store_id'));
		if($template && !array_key_exists('shipping_address',$this->request->post))
		{
			$firstname = (!empty($this->request->post['firstname'])) ? $this->request->post['firstname'] : '';
			$lastname = (!empty($this->request->post['lastname'])) ? $this->request->post['lastname'] : '';
			$email = (!empty($this->request->post['email'])) ? $this->request->post['email'] : '';
			$telephone = (!empty($this->request->post['telephone'])) ? $this->request->post['telephone'] : '';
			$password = (!empty($this->request->post['password'])) ? $this->request->post['password'] : '';
			$confirm = (!empty($this->request->post['confirm'])) ? $this->request->post['confirm'] : '';
			$newsletter = (!empty($this->request->post['newsletter'])) ? $this->request->post['newsletter'] : '';
			$agree = (!empty($this->request->post['agree'])) ? $this->request->post['agree'] : '';
			
			
			if($firstname!='' && $lastname!='' && $telephone!='' && $email!='' && $password != '' && $confirm!='')
			{
				if(!isset($this->session->data['two_fa_r_sms']))
				{
					$this->session->data['sa_email']=$email;
					$this->session->data['sa_password']=$password;
					$this->session->data['sa_firstname']=$firstname;
					$this->session->data['sa_lastname']=$lastname;
					$this->session->data['sa_telephone']=$telephone;
					$this->session->data['sa_confirm']=$confirm;
					$this->session->data['sa_newsletter']=$newsletter;
					$this->session->data['sa_agree']=$agree;
					$this->session->data['sa_register']=true;
					$this->response->redirect($this->url->link('extension/module/smsalert', '', true));
				}
			}
		}
	}
	public function eventPostModelAccountCustomerAddAffiliate($route, $args, $output) {
		$this->load->model('account/customer');
		$this->load->model('extension/module/smsalert');
		
		$customer_info = $this->model_account_customer->getCustomer($args[0]);
		
		$replace = array(
			$customer_info['firstname'],
			$customer_info['lastname'],
			$customer_info['email'],
			$customer_info['telephone']
		);
		
		$this->model_extension_module_smsalert->parseSMS('affiliate', $this->config->get('config_store_id'), $customer_info['telephone'], $replace);
	}
	
	public function eventPostModelAccountCustomerAddTransaction($route, $args, $output) {
		$this->load->model('account/customer');
		$this->load->model('extension/module/smsalert');
		
		$customer_id = isset($args[0]) ? $args[0] : 0;
		$description = isset($args[1]) ? $args[1] : '';
		$amount = isset($args[2]) ? $args[2] : 0;
		$order_id = isset($args[3]) ? $args[3] : 0;
		
		$customer_info = $this->model_account_customer->getCustomer($customer_id);
				
		$replace = array(
			$customer_info['firstname'],
			$customer_info['lastname'],
			$customer_info['email'],
			$this->currency->format($amount, $this->config->get('config_currency')),
			$this->currency->format($this->model_account_customer->getTransactionTotal($customer_id), $this->config->get('config_currency'))
		);
		
		$this->model_extension_module_smsalert->parseSMS('affiliate_transaction', $this->config->get('config_store_id'), $customer_info['telephone'], $replace);
	}
	
	public function eventPostModelAccountCustomerEditCode($route, $args, $output) {
		$this->load->model('account/customer');
		$this->load->model('extension/module/smsalert');
		
		$customer_info = $this->model_account_customer->getCustomerByEmail($args[0]);
		
		$replace = array(
			$customer_info['firstname'],
			$customer_info['lastname'],
			$customer_info['email'],
			$this->url->link('account/reset', 'code=' . $args[1], true)
		);
		
		$this->model_extension_module_smsalert->parseSMS('forgotten', $this->config->get('config_store_id'), $customer_info['telephone'], $replace);
	}
	
	public function eventPostModelCheckoutOrderAddOrderHistory($route, $args) {
		
		if (isset($args[0])) {
			$order_id = $args[0];
		} else {
			$order_id = 0;
		}

		if (isset($args[1])) {
			$order_status_id = $args[1];
		} else {
			$order_status_id = 0;
		}	

		if (isset($args[2])) {
			$comment = $args[2];
		} else {
			$comment = '';
		}
		
		if (isset($args[3])) {
			$notify = $args[3];
		} else {
			$notify = '';
		}
						
		$order_info = $this->model_checkout_order->getOrder($order_id);
		
		if ($order_info) {
			// Order confirmation
			if ($order_info['order_status_id'] && $order_status_id) {
				$this->load->model('extension/module/smsalert');
								
				if ($order_info['payment_address_format']) {
					$format = $order_info['payment_address_format'];
				} else {
					$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
				}
				
				$find = array(
					'{firstname}',
					'{lastname}',
					'{company}',
					'{address_1}',
					'{address_2}',
					'{city}',
					'{postcode}',
					'{zone}',
					'{country}'
				);
			
				$replace = array(
					'firstname' => $order_info['payment_firstname'],
					'lastname'  => $order_info['payment_lastname'],
					'company'   => $order_info['payment_company'],
					'address_1' => $order_info['payment_address_1'],
					'address_2' => $order_info['payment_address_2'],
					'city'      => $order_info['payment_city'],
					'postcode'  => $order_info['payment_postcode'],
					'zone'      => $order_info['payment_zone'],
					'country'   => $order_info['payment_country']  
				);
				
				$payment_address = trim(str_replace($find, $replace, $format));						
				
				if ($order_info['shipping_address_format']) {
					$format = $order_info['shipping_address_format'];
				} else {
					$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
				}
				
				$find = array(
					'{firstname}',
					'{lastname}',
					'{company}',
					'{address_1}',
					'{address_2}',
					'{city}',
					'{postcode}',
					'{zone}',
					'{country}'
				);
			
				$replace = array(
					'firstname' => $order_info['shipping_firstname'],
					'lastname'  => $order_info['shipping_lastname'],
					'company'   => $order_info['shipping_company'],
					'address_1' => $order_info['shipping_address_1'],
					'address_2' => $order_info['shipping_address_2'],
					'city'      => $order_info['shipping_city'],
					'postcode'  => $order_info['shipping_postcode'],
					'zone'      => $order_info['shipping_zone'],
					'country'   => $order_info['shipping_country']  
				);
				
				$shipping_address = trim(str_replace($find, $replace, $format));
				
				$order_product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

				$plain_product_table = '';
				
				foreach ($order_product_query->rows as $product) {
					$plain_product_table .= $product['quantity'] . 'x ' . $product['name'] . '(' . $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']) . ')' . "\n";
					
					$order_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$product['order_product_id'] . "'");

					foreach ($order_option_query->rows as $option) {
						$plain_product_table .= '- ' . $option['name'] . ': ' . $option['value'] . "\n";
					}
				}
				
				$replace = array(
					$order_info['firstname'],
					$order_info['lastname'],
					$order_info['email'],
					$order_info['telephone'],
					$order_info['order_id'],
					date($this->language->get('date_format_short'), strtotime($order_info['date_added'])),
					$order_info['payment_method'],
					$order_info['shipping_method'],
					$order_info['ip'],
					$notify ? $comment : '',
					$payment_address,
					$shipping_address,
					$plain_product_table,
					$this->currency->format($order_info['total'], $this->config->get('config_currency')),
				);
				
				$this->model_extension_module_smsalert->parseSMS($order_status_id, $order_info['store_id'], $order_info['telephone'], $replace);
			}
		}
	}
	

}
