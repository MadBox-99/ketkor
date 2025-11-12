<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Throwable;

class LogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Factory|View
    {
        $logs = Log::query()->latest()->with(['user'])->paginate(15);

        return view('log.index', ['logs' => $logs]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Log $log)
    {
        DB::beginTransaction();
        try {
            $log->delete();

            DB::commit();

            return to_route('logs.index')->with('success', __('Organizations deleted successfully.'));
        } catch (Throwable $throwable) {
            DB::rollback();

            return to_route('logs.index')->with('error', $throwable->getMessage());
        }
    }
}
