<div class="d-flex flex-column align-items-center justify-content-start
            flex-sm-row align-items-sm-start justify-content-sm-center
            flex-lg-column align-items-lg-center justify-content-lg-start
            flex-xl-row align-items-xl-start justify-content-xl-center
            flex-xl-wrap
            border m-10 p-5">
    <div>
        <canvas id="{{ $canvas }}-chart" width="130px" height="130px"></canvas>
    </div>
    <div class="d-flex flex-column m-l-15 m-t-15 ">
        <div class="f-s-20 m-b-10">{{ $title }}</div>
        <div class="d-flex flex-column">
            @yield($content)
        </div>
    </div>
</div>
