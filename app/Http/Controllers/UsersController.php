<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Spatie\Permission\Models\Role;
use DB;
use Hash;
use Validator;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = User::orderBy('id','DESC')->paginate(5);
        if ($request->is('api/*')) {
            return response()->json($data, 200);
        } else {
            return view('users.index',compact('data'))
            ->with('i', ($request->input('page', 1) - 1) * 5);
        }
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $roles = Role::pluck('name','name')->all();
        if ($request->is('api/*')) {
            return response()->json($roles, 200);
        } else {
            return view('users.create',compact('roles'));
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),  [
                        'name' => 'required',
                        'email' => 'required|email|unique:users,email',
                        'password' => 'required|same:confirm-password',
                        'roles' => 'required'
                    ]);
        if($validator->fails())
        {
            if ($request->is('api/*'))
            {
                return response()->json(['message' => $validator->messages()->all()],422); 
            } 
            else 
            {
                Former::withErrors($validator);
                return redirect()->back()->withErrors($validator)->withInput();
            }
        }


        $input = $request->all();
        $input['password'] = Hash::make($input['password']);


        $user = User::create($input);
        $user->assignRole($request->input('roles'));

        if ($request->is('api/*')) {

            return response()->json(['message' => "User created successfully"], 200);
        } else {
            return redirect()->route('users.index')
                    ->with('success','User created successfully');
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $user = User::find($id);
        if ($request->is('api/*')) {
            return response()->json($user, 200);
        } else {
            return view('users.show',compact('user'));
        }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name','name')->all();
        if ($request->is('api/*')) {
            return response()->json([$user,$roles,$userRole], 200);
        } else {
            return view('users.edit',compact('user','roles','userRole'));
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
                        'name' => 'required',
                        'email' => 'required|email|unique:users,email,'.$id,
                        'password' => 'same:confirm-password',
                        'roles' => 'required'
                    ]);
        if($validator->fails())
        {
            if ($request->is('api/*'))
            {
                return response()->json(['message' => $validator->messages()->all()],422); 
            } 
            else 
            {
                Former::withErrors($validator);
                return redirect()->back()->withErrors($validator)->withInput();
            }
        }

        $input = $request->all();
        if(!empty($input['password'])){ 
            $input['password'] = Hash::make($input['password']);
        }else{
            $input = array_except($input,array('password'));    
        }


        $user = User::find($id);
        $user->update($input);
        DB::table('model_has_roles')->where('model_id',$id)->delete();


        $user->assignRole($request->input('roles'));

        if ($request->is('api/*')) {
            return response()->json(['message' => "User updated successfully"], 200);
        } else {
            return redirect()->route('users.index')
                        ->with('success','User updated successfully');
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        User::find($id)->delete();
        if ($request->is('api/*')) {
            return response()->json(['message' => "User deleted successfully"], 200);
        } else {
            return redirect()->route('users.index')
                        ->with('success','User deleted successfully');
        }
    }
}
