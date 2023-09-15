<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Support\Facades\DB;

class LogController extends Controller
{


    public function __construct()
    {
        $this->middleware(['role:Admin|Operator']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $logs = Log::orderBy('created_at', 'desc')->with(['user'])->paginate(15);
        return view('log.index', compact('logs'));
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
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->route('logs.index')->with('error', $th->getMessage());
        }
    }
}