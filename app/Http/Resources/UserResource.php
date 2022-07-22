<?php
namespace App\Http\Resources;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        /** @var User $this */
        return [
            'id'         =>$this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'address'    => $this->address,
            'citizen_ship_no'    => $this->citizen_ship_no,
            'company_name'    => $this->company_name,
            'phone'     => $this->phone,
            // 'created_at' => Carbon::parse($this->created_at)->toDayDateTimeString(),

        ];
    }
}
