<style>
    a:hover {
        text-decoration: none;
    }
</style>

<div class="widget widget-stats bg-blue">
    {{-- info/data --}}
    <div class="stats-info d-flex">

      {!! HTML::decode (HTML::link('https://'.\Request::server('HTTP_HOST').'/cacti',
  			'<span class="btn btn-dark p-10 m-5 m-r-10 text-center">
  				<i style="font-size: 25px;" class="img-center fa fa-tachometer p-10"></i><br />
  				<span class="username text-ellipsis text-center">Cacti System</span>
  			</span>', ['target' => '_blank']))
  		!!}

      {!! HTML::decode (HTML::link('https://'.\Request::server('HTTP_HOST').'/icingaweb2',
  			'<span class="btn btn-dark p-10 m-5 m-r-10 text-center">
  				<i style="font-size: 25px;" class="img-center fa fa-info-circle p-10"></i><br />
  				<span class="username text-ellipsis text-center">Icinga2 System</span>
  			</span>', ['target' => '_blank']))
  		!!}

    </div>
    {{-- reference link --}}
    <div class="stats-link"><a href="#">{{trans('view.Dashboard_External')}}</a></div>
</div>
