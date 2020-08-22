<?php
class ModelAccountReservation extends Model {
	public function getReservations($data = array()) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "customer_reservation` WHERE customer_id = '" . (int)$this->customer->getId() . "'";

		$sort_data = array(
			'status',
			// 'description',
			'date_added'
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
	public function getExpiredReservations() {
		$sql = "SELECT * FROM `" . DB_PREFIX . "customer_reservation` WHERE `end_date` < '" . date("Y-m-d H:i:s") . "'";
		$query = $this->db->query($sql);
		return $query->rows;
	}

	public function expireReservation($reservation_id, $data){
		$this->db->query("UPDATE " . DB_PREFIX . "customer_reservation SET status =  " . (int)$data['status'] . " WHERE customer_reservation_id = '" . (int) $reservation_id . "'");
	}

	public function completeReservation($order_id, $data){
		$this->db->query("UPDATE " . DB_PREFIX . "customer_reservation SET status =  " . (int)$data['status'] . ", order_id = ". (int)$data['order_id'] ." WHERE order_id = '" . (int) $order_id . "'");
	}
	
	public function getTotalReservations() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "customer_reservation` WHERE customer_id = '" . (int)$this->customer->getId() . "'");

		return $query->row['total'];
	}

}