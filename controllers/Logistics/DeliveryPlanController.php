<?php
namespace App\Controllers\Logistics;

class DeliveryPlanController {

    public function index() {
        // TODO: List active delivery plans
    }

    public function create() {
        // TODO: Return view to select vehicle, driver, and assign sales invoices
    }

    public function store() {
        // TODO: Generate delivery plan and associated delivery_plan_items
    }

    public function updateStatus($id) {
        // TODO: Update delivery status (e.g., in_progress, completed)
    }

    public function uploadPOD($itemId) {
        // TODO: Process proof of delivery (signature, photo, OTP verification)
    }
}
