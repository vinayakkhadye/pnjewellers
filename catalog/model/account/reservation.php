<?php
class ModelAccountReservation extends Model {
	CONST PENDING = 0;
	CONST COMPLETE = 1;
	CONST EXPIRE = 2;
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
		$this->db->query("UPDATE " . DB_PREFIX . "customer_reservation SET status =  " . self::EXPIRE . " WHERE customer_reservation_id = '" . (int) $reservation_id . "'");
	}

	public function completeReservation($order_id, $data){
		$this->db->query("UPDATE " . DB_PREFIX . "customer_reservation SET status =  " . self::COMPLETE . ", order_id = ". (int)$data['order_id'] ." WHERE order_id = '" . (int) $order_id . "'");
	}
	
	public function getTotalReservations() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "customer_reservation` WHERE customer_id = '" . (int)$this->customer->getId() . "'");

		return $query->row['total'];
	}
	public function newReservation($order_id, $customer_id){
		$this->db->query("INSERT INTO " . DB_PREFIX . "customer_reservation SET order_id = '" . (int)$order_id . "', customer_id = '" . (int)$customer_id . "', status=".self::PENDING.", start_date = NOW(), end_date = NOW() + INTERVAL 2 DAY, date_added = NOW(),date_modified = NOW()");
	}

	public function getQuantity($order_id, $customer_id, $product_id){
		$query = $this->db->query("SELECT op.quantity FROM " . DB_PREFIX . "customer_reservation cr	INNER JOIN " . DB_PREFIX . "order_product op on cr.order_id =op.order_id WHERE  cr.order_id=" . $order_id . " and cr.customer_id = " . $customer_id . " and cr.end_date >= now() and cr.status=".self::PENDING." and op.product_id= ". $product_id);
		return $query->rows;

	}

}