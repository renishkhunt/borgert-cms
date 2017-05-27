@extends('admin.blog.base')

@section('title', trans('admin/blog.tags.index.title', ['total' => $tags->total()]), @parent)

@section('actions')
	<a href="{{ route('admin.blog.tags.create') }}" class="btn dim btn-primary"><i class="fa fa-plus"></i> @lang('admin/_globals.buttons.create')</a>
@endsection

@section('blog')
	
	@section('subtitle', trans('admin/blog.tags.index.title', ['total' => $tags->total()]))

	<div class="ibox">
        <div class="ibox-content">
            @if ($tags->total() > 0)
                <form action="{{ route('admin.blog.tags.destroy') }}" method="post">
                    {{ csrf_field() }}
                    <div class="table-responsive">
                        <table class="table table-striped table-align-middle">
                            <thead>
                                <tr>
                                    <th>@sortablelink('id', '#')</th>
                                    <th>@sortablelink('created_at', trans('admin/_globals.tables.created_at'))</th>
                                    <th>@sortablelink('title', trans('admin/_globals.tables.title'))</th>
                                    <th>@sortablelink('order', trans('admin/_globals.tables.order'))</th>
                                    <th>@sortablelink('status', trans('admin/_globals.tables.status'))</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tags as $key => $tag)
                                    <tr>
                                        <td><input type="checkbox" class="i-checks" name="tags[]" value="{{ $tag->id }}"></td>
                                        <td>{{ $tag->created_at->diffForHumans() }}</td>
                                        <td>{{ $tag->title }}</td>
                                        <td>{{ $tag->order }}</td>
                                        <td>
                                            @if ($tag->status === 1)
                                                <span class="badge badge-success">@lang('admin/_globals.tables.active')</span>
                                            @elseif ($tag->status === 0)
                                                <span class="badge">@lang('admin/_globals.tables.inactive')</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <a href="{{ route('admin.blog.tags.edit',$tag->id) }}" class="btn btn-primary">@lang('admin/_globals.buttons.edit')</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> @lang('admin/_globals.buttons.delete_selected')</button>
                </form>
                {!! $tags->render() !!}
            @else
                <div class="widget p-lg text-center">
                    <i class="fa fa-exclamation-triangle fa-2x"></i>
                    <h4 class="no-margins">@lang('admin/blog.tags.index.is_empty')</h4>
                </div>
            @endif
        </div>
    </div>

@endsection