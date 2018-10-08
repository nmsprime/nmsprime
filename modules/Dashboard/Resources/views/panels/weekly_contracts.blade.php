
<a href="/admin/createCSV" class="btn btn-primary">Download CSV</a>

<!-- Table -->
<table class="table table-hover table-bordered">
	<thead>
		<tr>
			@foreach (['Week', 'Balance'] as $column => $name)
				<th scope="col" rowspan="2" class="text-center" width="20">{{$name}}</th>
			@endforeach
			@foreach (['Internet', 'VoIP', 'TV', 'Other'] as $column => $value)
				<th scope="col" colspan="2" class="text-center">{{ $value }}</th>
			@endforeach
		</tr>
		<tr>
			@foreach (['Internet', 'VoIP', 'TV', 'Other'] as $column)
				<th width="20" class="text-center"><font color="green">+</font></th>
				<th width="20" class="text-center"><font color="red">-</font></th>
			@endforeach
		</tr>
	</thead>
	<tbody>
		@for($i = 0; $i <= 3; $i++)
			<tr>
				@foreach ([$data['contracts']['table']['weekly']['week'], $data['contracts']['table']['weekly']['ratio'], $data['contracts']['table']['weekly']['gain']['internet'], $data['contracts']['table']['weekly']['loss']['internet'], $data['contracts']['table']['weekly']['gain']['voip'], $data['contracts']['table']['weekly']['loss']['voip'], $data['contracts']['table']['weekly']['gain']['tv'], $data['contracts']['table']['weekly']['loss']['tv'], $data['contracts']['table']['weekly']['gain']['other'], $data['contracts']['table']['weekly']['loss']['other']] as $value)
					<td class="text-center">{{$value[$i]}}</td>
				@endforeach
			</tr>
		@endfor
	</tbody>
</table>
