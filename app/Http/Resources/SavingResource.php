<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class SavingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request):array
    {
        return [
            'id'         =>$this->id,
            'name'       => $this->customer->name,
            'saving_type'      => $this->saving_type,
            'saving_amount'    => $this->saving_amount,

            // 'created_at' => Carbon::parse($this->created_at)->toDayDateTimeString(),

        ];
    }
}
