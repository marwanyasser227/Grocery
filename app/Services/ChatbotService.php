<?php

declare(strict_types=1);

namespace App\Services;

use App\Ai\Agents\GroceryAssistant;
use App\Models\ChatbotMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Exceptions\RateLimitedException;
use RuntimeException;
use Throwable;

class ChatbotService
{
    /**
     * Process a user chat message and return the AI answer.
     *
     * @param array{
     *     question: string,
     *     conversation_id?: string|null,
     *     session_id?: string|null,
     *     locale?: string|null,
     *     rating?: int|null
     * } $data
     *
     * @return array{
     *     id: int,
     *     conversation_id: string|null,
     *     question: string,
     *     answer: string,
     *     rating: int|null
     * }
     */
    public function chat(User $user, array $data): array
    {
        $question = trim($data['question']);

        $conversationId =
            $data['conversation_id']
            ?? $data['session_id']
            ?? null;

        $locale = $data['locale'] ?? null;

        $providerName = config('ai.default', 'anthropic');

        $model = config(
            'ai.providers.' . $providerName . '.models.text.default',
            config('ai.providers.' . $providerName . '.model')
        );

        try {
            $provider = Lab::from($providerName);

            if (! config('ai.providers.' . $providerName . '.key')) {
                throw new RuntimeException(
                    "AI provider [{$providerName}] API key is not configured."
                );
            }

            $agent = new GroceryAssistant(locale: $locale);

            $response = $conversationId
                ? $agent
                ->continue($conversationId, as: $user)
                ->prompt(
                    $question,
                    provider: $provider,
                    model: $model,
                    timeout: 60
                )
                : $agent
                ->forUser($user)
                ->prompt(
                    $question,
                    provider: $provider,
                    model: $model,
                    timeout: 60
                );

            $answer = trim((string) $response);

            $conversationId = $response->conversationId ?? $conversationId;
        } catch (RateLimitedException $e) {
            Log::warning('AI rate limited', [
                'provider' => $providerName,
                'model' => $model,
            ]);

            throw new RuntimeException(
                "AI provider [{$providerName}] quota exceeded. Please try again in a moment.",
                previous: $e
            );
        } catch (Throwable $e) {
            Log::error('AI provider error', [
                'provider' => $providerName,
                'model' => $model,
                'message' => $e->getMessage(),
            ]);

            throw new RuntimeException(
                'AI request failed. Please try again later.',
                previous: $e
            );
        }

        if ($answer === '') {
            $answer = 'I apologize, I was unable to process your request at this time. Please try again.';
        }

        $message = $user->chatbotMessages()->create([
            'question' => $question,
            'answer' => $answer,
            'session_id' => $conversationId,
        ]);

        if (isset($data['rating'])) {
            $message->update([
                'rating' => $data['rating'],
            ]);
        }

        return [
            'id' => $message->id,
            'conversation_id' => $conversationId,
            'question' => $question,
            'answer' => $answer,
            'rating' => $message->rating,
        ];
    }

    /**
     * Rate a chatbot message.
     *
     * @return array{
     *     id: int,
     *     conversation_id: string|null,
     *     question: string,
     *     answer: string,
     *     rating: int|null
     *
     * }
     *
     * @throws RuntimeException
     */
    public function rateMessage(int $messageId, int $rating): array
    {
        $message = ChatbotMessage::query()->findOrFail($messageId);

        $message->update([
            'rating' => $rating,
        ]);

        return [
            'id' => $message->id,
            'conversation_id' => $message->session_id,
            'question' => $message->question,
            'answer' => $message->answer,
            'rating' => $message->rating,
        ];
    }

    /**
     * Get authenticated user's chatbot history.
     */
    public function getHistory(
        User $user,
        int $perPage = 15
    ): Collection {
        return $user
            ->chatbotMessages()
            ->latest()
            ->limit($perPage)
            ->get();
    }

    /**
     * Get chatbot suggestions based on locale.
     *
     * @return array<int, string>
     */
    public function getSuggestions(string $locale = 'en'): array
    {
        $suggestions = [
            'en' => [
                'What groceries are available?',
                'Show me today\'s offers.',
                'Help me find a product.',
                'What are the best-selling products?',
            ],

            'ar' => [
                'ما هي المنتجات المتاحة؟',
                'أرني عروض اليوم.',
                'ساعدني في العثور على منتج.',
                'ما هي المنتجات الأكثر مبيعًا؟',
            ],

            'it' => [
                'Quali prodotti sono disponibili?',
                'Mostrami le offerte di oggi.',
                'Aiutami a trovare un prodotto.',
                'Quali sono i prodotti più venduti?',
            ],
        ];

        return $suggestions[$locale] ?? $suggestions['en'];
    }
}
