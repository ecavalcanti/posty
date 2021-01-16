<?php

namespace App\Http\Controllers;

use App\Mail\PostLiked;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class PostLikeController extends Controller
{
    public function __construct() 
    {
        $this->middleware(['auth']);
    }

    public function store(Post $post) 
    {
        $user = Auth::user();

        if ($post->likedBy($user)) {
            return response(null, 409);
        }
        
        $post->likes()->create([
            'user_id' => $user->id,
        ]);

        if (!$post->likes()->onlyTrashed()->where('user_id', $user->id)->count()) {
            Mail::to($post->user)->send(new PostLiked($user, $post));
        }

        return back();
    }

    public function destroy(Post $post)
    {
        $post->likes()->where('user_id', Auth::user()->id)->delete();
        
        return back();
    }

}
