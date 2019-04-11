<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use DB;
use Validator;

class RolesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
         $this->middleware('permission:role-list');
         $this->middleware('permission:role-create', ['only' => ['create','store']]);
         $this->middleware('permission:role-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:role-delete', ['only' => ['destroy']]);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $roles = Role::orderBy('id','DESC')->paginate(5);
        if ($request->is('api/*')) {
            return response()->json($roles, 200);
        } else {
            return view('roles.index',compact('roles'))
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
        $permission = Permission::get();
        if ($request->is('api/*')) {
            return response()->json($permission, 200);
        } else {
            return view('roles.create',compact('permission'));
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
                        'name' => 'required|unique:roles,name',
                        'permission' => 'required',
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


        $role = Role::create(['name' => $request->input('name')]);
        $role->syncPermissions($request->input('permission'));

        if ($request->is('api/*')) {
            return response()->json(['message' => "Role created successfully"], 200);
        } else {
            return redirect()->route('roles.index')
                        ->with('success','Role created successfully');
        }
        
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,$id)
    {
        $role = Role::find($id);
        $rolePermissions = Permission::join("role_has_permissions","role_has_permissions.permission_id","=","permissions.id")
            ->where("role_has_permissions.role_id",$id)
            ->get();
        if ($request->is('api/*')) {
            return response()->json(['roles' => $role,'role_permissions' => $rolePermissions], 200);
        } else {
            return view('roles.show',compact('role','rolePermissions'));
        }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request,$id)
    {
        $role = Role::find($id);
        $permission = Permission::get();
        $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id",$id)
            ->pluck('role_has_permissions.permission_id','role_has_permissions.permission_id')
            ->all();

        if ($request->is('api/*')) {
            return response()->json(['roles' => $role,'permission' => $permission,'role_permissions' => $rolePermissions], 200);
        } else {
            return view('roles.edit',compact('role','permission','rolePermissions'));
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
        $validator = Validator::make($request->all(),  [
                        'name' => 'required|unique:roles,name',
                        'permission' => 'required',
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

        $role = Role::find($id);
        $role->name = $request->input('name');
        $role->save();


        $role->syncPermissions($request->input('permission'));

        if ($request->is('api/*')) {
            return response()->json(['message' => "Role updated successfully"], 200);
        } else {
            return redirect()->route('roles.index')
                        ->with('success','Role updated successfully');
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$id)
    {
        DB::table("roles")->where('id',$id)->delete();

        if ($request->is('api/*')) {
            return response()->json(['message' => "Role deleted successfully"], 200);
        } else {
            return redirect()->route('roles.index')
                        ->with('success','Role deleted successfully');
        }
    }
}
