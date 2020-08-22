<?php
class ControllerExtensionTotalWallet extends Controller {
	public function index() {
		$this->load->model('account/wallet');
		$this->session->data['wallet'] = $this->model_account_wallet->getWalletBalance();

		$points_total = 0;

		foreach ($this->cart->getProducts() as $product) {
			if ($product['price']) {
				$points_total += $product['price'];
			}
		}

		if ($this->session->data['wallet'] && $points_total && $this->config->get('total_wallet_status')) {
			$this->load->language('extension/total/wallet');

			$data['heading_title'] = sprintf($this->language->get('heading_title'), $this->session->data['wallet']);

			$data['entry_wallet'] = sprintf($this->language->get('entry_wallet'), $points_total);

			if (isset($this->session->data['wallet'])) {
				$data['wallet'] = $this->session->data['wallet'];
			} else {
				$data['wallet'] = 0;
			}

			return $this->load->view('extension/total/wallet', $data);
		}
	}

	public function wallet() {
		
		$this->load->language('extension/total/wallet');

		$json = array();

		$this->load->model('account/wallet');
		$points = $this->model_account_wallet->getWalletBalance();
		$points_total = 0;

		foreach ($this->cart->getProducts() as $product) {
			if ($product['price']) {
				$points_total += $product['price'];
			}
		}

		if (empty($this->request->post['wallet'])) {
			$json['error'] = $this->language->get('error_wallet');
		}

		if ($this->request->post['wallet'] > $points) {
			$json['error'] = sprintf($this->language->get('error_points'), $this->request->post['wallet']);
		}

		if ($this->request->post['wallet'] > $points_total) {
			$json['error'] = sprintf($this->language->get('error_maximum'), $points_total);
		}

		if (!$json) {
			$this->session->data['wallet'] = abs($this->request->post['wallet']);

			$this->session->data['success'] = $this->language->get('text_success');

			if (isset($this->request->post['redirect'])) {
				$json['redirect'] = $this->url->link($this->request->post['redirect']);
			} else {
				$json['redirect'] = $this->url->link('checkout/cart');	
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
