<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OutletSwitchController extends Controller
{
    public function switch(Request $request, Outlet $outlet): RedirectResponse
    {
        $user = $request->user();

        if (! $user || ! $user->isOwner()) {
            abort(403, 'Hanya owner yang dapat mengganti outlet aktif.');
        }

        if ((int) $outlet->tenant_id !== (int) $user->tenant_id) {
            abort(403, 'Outlet tidak berada dalam tenant Anda.');
        }

        if ($outlet->status !== 'active') {
            return back()->with('error', 'Outlet nonaktif tidak dapat dijadikan outlet aktif.');
        }

        $request->session()->put('active_outlet_id', $outlet->id);
        $request->session()->put('outlet_slug', $outlet->slug);

        return redirect()
            ->route('dashboard')
            ->with('success', "Outlet aktif berhasil diganti ke {$outlet->name}.");
    }
}
