<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

//! requests
use App\Http\Requests\Chatbot\ChatbotHistoryRequest;
use App\Http\Requests\Chatbot\ChatbotRequest;
use App\Http\Requests\Chatbot\ChatbotSuggestionsRequest;

//! resource file
use App\Http\Resources\Api\ChatbotMessageResource;

//! services
use App\Services\ChatbotService;

//! Trait which handle the response 
use App\Traits\ApiResponse;

class ChatbotController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ChatbotService $chatbotService
    ) {}

    public function chat(ChatbotRequest $request): JsonResponse
    {
        $result = $this->chatbotService->chat(
            user: $request->user(),
            data: $request->validated(),
        );

        return self::success(
            'Chat response generated successfully',
            new ChatbotMessageResource($result)
        );
    }

    public function history(ChatbotHistoryRequest $request): JsonResponse
    {
        $messages = $this->chatbotService->getHistory(
            user: $request->user(),
            perPage: $request->validated()['per_page'] ?? 15,
        );

        return self::success(
            'Chat history retrieved successfully',
            ChatbotMessageResource::collection($messages)
        );
    }

    public function suggestions(ChatbotSuggestionsRequest $request): JsonResponse
    {
        $suggestions = $this->chatbotService->getSuggestions(
            locale: $request->validated()['locale'] ?? 'en',
        );

        return self::success(
            'Suggestions retrieved successfully',
            [
                'suggestions' => $suggestions,
            ],
            200
        );
    }
}
