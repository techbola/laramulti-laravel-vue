<?php

namespace App\Http\Controllers\API;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function index()
    {
        $users = User::latest()->paginate(10);
        return $users;
    }

    public function store(Request $request)
    {
         $this->validate($request, [
             'name' => 'required|string|max:191',
             'email' => 'required|string|email|max:191|unique:users',
             'type' => 'required',
             'password' => 'required|string|min:6',
         ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'type' => $request->type,
            'bio' => $request->bio,
            'password' => Hash::make($request->password),
        ];

        $user = User::create($data);

        return $user;

    }

    public function show($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
