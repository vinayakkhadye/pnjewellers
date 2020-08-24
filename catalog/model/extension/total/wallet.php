<?php
class ModelExtensionTotalWallet extends Model {
	public function getTotal($total) {
		// print_r($this->session->data);exit;
		$this->session->data['wallet'] = 0;
		if(isset($this->session->data['payment_methods']['wallet']['balance'])){
			$this->session->data['wallet'] = $this->session->data['payment_methods']['wallet']['balance'];
		}
		
		if (isset($this->session->data['wallet'])) {
			$this->load->language('extension/total/wallet', 'wallet');
			$this->load->model('account/wallet');

			$discount_total = 0;
			$price_total = 0;

			foreach ($this->cart->getProducts() as $product) {
				if ($product['total']) {
					$price_total += $product['total'];
				}
			}
			if( isset($this->session->data['shipping_method']) 
			&& isset($this->session->data['booking_method']['code'])
			&& $this->session->data['booking_method']['code'] == 'buy' 
			) {
				$price_total = $price_total + $this->session->data['shipping_method']['cost'];
			}
			
			if(  $price_total < $this->session->data['wallet'] ){
				$discount_total = $price_total;	
				$total['totals'][] = array(
					'code'       => 'wallet',
					'title'      => sprintf($this->language->get('wallet')->get('text_wallet'), $this->session->data['wallet']),
					'value'      => -$discount_total,
					'sort_order' => $this->config->get('total_wallet_sort_order')
				);
	
				$total['total'] -= $discount_total;
			}
		}
	}

	public function confirm($order_info, $order_total) {
		$this->load->language('extension/total/wallet');

		$points = 0;
		// print_r($order_total);
		// $start = strpos($order_total['title'], '(') + 1;
		// $end = strrpos($order_total['title'], ')');

		// if ($start && $end) {
		// 	$points = substr($order_total['title'], $start, $end - $start);
		// }
		$points = $order_total['value'];
		
		$this->load->model('account/wallet');

		if ($this->model_account_wallet->getWalletBalance() >= $points) 
		{
			$sql = "INSERT INTO " . DB_PREFIX . "wallet SET customer_id = '" . (int)$order_info['customer_id'] . "', order_id = '" . (int)$order_info['order_id'] . "', description = '" . $this->db->escape(sprintf($this->language->get('text_order_id'), (int)$order_info['order_id'])) . "', `transaction_type` = 'debit', amount = '" . (float)$points . "', date_added = NOW()";
			$this->db->query($sql);
		} else {
			return $this->config->get('config_fraud_status_id');
		}
	}

	public function unconfirm($order_id) {
		$sql =  "select customer_id, order_id, amount, status from " . DB_PREFIX . "wallet where order_id =  '" . (int)$order_id . "' AND amount < 0";
		$query = $this->db->query($sql);
		
		if($query->num_rows > 0) {
			foreach($query->rows as $wallet_row) {
				$sql = "INSERT INTO " . DB_PREFIX . "wallet SET customer_id = '". $wallet_row['customer_id'] ."', order_id = '". (int)$order_id ."', amount =  '". abs($wallet_row['amount']) ."', transaction_type = 'credit', status = 1, date_added = NOW()";
				$this->db->query($sql);
			}
		}
	}
}
