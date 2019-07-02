<?php

namespace App\Http\Controllers\API;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
//        users that are not admin can't access any method in this controller
//        $this->authorize('isAdmin');
    }

    public function index()
    {
//        $this->authorize('isAdmin');

//        to authorize multiple user roles we use Gate::allows or Gate::denies
        if (\Gate::allows('isAdmin') || \Gate::allows('isAuthor')){
            $users = User::latest()->paginate(2);
            return $users;
        }

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

    public function profile()
    {
//        to access authenticated user's information
//        you can use this auth('api')->user(); --> using this won't need the auth:api in the construct
//         or Auth::user();
        return auth('api')->user();
    }

    public function updateProfile(Request $request)
    {
        $user = auth('api')->user();

        $this->validate($request, [
            'name' => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users,email,'.$user->id,
            'password' => 'sometimes|string|min:6',
        ]);

        $currentPhoto = $user->photo;

        if($request->photo != $currentPhoto){

            $name = time().'.' . explode('/', explode(':', substr($request->photo, 0,
                    strpos($request->photo, ';')))[1])[1];

            \Image::make($request->photo)->save(public_path('img/profile/').$name);
            $request->merge(['photo' => $name]);

            $previousPhoto = public_path('img/profile/').$currentPhoto;
//            dd($previousPhoto);
            if(file_exists($previousPhoto)){
                @unlink($previousPhoto);
            }

        }

        if (!empty($request->password)){
//            dd($request['password']);
            $request->merge(['password' => Hash::make($request['password'])]);
        }

        $user->update($request->all());

        return ['message' => 'Success'];
    }

    public function update(Request $request, $id)
    {

        $user = User::findOrFail($id);

        $this->validate($request, [
            'name' => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users,email,'.$user->id,
            'password' => 'sometimes|min:6',
        ]);

        $request->merge(['password' => Hash::make($request['password'])]);

        $user->update($request->all());

        return $user;

    }

    public function searchUser(Request $request)
    {

//        dd($request->q);

        if ($search = \Request::get('q')){
            $users = User::where(function ($query) use ($search){
               $query->where('name','LIKE',"%$search%")->orWhere('email','LIKE',"%$search%");
            })->paginate(2);
        }else{
            $users = User::latest()->paginate(2);
        }

        return $users;

    }

    public function destroy($id)
    {

        $this->authorize('isAdmin');

//        get user
        $user = User::findOrFail($id);
//        delete user
        $user->delete();
//        return message
        return ['message' => 'User Deleted'];

    }
}
