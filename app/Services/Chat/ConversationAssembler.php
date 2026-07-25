<?php

namespace App\Services\Chat;

use App\DTOs\Chat\ConversationView;
use App\Repositories\Patients\PatientRepository;

class ConversationAssembler
{
    private PatientRepository $patientRepository;

    public function __construct(PatientRepository $patientRepository)
    {
        $this->patientRepository = $patientRepository;
    }

    /**
     * Assemble a single conversation with its participant (Patient) data
     */
    public function assembleConversation(array $conversationData)
    {
        $participantData = null;
        if (!empty($conversationData['patient_id'])) {
            $patient = $this->patientRepository->findById($conversationData['patient_id']);
            if ($patient) {
                $participantData = [
                    'id' => $patient->id,
                    'name' => $patient->name,
                    'phone' => $patient->phone,
                    'orders_count' => $this->patientRepository->getOrdersCount($patient->id)
                ];
            }
        }

        return [
            'id' => $conversationData['id'],
            'status' => strtolower($conversationData['status'] ?? 'open'),
            'patient' => $participantData ?? [
                'id' => 0,
                'name' => 'مريض غير معروف',
                'phone' => '-',
                'orders_count' => 0
            ],
            'is_assigned' => $conversationData['is_assigned'] ?? false,
            'assigned_to' => null,
            'created_at' => $conversationData['created_at'] ?? null,
            'last_message_preview' => $conversationData['last_message'] ?? null,
            'last_message_at' => $conversationData['last_message_at'] ?? null,
            'unread_count' => 0,
        ];
    }

    /**
     * Assemble multiple conversations
     */
    public function assembleMany(array $conversations): array
    {
        $patientIds = collect($conversations)->pluck('patient_id')->filter()->unique()->toArray();
        $patients = $this->patientRepository->findMany($patientIds)->keyBy('id');

        return array_map(function ($conv) use ($patients) {
            $patientData = null;
            if (!empty($conv['patient_id']) && $patients->has($conv['patient_id'])) {
                $patient = $patients->get($conv['patient_id']);
                $patientData = [
                    'id' => $patient->id,
                    'name' => $patient->name,
                    'phone' => $patient->phone,
                    'orders_count' => $this->patientRepository->getOrdersCount($patient->id)
                ];
            }

            return [
                'id' => $conv['id'],
                'status' => strtolower($conv['status'] ?? 'open'),
                'patient' => $patientData ?? [
                    'id' => 0,
                    'name' => 'مريض غير معروف',
                    'phone' => '-',
                    'orders_count' => 0
                ],
                'is_assigned' => $conv['is_assigned'] ?? false,
                'assigned_to' => null, // Can map later
                'created_at' => $conv['created_at'] ?? null,
                'last_message_preview' => $conv['last_message'] ?? null,
                'last_message_at' => $conv['last_message_at'] ?? null,
                'unread_count' => 0,
            ];
        }, $conversations);
    }

    /**
     * Assemble full view (chat open)
     */
    public function assembleFullView(array $conversationData, array $messages, array $historyStats)
    {
        $participantData = null;
        if (!empty($conversationData['patient_id'])) {
            $patient = $this->patientRepository->findById($conversationData['patient_id']);
            if ($patient) {
                $participantData = [
                    'id' => $patient->id,
                    'name' => $patient->name,
                    'phone' => $patient->phone,
                ];
            }
        }

        return [
            'conversation' => [
                'id' => $conversationData['id'],
                'status' => strtolower($conversationData['status'] ?? 'open'),
                'patient' => $participantData ?? [
                    'id' => 0,
                    'name' => 'مريض غير معروف',
                    'phone' => '-',
                    'orders_count' => 0
                ],
                'is_assigned' => $conversationData['is_assigned'] ?? false,
                'assigned_to' => null,
                'created_at' => $conversationData['created_at'] ?? null,
                'last_message_preview' => $conversationData['last_message'] ?? null,
                'last_message_at' => $conversationData['last_message_at'] ?? null,
                'unread_count' => 0,
            ],
            'messages' => $messages,
            'patient_history' => $historyStats
        ];
    }

    /**
     * Map a single message to the Vue frontend format
     */
    public function assembleMessage(array $msg, ?array $participantData = null)
    {
        return [
            'id' => $msg['id'],
            'conversation_id' => $msg['conversation_id'],
            'sender_id' => $msg['sender_id'],
            'is_admin' => $msg['sender_type'] === 'Admin',
            'is_system' => $msg['message_type'] === 'SYSTEM',
            'sender_name' => $msg['sender_type'] === 'Admin' ? 'الإدارة' : ($participantData['name'] ?? 'المريض'),
            'body' => $msg['text'],
            'attachment' => !empty($msg['attachment_url']) ? [
                'url' => $msg['attachment_url'],
                'type' => 'image',
                'name' => 'attachment',
            ] : null,
            'created_at' => $msg['created_at'],
        ];
    }
}
