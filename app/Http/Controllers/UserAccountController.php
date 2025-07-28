<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAccountRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class UserAccountController extends Controller
{
    use ApiResponse;

    public function show(Request $request)
    {
        return $this->success($request->user(), 'Account retrieved');
    }

    public function update(UpdateAccountRequest $request)
    {
        $user = $request->user();
        $user->update($request->validated());

        return $this->success($user, 'Account updated');
    }

    public function destroy(Request $request)
    {
        $request->user()->delete();

        return $this->success(null, 'Account deleted');
    }
}
