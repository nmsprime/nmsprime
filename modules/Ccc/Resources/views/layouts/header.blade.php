<div id="page-container" class="fade page-sidebar-fixed page-header-fixed in">
	<div id="sidebar" class="sidebar">
</div>


    {{-- begin #header --}}
    <div id="header" class="header navbar navbar-default navbar-fixed-top d-flex">
      <!-- begin container-fluid -->
      <div class="container-fluid d-flex align-items-center">
        <a href="javascript:;" class="navbar-brand d-none d-md-block">{{ trans('messages.ccc') }}</a>
        <div class="d-flex justify-self-start" style="flex: 1;">
          <h5> <a href="{{route('HomeCcc')}}" style="color: #333;">{{ trans('messages.home') }}</a> </h5>
        </div>

        <!-- global search form -->
        <div class="nav-item dropdown justify-self-end">
          <a id="navbarDropdown"
            class="nav-link dropdown-toggle"
            href="#"
            role="button"
            data-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false">
            <i class="fa fa-user-circle-o fa-lg d-inline" aria-hidden="true" style="color: #333;"></i>
            <span class="d-none d-sm-none d-md-inline" style="color: #333;">
              {{ Auth::guard('ccc')->user()->first_name.' '.Auth::guard('ccc')->user()->last_name}}
            </span>
            <b class="caret" style="color: #333;"></b>
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
