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

		/** if session has success message then set to data unset sessioin success message */
		$data['success'] = '';
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		}

		/** set sorting order and default page number */
		$sort = 'date_added';
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		}
		$order = 'DESC';
		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		}
		$page = 1;
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		}

		$data = array(
			'sort'		=> $sort,
			'order'		=> $order,
			'page'		=> $page,
			'start'		=> $this->config->get('config_limit_admin') * ($page - 1),
			'limit'		=> $this->config->get('config_limit_admin')
		);
		$this->load->model('extension/module/wallet');
		$data['transactions'] = $this->model_extension_module_wallet->getTransactions($data);


		$url = '';
		
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
		
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
		
		$pagination = new Pagination();
		$pagination->total = $this->model_extension_module_wallet->getTotalTransactions();
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('extension/module/wallet/transactions', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);
		$data['pagination'] = $pagination->render();
		

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
		 	'href'      => $this->url->link('extension/module/wallet_transactions', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		$htmlOutput = $this->load->view('extension/module/wallet_transaction', $data);
		$this->response->setOutput($htmlOutput);
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