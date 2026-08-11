<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToClinic
{
    protected static function bootBelongsToClinic()
    {
        // عند جلب البيانات (Read)
        static::addGlobalScope('clinic', function (Builder $builder) {
            if (Auth::check()) {
                $user = Auth::user();
                
                // إذا لم يكن Super Admin (أي أنه مدير عيادة أو طبيب)، نقوم بفلترة البيانات حسب عيادته
                if ($user->role !== 'super_admin' && !empty($user->clinic_id)) {
                    $builder->where($builder->getModel()->getTable() . '.clinic_id', $user->clinic_id);
                }
            }
        });

        // عند إنشاء سجل جديد (Create)
        static::creating(function ($model) {
            if (Auth::check()) {
                $user = Auth::user();
                
                // إذا كان مدير عيادة أو طبيب ولم يتم تحديد الclinic_id، نضع له clinic_id الخاص به تلقائياً
                if ($user->role !== 'super_admin' && !empty($user->clinic_id) && empty($model->clinic_id)) {
                    $model->clinic_id = $user->clinic_id;
                }
                
                // (ملاحظة: الـ Super Admin يمكنه إنشاء سجل لأي عيادة إذا حدد الـ clinic_id صراحةً في الطلب)
            }
        });
    }
}