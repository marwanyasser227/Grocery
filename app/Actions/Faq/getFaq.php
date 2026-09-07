<?php

namespace App\Actions\Faq;

use App\Services\FaqService;

class GetFaqs
{
    public function __construct(
        private readonly FaqService $faqService
    ) {}

    public function handle(array $data)
    {
        return $this->faqService->getFaqs($data);
    }

    public function categories()
    {
        return $this->faqService->getCategories();
    }

    public function byCategory(string $category)
    {
        return $this->faqService->getByCategory($category);
    }
}