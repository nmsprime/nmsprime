@section("content_items_list")

<script>
	function myFunction(){
		var row = document.getElementById("1");
		row.parentNode.removeChild(row);
		// row.deleteCell(0);
	}
		// document.removeChild();
</script>
	<button onclick="myFunction()">Delete rows</button>
	{{ Form::openDivClass(12) }}

		<table width="100%" id="0">
			@foreach ($price_entries as $p)
				<tr id="1">
					<td> {{ HTML::linkRoute('Price.edit', $p->name, $p->id) }} </td>
					<td draggable="true"> {{ $p->type }} </td>
					<td> {{ $p->price.' €' }} </td>
				</tr>
			@endforeach
		</table>

	{{ Form::closeDivClass() }}
@stop

@include ('bootstrap.panel', array ('content' => "content_items_list", 'view_header' => 'Item List', 'md' => 5))

