<?php

namespace App\Http\Requests;

use App\Enums\DispatchStatus;
use App\Enums\TransportMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDispatchRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->can('update', $this->route('dispatch')); }
    public function rules(): array
    {
        $dispatch = $this->route('dispatch');
        return [
            'sales_order_id' => ['required','exists:sales_orders,id'],
            'dr_number' => ['required','string','max:255', Rule::unique('dispatches','dr_number')->ignore($dispatch->id)],
            'dispatch_date' => ['required','date'],
            'status' => ['required', Rule::in(DispatchStatus::all())],
            'pod_location' => ['required','string','max:255'],
            'hauler_id' => ['required','exists:haulers,id'],
            'vehicle_id' => ['required','exists:vehicles,id'],
            'driver_id' => ['required','exists:drivers,id'],
            'balance' => ['nullable','numeric','min:0'],
            'operational_area_id' => ['required','exists:operational_areas,id'],
            'transport_mode' => ['required', Rule::in(TransportMode::all())],
            'notes' => ['nullable','string'],
            'items' => ['required','array','min:1'],
            'items.*.sales_order_item_id' => ['required','exists:sales_order_items,id'],
            'items.*.quantity' => ['required','numeric','min:1'],
        ];
    }
}