<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserManagementController extends Controller
{
    public function __construct()
    {
        // apply middleware example: require manage_users permission
        $this->middleware('permission:manage_users');
    }

    public function index()
    {
        $users = User::with(['roles','employee'])->paginate(25);
        return response()->json($users);
    }

    public function show($id)
    {
        $user = User::with(['roles','employee'])->findOrFail($id);
        return response()->json($user);
    }
}
