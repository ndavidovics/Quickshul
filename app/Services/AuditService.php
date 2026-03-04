<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    public function log(
        string $action,
        ?Model $model = null,
        array $old = [],
        array $new = [],
        string $description = ''
    ): AuditLog {
        return AuditLog::create([
            'user_id'        => auth()->id(),
            'action'         => $action,
            'auditable_type' => $model ? get_class($model) : null,
            'auditable_id'   => $model?->getKey(),
            'old_values'     => $old ?: null,
            'new_values'     => $new ?: null,
            'ip_address'     => request()->ip(),
            'description'    => $description,
            'created_at'     => now(),
        ]);
    }

    public function logModelChange(Model $model, array $old, array $new): AuditLog
    {
        $action = $model->wasRecentlyCreated ? 'created' : 'updated';
        $class  = class_basename($model);

        // Only store fields that actually changed
        $changed = array_filter($new, fn($v, $k) => ($old[$k] ?? null) !== $v, ARRAY_FILTER_USE_BOTH);

        return $this->log(
            "{$class}.{$action}",
            $model,
            $old,
            $changed,
            "{$class} #{$model->getKey()} {$action}"
        );
    }
}
