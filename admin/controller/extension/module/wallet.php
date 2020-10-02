<?php
        
class ControllerExtensionModuleWallet extends Controller {
   
    private $error = array();

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'extension/module/wallet')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	protected function validateAddCreditForm() {
		if (!$this->user->hasPermission('modify', 'extension/module/wallet')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		if (empty($this->request->post['customer_id'])) {
			$this->error['warning'] = $this->language->get('error_customer_id');
		}
		if (empty($this->request->post['amount'])) {
			$this->error['warning'] = $this->language->get('error_amount');
			
		}

		return !$this->error;
	}	
	

    public function index() {

		$this->load->language('extension/module/wallet');

		/** page title */
		$this->document->setTitle($this->language->get('heading_title'));
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->load->model('setting/setting');
			$this->load->model('setting/extension');
			
			// print_r($this->request->post);exit;
			$post = $this->request->post;
			$this->model_setting_setting->editSetting('payment_wallet',
				[
					'payment_wallet_total'=> $post['payment_wallet_total'],
					'payment_wallet_order_status_id'=> $post['payment_wallet_order_status_id'],
					'payment_wallet_geo_zone_id'=> $post['payment_wallet_geo_zone_id'],
					'payment_wallet_status'=> $post['payment_wallet_status'],
					'payment_wallet_sort_order'=> $post['payment_wallet_sort_order']
				]
			);
			$this->model_setting_setting->editSetting('total_wallet',
				[
					'total_wallet_sort_order'=> $post['total_wallet_sort_order'],
					'total_wallet_status'=> $post['total_wallet_status']
				]
			);
			
			$this->model_setting_setting->editSetting('module_wallet', 
				[
					'module_wallet_status' => $this->request->post['module_wallet_status']
				]
			);

			$this->model_setting_extension->install('payment', 'wallet');
			$this->model_setting_extension->install('total', 'wallet');
			$this->model_setting_extension->install('module', 'wallet');

			$this->session->data['success'] = $this->language->get('text_success');
		}

		/** warnings */
		$data['error_warning'] = '';
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		}

		/** if session has success message then set to data unset sessioin success message */
		$data['success'] = '';
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		}

		$data['action'] = $this->url->link('extension/module/wallet', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->post['payment_wallet_total'])) {
			$data['payment_wallet_total'] = $this->request->post['payment_wallet_total'];
		} else {
			$data['payment_wallet_total'] = $this->config->get('payment_wallet_total');
		}

		if (isset($this->request->post['payment_wallet_order_status_id'])) {
			$data['payment_wallet_order_status_id'] = $this->request->post['payment_wallet_order_status_id'];
		} else {
			$data['payment_wallet_order_status_id'] = $this->config->get('payment_wallet_order_status_id');
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		if (isset($this->request->post['payment_wallet_geo_zone_id'])) {
			$data['payment_wallet_geo_zone_id'] = $this->request->post['payment_wallet_geo_zone_id'];
		} else {
			$data['payment_wallet_geo_zone_id'] = $this->config->get('payment_wallet_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['module_wallet_status'])) {
			$data['module_wallet_status'] = $this->request->post['module_wallet_status'];
		} else {
			$data['module_wallet_status'] = $this->config->get('module_wallet_status');
		}
	
		if (isset($this->request->post['payment_wallet_status'])) {
			$data['payment_wallet_status'] = $this->request->post['payment_wallet_status'];
		} else {
			$data['payment_wallet_status'] = $this->config->get('payment_wallet_status');
		}

		if (isset($this->request->post['payment_wallet_sort_order'])) {
			$data['payment_wallet_sort_order'] = $this->request->post['payment_wallet_sort_order'];
		} else {
			$data['payment_wallet_sort_order'] = $this->config->get('payment_wallet_sort_order');
		}

		if (isset($this->request->post['total_wallet_status'])) {
			$data['total_wallet_status'] = $this->request->post['total_wallet_status'];
		} else {
			$data['total_wallet_status'] = $this->config->get('total_wallet_status');
		}

		if (isset($this->request->post['total_wallet_sort_order'])) {
			$data['total_wallet_sort_order'] = $this->request->post['total_wallet_sort_order'];
		} else {
			$data['total_wallet_sort_order'] = $this->config->get('total_wallet_sort_order');
		}

		/** page breadcrumbs */
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
		 	'href'      => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_module'),
		 	'href'      => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);
	 
		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
		 	'href'      => $this->url->link('extension/module/wallet', 'user_token=' . $this->session->data['user_token'], true)
		);
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		$htmlOutput = $this->load->view('extension/module/wallet', $data);
		$this->response->setOutput($htmlOutput);
	}
	
	public function add_credit() {
		$this->load->language('extension/module/wallet');

		/** page title */
		$this->document->setTitle($this->language->get('heading_title'));
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateAddCreditForm()) {
			$this->load->model('extension/module/wallet');
			$isAdded = $this->model_extension_module_wallet->addCredit($this->request->post);
			$url = '';
			if($isAdded){
				$this->session->data['success'] = $this->language->get('text_add_credit_success');
				$this->response->redirect($this->url->link('extension/module/wallet/transactions', 'user_token=' . $this->session->data['user_token'] . $url, true));
			}else{
				$this->error['warning'] = 'Credit add failed!'; 
			}

		}

		/** warnings */
		$data['error_warning'] = '';
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		}

		/** page breadcrumbs */
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
		 	'href'      => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_module'),
		 	'href'      => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);
	 
		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_add_credit'),
		 	'href'      => $this->url->link('extension/module/wallet/add_credit', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		$htmlOutput = $this->load->view('extension/module/wallet_add_credit', $data);
		$this->response->setOutput($htmlOutput);
	}

	public function transactions() {
		$this->load->language('extension/module/wallet');

		/** page title */
		$this->document->setTitle($this->language->get('heading_title'));

		/** filters */
		if (isset($this->request->get['filter_customer_email'])) {
			$filter_customer_email = $this->request->get['filter_customer_email'];
		} else {
			$filter_customer_email = '';
		}

		if (isset($this->request->get['filter_date_added'])) {
			$filter_date_added = $this->request->get['filter_date_added'];
		} else {
			$filter_date_added = '';
		}

		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = '';
		}

		if (isset($this->request->get['filter_transaction_type'])) {
			$filter_transaction_type = $this->request->get['filter_transaction_type'];
		} else {
			$filter_transaction_type = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'date_added';
		}
		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'DESC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		
		$this->load->model('extension/module/wallet');
		$filter_data = array(
			'filter_customer_email' => $filter_customer_email,
			'filter_status' => $filter_status,
			'filter_date_added' => $filter_date_added,
			'filter_transaction_type' => $filter_transaction_type,
			'sort' => $sort,
			'order' => $order,
			'page' => $page,
			'start' => $this->config->get('config_limit_admin') * ($page - 1),
			'limit' => $this->config->get('config_limit_admin')
		);

		$data['transactions'] = $this->model_extension_module_wallet->getTransactions($filter_data);
		$total_transactions = $this->model_extension_module_wallet->getTotalTransactions($filter_data);

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['success'] = '';
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		}

		$filter_url = '';
		if (isset($this->request->get['filter_customer_email'])) {
			$filter_url .= '&filter_customer_email=' . urlencode(html_entity_decode($this->request->get['filter_customer_email'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_date_added'])) {
			$filter_url .= '&filter_date_added=' . urlencode(html_entity_decode($this->request->get['filter_date_added'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$filter_url .= '&filter_status=' . urlencode(html_entity_decode($this->request->get['filter_status'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_transaction_type'])) {
			$filter_url .= '&filter_transaction_type=' . urlencode(html_entity_decode($this->request->get['filter_transaction_type'], ENT_QUOTES, 'UTF-8'));
		}
		
		$url = $filter_url;

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
	
		$data['sort_customer_email'] = $this->url->link('extension/module/wallet/transactions', 'user_token=' . $this->session->data['user_token'] . '&sort=customer_email' . $url, true);

		$data['sort_status'] = $this->url->link('extension/module/wallet/transactions', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url, true);
		
		$data['sort_transaction_type'] = $this->url->link('extension/module/wallet/transactions', 'user_token=' . $this->session->data['user_token'] . '&sort=transaction_type' . $url, true);

		$data['sort_date_added'] = $this->url->link('extension/module/wallet/transactions', 'user_token=' . $this->session->data['user_token'] . '&sort=date_added' . $url, true);

		$url = $filter_url;

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $total_transactions;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('extension/module/wallet/transactions', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);
		$data['pagination'] = $pagination->render();
		
		$data['filter_customer_email'] = $filter_customer_email;
		$data['filter_transaction_type'] = $filter_transaction_type;
		$data['filter_status'] = is_numeric($filter_status)  ? (int)$filter_status:$filter_status;
		$data['filter_date_added'] = $filter_date_added;

		/** page breadcrumbs */
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
		 	'href'      => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_module'),
		 	'href'      => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);
	 
		$data['breadcrumbs'][] = array(
			'text'      => 'Transactions',
		 	'href'      => $this->url->link('extension/module/wallet/transactions', 'user_token=' . $this->session->data['user_token'], true)
		);


		$data['transaction_types'] = array('credit','debit');
		$data['status_types'] = array(0 => "inactive", 1 => "active");
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		

		$htmlOutput = $this->load->view('extension/module/wallet_transaction', $data);
		$this->response->setOutput($htmlOutput);
	}

	public function customer_transactions() {
		$json = array();

		if (isset($this->request->get['filter_customer_email'])) {
			$filter_customer_email = $this->request->get['filter_customer_email'];
		} else {
			$filter_customer_email = '';
		}

		if (isset($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];
		} else {
			$limit = '';
		}

		$this->load->model('extension/module/wallet');

		$filter_data = array(
			'filter_customer_email' => $filter_customer_email,
			'start' => 0,
			'limit' => $limit
		);

		$json = $this->model_extension_module_wallet->getTransactions($filter_data);


		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	
    public function install() {
		if (!$this->user->hasPermission('modify', 'extension/extension/module')) {
			return;
		}
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "oc_wallet` (
			`wallet_id` int(11) NOT NULL AUTO_INCREMENT,
			`store_id` int(11) NOT NULL,
			`customer_id` int(11) NOT NULL,
			`order_id` int(11) NOT NULL,
			`amount` int(11) NOT NULL,
			`transaction_type` enum('credit','debit') DEFAULT 'credit',
			`status` tinyint(1) DEFAULT 1,   
			`date_added` datetime NOT NULL,
			`date_modified` datetime NOT NULL,
			PRIMARY KEY (`wallet_id`)
		   ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

		$this->load->model('setting/event');
        $this->model_setting_event->addEvent('wallet', 'admin/view/common/column_left/before', 'extension/module/wallet/walletMenu');
	}
	
    public function uninstall() {
		if (!$this->user->hasPermission('modify', 'extension/extension/module')) {
			return;
		}

		$this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "wallet");

        $this->load->model('setting/setting');
		$this->model_setting_extension->uninstall('payment', 'wallet');
		$this->model_setting_extension->uninstall('total', 'wallet');
		$this->model_setting_extension->uninstall('module', 'wallet');

		$this->load->model('setting/event');
		$this->model_setting_event->deleteEventByCode('wallet');
    }


    public function walletMenu(&$route = false, &$data = false, &$output = false) {

		$this->load->language('extension/module/wallet');
        $children[] = array(
            'name'	   => $this->language->get('text_add_credit'),
            'href'     => $this->url->link('extension/module/wallet/add_credit', 'user_token=' . $this->session->data['user_token'], true),
            'children'=> array()
		);
		$children[] = array(
            'name'	   => $this->language->get('text_credit'),
            'href'     => $this->url->link('extension/module/wallet/transactions', 'user_token=' . $this->session->data['user_token'], true),
            'children'=> array()
		);

        $data['menus'][] = array(
            'id'       => 'menu-wallet',
            'icon'	   => 'fa-puzzle-piece',
            'name'	   => $this->language->get('text_wallet'),
            'href'     => "",
            'children' => $children
        );

    }
 

}