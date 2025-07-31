<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\CarMake;

class CarMakeController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->query('q');
        $makes = CarMake::where('name', 'like', "%$q%")
                    ->select('id', 'name')
                    ->limit(20)->get();
        return response()->json(['data' => $makes]);
    }
}
