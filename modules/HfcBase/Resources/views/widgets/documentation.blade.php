<style>
    a:hover {
        text-decoration: none;
    }
</style>

<div class="widget widget-stats bg-aqua-darker">
    {{-- info/data --}}
    <div class="stats-info d-flex">

      {!! HTML::decode (HTML::link('https://devel.roetzer-engineering.com/confluence/display/NMS/IT+Maintenance',
  			'<span class="btn btn-dark p-10 m-5 m-r-10 text-center">
  				<i style="font-size: 25px;" class="img-center fa fa-question-circle p-10"></i><br />
  				<span class="username text-ellipsis text-center">'.trans('view.Dashboard_Docu').'</span>
  			</span>',['target' => '_blank']))
  		!!}

      {!! HTML::decode (HTML::link('https://www.youtube.com/playlist?list=PL07ZNkpZW6fyYWJ8xLHHhVLxoGQc72t2J',
  			'<span class="btn btn-dark p-10 m-5 m-r-10 text-center">
  				<i style="font-size: 25px;" class="img-center fa fa-tv p-10"></i><br />
  				<span class="username text-ellipsis text-center">Youtube</span>
  			</span>', ['target' => '_blank']))
  		!!}

      {!! HTML::decode (HTML::link('https://devel.roetzer-engineering.com/confluence/pages/viewpage.action?pageId=22773888',
  			'<span class="btn btn-dark p-10 m-5 m-r-10 text-center">
  				<i style="font-size: 25px;" class="img-center fa fa-wpforms p-10"></i><br />
  				<span class="username text-ellipsis text-center">Forum</span>
  			</span>', ['target' => '_blank']))
  		!!}

      {!! HTML::decode (HTML::linkRoute('SupportRequest.index',
  			'<span class="btn btn-dark p-10 m-5 m-r-10 text-center">
  				<i style="font-size: 25px;" class="img-center fa fa-envelope-open p-10"></i><br />
  				<span class="username text-ellipsis text-center">'.trans('view.Dashboard_RequestHelp').'</span>
  			</span>'))
  		!!}

    </div>
    {{-- reference link --}}
    <div class="stats-link"><a href="#">{{ trans('view.Dashboard_Help') }}</a></div>
</div>
