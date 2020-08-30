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
			if(isset($this->session->data['reserved_order_id'])){
				$reserved_order_id = $this->session->data['reserved_order_id'];
			}
			$order_id = $this->session->data['order_id'];
			$order_info = $this->model_checkout_order->getOrder($order_id);
			
			// debit wallet balance using order total
			$desc = $this->db->escape(sprintf($this->language->get('text_order_id'), (int)$order_info['order_id']));
			$total = $this->cart->getTotal();
			$this->model_account_wallet->debit($order_info['customer_id'], $order_info['order_id'], $desc, $total);

			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_wallet_order_status_id'));

			if(isset($reserved_order_id)) {
				$reserve_order_info = $this->model_checkout_order->getOrder($reserved_order_id);

				$desc = $this->db->escape(sprintf($this->language->get('text_order_id'), (int)$reserve_order_info['order_id']));
				$total = $reserve_order_info['total'];

				$this->model_account_wallet->credit($reserve_order_info['customer_id'], $reserve_order_info['order_id'], $desc, $total);
			}
			$json['redirect'] = $this->url->link('checkout/success');
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));		
	}
}
