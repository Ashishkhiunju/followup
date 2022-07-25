<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RecommenderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'         =>$this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'address'    => $this->address,
            'citizen_ship_no'    => $this->citizen_ship_no,
            'company_name'    => $this->company_name,
            'phone'     => $this->phone,
            // 'created_at' => Carbon::parse($this->created_at)->toDayDateTimeString(),

        ];    }
}
