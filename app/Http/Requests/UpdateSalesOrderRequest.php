<?php

namespace App\Http\Requests;

use App\Enums\SalesOrderStatus;
use App\Enums\TransportMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSalesOrderRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->can('update', $this->route('sales_order')); }
    public function rules(): array
    {
        return [
            'po_date' => ['required','date'],
            'status' => ['required', Rule::in(SalesOrderStatus::all())],
            'customer_id' => ['required','exists:customers,id'],
            'sales_agent_id' => ['nullable','exists:sales_agents,id'],
            'payment_term_id' => ['required','exists:payment_terms,id'],
            'location' => ['nullable','string','max:255'],
            'operational_area_id' => ['required','exists:operational_areas,id'],
            'transport_mode' => ['required', Rule::in(TransportMode::all())],
            'truck_or_vessel_identifier' => ['nullable','string','max:255'],
            'notes' => ['nullable','string'],
            'items' => ['required','array','min:1'],
            'items.*.product_id' => ['required','exists:products,id'],
            'items.*.description' => ['nullable','string','max:255'],
            'items.*.quantity' => ['required','numeric','min:1'],
            'items.*.uom' => ['required','string','max:30'],
            'items.*.unit_price' => ['required','numeric','min:0'],
        ];
    }
}