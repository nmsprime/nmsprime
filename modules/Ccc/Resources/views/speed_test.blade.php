


<div class="col-md-6 ui-sortable">
  <div class="panel panel-inverse card-2 d-flex flex-column">
    <div class="panel-heading d-flex flex-row justify-content-between">
      <h4 class="panel-title d-flex">
        <span data-click="panel-collapse" data-original-title="" title="" data-init="true">Speed Test </span>
        <span data-click="panel-collapse"></span>
      </h4>
      <div class="panel-heading-btn d-flex flex-row">
        <a href="javascript:;"
           class="btn btn-xs btn-icon btn-circle btn-warning d-flex"
           data-click="panel-collapse"
           style="justify-content: flex-end;align-items: center">
          <i class="fa fa-minus"></i>
        </a>
      </div>
    </div>
    <div class="panel-body fader d-flex flex-column" style="overflow-y:auto; height:100%">
      <div class="text-center">
        <div class="testGroup">
          <div class="testArea">
            <div class="testName">Download</div>
            <canvas id="dlMeter" class="meter"></canvas>
            <div id="dlText" class="meterText"></div>
            <div class="unit">Mbps</div>
          </div>
          <div class="testArea">
            <div class="testName">Upload</div>
            <canvas id="ulMeter" class="meter"></canvas>
            <div id="ulText" class="meterText"></div>
            <div class="unit">Mbps</div>
          </div>
        </div>
        <div class="testGroup">
          <div class="testArea">
            <div class="testName">Ping</div>
            <canvas id="pingMeter" class="meter"></canvas>
            <div id="pingText" class="meterText"></div>
            <div class="unit">ms</div>
          </div>
          <div class="testArea">
            <div class="testName">Jitter</div>
            <canvas id="jitMeter" class="meter"></canvas>
            <div id="jitText" class="meterText"></div>
            <div class="unit">ms</div>
          </div>
        </div>
        <div id="ipArea">
          IP Address: <span id="ip"></span>
        </div>
          <div id="startStopBtn" onclick="startStop()"></div>
        <div id="shareArea" style="display:none">

        </div>
    </div>
  </div>
</div>





