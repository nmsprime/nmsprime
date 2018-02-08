@extends ('Layout.split-nopanel')

@section('content_left')

<head>
<meta http-equiv="Pragma" content="no-cache">

<link href="{{asset('/modules/hfcbase/alert.css')}}" rel="stylesheet" type="text/css" media="screen" />
<script type="text/javascript" src="{{asset('/modules/hfcbase/alert.js')}}"></script>

<script type="text/javascript">

	/*
	 * Right Click in Schaltplan
	 */
	function getEl (id)
	{
			url    = '<?php echo \BaseRoute::get_base_url() ?>'
	        kml    = 0;

	        alert ("Element Number: "+id, "<li><a href="+url+"/NetElement/"+id+"/edit>Change</a></li>" +
	                                      "<li><a href="+url+"/NetElement/"+id+"/delete>Delete </a></li>" +
	                                      "<li><a href=details.php?id="+id+">Details</a>", {width:150});
	        return false;
	}

</script>

<body>

	@section('content_top')
		<li>{{ HTML::linkRoute('TreeErd.show', $view_header, [$field, $search]) }}</li>
	@stop

	@DivOpen(12)
		<img usemap="#tree{{$gid}}" src="{{asset("$file.svg")}}"></img>

		<?php
			echo $usemap;

			if ($is_pos)
			{
				$url = \BaseRoute::get_base_url().'/NetElement/create?pos='.$search;
				echo "<h4><div align=\"center\"> <a href=$url>Add Device</a></div></h4>";
			}
		?>
	@DivClose()


</body>

@stop
