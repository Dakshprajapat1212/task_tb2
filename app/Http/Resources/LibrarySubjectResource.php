<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LibrarySubjectResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
        ];

        if ($this->pivot) {
            $data['class_link'] = $this->pivot->class_link;
            $data['class_date'] = $this->pivot->class_date;
            $data['start_time'] = $this->pivot->start_time;
            $data['end_time'] = $this->pivot->end_time;
            
            if ($this->pivot->faculty_id) {
                $faculty = \App\Models\Faculty::with('user')->find($this->pivot->faculty_id);
                $data['faculty'] = $faculty && $faculty->user ? [
                    'id' => $faculty->id,
                    'name' => $faculty->user->name,
                    'email' => $faculty->user->email,
                ] : null;
            }
        }

        return $data;
    }
}
