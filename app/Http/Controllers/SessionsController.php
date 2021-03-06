<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionsController extends Controller
{

    public function __construct()
    {
        // 登录页面只有未登录用户才可以访问
        $this->middleware('guest', [
            'only' => ['create']
        ]);

          // 限流 10 分钟十次
          $this->middleware('throttle:10,10', [
            'only' => ['store']
        ]);
    }

    public function create()
    {
        return view('sessions.create');
    }

    public function store(Request $request)
    {
        $credentials = $this->validate($request, [
            'email' => 'required|email|max:255',
            'password' => 'required'
        ]);
        // dd($credentials);
        if (Auth::attempt($credentials, $request->has('remember'))) {
            if(Auth::user()->activated) {
                session()->flash('success', '欢迎回来！');
                // 体验不好，应该重定向到他原先想访问的页面
                // return redirect()->route('users.show', [Auth::user()]);
                $fallback = route('users.show', [Auth::user()]);
                return redirect()->intended($fallback);
            }else{
                Auth::logout();
                session()->flash('warning', '你的账号未激活，请检查邮箱中的注册邮件进行激活。');
               return redirect('/');
            }

        } else {
            session()->flash('danger', '很抱歉，您的邮箱或密码错误');
            return redirect()->back()->withInput();
        }
    }

    public function destroy()
    {
        Auth::logout();
        session()->flash('success', '您已成功退出！');
        return redirect('login');
    }
}
