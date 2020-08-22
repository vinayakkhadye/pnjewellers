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
//File Path = "catalog/model/extension/payment/upipayment.php"
//

class ModelExtensionPaymentUpiPayment extends Model {
	public function getMethod($address, $total) {
		$this->load->language('extension/payment/upipayment');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_upipayment_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if ($this->config->get('payment_upipayment_total') > 0 && $this->config->get('payment_upipayment_total') > $total) {
			$status = false;
		} elseif (!$this->config->get('payment_upipayment_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = array();

		if ($status) {
			$method_data = array(
				'code'       => 'upipayment',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payment_upipayment_sort_order')
			);
		}

		return $method_data;
	}
}