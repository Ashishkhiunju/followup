<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserLoanResource extends JsonResource
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
            'name'       => $this->customer->name,
           'installation_type'=>$this->installation_type,
           'loan_amount'=>$this->loan_amount,
            // 'created_at' => Carbon::parse($this->created_at)->toDayDateTimeString(),

        ];
    }
}
