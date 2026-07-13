<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TopicNoteResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'class_id' => $this->class_id,
            'subject_id' => $this->subject_id,
            'chapter_id' => $this->chapter_id,
            'title' => $this->topic,
            'file_url' => $this->file_url,
            'completed_at' => $this->completed_at ?? null,
        ];
    }
}
