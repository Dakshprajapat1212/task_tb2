<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlashcardResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'question_type' => $this->question_type,
            'question' => $this->question,
            'correct_answer' => $this->question_type === 'flashcard' 
                                ? $this->correct_answer 
                                : $this->{'option_' . $this->correct_option},
            'explanation' => $this->explanation,
        ];
    }
}
