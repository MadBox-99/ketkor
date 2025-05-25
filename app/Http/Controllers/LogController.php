<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Support\Facades\DB;
use Throwable;

class LogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $logs = Log::orderBy('created_at', 'desc')->with(['user'])->paginate(15);

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

            return redirect()->route('logs.index')->with('success', __('Organizations deleted successfully.'));
        } catch (Throwable $throwable) {
            DB::rollback();

            return redirect()->route('logs.index')->with('error', $throwable->getMessage());
        }
    }
}
