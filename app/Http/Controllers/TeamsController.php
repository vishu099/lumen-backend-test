<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Team;
use App\User;

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
    public function index()
    {
        $teams = Team::latest()->paginate(5);
        return view('teams.index',compact('teams'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $users = User::pluck('name','id');
        return view('teams.create',compact('users'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        request()->validate([
            'name' => 'required',
            'user_id' => 'required',
        ]);


        $team = Team::create($request->all());
        $team->users()->sync($request->get('users'));


        return redirect()->route('teams.index')
                        ->with('success','Team created successfully.');
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function show(Team $team)
    {
        return view('teams.show',compact('team'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function edit(Team $team)
    {
        $users = User::pluck('name','id');
        $userTeam = $team->users->pluck('id','id')->all();
        return view('teams.edit',compact('team','users','userTeam'));
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
         request()->validate([
            'name' => 'required',
            'user_id' => 'required',
        ]);


        $team->update($request->all());
        $team->users()->sync($request->get('users'));

        return redirect()->route('teams.index')
                        ->with('success','Team updated successfully');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function destroy(Team $team)
    {
        $team->delete();


        return redirect()->route('teams.index')
                        ->with('success','Team deleted successfully');
    }

}
