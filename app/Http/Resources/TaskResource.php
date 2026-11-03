<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'deadline' => $this->deadline instanceof \DateTimeInterface
                ? $this->deadline->format('Y-m-d')
                : (is_string($this->deadline) ? substr($this->deadline, 0, 10) : $this->deadline),
            'status' => $this->status,
        ];
    }
}
