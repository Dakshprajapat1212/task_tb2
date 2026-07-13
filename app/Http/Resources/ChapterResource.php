<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChapterResource extends JsonResource
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
        ];
    }
}
