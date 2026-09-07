<?php

namespace App\Actions\Faq;

use App\Models\Faq;

class UpdateFaq
{
    public function handle(Faq $faq, array $data): Faq
    {
        $faq->update($data);

        return $faq->refresh();
    }
}