<?php
class ControllerExtensionPaymentWallet extends Controller {
	public function index() {
		return $this->load->view('extension/payment/wallet');
	}

	public function confirm() {
		$json = array();
		
		if ($this->session->data['payment_method']['code'] == 'wallet') {
			$this->load->model('checkout/order');
			$this->load->model('account/wallet');

			// get order information
			// if(isset($this->session->data['reserved_order_id'])){
			// 	$reserved_order_id = $this->session->data['reserved_order_id'];
			// }
			$order_id = $this->session->data['order_id'];
			$order_info = $this->model_checkout_order->getOrder($order_id);

			// debit wallet balance using order total
			$desc = $this->db->escape(sprintf($this->language->get('text_order_id'), (int)$order_info['order_id']));
			$total = $this->cart->getTotal();
			$this->model_account_wallet->debit($order_info['customer_id'], $order_info['order_id'], $desc, $total);

			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_wallet_order_status_id'));

			// $products = $this->cart->getProducts();
			// print_r($products); exit;


			// if(isset($reserved_order_id)) {
			// 	$reserve_order_info = $this->model_checkout_order->getOrder($reserved_order_id);

			// 	$desc = $this->db->escape(sprintf($this->language->get('text_order_id'), (int)$reserve_order_info['order_id']));
			// 	$total = $reserve_order_info['total'];

			// 	$this->model_account_wallet->credit($reserve_order_info['customer_id'], $reserve_order_info['order_id'], $desc, $total);
			// }

			$order_products = $this->model_checkout_order->getOrderProducts($order_id);
			foreach ($order_products as $order_product) {
				if($order_product['reservation_order_id'] > 0 ) {
					$reserve_order_product = $this->model_checkout_order->getOrderProduct($order_product['reservation_order_id'], $order_product['product_id']);
					if ($reserve_order_product['order_id'] == $order_product['reservation_order_id'] ) {
						$desc = $this->db->escape(sprintf($this->language->get('text_order_id'), (int)$reserve_order_product['order_id']));
						$total = $reserve_order_product['price'] * $order_product['quantity'];
		
						$this->model_account_wallet->credit($order_info['customer_id'], $reserve_order_product['order_id'], $desc, $total);						

						$this->model_checkout_order->addOrderHistory($reserve_order_product['order_id'],11);

						if($reserve_order_product['quantity'] > $order_product['quantity'] ){
							$reserve_order_product_quantity = $reserve_order_product['quantity'] - $order_product['quantity'];
							$reserve_order_product_status = 3; # partial buy of reserved produc 
						} else {
							$reserve_order_product_quantity = 0; # all reservations exausted 
							$reserve_order_product_status = 1; # complete buy
						}
						$this->model_checkout_order->updateOrderProduct($order_product['reservation_order_id'], $order_product['product_id'], array('quantity'=>$reserve_order_product_quantity, 'reservation_status'=> $reserve_order_product_status));	
					}
				}
			}

			$json['redirect'] = $this->url->link('checkout/success');
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));		
	}
}
