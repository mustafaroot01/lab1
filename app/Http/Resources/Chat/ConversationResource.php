<?php

namespace App\Http\Resources\Chat;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'     => $this->id,
            'status' => $this->status->value,
            'patient' => [
                'id'       => $this->patient?->id,
                'name'     => $this->patient?->name,
                'phone'    => $this->patient?->phone,
                'district' => $this->patient && $this->patient->relationLoaded('district') ? $this->patient->district?->name_ar : null,
                'area'     => $this->patient && $this->patient->relationLoaded('area') ? $this->patient->area?->name_ar : null,
                'orders_count' => $this->patient?->orders_count ?? ($this->patient && $this->patient->relationLoaded('orders') ? $this->patient->orders->count() : 0),
            ],
            'closed_at' => $this->closed_at?->toIso8601String(),
            'closed_by' => $this->relationLoaded('closedBy') ? $this->closedBy?->name : null,
            'assigned_to' => $this->relationLoaded('assignedTo') && $this->assignedTo ? [
                'id'   => $this->assignedTo->id,
                'name' => $this->assignedTo->name,
            ] : null,
            'assigned_at'          => $this->assigned_at?->toIso8601String(),
            'is_assigned'          => $this->isAssigned(),
            'last_message_preview' => $this->last_message_preview,
            'last_message_at'      => $this->last_message_at?->toIso8601String(),
            'unread_count'         => $this->unread_count ?? 0,
            'admin_last_read_message_id' => $this->admin_last_read_message_id,
            'patient_last_read_message_id' => $this->patient_last_read_message_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

}
