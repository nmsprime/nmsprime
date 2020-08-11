<div id="historyslider" class="d-flex">
    <div style="flex:1 auto;">
        <div class="d-flex align-items-center">
            <h4 style="width:5.5rem">Outages:</h4>
            <div id="slide1" style="border: 1px solid white;height:30px;flex:1 auto" class="d-flex align-items-center m-l-15 m-r-0">
                <div v-for="(state, index) in slider_online" :key="index"
                    :class="state >= 2 ? 'bg-danger' : (state == 1 ? 'bg-warning' : 'bg-success')"
                    style="height:15px;width:{{ 100 / 60 }}%;"
                    v-on:mouseenter="enter($event, index)"
                    v-on:mouseleave="$event.target.style.cssText = 'height:15px;width:{{ 100 / 60 }}%'"
                    >
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center">
            <h4 style="width:5.5rem;">Proactive:</h4>
            <div id="slide2" style="border: 1px solid white;height:30px;flex:1 auto" class="d-flex align-items-center m-l-15 m-r-0">
                <div v-for="(state, index) in slider_power" :key="index"
                    :class="state >= 2 ? 'bg-danger' : (state == 1 ? 'bg-warning' : 'bg-success')"
                    style="height:15px;width:{{ 100 / 60 }}%;"
                    v-on:mouseenter="enter($event, index)"
                    v-on:mouseleave="$event.target.style.cssText = 'height:15px;width:{{ 100 / 60 }}%'"
                    >
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex align-items-center justify-content-center" style="width:7rem;">
        <h4 v-text="date"></h4>
    </div>
</div>
