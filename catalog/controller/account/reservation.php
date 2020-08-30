<?php
class ControllerAccountReservation extends Controller {
	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/reservation', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('account/reservation');

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
			'text' => $this->language->get('text_reservation'),
			'href' => $this->url->link('account/reservation', '', true)
		);

		$this->load->model('account/reservation');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['reservations'] = array();

		$filter_data = array(
			'sort'  => 'date_added',
			'order' => 'DESC',
			'start' => ($page - 1) * 10,
			'limit' => 10
		);

		$reservation_total = $this->model_account_reservation->getTotalReservations();

		$results = $this->model_account_reservation->getReservations($filter_data);
		$text_status = array(0=>'Pending', 1=>'Complete', 2=>'Expired');
		foreach ($results as $result) {
			$data['reservations'][] = array(
				'start_date'  => date($this->language->get('date_format_short'), strtotime($result['start_date'])),
				'end_date'  => date($this->language->get('date_format_short'), strtotime($result['end_date'])),
				'order_id'    => $result['order_id'],
				'status'      => $text_status[$result['status']],
				'href'        => $this->url->link('account/order/info', 'order_id=' . $result['order_id'], true),
				'buyHref' => $this->url->link('account/order/buyReserved', 'order_id=' . $result['order_id'] , true)
			);
		}
		
		$pagination = new Pagination();
		$pagination->total = $reservation_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->url = $this->url->link('account/reservation', 'page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($reservation_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($reservation_total - 10)) ? $reservation_total : ((($page - 1) * 10) + 10), $reservation_total, ceil($reservation_total / 10));


		$data['continue'] = $this->url->link('account/account', '', true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/reservation', $data));
	}

	public function expireReservation() {
		$this->load->model('account/reservation');
		$this->load->model('checkout/order');
		$expired_reservations = $this->model_account_reservation->getExpiredReservations();
		foreach ($expired_reservations  as $reservation ) {
			#order status set to cancel
			$this->model_checkout_order->addOrderHistory($reservation['order_id'], 7); 

			# mark reservation as expired
			$this->model_account_reservation->expireReservation($reservation['customer_reservation_id'], array('status'=>2));
		}
	}
}