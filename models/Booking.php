<?php
namespace Models;

require_once __DIR__ . '/../classes/DatabaseGeneral.php';

use App\DatabaseGeneral;
use PDO;

class Booking
{
    private $db;

    public function __construct()
    {
        $this->db = new DatabaseGeneral();
    }

    public function insert($data)
    {
        $sql = "INSERT INTO bookings (
            teach_id, term, pee, date, time_start, time_end, purpose, location, media, phone, status, created_at
        ) VALUES (
            :teach_id, :term, :pee, :date, :time_start, :time_end, :purpose, :location, :media, :phone, :status, NOW()
        )";
        $params = [
            'teach_id' => $data['teach_id'],
            'term' => $data['term'],
            'pee' => $data['pee'],
            'date' => $data['date'],
            'time_start' => $data['time_start'],
            'time_end' => $data['time_end'],
            'purpose' => $data['purpose'],
            'location' => $data['location'],
            'media' => $data['media'] ?? '',
            'phone' => $data['phone'] ?? '',
            'status' => $data['status'] ?? 0
        ];
        $stmt = $this->db->query($sql, $params);
        return $stmt->rowCount() > 0;
    }

    public function getByTeacher($teach_id, $term = null, $pee = null)
    {
        $params = ['teach_id' => $teach_id];
        $where = "teach_id = :teach_id";
        if ($term !== null && $pee !== null) {
            $where .= " AND term = :term AND pee = :pee";
            $params['term'] = $term;
            $params['pee'] = $pee;
        }
        $sql = "SELECT * FROM bookings WHERE $where ORDER BY date DESC, time_start DESC";
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $sql = "SELECT * FROM bookings WHERE id = :id";
        $stmt = $this->db->query($sql, ['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($data, $teacher_id = null)
    {
        $whereClause = "id = :id";
        $params = ['id' => $data['id']];
        
        if ($teacher_id) {
            $whereClause .= " AND teach_id = :teach_id";
            $params['teach_id'] = $teacher_id;
        }

        $sql = "UPDATE bookings SET 
            date = :date, time_start = :time_start, time_end = :time_end,
            purpose = :purpose, location = :location, media = :media, phone = :phone
            WHERE $whereClause";

        $updateParams = array_merge($params, [
            'date' => $data['date'],
            'time_start' => $data['time_start'],
            'time_end' => $data['time_end'],
            'purpose' => $data['purpose'],
            'location' => $data['location'],
            'media' => $data['media'] ?? '',
            'phone' => $data['phone'] ?? ''
        ]);

        $stmt = $this->db->query($sql, $updateParams);
        return $stmt->rowCount() > 0;
    }

    public function delete($id)
    {
        $sql = "DELETE FROM bookings WHERE id = :id";
        $stmt = $this->db->query($sql, ['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function checkAvailability($date, $time_start, $time_end, $location, $exclude_id = null)
    {
        $sql = "SELECT COUNT(*) as count FROM bookings 
                WHERE date = :date AND location = :location AND status != 2
                AND (
                    (time_start < :time_end AND time_end > :time_start)
                )";
        $params = [
            'date' => $date,
            'time_start' => $time_start,
            'time_end' => $time_end,
            'location' => $location
        ];

        if ($exclude_id) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $exclude_id;
        }

        $stmt = $this->db->query($sql, $params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] == 0;
    }

    public function updateStatus($id, $status)
    {
        $sql = "UPDATE bookings SET status = :status WHERE id = :id";
        $stmt = $this->db->query($sql, ['id' => $id, 'status' => $status]);
        return $stmt->rowCount() > 0;
    }
}
