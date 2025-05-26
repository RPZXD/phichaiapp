<?php
namespace Controllers;

require_once __DIR__ . '/../models/CarBooking.php';

use Models\CarBooking;

class CarBookingController
{
    public $model;

    public function __construct()
    {
        $this->model = new CarBooking();
    }

    public function create($data)
    {
        $success = $this->model->insert($data);
        return ['success' => $success];
    }

    public function getByTeacher($teach_id)
    {
        return $this->model->getByTeacher($teach_id);
    }
}
