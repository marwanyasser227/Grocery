<?php
declare(strict_types=1);

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatbotMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'session_id' => $this->conversation_id,
            'question' => $this->question,
            'answer' => $this->answer,
            'rating' => $this->rating,
            'created_at' => $this->created_at,
        ];
    }
}