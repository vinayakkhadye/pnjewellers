<?php
class ModelAccountWallet extends Model {
	public function getTransactions($data = array()) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "wallet` WHERE customer_id = '" . (int)$this->customer->getId() . "'";

		$sort_data = array(
			'amount',
			// 'description',
			'date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY date_added desc";
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

	public function getTotalTransactions() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "wallet` WHERE customer_id = '" . (int)$this->customer->getId() . "'");

		return $query->row['total'];
	}

	public function getWalletBalance() {
		$query = $this->db->query("SELECT SUM(amount) AS total FROM `" . DB_PREFIX . "wallet` WHERE customer_id = '" . (int)$this->customer->getId() . "' GROUP BY customer_id");

		if ($query->num_rows) {
			return $query->row['total'];
		} else {
			return 0;
		}
	}
	public function debit($customer_id, $order_id, $description, $amount) {
		$sql = "INSERT INTO " . DB_PREFIX . "wallet SET customer_id = '" . (int)$customer_id . "', order_id = '" . (int)$order_id . "', description = '" . $description . "', `transaction_type` = 'debit', amount = '" . (float)-$amount . "', date_added = NOW()";

		$this->db->query($sql);

	}
	public function credit($customer_id, $order_id, $description, $amount) {
		$sql = "INSERT INTO " . DB_PREFIX . "wallet SET customer_id = '" . (int)$customer_id . "', order_id = '" . (int)$order_id . "', description = '" . $description . "', `transaction_type` = 'credit', amount = '" . (float)$amount . "', date_added = NOW()";

		$this->db->query($sql);

	}

}