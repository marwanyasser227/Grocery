<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'images' => $this->images ?? [],
            'is_approved' => $this->is_approved,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),

            // User information
            'user' => [
                'id' => $this->user->id,
                'username' => $this->user->username,
            ],

            // Meal information
            'meal' => [
                'id' => $this->meal->id,
                'title' => $this->meal->title,
                'slug' => $this->meal->slug,
                'image_url' => $this->meal->image_url,
            ],
        ];
    }
}
