<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Botble\Blog\Models\Post;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PostApiController extends Controller
{
    public function index(Request $request)
    {
        // Fetch all posts, you can modify this to include pagination if necessary
        $posts = Post::with(['tags', 'categories', 'author'])->get();

        return response()->json([
            'status' => 'success',
            'data' => $posts,
        ], Response::HTTP_OK);
    }
}
