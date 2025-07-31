<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\CarModel;

class CarModelController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->query('q');
        $makeId = $request->query('make_id');

        $models = CarModel::where('car_make_id', 448)
            ->where('name', 'like', "%$q%")
            ->select('id', 'name', 'car_make_id')
            ->limit(20)
            ->get();

        return response()->json(['data' => $models]);
    }
}
