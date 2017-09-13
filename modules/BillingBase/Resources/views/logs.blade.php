<div class="table-responsive">
	<table class="table streamtable table-bordered" width="100%">
	<thead>
		<tr class="active">
			<th></th>
			<th>Time</th>
			<th>Type</th>
			<th>Message</th>
		</tr>
	</thead>
	<tbody>
		@foreach($logs as $row)
			<tr class="{{ $row['color'] }}">
				<td></td>
				<?php unset($row['color']) ?>
					
				@foreach($row as $cell)
					<td>{{ $cell }}</td>
				@endforeach
			</tr>
		@endforeach
	</tbody>
</table>
