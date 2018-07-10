<div id="page-container" class="fade page-sidebar-fixed page-header-fixed in">
	<div id="sidebar" class="sidebar">
</div>


    {{-- begin #header --}}
    <div id="header" class="header navbar navbar-default navbar-fixed-top">
      <!-- begin container-fluid -->
      <div class="container-fluid">
        <!-- begin mobile sidebar expand / collapse button -->
        <div class="navbar-header">
          <a href="javascript:;" class="navbar-brand">{{ trans('messages.ccc') }}</a>
          <button type="button" class="navbar-toggle" data-click="sidebar-toggled">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
        </div>
        <!-- end mobile sidebar expand / collapse button -->

        <div class="col-md-5">
          <br>
          <h5>
              <a href="{{route('HomeCcc')}}">{{ trans('messages.home') }}</a>
          </h5>
        </div>

        <!-- global search form -->
        <div class="nav-item dropdown m-r-20">
          <a id="navbarDropdown"
            class="nav-link dropdown-toggle"
            href="#"
            role="button"
            data-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false">
            <i class="fa fa-user-circle-o fa-lg d-inline" aria-hidden="true"></i>
            <span class="d-none d-sm-none d-md-inline">
              {{ Auth::guard('ccc')->user()->first_name.' '.Auth::guard('ccc')->user()->last_name}}
            </span>
            <b class="caret"></b>
          </a>
          <div class="dropdown-menu" aria-labelledby="navbarDropdown" style="right: 0;left:auto;">
            <a class="dropdown-item" href="{{ route('CustomerPsw') }}">
              <i class="fa fa-key" aria-hidden="true"></i>
              {{ trans('messages.password_change') }}
            </a>
            <div class="dropdown-divider"></div>
              {!! Form::open(['url' => route('customerLogout.post')]) !!}
                <button class="dropdown-item" href="#">
                  <i class="fa fa-sign-out" aria-hidden="true"></i>
                  {{ trans('messages.log_out') }}
                </button>
              {!!Form::close() !!}

          </div>


        </ul>
        <!-- end header navigation right -->
      </div>
      <!-- end container-fluid -->
    </div>
    {{-- end #header --}}
</div>
