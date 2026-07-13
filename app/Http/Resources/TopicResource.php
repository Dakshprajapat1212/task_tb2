<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TopicResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'class_id' => $this->class_id,
            'subject_id' => $this->subject_id,
            'title' => $this->title,
            'description' => $this->description,
            'display_order' => $this->display_order,
            'status' => $this->status,
            'notes_count' => $this->whenCounted('notes'),
            'quiz_id' => $this->whenLoaded('quiz', fn () => optional($this->quiz)->id),
        ];
    }
}
