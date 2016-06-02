@extends ('Generic.edit')

{{-- overwrite generic content_right --}}
@section('content_right')

	<?php
		if ($view_var->view_has_many())
			$view_header_0 = '';
		$i = 0;
		$view_header_0 = '';
	?>

	@foreach($view_var->view_has_many() as $view => $relation)

		<?php
			$i++;

			$model = new $model_name;
			$key   = strtolower($model->table).'_id';
			${"view_header_$i"} = " Assigned $view";
		?>

		@if ($view == 'EnviaOrderDocument')

			@section("content_$i")
				{{-- overwrite relation.blade.php --}}
				<?php
					// check if given relation is a Collection => in this case we assume a x-to-many
					// other checks can easily be OR connected
					// else it seems to be a 1:1
					$is_x_to_many = is_a($relation, 'Illuminate\Database\Eloquent\Collection');

					// helper flag to indicate no related data
					$rel_is_null = is_null($relation);

					// put in array => later we can use this in
					if (!$is_x_to_many) {
						$relation = array($relation);
					}
				?>

				{{ Form::openDivClass(12) }}

					{{ Form::open(array('route' => $view.'.create', 'method' => 'GET')) }}
					{{ Form::hidden($key, $view_var->id) }}

						{{-- Add a hidden form field if create tag is set in $form_fields --}}
						@foreach($form_fields as $field)
							<?php
								if (array_key_exists('create', $field))
								echo Form::hidden($field["name"], $view_var->{$field["name"]});
							?>
						@endforeach

						{{ Form::submit('Create '.$view, ['style' => 'simple']) }}
					{{ Form::close() }}
				{{ Form::closeDivClass() }}
				<br>
				<br>
				<br>

				{{-- List of related data --}}
				@if (!$rel_is_null)
					<ul>
					@foreach ($relation as $rel_elem)
						<li>
							{{ HTML::linkRoute($view.'.show', $rel_elem->view_index_label(), $rel_elem->id) }}
						</li>
					@endforeach
					</ul>
				@endif
			@stop
			@include ('bootstrap.panel', array ('content' => "content_$i", 'view_header' => ${"view_header_$i"}, 'md' => 3))
		@endif

	@endforeach
@stop
