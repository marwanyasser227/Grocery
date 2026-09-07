<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

//! requests 
use App\Http\Requests\Faq\FaqIndexRequest;
use App\Http\Requests\Faq\StoreFaqRequest;
use App\Http\Requests\Faq\UpdateFaqRequest;

//! resource
use App\Http\Resources\FaqResource;

//! model
use App\Models\Faq;

//! services
use App\Services\FaqService;

//! trait
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class FaqController extends Controller
{
    use ApiResponse;
    public function index(
        FaqIndexRequest $request,
        FaqService $faqService
    ): JsonResponse {
        $result = $faqService->getFaqs(
            $request->validated()
        );

        return self::success(
            'FAQs retrieved successfully',
            $result
        );
    }

    public function store(
        StoreFaqRequest $request,
        FaqService $faqService
    ): JsonResponse {
        $faq = $faqService->create(
            $request->validated()
        );

        return self::success(
            'FAQ created successfully',
            new FaqResource($faq),
            201
        );
    }

    public function show(Faq $faq): JsonResponse
    {
        return self::success(
            'FAQ retrieved successfully',
            new FaqResource($faq),
            200
        );
    }

    public function update(
        UpdateFaqRequest $request,
        Faq $faq,
        FaqService $faqService
    ): JsonResponse {
        $faq = $faqService->update(
            $faq,
            $request->validated()
        );

        return self::success(
            'FAQ updated successfully',
            new FaqResource($faq),
            200
        );
    }

    public function destroy(
        Faq $faq,
        FaqService $faqService
    ): JsonResponse {
        $faqService->delete($faq);

        return self::success(
            'FAQ deleted successfully',
            status: 200
        );
    }

    public function categories(
        FaqService $faqService
    ): JsonResponse {
        $categories = $faqService->getCategories();

        return self::success(
            'FAQ categories retrieved successfully',
            $categories,
            200
        );
    }

    public function byCategory(
        string $category,
        FaqService $faqService
    ): JsonResponse {
        $faqs = $faqService->getByCategory($category);

        return self::success(
            'FAQs retrieved successfully',
            FaqResource::collection($faqs),
            200
        );
    }
}
