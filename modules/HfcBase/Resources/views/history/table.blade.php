<div id="historytable">
    <h3 class="m-b-20 ">History</h3>
    <div style="height:85vh;max-height:700px;overflow-y:scroll;overflow-x:hidden;">
        <table class="table datatable m-b-0" style="width:100%;">
            <thead>
                <th></th>
                <th data-priority="1">Type</th>
                <th>Output</th>
                <th data-priority="5">Time</th>
            </thead>
            <tbody>
                <tr v-for="data in history" :key="data.statehistory_id">
                    <td></td>
                    <td>
                        <div class="d-flex align-items-baseline">
                            <i class="fa fa-circle" :class="data.last_hard_state >= 2 ? 'text-danger' : (data.last_hard_state == 1 ? 'text-warning' : 'text-success')"></i>
                            <span v-text="data.service == 'clusters_online' ? 'Outage' : 'Proactive'"></span>
                        </div>
                    </td>
                    <td v-text="data.output"></td>
                    <td v-text="data.state_time"></td>
                </tr>
            </tbody>
        </table>
        <div class="d-flex justify-content-center m-t-20">
            <div id="loader" v-show="! init"></div>
        </div>
    </div>
</div>
