<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LibraryNoteResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'class_id' => $this->class_id,
            'subject_id' => $this->subject_id,
            'chapter_id' => $this->chapter_id,
            'title' => $this->chapter,
            'file_url' => $this->file_url,
            'is_completed' => !is_null($this->completed_at),
            'completed_at' => $this->completed_at,
        ];
    }
}
