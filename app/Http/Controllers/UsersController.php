<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Illuminate\Mail\Message;
use Illuminate\Notifications\Messages\MailMessage;
use Mail;

class UsersController extends Controller
{

    public function __construct()
    {
        // 除了指定方法，其他请求处理方法必须要登录才可访问
        $this->middleware('auth', [
            'except' => ['show', 'create', 'store','index', 'confirmEmail']
        ]);
//只让未登录用户访问注册页面：
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }
    public function index()
    {
        $users = User::paginate(6);
        return view('users.index', compact('users'));
    }
    public function create()
    {
        return view('users.create');
    }

    public function show(User $user)
    {
        $statuses = $user->statuses()
                           ->orderBy('created_at', 'desc')
                           ->paginate(10);
        return view('users.show', compact('user', 'statuses'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        //  Auth::login($user);
        // session()->flash('success', '欢迎，您将在这里开启一段新的旅程');
        // return redirect()->route('users.show', [$user]);
        // 把直接登录改为发送激活邮件
        $this->sendEmailConfirmationTo($user);
        session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收。');
        return redirect('/');
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }

    public function update(User $user, Request $request)
    {
        $this->authorize('update', $user);
        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6'
        ]);

        $data = [];
        $data['name'] = $request->name;
        if ($request->password) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        session()->flash('success', '个人资料更新成功！');
        return redirect()->route('users.show', $user->id);
    }

    public function destroy(User $user)
    {
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '成功删除用户！');
        return back();
    }

    public function sendEmailConfirmationTo($user)
    {
        $view='emails.confirm';
        $data=compact('user');
        $to = $user->email;
        $subject = "感谢注册 Weibo 应用！请确认你的邮箱。";

        Mail::send($view,$data,function(Message $message)use($to,$subject){
            // env里面已经配置了$from参数，所以此处不需要了
            // $message->from($from,$name)->to($to)->subject($subject);
            $message->to($to)->subject($subject);
        });
    }

    public function confirmEmail($token)
    {
        $user=User::where('activation_token',$token)->firstOrFail();

        $user->activated = true;
        $user->activation_token = null; //防止重复点击验证链接
        $user->save();

        Auth::login($user);
        session()->flash('success', '恭喜你，激活成功！');
        return redirect()->route('users.show', [$user]);
    }

    public function followings(User $user)
    {
        $users = $user->followings()->paginate(30);
        $title = $user->name . '关注的人';
        return view('users.show_follow', compact('users', 'title'));
    }

    public function followers(User $user)
    {
        $users = $user->followers()->paginate(30);
        $title = $user->name . '的粉丝';
        return view('users.show_follow', compact('users', 'title'));
    }

}
