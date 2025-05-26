<?php
namespace Controllers;

require_once __DIR__ . '/../models/Booking.php';

use Models\Booking;

class BookingController
{
    public $model;

    public function __construct()
    {
        $this->model = new Booking();
    }

    public function create($data)
    {
        // ตรวจสอบความพร้อมของห้อง
        if (!$this->model->checkAvailability($data['date'], $data['time_start'], $data['time_end'], $data['location'])) {
            return ['success' => false, 'message' => 'ห้องไม่ว่างในช่วงเวลานี้'];
        }
        
        $success = $this->model->insert($data);
        return ['success' => $success];
    }

    public function update($data, $teacher_id = null)
    {
        // ตรวจสอบความพร้อมของห้อง (ยกเว้นการจองปัจจุบัน)
        if (!$this->model->checkAvailability($data['date'], $data['time_start'], $data['time_end'], $data['location'], $data['id'])) {
            return ['success' => false, 'message' => 'ห้องไม่ว่างในช่วงเวลานี้'];
        }

        $success = $this->model->update($data, $teacher_id);
        return ['success' => $success];
    }

    public function delete($id, $teacher_id = null)
    {
        return $this->model->delete($id, $teacher_id);
    }

    public function getDetail($id)
    {
        return $this->model->getById($id);
    }

    public function getByTeacher($teach_id, $term = null, $pee = null)
    {
        return $this->model->getByTeacher($teach_id, $term, $pee);
    }

    // เพิ่มเมธอดนี้
    public function updateStatus($id, $status)
    {
        $success = $this->model->updateStatus($id, $status);
        return ['success' => $success];
    }
}
