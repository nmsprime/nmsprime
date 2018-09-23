<style>
    a:hover {
        text-decoration: none;
    }
</style>

<div class="widget widget-stats bg-aqua-darker">
    {{-- info/data --}}
    <div class="stats-info d-flex">
        <div class="btn btn-dark m-5 m-r-10">
            {!! HTML::decode (HTML::link('https://devel.roetzer-engineering.com',
                '<h3><div class="text-center"><i style="color: white;" class="img-center fa fa-question-circle"></i></div></h3>
                <div style="color: white;" class="username text-ellipsis text-center">Documentation</div>', ['target' => '_blank']))
            !!}
        </div>

        <div class="btn btn-dark m-5 m-r-10">
            {!! HTML::decode (HTML::link('https://www.youtube.com/channel/UCpFaWPpJLQQQLpTVeZnq_qA',
                '<h3><div class="text-center"><i style="color: white;" class="img-center fa fa-tv"></i></div></h3>
                <div style="color: white;" class="username text-ellipsis text-center">Youtube</div>', ['target' => '_blank']))
            !!}
        </div>

        <div class="btn btn-dark m-5 m-r-10">
            {!! HTML::decode (HTML::link('https://nmsprime.com/forum',
                '<h3><div class="text-center"><i style="color: white;" class="img-center fa fa-wpforms"></i></div></h3>
                <div style="color: white;" class="username text-ellipsis text-center">Forum <br></div>', ['target' => '_blank']))
            !!}
        </div>

        <div class="btn btn-dark m-5 m-r-10">
            {!! HTML::decode (HTML::link(route('SupportRequest.index'),
                '<h3><div class="text-center"><i style="color: white;" class="img-center fa fa-envelope-open"></i></div></h3>
                <div style="color: white;" class="username text-ellipsis text-center">Request Help</div>'))
            !!}
        </div>

    </div>
    {{-- reference link --}}
    <div class="stats-link"><a href="#">Help</a></div>
</div>

