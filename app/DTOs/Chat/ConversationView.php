<?php

namespace App\DTOs\Chat;

class ConversationView
{
    public array $conversation;
    public ?array $participant;
    public array $messages;
    public array $history;

    public function __construct(
        array $conversation,
        ?array $participant = null,
        array $messages = [],
        array $history = []
    ) {
        $this->conversation = $conversation;
        $this->participant = $participant;
        $this->messages = $messages;
        $this->history = $history;
    }

    public function toArray(): array
    {
        return [
            'conversation' => $this->conversation,
            'participant'  => $this->participant,
            'messages'     => $this->messages,
            'history'      => $this->history,
        ];
    }
}
