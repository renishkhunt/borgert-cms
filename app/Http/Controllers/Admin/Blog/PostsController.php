<?php

namespace App\Http\Controllers\Admin\Blog;

use App\Libraries\Upload;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Admin\Blog\Posts;
use App\Http\Controllers\Controller;
use App\Models\Admin\Blog\Categorys;
use App\Models\Admin\Blog\CategoryPosts;
use App\Models\Admin\Blog\Tags;
use App\Models\Admin\Blog\TagsPosts;

class PostsController extends Controller
{
    const UPLOAD_PATH = 'blog/posts/';
    const UPLOAD_ROUTE = 'admin.blog.posts.upload';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Posts::sortable(['created_at' => 'desc'])->paginate(10);

        return view('admin.blog.posts.index', ['posts' => $posts]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categorys = Categorys::all();
        $tags = Tags::All();

        return view('admin.blog.posts.create', ['categorys' => $categorys,'tags' => $tags]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'publish_at' => 'required',
            'categorys_id' => 'required',
            'tags_id' => 'required',
            'title' => 'required',
            'description' => 'required',
        ]);
        $post = new Posts();

        $post->publish_at = new Carbon($request->publish_at);
        $post->title = $request->title;
        $post->description = $request->description;
        $post->status = (isset($request->status) ? 1 : 0);
        $post->seo_title = $request->seo_title;
        $post->seo_description = $request->seo_description;
        $post->seo_keywords = $request->seo_keywords;

        $post->save();

        $categories_posts = [];
        $tags_posts = [];
        if( isset($request->categorys_id) && !empty($request->categorys_id) ){
            foreach ($request->categorys_id as $key => $value) {
                $categories_posts[] = [
                    'category_id' => $value,
                    'post_id' => $post->id
                ];
            }
        }
        if( isset($request->tags_id) && !empty($request->tags_id) ){
            foreach ($request->tags_id as $key => $value) {
                $tags_posts[] = [
                    'tag_id' => $value,
                    'post_id' => $post->id
                ];
            }
        }
        CategoryPosts::insert($categories_posts);
        TagsPosts::insert($tags_posts);

        $user = \Auth::User();
        $path_from = self::UPLOAD_PATH.'temp-'.$user->id.'/';
        $path_to = self::UPLOAD_PATH.$post->id;

        if (\Storage::disk('uploads')->exists($path_from)) {
            \Storage::disk('uploads')->move($path_from, $path_to);
        }

        \Session::flash('success', trans('admin/blog.posts.store.messages.success'));

        return redirect()->route('admin.blog.posts.index')->withInput();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $categorys = Categorys::all();
        $tags = Tags::all();
        $post = Posts::with(['categorys_id','tags_id'])->where('id','=',$id)->first();

        $post_categorys = $post->categorys_id;
        $selected_category = $post_categorys->pluck('category_id')->all();

        $post_tags = $post->tags_id;
        $selected_tags = $post_tags->pluck('tag_id')->all();

        return view('admin.blog.posts.edit', ['categorys' => $categorys, 'post' => $post,'tags' => $tags,'selected_category' => $selected_category, 'selected_tags' => $selected_tags]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'publish_at' => 'required',
            'categorys_id' => 'required',
            'tags_id' => 'required',
            'title' => 'required',
            'description' => 'required',
        ]);
        
        $post = Posts::find($request->id);

        $post->publish_at = new Carbon($request->publish_at);
        $post->title = $request->title;
        $post->description = $request->description;
        $post->status = (isset($request->status) ? 1 : 0);
        $post->seo_title = $request->seo_title;
        $post->seo_description = $request->seo_description;
        $post->seo_keywords = $request->seo_keywords;

        $post->save();

        $post_categorys = $post->categorys_id;
        $selected_category = $post_categorys->pluck('category_id')->all();
        $post_tags = $post->tags_id;
        $selected_tags = $post_tags->pluck('tag_id')->all();
        $categories = $request->categorys_id;
        $tags = $request->tags_id;
        
        $removedCategory = array_diff($selected_category, $categories);
        $selected_category = array_diff($categories, $selected_category);

        $removedTags = array_diff($selected_tags, $tags);
        $selected_tags = array_diff($tags, $selected_tags);

        $categories_posts = [];
        $tags_posts = [];
        foreach ($selected_category as $key => $value) {
            $categories_posts[] = [
                'category_id' => $value,
                'post_id' => $post->id
            ];
        }
        foreach ($selected_tags as $key => $value) {
            $tags_posts[] = [
                'tag_id' => $value,
                'post_id' => $post->id
            ];
        }
        CategoryPosts::insert($categories_posts);
        TagsPosts::insert($tags_posts);

        if( !empty($removedCategory) ){
            CategoryPosts::destroy($removedCategory);
        }
        if( !empty($removedTags) ){
            TagsPosts::destroy($removedTags);
        }

        \Session::flash('success', trans('admin/blog.posts.update.messages.success'));

        return redirect()->route('admin.blog.posts.index')->withInput();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if (is_null($request->posts)) {
            \Session::flash('info', trans('admin/blog.posts.destroy.messages.info'));

            return redirect()->route('admin.blog.posts.index');
        }

        Posts::destroy($request->posts);
        \Session::flash('success', trans('admin/blog.posts.destroy.messages.success'));

        // Precisamos remover as imagens desse ID também
        // tem que ser um foreach porque é um array de galerias
        foreach ($request->posts as $id) {
            // Checamos se o diretório existe
            $path = self::UPLOAD_PATH.$id;

            // Deletamos o diretório da imagem
            if (\Storage::disk('uploads')->exists($path)) {
                \Storage::disk('uploads')->deleteDirectory($path);
            }
        }

        return redirect()->route('admin.blog.posts.index');
    }

    /**
     * Faz o envio ou carrrega as imagens de um diretório.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request, $id = null)
    {

        new Upload(
            $request,
            [
                'id' => $id,
                'route' => self::UPLOAD_ROUTE, // Route `routes/web.app`
                'path' => self::UPLOAD_PATH, // Path to upload file
            ]
        );

        return;

    }
}
