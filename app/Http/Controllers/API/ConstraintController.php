<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConstraintResource;
use App\Models\Constraint;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ConstraintController extends Controller
{
    public function getActiveConstraints()
    {
        $activeConstraints = ConstraintResource::Collection(Constraint::where('constraint_status', 1)->get());

        return $activeConstraints;
    }

    public function applyConstraint(Request $request)
    {
        Constraint::updateOrCreate(
            [
            'constraint_code' => $request->constraint_code,
            ],
            [
            'user_id' => auth()->user()->id,
            'constraint_time_register' => Carbon::now()->format('Y-m-d H:i:s'),
            'constraint_end_time' => $request->constraint_end_time == 0 ? null: Carbon::parse($request->constraint_end_time)->format('Y-m-d H:i:s') ,
            'prohibition_code' => $request->prohibition_code,
            'constrained_user' => $request->constrained_user,
            'quarantined_users' => $request->prohibition_code == 1 ? null : $request->quarantined_users ,
            'quarantined_neighborhood' => $request->quarantined_neighborhood,
            'constraint_status' => $request->constraint_status,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Constraint Applied Successfully!',
        ]);
    }

    public function changeStatus(Request $request)
    {
        $constraint = Constraint::where('constraint_code', $request->constraint_code)->first();
        $constraint->update([
            'constraint_status' => $request->constraint_code,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Status changed Successfully!',
        ]);
    }
}
