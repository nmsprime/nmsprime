<h4 style="color: #000;">
	{{ \App\Http\Controllers\BaseViewController::translate_view('Quickstart', 'Dashboard') }}
</h4>

<p>
	<ul class="registered-users-list clearfix">
<!-- 		<li>
			{{ HTML::decode (HTML::linkRoute('Contract.index',
                '<h1><div class="text-center"><i class="img-center fa fa-address-book-o"></i></div></h1>
                 <h4 class="username text-ellipsis text-center">Show Contracts<small>Easy</small></h4>')) }}
		</li>
 -->	<li>
			{{ HTML::decode (HTML::linkRoute('Contract.create',
                '<h1><div class="text-center"><i class="img-center fa fa-address-book-o"></i></div></h1>
                 <h4 class="username text-ellipsis text-center">Add Contract<small>Easy</small></h4>')) }}
		</li>
		<li>
			{{ HTML::decode (HTML::linkRoute('Ticket.create',
				'<h1><div class="text-center"><i class="img-center fa fa-ticket"></i></div></h1>
				 <h4 class="username text-ellipsis text-center">Add Ticket<small>Easy</small></h4>')) }}
		</li>
	</ul>
</p>