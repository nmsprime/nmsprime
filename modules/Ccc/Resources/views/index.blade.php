@extends('ccc::layouts.master')

<?php $classes = ['info', 'active']; ?>

@section('content_left')
	<div class="row">
		@foreach($invoice_links as $year => $years)
			<div class="col-md-6 ui-sortable">
				<div class="panel panel-inverse card-2 d-flex flex-column">
					<div class="panel-heading d-flex flex-row justify-content-between">
						<h4 class="panel-title d-flex">
							<span data-click="panel-collapse">
								{{ trans('messages.Invoices') }} {{$year}}
							</span>
						</h4>
						<div class="panel-heading-btn d-flex flex-row">
							<a href="javascript:;"
							   class="btn btn-xs btn-icon btn-circle btn-warning d-flex"
							   data-click="panel-collapse"
							   style="justify-content: flex-end;align-items: center">
								<i class="fa fa-minus"></i>
							</a>
						</div>
					</div>
					<div class="panel-body fader d-flex flex-column" style="overflow-y:auto;@if($loop->first)@else display: none;@endif; height:100%">
						<table class="table table-bordered">
							@foreach($years as $month => $months)
								<?php
									if (! is_int($month)) {
										continue;
									}
									$bsclass = $classes[$month % 2];
								?>
								<tr class="{{$bsclass}}">
									<!-- Invoice(s) -->
									<td class="" align="center">
									@if(isset($months['INVOICE']))
										@foreach($months['INVOICE'] as $i => $invoice)
											<i class="fa fa-id-card-o"></i>&nbsp; {{ $invoice }}
											@if(isset($months['INVOICE'][$i+1]))
												&emsp; | &emsp;
											@endif
										@endforeach
									@endif
									</td>
									<!-- CDR -->
									@if($years['formatting']['cdr'])
										<td class="" align="center" style="width: 50%">
											@if(isset($months['CDR'][0]))
												<i class="fa fa-id-card-o"></i>&nbsp; {{ $months['CDR'][0] }}
											@else
												-
											@endif
										</td>
									@endif
								</tr>
							@endforeach
						</table>
					</div>
				</div>
			</div>
		@endforeach
	</div>
@stop

@section('content_emails')

	<table class="table">
		@foreach($emails as $email)
			<tr><td> {!! HTML::linkRoute('CustomerPsw', $email->view_index_label()['header'], ['email_id' => $email->id]) !!} </td><td>{{ $email->get_type() }}</td></tr>
		@endforeach
	</table>

@stop

@section('content')

	@include ('bootstrap.blank', array ('content' => 'content_left', 'invoice_links' => $invoice_links, 'view_header' => trans('messages.Invoices'), 'md' => 8))

	@if (!$emails->isEmpty())
		@include ('bootstrap.panel', array ('content' => 'content_emails', 'emails' => $emails, 'view_header' => App\Http\Controllers\BaseViewController::translate_label('E-Mail Address'), 'md' => 4))
	@endif

@stop
