<style>
    a:hover {
        text-decoration: none;
    }
</style>

<div class="widget widget-stats bg-grey" style="width: max-content">
    {{-- info/data --}}
    <div class="stats-info text-center">

        {!! HTML::decode (HTML::linkRoute('Contract.create',
            '<span class="btn btn-dark p-10 m-5 m-r-10 text-center">
                <i style="font-size: 25px;" class="img-center fa fa-address-book-o p-10"></i><br />
                <span class="username text-ellipsis text-center">'.trans('view.Dashboard_AddContract').'</span>
            </span>'))
        !!}

        {!! HTML::decode (HTML::linkRoute('Ticket.create',
            '<span class="btn btn-dark p-10 m-5 m-r-10 text-center">
                <i style="font-size: 25px;" class="img-center fa fa-ticket p-10"></i><br />
                <span class="username text-ellipsis text-center">'.trans('view.Dashboard_AddTicket').'</span>
            </span>'))
        !!}

    </div>
    {{-- reference link --}}
    <div class="stats-link"><a href="#">{{ trans('view.Dashboard_Quickstart') }}</a></div>
</div>
