<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerLoanDetailResource extends JsonResource
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
            'loan_amount'       => $this->loan_amount,
            'loan_type'      => $this->loan_type,
            'loan_duration'    => $this->loan_duration,
            'installation_type'    => $this->installation_type,
            'recommend_to'    => $this->recommend_to,
            'issue_date'    => $this->issue_date_nep,
            'due_date'     => $this->due_date_nep,
            // 'created_at' => Carbon::parse($this->created_at)->toDayDateTimeString(),

        ];
    }
}
