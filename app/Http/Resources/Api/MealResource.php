<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MealResource extends JsonResource
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
            'description' => $this->description,
            'image_url' => $this->image_url,
            'offer_title' => $this->offer_title,
            ...$this->getApiPriceAttributes(),
            'has_offer' => $this->hasOffer(),
            'rating' => (float) $this->rating,
            'rating_count' => (int) $this->rating_count,
            'size' => $this->whenNotNull($this->size),
            'brand' => $this->whenNotNull($this->brand),
            'stock_quantity' => $this->whenNotNull($this->stock_quantity),
            'in_stock' => $this->when(isset($this->stock_quantity), fn() => $this->isInStock()),
            'is_featured' => $this->is_featured,
            'expiry_date' => $this->whenNotNull($this->expiry_date),
            'days_until_expiry' => $this->when(isset($this->expiry_date), fn() => $this->daysUntilExpiry()),
            'is_expired' => $this->when(isset($this->expiry_date), fn() => $this->isExpired()),
            'features' => $this->features,
            'subcategory' => $this->whenLoaded('subcategory', fn() => [
                'id' => $this->subcategory->id,
                'name' => $this->subcategory->name,
                'slug' => $this->subcategory->slug,
            ]),
        ];
    }
}
