<?php

namespace App\Services;

use App\Models\SmartList;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;

class SmartListService
{
    public function getUserSmartLists(User $user): Collection
    {
        return SmartList::query()
            ->where('user_id', $user->id)
            ->with('meals')
            ->get();
    }

    public function getSmartListForUser(SmartList $smartList, User $user): SmartList
    {
        $this->ensureBelongsToUser($smartList, $user);

        return $smartList->load('meals');
    }

    public function createSmartList(User $user, array $validated, ?UploadedFile $image = null): SmartList
    {
        $validated['user_id'] = $user->id;
        $validated['description'] = $validated['description'] ?? '';
        $mealIds = $validated['meal_ids'] ?? [];
        unset($validated['meal_ids']);

        if ($image) {
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('images/smart-lists'), $imageName);
            $validated['image'] = $imageName;
        }

        $smartList = SmartList::create($validated);

        if (! empty($mealIds)) {
            $smartList->meals()->attach($mealIds);
        }

        return $smartList->load('meals');
    }

    public function updateSmartList(SmartList $smartList, User $user, array $validated, ?UploadedFile $image = null): SmartList
    {
        $this->ensureBelongsToUser($smartList, $user);

        if (array_key_exists('description', $validated) && $validated['description'] === null) {
            $validated['description'] = '';
        }

        $mealIds = $validated['meal_ids'] ?? null;
        unset($validated['meal_ids']);

        if ($image) {
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('images/smart-lists'), $imageName);
            $validated['image'] = $imageName;
        }

        $smartList->update($validated);

        if ($mealIds !== null) {
            $smartList->meals()->sync($mealIds);
        }

        return $smartList->load('meals');
    }

    public function deleteSmartList(SmartList $smartList, User $user): void
    {
        $this->ensureBelongsToUser($smartList, $user);

        $smartList->meals()->detach();
        $smartList->delete();
    }

    public function addMeal(SmartList $smartList, User $user, int|string $mealId): SmartList
    {
        $this->ensureBelongsToUser($smartList, $user);

        $smartList->meals()->syncWithoutDetaching([$mealId]);

        return $smartList->load('meals');
    }

    public function removeMeal(SmartList $smartList, User $user, int|string $mealId): SmartList
    {
        $this->ensureBelongsToUser($smartList, $user);

        $smartList->meals()->detach($mealId);

        return $smartList->load('meals');
    }

    private function ensureBelongsToUser(SmartList $smartList, User $user): void
    {
        if ($smartList->user_id !== $user->id) {
            throw new ModelNotFoundException('Smart list not found');
        }
    }
}
