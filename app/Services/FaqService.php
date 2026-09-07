<?php
declare(strict_types=1);

namespace App\Services;

use App\Http\Resources\FaqCollection;
use App\Models\Faq;
use Illuminate\Database\Eloquent\Collection;

class FaqService
{
    public function getFaqs(array $data): array
    {
        $query = Faq::query();

        if (!empty($data['category'])) {
            $query->category($data['category']);
        }

        if ($data['active_only'] ?? true) {
            $query->active();
        }

        if (!empty($data['search'])) {
            $search = $data['search'];

            $query->where(function ($query) use ($search) {
                $query
                    ->where('question', 'LIKE', "%{$search}%")
                    ->orWhere('answer', 'LIKE', "%{$search}%");
            });
        }

        $faqs = $query
            ->ordered()
            ->paginate($data['per_page'] ?? 15);

        $result = [
            'data' => new FaqCollection($faqs),
        ];

        if ($data['with_categories'] ?? false) {
            $result['categories'] = $this->getCategories();
        }

        return $result;
    }

    public function create(array $data): Faq
    {
        return Faq::create($data);
    }

    public function update(Faq $faq, array $data): Faq
    {
        $faq->update($data);

        return $faq->refresh();
    }

    public function delete(Faq $faq): void
    {
        $faq->delete();
    }

    public function getCategories(): Collection
    {
        return Faq::active()
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();
    }

    public function getByCategory(string $category): Collection
    {
        return Faq::active()
            ->category($category)
            ->ordered()
            ->get();
    }
}