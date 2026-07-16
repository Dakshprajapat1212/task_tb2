<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DoubtResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'class_id' => $this->class_id,
            'subject_id' => $this->subject_id,
            'chapter_id' => $this->chapter_id,
            'question' => $this->question,
            'status' => $this->status,
            'answer' => $this->answer,
            'explanation' => $this->explanation,
            'answered_by' => $this->answered_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
