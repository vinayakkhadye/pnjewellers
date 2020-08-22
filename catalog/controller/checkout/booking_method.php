<?php
class ControllerCheckoutBookingMethod extends Controller {
	public function index() {
		$this->load->language('checkout/checkout');
		// Booking Methods
		$data = array();
		$data['booking_methods']['buy'] = ['code'=>'buy','title'=>'Buy'];
		$data['booking_methods']['reserve']= ['code'=>'reserve','title'=>'Reserve'];
		if (isset($this->session->data['booking_method']['code'])) {
			$data['booking_method_code'] = $this->session->data['booking_method']['code'];
		}

		$this->response->setOutput($this->load->view('checkout/booking_method', $data));
	}

	public function save() {
		$this->session->data['booking_method']['code'] = $this->request->post['booking_method'];
		$json = array("booking_method" => $this->request->post['booking_method']);
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}