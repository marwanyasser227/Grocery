<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MealBriefResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'image_url' => $this->image_url,
            ...$this->getApiPriceAttributes(),
            'rating' => (float) $this->rating,
            'is_featured' => $this->is_featured,
            'features' => $this->features,
        ];
    }
}
