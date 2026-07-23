<?php

namespace App\Repositories\Patients;

use App\Models\Patient;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

class PatientRepository
{
    /**
     * Find a patient by ID
     */
    public function findById(int $id): ?Patient
    {
        return Patient::find($id);
    }

    /**
     * Find multiple patients by their IDs
     */
    public function findMany(array $ids): Collection
    {
        return Patient::whereIn('id', $ids)->get();
    }

    /**
     * Get orders count for a patient
     */
    public function getOrdersCount(int $patientId): int
    {
        return Order::where('patient_id', $patientId)
            ->orWhere('user_id', $patientId) // Depending on your auth structure
            ->count();
    }

    /**
     * Get patient history statistics for Chat
     */
    public function getChatHistoryStats(int $patientId): array
    {
        $orders = Order::where('patient_id', $patientId)
            ->orWhere('user_id', $patientId)
            ->latest()
            ->take(5)
            ->get(['id', 'status', 'total', 'created_at']);

        $totalSpent = Order::where(function($q) use ($patientId) {
            $q->where('patient_id', $patientId)
              ->orWhere('user_id', $patientId);
        })->where('status', 'completed')->sum('total');

        return [
            'orders' => $orders->toArray(),
            'statistics' => [
                'total_orders' => $this->getOrdersCount($patientId),
                'total_spent'  => $totalSpent,
                'joined_at'    => Patient::find($patientId)?->created_at?->toIso8601String()
            ]
        ];
    }
}
