<?php
class ControllerAccountWallet extends Controller {
	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/wallet', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('account/wallet');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_wallet'),
			'href' => $this->url->link('account/wallet', '', true)
		);

		$this->load->model('account/wallet');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['transactions'] = array();

		$filter_data = array(
			'sort'  => 'date_added',
			'order' => 'DESC',
			'start' => ($page - 1) * 10,
			'limit' => 10
		);

		$wallet_total = $this->model_account_wallet->getTotalTransactions();

		$results = $this->model_account_wallet->getTransactions($filter_data);

		foreach ($results as $result) {
			$data['transactions'][] = array(
				'date_added'  => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'order_id'    => $result['order_id'],
				'transaction_type' => ucfirst($result['transaction_type']),
				'amount'      => $result['amount'],
				'href'        => $this->url->link('account/order/info', 'order_id=' . $result['order_id'], true)
			);
		}

		$pagination = new Pagination();
		$pagination->total = $wallet_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->url = $this->url->link('account/wallet', 'page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($wallet_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($wallet_total - 10)) ? $wallet_total : ((($page - 1) * 10) + 10), $wallet_total, ceil($wallet_total / 10));

		$data['total'] = $this->model_account_wallet->getWalletBalance();

		$data['continue'] = $this->url->link('account/account', '', true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/wallet', $data));
	}
	public function useWallet() {
		$json = array();
		$this->load->language('account/wallet');
		$this->load->model('account/wallet');
		$points = $this->model_account_wallet->getWalletBalance();
		$this->request->post['wallet'] = $points; 
		$points_total = 0;
		// print_r($this->request->post);exit;
		foreach ($this->cart->getProducts() as $product) {
			if ($product['points']) {
				$points_total += $product['points'];
			}
		}
		if (empty($this->request->post['wallet'])) {
			$json['error'] = $this->language->get('error_wallet');
		}

		if ($this->request->post['wallet'] > $points) {
			$json['error'] = sprintf($this->language->get('error_points'), $this->request->post['wallet']);
		}

		// if ($this->request->post['wallet'] > $points_total) {
		// 	$json['error'] = sprintf($this->language->get('error_maximum'), $points_total);
		// }
		if (!$json) {
			$this->session->data['wallet'] = abs($this->request->post['wallet']);
			$json = array('payment_method_code' => 'wallet');
			// $json = {"payment_methods":{"":{"code":"wallet","title":"Wallet Pay","terms":"","sort_order":""}}}
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
		
	}	
}