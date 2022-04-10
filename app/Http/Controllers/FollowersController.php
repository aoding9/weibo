<?php

namespace App\Http\Controllers;

use App\Models\User;
use Auth;
use Illuminate\Http\Request;

class FollowersController extends Controller
{
    //
    public function __construct(){
        $this->middleware('auth'); // 要登录才可以进行关注
    }

    public function store(User $user){
        $this->authorize('follow',$user); // 先验证能不能进行关注操作
        if(!Auth::user()->isFollowing($user->id)){  // 再判断是否已关注
            Auth::user()->follow($user->id);
        }
        return redirect()->route('users.show',$user->id);
    }

    public function destroy(User $user)
    {
        $this->authorize('follow', $user);

        if (Auth::user()->isFollowing($user->id)) {
            Auth::user()->unfollow($user->id);
        }

        return redirect()->route('users.show', $user->id);
    }
}
