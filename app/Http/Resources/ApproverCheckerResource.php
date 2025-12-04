<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApproverCheckerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'fullName'          => $this->fullName,
            'checkers'          => $this->approverCheckers->map(function ($checker) {
                return [
                    'id'        => $checker->checker->id ?? null,
                    'fullName'  => $checker->checker->fullName ?? "No checker",
                ];
            }),
            'checker_category'  => $this->approverCheckers[0]->checker_category,
            'item_id'           => $this->approverCheckers[0]->id,
        ];
    }
}
