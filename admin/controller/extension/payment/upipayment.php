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
//File Path = "admin/controller/extension/payment/upipayment.php"
//Developer Logo Image Path = "admin/view/image/payment/abcdpayupiqr.png"
//NPCI-UPI Logo Image Path = "admin/view/image/payment/upi-logo.png"
//

class ControllerExtensionPaymentUpiPayment extends Controller {
	private $error = array();

	public function index() {

            $this->load->model('setting/event');
            $event_get_upi_qr_code = "get_upi_qr_code";
            if(!($this->model_setting_event->getEventByCode($event_get_upi_qr_code))){
                $this->model_setting_event->addEvent($event_get_upi_qr_code,'catalog/model/checkout/order/addOrderHistory/before','extension/payment/upipaymentqr');
            }
            $event_mail_order_add_upipayment = "mail_order_add_upipayment";
            if(!($this->model_setting_event->getEventByCode($event_mail_order_add_upipayment))){
                $this->model_setting_event->addEvent($event_mail_order_add_upipayment,'catalog/model/checkout/order/addOrderHistory/before','mail/upipayment');
                $query = $this->model_setting_event->getEventByCode($event_mail_order_add_upipayment);
                if($query){
                    $this->model_setting_event->disableEvent($query['event_id']);
                }
            }
            $event_mail_order_add_upipayment_qr = "mail_order_add_upipayment_qr";
            if(!($this->model_setting_event->getEventByCode($event_mail_order_add_upipayment_qr))){
                $this->model_setting_event->addEvent($event_mail_order_add_upipayment_qr,'catalog/model/checkout/order/addOrderHistory/before','mail/upipaymentqr');
            }
            
            $this->load->language('extension/payment/upipayment');

            $this->document->setTitle($this->language->get('heading_title'));

            $this->load->model('setting/setting');

            if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
                    $this->model_setting_setting->editSetting('payment_upipayment', $this->request->post);

                    $this->session->data['success'] = $this->language->get('text_success');

                    $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
            }

            if (isset($this->error['warning'])) {
                    $data['error_warning'] = $this->error['warning'];
            } else {
                    $data['error_warning'] = '';
            }

            if (isset($this->error['upi_id'])) {
                    $data['error_upi_id'] = $this->error['upi_id'];
            } else {
                    $data['error_upi_id'] = '';
            }

            if (isset($this->error['upi_reg_name'])) {
                    $data['error_upi_reg_name'] = $this->error['upi_reg_name'];
            } else {
                    $data['error_upi_reg_name'] = '';
            }

            if (isset($this->error['upi_total'])) {
                    $data['error_upi_total'] = $this->error['upi_total'];
            } else {
                    $data['error_upi_total'] = '';
            }

            if (isset($this->error['remarks_prefix'])) {
                    $data['error_remarks_prefix'] = $this->error['remarks_prefix'];
            } else {
                    $data['error_remarks_prefix'] = '';
            }
            
            $data['breadcrumbs'] = array();

            $data['breadcrumbs'][] = array(
                    'text' => $this->language->get('text_home'),
                    'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
            );

            $data['breadcrumbs'][] = array(
                    'text' => $this->language->get('text_extension'),
                    'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
            );

            $data['breadcrumbs'][] = array(
                    'text' => $this->language->get('heading_title'),
                    'href' => $this->url->link('extension/payment/upipayment', 'user_token=' . $this->session->data['user_token'], true)
            );

            $data['action'] = $this->url->link('extension/payment/upipayment', 'user_token=' . $this->session->data['user_token'], true);

            $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

            if (isset($this->request->post['payment_upipayment_upi_id'])) {
                    $data['payment_upipayment_upi_id'] = $this->request->post['payment_upipayment_upi_id'];
            } else {
                    $data['payment_upipayment_upi_id'] = $this->config->get('payment_upipayment_upi_id');
            }

            if (isset($this->request->post['payment_upipayment_upi_reg_name'])) {
                    $data['payment_upipayment_upi_reg_name'] = $this->request->post['payment_upipayment_upi_reg_name'];
            } else {
                    $data['payment_upipayment_upi_reg_name'] = $this->config->get('payment_upipayment_upi_reg_name');
            }

            if (isset($this->request->post['payment_upipayment_total'])) {
                    $data['payment_upipayment_total'] = $this->request->post['payment_upipayment_total'];
            } else {
                    $data['payment_upipayment_total'] = $this->config->get('payment_upipayment_total');
            }

            if (isset($this->request->post['payment_upipayment_remarks_prefix'])) {
                    $data['payment_upipayment_remarks_prefix'] = $this->request->post['payment_upipayment_remarks_prefix'];
            } else {
                    $data['payment_upipayment_remarks_prefix'] = $this->config->get('payment_upipayment_remarks_prefix');
            }
            
            $data['add_label_text'] = array("TRUE","FALSE");

            if (isset($this->request->post['payment_upipayment_add_label_text'])) {
                    $data['payment_upipayment_add_label_text'] = $this->request->post['payment_upipayment_add_label_text'];
            } else {
                    $data['payment_upipayment_add_label_text'] = $this->config->get('payment_upipayment_add_label_text');
            }

            $data['upi_txn_input'] = array("TRUE","FALSE");

            if (isset($this->request->post['payment_upipayment_transaction_input'])) {
                    $data['payment_upipayment_transaction_input'] = $this->request->post['payment_upipayment_transaction_input'];
            } else {
                    $data['payment_upipayment_transaction_input'] = $this->config->get('payment_upipayment_transaction_input');
            }

            $data['qr_show_time'] = array(5,4,3);

            if (isset($this->request->post['payment_upipayment_qr_show_time'])) {
                    $data['payment_upipayment_qr_show_time'] = $this->request->post['payment_upipayment_qr_show_time'];
            } else {
                    $data['payment_upipayment_qr_show_time'] = $this->config->get('payment_upipayment_qr_show_time');
            }

            $this->load->model('localisation/order_status');

            $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

            if (isset($this->request->post['payment_upipayment_order_status_id'])) {
                    $data['payment_upipayment_order_status_id'] = $this->request->post['payment_upipayment_order_status_id'];
            } else {
                    $data['payment_upipayment_order_status_id'] = $this->config->get('payment_upipayment_order_status_id');
            }

            if (isset($this->request->post['payment_upipayment_cancel_order_status_id'])) {
                    $data['payment_upipayment_cancel_order_status_id'] = $this->request->post['payment_upipayment_cancel_order_status_id'];
            } else {
                    $data['payment_upipayment_cancel_order_status_id'] = $this->config->get('payment_upipayment_cancel_order_status_id');
            }

            if (isset($this->request->post['payment_upipayment_geo_zone_id'])) {
                    $data['payment_upipayment_geo_zone_id'] = $this->request->post['payment_upipayment_geo_zone_id'];
            } else {
                    $data['payment_upipayment_geo_zone_id'] = $this->config->get('payment_upipayment_geo_zone_id');
            }

            $this->load->model('localisation/geo_zone');

            $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

            if (isset($this->request->post['payment_upipayment_status'])) {
                    $data['payment_upipayment_status'] = $this->request->post['payment_upipayment_status'];
            } else {
                    $data['payment_upipayment_status'] = $this->config->get('payment_upipayment_status');
            }

            if (isset($this->request->post['payment_upipayment_sort_order'])) {
                    $data['payment_upipayment_sort_order'] = $this->request->post['payment_upipayment_sort_order'];
            } else {
                    $data['payment_upipayment_sort_order'] = $this->config->get('payment_upipayment_sort_order');
            }

            $readmefile = DIR_SYSTEM."library/upiqr/README.txt";
            if (file_exists($readmefile)){
                $data['text_readme'] = "<pre>".htmlentities(file_get_contents($readmefile),ENT_QUOTES,'UTF-8')."</pre>";
            }
            unset($readmefile);

            $data['header'] = $this->load->controller('common/header');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['footer'] = $this->load->controller('common/footer');
            
            $this->response->setOutput($this->load->view('extension/payment/upipayment', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/upipayment')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['payment_upipayment_upi_id']) {
			$this->error['upi_id'] = $this->language->get('error_upi_id');
		}
                
		if (!$this->request->post['payment_upipayment_upi_reg_name']) {
			$this->error['upi_reg_name'] = $this->language->get('error_upi_reg_name');
		}

		if (!$this->request->post['payment_upipayment_total']) {
			$this->error['upi_total'] = $this->language->get('error_upi_total');
		}
                
		return !$this->error;
	}
}