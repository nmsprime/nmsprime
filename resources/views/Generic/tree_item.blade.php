{{-- https://laracasts.com/discuss/channels/laravel/categories-tree-view/replies/114604 --}}

<?php	$color_classes = ['default-1', 'default-2', 'default-3', 'default-4']; // default
?>

<ul>
@foreach($items as $item)
	<?php
		$type = method_exists($item, 'get_icon_type') ? $item->get_icon_type() : $color_classes[$color % 4];
	?>
	<li id="ids[{{$item->id}}]"
		class="f-s-14 p-t-5 {{in_array($item->id, $undeletables) ? 'nocheck' : ''}}
			{{ in_array($item->id, $undeletables) && $item->parent_id ? 'p-l-25' : ''}}"
		data-jstree='{"type":"{{ $type }}" }'>

		{{ HTML::linkRoute("$route_name.edit", $item->view_index_label(), $item->id) }}

		@if($item->children()->count() > 0)
			@include('Generic.tree_item', array('items' => $item->children, 'color' => $color++))
		@else
			<?php $color++; ?>
		@endif
	</li>
@endforeach
</ul>
