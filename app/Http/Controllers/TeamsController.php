<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Team;
use App\User;
use Validator;

class TeamsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
         $this->middleware('permission:team-list');
         $this->middleware('permission:team-create', ['only' => ['create','store']]);
         $this->middleware('permission:team-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:team-delete', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $teams = Team::latest()->paginate(5);
        if ($request->is('api/*')) {
            return response()->json($teams, 200);
        } else {
            return view('teams.index',compact('teams'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
        }
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $users = User::pluck('name','id');
        if ($request->is('api/*')) {
            return response()->json($users, 200);
        } else {
            return view('teams.create',compact('users'));
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
                        'user_id' => 'required',
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

        $team = Team::create($request->all());
        $team->users()->sync($request->get('users'));

        if ($request->is('api/*')) {
            return response()->json(['message' => "Team created successfully"], 200);
        } else {
            return redirect()->route('teams.index')
                        ->with('success','Team created successfully.');
        }
        
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Team $team)
    {
        $team = Team::find($id);
        if ($request->is('api/*')) {
            return response()->json($team, 200);
        } else {
            return view('teams.show',compact('team'));
        }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Team $team)
    {
        $users = User::pluck('name','id');
        $userTeam = $team->users->pluck('id','id')->all();
        if ($request->is('api/*')) {
            return response()->json([$user,$userTeam], 200);
        } else {
            return view('teams.edit',compact('team','users','userTeam'));
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Team $team)
    {
        $validator = Validator::make($request->all(),  [
                        'name' => 'required',
                        'user_id' => 'required',
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

        $team->update($request->all());
        $team->users()->sync($request->get('users'));

        if ($request->is('api/*')) {
            return response()->json(['message' => "Team updated successfully"], 200);
        } else {
            return redirect()->route('teams.index')
                        ->with('success','Team updated successfully.');
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Team $team)
    {
        $team->delete();
        if ($request->is('api/*')) {
            return response()->json(['message' => "Team deleted successfully"], 200);
        } else {
            return redirect()->route('teams.index')
                        ->with('success','Team deleted successfully.');
        }

    }

}
