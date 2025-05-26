<?php
namespace Models;

use App\DatabaseGeneral;
use PDO;

class CarBooking
{
    private $db;

    public function __construct()
    {
        $this->db = new DatabaseGeneral();
    }

    public function insert($data)
    {
        $sql = "INSERT INTO car_bookings (
            teach_id, date, time, car_type, destination, purpose, passengers, phone, created_at
        ) VALUES (
            :teach_id, :date, :time, :car_type, :destination, :purpose, :passengers, :phone, NOW()
        )";
        $params = [
            'teach_id' => $data['teach_id'],
            'date' => $data['date'],
            'time' => $data['time'],
            'car_type' => $data['car_type'],
            'destination' => $data['destination'],
            'purpose' => $data['purpose'],
            'passengers' => $data['passengers'] ?? '',
            'phone' => $data['phone'] ?? ''
        ];
        $stmt = $this->db->query($sql, $params);
        return $stmt->rowCount() > 0;
    }

    public function getByTeacher($teach_id)
    {
        $sql = "SELECT * FROM car_bookings WHERE teach_id = :teach_id ORDER BY date DESC, time DESC";
        $stmt = $this->db->query($sql, ['teach_id' => $teach_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
