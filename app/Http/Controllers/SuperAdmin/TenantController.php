<?php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::withoutGlobalScopes()
            ->withTrashed()
            ->withCount(['families', 'users'])
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('superadmin.tenants.index', compact('tenants'));
    }

    public function show(int $id)
    {
        $tenant = Tenant::withoutGlobalScopes()->withTrashed()->findOrFail($id);
        $stats = [
            'families' => \App\Models\Family::withoutGlobalScopes()->where('tenant_id', $id)->count(),
            'users'    => \App\Models\User::withoutGlobalScopes()->where('tenant_id', $id)->count(),
            'payments' => \App\Models\Payment::withoutGlobalScopes()->where('tenant_id', $id)->count(),
        ];
        return view('superadmin.tenants.show', compact('tenant', 'stats'));
    }

    public function activate(int $id)
    {
        Tenant::withoutGlobalScopes()->findOrFail($id)->update(['status' => 'active']);
        return back()->with('success', 'Tenant activated.');
    }

    public function suspend(int $id)
    {
        Tenant::withoutGlobalScopes()->findOrFail($id)->update(['status' => 'suspended']);
        return back()->with('success', 'Tenant suspended.');
    }

    public function destroy(int $id)
    {
        Tenant::withoutGlobalScopes()->findOrFail($id)->delete();
        return redirect()->route('superadmin.index')->with('success', 'Tenant deleted.');
    }
}
