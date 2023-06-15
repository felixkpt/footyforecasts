<?php

namespace App\Http\Controllers\Admin\Posts;

use App\Http\Controllers\Controller;
use App\Repositories\PostRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PostsController extends Controller
{
    private $postRepository;


    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function index()
    {
        return Inertia::render('Posts/Index', $this->postRepository->all());
    }

    //Create post
    public function create()
    {
        return Inertia::render('Posts/Create');
    }

    //Get post by id
    public function find(Request $request)
    {
        $id = $request->id;
        return response()->json($this->postRepository->find($id, ['*'], ['keytoken', 'endpoint']), 200);
    }

    //Store post
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|unique:posts,title,' . $request->id . ',id',
            'content_short' => 'required',
            'content' => 'required',
        ]);

        $post = [
            'title' => $request->title,
            'content_short' => $request->content_short,
            'content' => $request->content,
            'img' => $request->img,
            'status' => $request->status,
        ];
        return response()->json($this->postRepository->create($post), 201);
    }
}
