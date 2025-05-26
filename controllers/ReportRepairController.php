<?php
namespace Controllers;

require_once __DIR__ . '/../models/ReportRepair.php';

use Models\ReportRepair;

class ReportRepairController
{
    public $model;

    public function __construct()
    {
        $this->model = new ReportRepair();
    }

    public function create($data)
    {
        return $this->model->insert($data);
    }

    public function update($data, $teacher_id = null)
    {
        return $this->model->update($data, $teacher_id);
    }

    public function delete($id, $teacher_id = null)
    {
        return $this->model->delete($id, $teacher_id);
    }

    public function getDetail($id)
    {
        return $this->model->getById($id);
    }

    // เพิ่มสำหรับเจ้าหน้าที่
    public function getAll()
    {
        return $this->model->getAll();
    }

    // เพิ่มสำหรับเจ้าหน้าที่
    public function updateStatusById($id, $status)
    {
        return $this->model->updateStatusById($id, $status);
    }
}
