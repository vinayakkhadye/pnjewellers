<?php
class ModelExtensionPaymentWallet extends Model {
	public function getMethod($address, $total) {
		// echo "total amount: " . $total;
		$this->load->language('extension/payment/wallet');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_wallet_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		$this->load->model('account/wallet');
		$wallet_balance = $this->model_account_wallet->getWalletBalance();
		if ($wallet_balance > $total
			&& !$this->config->get('payment_wallet_geo_zone_id')) {
			$status = true;
		} elseif (!$this->cart->hasShipping()) {
			$status = false;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}
		// var_dump($status);exit;
		$method_data = array();

		if ($status) {
			$method_data = array(
				'code'       => 'wallet',
				'balance' 	 => $wallet_balance,
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payment_wallet_sort_order')
			);
		}

		return $method_data;
	}
}
