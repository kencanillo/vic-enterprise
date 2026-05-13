<?php

namespace App\Services;

use App\Enums\InventoryMovementType;
use App\Models\InventoryStock;
use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    private $audit;

    public function __construct(AuditLogService $audit)
    {
        $this->audit = $audit;
    }

    public function update(array $data, int $userId): InventoryTransaction
    {
        return DB::transaction(function () use ($data, $userId) {
            $stock = InventoryStock::firstOrCreate(['warehouse_id' => $data['warehouse_id'], 'product_id' => $data['product_id']]);
            $column = $data['quantity_type'] . '_quantity';
            $previous = (float) $stock->{$column};
            $quantity = (float) $data['quantity'];
            $new = in_array($data['movement_type'], [InventoryMovementType::ADJUSTMENT, InventoryMovementType::CORRECTION], true) ? $quantity : $previous + $quantity;
            $discrepancy = $previous > 0 ? abs(($new - $previous) / $previous) * 100 : ($new != 0 ? 100 : 0);
            $flagged = in_array($data['movement_type'], [InventoryMovementType::ADJUSTMENT, InventoryMovementType::CORRECTION], true) && $discrepancy > 5;
            $stock->update([$column => $new]);
            $transaction = InventoryTransaction::create(array_merge($data, [
                'previous_quantity' => $previous,
                'new_quantity' => $new,
                'discrepancy_percentage' => $discrepancy,
                'flagged_for_review' => $flagged,
                'created_by' => $userId,
            ]));
            $this->audit->log('inventory.updated', $transaction, ['previous_quantity' => $previous], $transaction->toArray());
            return $transaction;
        });
    }
}