<?php

namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SentReminderResource extends JsonResource
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
            'loan_id' => $this->loan_id,
            'customer_name'=>$this->loan->customer->name,
            'customer_email' => $this->loan->customer->email,
            'loan_purpose'=>$this->loan->loan_purpose,
            'reminder_date' => $this->reminder_date,
            'send_via' => $this->reminder_type,

        ];
    }
}
