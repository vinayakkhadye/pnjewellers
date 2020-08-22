<?php
class ModelExtensionModuleWallet extends Model {
    
    public function addCredit($data)
	{
        $this->db->query("INSERT INTO " . DB_PREFIX . "wallet SET store_id = 0, customer_id = '" . $this->db->escape($data['customer_id']) . "', amount = '" . $this->db->escape($data['amount']) . "', transaction_type = 'credit', date_added = '". date("Y-m-d H:i:s") ."', date_modified = '". date("Y-m-d H:i:s") ."'");
        return $this->db->getLastId();
    }

    public function editCredit($wallet_id, $data)
	{
		if(array_key_exists('status',$data) && $data['status']=='on')
		{
			$data['status']='1';
		}
		else{
			$data['status']='0';
		}
		$this->db->query("UPDATE " . DB_PREFIX . "wallet SET store_id = '" . (int)$data['store_id'] . "', customer_id = '" . $this->db->escape($data['customer_id']) . "', amount = '" . $this->db->escape($data['amount']) . "', status = '" . $this->db->escape($data['status']) . "'WHERE wallet_id = '" . (int)$wallet_id . "'");
    }

	public function getTotalTransactions() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "wallet");
	  
	  return $query->row['total'];
  	}

    public function getTransactions($data)
    {
        $sql = "SELECT customer.email, wallet.* FROM `" . DB_PREFIX . "wallet` as wallet LEFT JOIN `" . DB_PREFIX . "customer` as customer on wallet.customer_id = customer.customer_id  ";
        
        $sort_data = array(
            'date_added',
			'transaction_type'
        );	
        
        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];	
		} else {
			$sql .= " ORDER BY date_added";	
        }
        
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
        }
        
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}					

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	
		
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];

        }	
        $query = $this->db->query($sql);

		return $query->rows;
    }


}