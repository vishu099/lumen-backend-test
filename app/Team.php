<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
	/**
     * The attributes that are mass assignable.
     *	
     * @var array
     */
    protected $fillable = [
        'name', 'user_id'
    ];

    public function user()
    {
    	return $this->belongsTo('App\User');
    }

    //
    public function users()
    {
    	return $this->belongsToMany('App\User');
    }
}
