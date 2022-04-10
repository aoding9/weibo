<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Str;

class User extends Authenticatable
{
	use HasApiTokens, HasFactory, Notifiable;
	protected $table = 'users';
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = [
		'name',
		'email',
		'password',
	];

	/**
	 * The attributes that should be hidden for serialization.
	 *
	 * @var array<int, string>
	 */
	protected $hidden = [
		'password',
		'remember_token',
	];

	/**
	 * The attributes that should be cast.
	 *
	 * @var array<string, string>
	 */
	protected $casts = [
		'email_verified_at' => 'datetime',
	];

	public function gravatar($size = '100')
	{
		$hash = md5(strtolower(trim($this->attributes['email'])));
		return "http://www.gravatar.com/avatar/$hash?s=$size";
	}


	public static function boot()
	{
		parent::boot();

		static::creating(function ($user) {
			$user->activation_token = Str::random(10);
		});
	}
	public function statuses()
	{
		return $this->hasMany(Status::class);
	}


	/**
	 * 获取已关注用户和自己的最新发布的微博动态
	 */
    public function feed()
    {
      // 获取关注用户的id，然后push自己的用户id，最后whereIn一起查出来
        $user_ids = $this->followings->pluck('id')->toArray();
        array_push($user_ids, $this->id);
        return Status::whereIn('user_id', $user_ids)
                              ->with('user') // 预加载避免n+1
                              ->orderBy('created_at', 'desc');
    }

    /**
     * 获取粉丝列表
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'followers', 'user_id', 'follower_id');
    }

    /**
     * 获取关注列表
     */
    public function followings()
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'user_id');
    }

    public function follow($user_ids)
    {
        if ( ! is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->sync($user_ids, false); // sync可以防止添加重复的关联,第二个参数表示添加关联时是否保留旧的关联
    }

    public function unfollow($user_ids)
    {
        if ( ! is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->detach($user_ids);
    }
    public function isFollowing($user_id)
    {
        return $this->followings->contains($user_id);
    }
}
