<?php

namespace App\Http\Controllers\Admin\Blog;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin\Blog\Tags;

class TagsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tags = Tags::sortable(['created_at' => 'desc'])->paginate(10);

        return view('admin.blog.tags.index', ['tags' => $tags]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.blog.tags.create');
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
            'title' => 'required',
        ]);

        $tags = new Tags();

        $tags->title = $request->title;
        $tags->order = $request->order;
        $tags->status = (isset($request->status) ? 1 : 0);

        $tags->save();

        \Session::flash('success', trans('admin/blog.tags.store.messages.success'));

        return redirect()->route('admin.blog.tags.index')->withInput();
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
        $tags = Tags::find($id);

        return view('admin.blog.tags.edit', ['tag' => $tags]);
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
            'title' => 'required',
        ]);

        $tags = Tags::find($request->id);

        $tags->title = $request->title;
        $tags->order = $request->order;
        $tags->status = (isset($request->status) ? 1 : 0);

        $tags->save();

        \Session::flash('success', trans('admin/blog.tags.update.messages.success'));

        return redirect()->route('admin.blog.tags.index')->withInput();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if (is_null($request->tags)) {
            \Session::flash('info', trans('admin/blog.tags.destroy.messages.info'));

            return redirect()->route('admin.blog.tags.index');
        }

        Tags::destroy($request->tags);
        \Session::flash('success', trans('admin/blog.tags.destroy.messages.success'));

        return redirect()->route('admin.blog.tags.index');
    }
}
