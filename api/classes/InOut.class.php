<?php
/**
 * InOut Class
 */
class InOut {

	/**
	 * table name
	 * @var string
	 */
	private $table = 'v3';

	/**
	 * PDO Connection
	 * @var
	 */
	private $conn;

	/**
	 * __construct
	 */
	public function __construct()
	{
		$this->conn = Connection::getInstance();
	}

	/**
	 * save method
	 * @param  array  $log
	 * @return bool
	 */
	public function save($log = array())
	{
		$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		try
		{
			$sql = "INSERT INTO {$this->table} (in_placename,  in_temperature,  in_humidity,  in_dew_point,  out_placename,  out_temperature,  out_humidity,  out_dew_point,  created_at)
																 VALUES (:in_placename, :in_temperature, :in_humidity, :in_dew_point, :out_placename, :out_temperature, :out_humidity, :out_dew_point, :created_at);";

			$stmt = $this->conn->prepare($sql);
			$stmt->bindParam('in_placename', 	 $log['in_placename']);
			$stmt->bindParam('in_temperature', $log['in_temperature'], PDO::PARAM_INT);
			$stmt->bindParam('in_humidity', 	 $log['in_humidity']);
			$stmt->bindParam('in_dew_point', 	 $log['in_dew_point']);
			$stmt->bindParam('out_placename',  $log['out_placename']);
			$stmt->bindParam('out_temperature',$log['out_temperature'], PDO::PARAM_INT);
			$stmt->bindParam('out_humidity',   $log['out_humidity']);
			$stmt->bindParam('out_dew_point',  $log['out_dew_point']);			
			$stmt->bindParam('created_at', 		 date('y-m-d H:i:s'));

			return $stmt->execute();

		}
		catch (Exception $e)
		{
			echo $e->getMessage();
			return false;
		}
	}

	public static function getDatesList()
	{
		$table = 'v3';
		$conn  = Connection::getInstance();

		$sql   = "SELECT DATE(created_at) as date FROM {$table} GROUP BY date ORDER BY created_at DESC;";
		$query = $conn->query( $sql );

		$result = array();
		while($q = $query->fetch(PDO::FETCH_OBJ))
		{
			$result[] = $q->date;
		}

		return $result;
	}

	public function getData($field, $date = null)
	{
		$date = ($date == null) ? date('Y-m-d') : $date;

		try
		{
			$in_field  = "in_{$field}";
			$out_field = "out_{$field}";


			$sql  = "SELECT {$in_field}, {$out_field}, DATE_FORMAT(created_at, '%H:%i') AS time
							 FROM {$this->table}
							 WHERE DATE(created_at) = :created_at
							 ORDER BY id";

			$stmt = $this->conn->prepare( $sql );
			$stmt->bindParam(':created_at', $date, PDO::PARAM_STR);
			if ($stmt->execute())
			{
				$logs = $stmt->fetchAll(PDO::FETCH_OBJ);
			}

			$sqlPlaces  = "SELECT MIN({$in_field}) AS min_in, MAX({$in_field}) AS max_in, MIN({$out_field}) AS min_out, MAX({$out_field}) AS max_out, in_placename, out_placename 
										 FROM {$this->table} WHERE DATE(created_at) = :created_at LIMIT 1;";
			$stmtPlaces = $this->conn->prepare($sqlPlaces);
			$stmtPlaces->bindParam(':created_at', $date, PDO::PARAM_STR);
			$stmtPlaces->execute();

			$places = $stmtPlaces->fetch(PDO::FETCH_OBJ);

			$data = array();
			$data[] = array('Data/Hora', $places->in_placename, $places->out_placename);

			foreach ($logs as $key => $value)
			{
				$data[] = [
					$value->time,
					(int) $value->$in_field,
					(int) $value->$out_field					
				];
			}

			$result = [
				'data' => $data,
				'in'  => ['min' => $places->min_in,  'max' => $places->max_in],
				'out' => ['min' => $places->min_out, 'max' => $places->max_out]
			];

			return json_encode($result);
		}
		catch (Exception $e)
		{
			die( $e->getMessage() );
		}
	}

	public function getAll($date = null) {
		try {

			$date = ($date == null) ? date('Y-m-d') : $date;
			$sql = "SELECT temp_interna, temp_externa, humidity_interna, humidity_externa, dew_point_interna, dew_point_externa, DATE_FORMAT(datahora, '%H:%i') AS hora
					FROM {$this->table}
					WHERE DATE(datahora) = :datahora
					ORDER BY id;";

			$stmt = $this->conn->prepare( $sql );
			$stmt->bindParam(':datahora', $date);

			if ($stmt->execute()) {
				$logs = $stmt->fetchAll(PDO::FETCH_OBJ);
			}

			$result = array();
			$result[] = array('Data/Hora', 'Temp Interna', 'Temp Externa', 'Humidade Interna', 'Humidade Externa', 'P.O. Interno', 'P.O. Externo');

			foreach ($logs as $key => $value) {

				$result[] = array(
					$value->hora,
					(int) $value->temp_interna,
					(int) $value->temp_externa,
					(int) $value->humidity_interna,
					(int) $value->humidity_externa,
					(int) $value->dew_point_interna,
					(int) $value->dew_point_externa
				);
			}

			return json_encode($result);

		} catch (Exception $e) {

			die( $e->getMessage() );
		}
	}
}