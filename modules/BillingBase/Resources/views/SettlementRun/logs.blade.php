<div class='row'>
    <div class='col-md-11'>
        <ul class="nav nav-pills" id='settlementrunlogs'>
            <li class="nav-items">
                <a href="#nav-tab-1" data-toggle="tab" class="show" id='first'>
                    <span class="d-sm-block d-none">{{ trans('view.SettlementRun') }}</span>
                </a>
            </li>
            @if (\Module::collections()->has('Dunning'))
                <li class="nav-items">
                    <a href="#nav-tab-2" data-toggle="tab" class="show">
                        <span class="d-sm-block d-none">{{ trans('view.bankTransfer') }}</span>
                    </a>
                </li>
            @endif
            <li class="nav-items">
                <a href="#nav-tab-3" data-toggle="tab" class="show">
                    <span class="d-sm-block d-none">Download</span>
                </a>
            </li>
        </ul>
    </div>
</div>

<div class="tab-content">
    <div class="tab-pane active" id="nav-tab-1">
        @include('billingbase::SettlementRun.logs-table', ['logs' => $logs['settlementrun']])
    </div>

    @if (\Module::collections()->has('Dunning'))
        <div class="tab-pane" id="nav-tab-2">
            @include('billingbase::SettlementRun.logs-table', ['logs' => $logs['bankTransfer']])
        </div>
    @endif

    <div class="tab-pane" id="nav-tab-3">
        {{-- Download logfile button --}}
        <a href="{{ route('SettlementRun.log_dl', $view_var->id) }}" class="btn btn-primary">{{ trans('view.sr_dl_logs') }}</a>
    </div>
</div>
