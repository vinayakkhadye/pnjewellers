<?php
class ModelExtensionModuleSmsAlert extends Model {
	/**added on 06-05-2019 */
	const PATTERN_PHONE	= '/^(\+)?(country_code)?0?\d+$/';
	
	public function isCustomerExists($email,$pwd)
	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "' AND (password = SHA1(CONCAT(salt, SHA1(CONCAT(salt, SHA1('" . $this->db->escape($pwd) . "'))))) OR password = '" . $this->db->escape(md5($pwd)) . "') AND status = '1'");
		

		return $query->row;
	}

	public static function getCountryPattern($countryCode=NULL)
    {
		$c = self::$countries;
		$pattern ='';
			foreach($c as $list)
			{
				if($list['countryCode']==$countryCode){
					
					if(array_key_exists('pattern',$list)){
						$pattern = $list['pattern'];
						break;
					}
				}
			}			
		
		return $pattern;
    }
	
	
	public function getPhonePattern()
	{
		$country_code=$this->config->get('smsalert_country');
		$pattern = ($this->config->get('smsalert_mobile_pattern')!='') ? $this->config->get('smsalert_mobile_pattern'):self::PATTERN_PHONE;
		
		$country_code = str_replace('+', '', $country_code);
		$pattern_phone = str_replace("country_code",$country_code,$pattern);
		return $pattern_phone;
	}
		
	/** closed added on 06-05-2019 */
	public function getTemplates($type, $store_id) { 
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "sms_template st LEFT JOIN " . DB_PREFIX . "sms_template_message stm ON st.sms_template_id = stm.sms_template_id WHERE type = '" . $this->db->escape($type) . "' AND store_id = '" . (int)$store_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
		
		return $query->rows;
	}
	
	private function getSearch($type) {
		if ($type == 'register') {
			$search = array(
				'{firstname}',
				'{lastname}',
				'{email}',
				'{telephone}',
				'{password}'
			);
		} elseif ($type == 'affiliate') {
			$search = array(
				'{firstname}',
				'{lastname}',
				'{email}',
				'{telephone}'
			);
		} elseif ($type == 'affiliate_transaction') {
			$search = array(
				'{firstname}',
				'{lastname}',
				'{email}',
				'{commission}',
				'{total_commission}'
			);
		} elseif ($type == 'affiliate_approve') {
			$search = array(
				'{firstname}',
				'{lastname}',
				'{email}'
			);
		} elseif ($type == 'forgotten') {
			$search = array(
				'{firstname}',
				'{lastname}',
				'{email}',
				'{password}'
			);
		} elseif ($type == 'order') {
			$search = array(
				'{firstname}',
				'{lastname}',
				'{email}',
				'{telephone}',
				'{order_id}',
				'{date_added}',
				'{payment_method}',
				'{shipping_method}',
				'{ip}',
				'{order_comment}',
				'{payment_address}',
				'{shipping_address}',
				'{products}',
				'{order_amount}',
			);
		} elseif ($type == 'reward') {
			$search = array(
				'{firstname}',
				'{lastname}',
				'{email}',
				'{points}',
				'{total_points}'
			);
		} elseif ($type == 'account_approve') {
			$search = array(
				'{firstname}',
				'{lastname}',
				'{email}'
			);
		} elseif ($type == 'account_transaction') {
			$search = array(
				'{firstname}',
				'{lastname}',
				'{email}',
				'{credits}',
				'{total_credits}'
			);
		} else {
			$search = array(
				'{firstname}',
				'{lastname}',
				'{email}',
				'{telephone}',
				'{order_id}',
				'{date_added}',
				'{payment_method}',
				'{shipping_method}',
				'{ip}',
				'{order_comment}',
				'{payment_address}',
				'{shipping_address}',
				'{products}',
				'{order_amount}',
			);
		}
	
		return $search;
	}
	
	public function parseSMS($type, $store_id, $number, $replace) {
	    $number = preg_replace('/[^0-9]/', '', $number);
		$templates = $this->getTemplates($type, $store_id);
	
		$template_data = false;
		foreach ($templates as $template) {
				$template_data = $template;
			
		}
	    
		if ($template_data && !empty($template_data) && $template_data['status']=='1') {
			$search = $this->getSearch($type);
			
			$message = strip_tags(str_replace($search, $replace, html_entity_decode($template_data['message'])));
			
			$this->sendSMS($number, $message);
			
			$numbers = explode(',', $template_data['bcc']);
			foreach ($numbers as $number) {
				if ($number) {
					$this->sendSMS($number, $message);
				}
			}
		} else {
			return false;
		}
	}
	
	private function sendCurl($url, $post_data) {
	
		$curl = curl_init();
		
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLINFO_HEADER_OUT, true);
		curl_setopt($curl, CURLOPT_USERAGENT, 'OpenCart Two Factor Authentication');
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_FORBID_REUSE, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_data));

		$response = curl_exec($curl);	
		if (curl_errno($curl)) {
			$curl_error = 'SmsAlert cURL Error ' . curl_errno($curl) . ': ' . curl_error($curl);
		} else {
			$curl_error = '';
		}
		
		if ($curl_error) {
			$this->log->write($curl_error);
		}
		
		if ($this->config->get('smsalert_debug')) {
			$this->log->write($response);
		}

		curl_close($curl);
		
		return json_decode($response, true);
	}
	
	private function sendSMS($receiver, $message) {
		if (!$this->config->get('smsalert_auth_key') || !$this->config->get('smsalert_auth_secret')) {
			return;
		}
		
		/*added on 06-05-2019 */
		$country_code=$this->config->get('smsalert_country');
		$no = str_replace('+','',$receiver); //if nos with prefix "+"
		$no = ltrim($no, '0');
		$no = (substr($no,0,strlen($country_code))!=$country_code) ? $country_code.$no : $no;
		$match = preg_match($this->getPhonePattern(),$no);
		/*closed added on 06-05-2019 */
		if($match)
		{	
			$post_data = array(
				'user'			=> $this->config->get('smsalert_auth_key'),
				'pwd'			=> $this->config->get('smsalert_auth_secret'),
				'sender'		=> $this->config->get('smsalert_default_senderid'),
				'mobileno'		=> $no,
				'text'			=> $message
			);
		
			#https://www.smsalert.co.in/api/push.json url to changed

			$this->sendCurl(base64_decode('aHR0cHM6Ly93d3cuc21zYWxlcnQuY28uaW4vYXBpL3B1c2guanNvbg=='), $post_data);
		}
	}
	
	public function sendOtp($receiver,$template=NULL) {
		
		if (!$this->config->get('smsalert_auth_key') || !$this->config->get('smsalert_auth_secret')) {
			return;
		}
		
		
		
		/*added on 06-05-2019 */
		$country_code=$this->config->get('smsalert_country');
		$no = str_replace('+','',$receiver); //if nos with prefix "+"
		$no = ltrim($no, '0');
		$no = (substr($no,0,strlen($country_code))!=$country_code) ? $country_code.$no : $no;
		$match = preg_match($this->getPhonePattern(),$no);
		/*closed added on 06-05-2019 */
		if($match)
		{
			$post_data = array(
				'user'			=> $this->config->get('smsalert_auth_key'),
				'pwd'			=> $this->config->get('smsalert_auth_secret'),
				'sender'		=> $this->config->get('smsalert_default_senderid'),
				'mobileno'		=> $no,
				'template'			=> $template
			);
			return $this->sendCurl(base64_decode('aHR0cHM6Ly93d3cuc21zYWxlcnQuY28uaW4vYXBpL212ZXJpZnkuanNvbg=='), $post_data);
		}
	}
	
	public function verifyOtp($receiver,$code) {
		
		if (!$this->config->get('smsalert_auth_key') || !$this->config->get('smsalert_auth_secret')) {
			return;
		}
		
		
		/*added on 06-05-2019 */
		$country_code=$this->config->get('smsalert_country');
		$no = str_replace('+','',$receiver); //if nos with prefix "+"
		$no = ltrim($no, '0');
		$no = (substr($no,0,strlen($country_code))!=$country_code) ? $country_code.$no : $no;
		$match = preg_match($this->getPhonePattern(),$no);
		/*closed added on 06-05-2019 */
		if($match)
		{
			$post_data = array(
				'user'			=> $this->config->get('smsalert_auth_key'),
				'pwd'			=> $this->config->get('smsalert_auth_secret'),
				'sender'		=> $this->config->get('smsalert_default_senderid'),
				'mobileno'		=> $no,
				'code'=>$code
				//'text'			=> $message
			);
			return $this->sendCurl(base64_decode('aHR0cHM6Ly93d3cuc21zYWxlcnQuY28uaW4vYXBpL212ZXJpZnkuanNvbg=='), $post_data);
		}
	}
	
	
	
}