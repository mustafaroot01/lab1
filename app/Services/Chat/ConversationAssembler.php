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
    public function assembleConversation(array $conversationData): ConversationView
    {
        // Supabase 'conversations' might not have a direct patient_id, 
        // we might need to rely on the participant logic if the schema splits them.
        // But assuming the schema simplifies this or we pass the participant explicitly:
        
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

        return new ConversationView(
            conversation: $conversationData,
            participant: $participantData
        );
    }

    /**
     * Assemble multiple conversations
     */
    public function assembleMany(array $conversations): array
    {
        $patientIds = collect($conversations)->pluck('patient_id')->filter()->unique()->toArray();
        $patients = $this->patientRepository->findMany($patientIds)->keyBy('id');

        return array_map(function ($conv) use ($patients) {
            $participantData = null;
            if (!empty($conv['patient_id']) && $patients->has($conv['patient_id'])) {
                $patient = $patients->get($conv['patient_id']);
                $participantData = [
                    'id' => $patient->id,
                    'name' => $patient->name,
                    'phone' => $patient->phone,
                ];
            }

            return (new ConversationView(
                conversation: $conv,
                participant: $participantData
            ))->toArray();
        }, $conversations);
    }

    /**
     * Assemble full view (chat open)
     */
    public function assembleFullView(array $conversationData, array $messages, array $historyStats): ConversationView
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

        return new ConversationView(
            conversation: $conversationData,
            participant: $participantData,
            messages: $messages,
            history: $historyStats
        );
    }
}
