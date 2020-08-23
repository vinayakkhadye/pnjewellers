<?php
class ModelExtensionTotalSubTotal extends Model {
	public function getTotal($total) {
		$this->load->language('extension/total/sub_total');
		// if (isset($this->session->data['booking_method']['code']) && $this->session->data['booking_method']['code'] == 'reserve' ) {
			
		// 	$sub_total = $this->cart->getReserveSubTotal();
		// } else {
		$sub_total = $this->cart->getSubTotal();
		// }
		// print_r($sub_total);exit;
		if (!empty($this->session->data['vouchers'])) {
			foreach ($this->session->data['vouchers'] as $voucher) {
				$sub_total += $voucher['amount'];
			}
		}

		$total['totals'][] = array(
			'code'       => 'sub_total',
			'title'      => $this->language->get('text_sub_total'),
			'value'      => $sub_total,
			'sort_order' => $this->config->get('sub_total_sort_order')
		);

		$total['total'] += $sub_total;
	}
}
