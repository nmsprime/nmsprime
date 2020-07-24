<div id="troubleDash">
    <vue-snotify></vue-snotify>
    <div v-show="init" class="d-flex justify-content-between m-b-15" style="display:none;">
        <div class="d-flex">
            <div>Filter:</div>
        </div>
        <div>
            <div v-if="filter == 1" v-on:click="filter = filter - 1" style="cursor: pointer;" class="badge badge-pill badge-light m-r-5">show impaired Elements</div>
            <div v-if="filter == 0" v-on:click="filter = filter + 1" style="cursor: pointer;" class="badge badge-pill badge-dark m-r-5">show all</div>
            <div v-if="!showMuted" v-on:click="showMuted = !showMuted" style="cursor: pointer;" class="badge badge-pill badge-light m-r-10">with muted</div>
            <div v-if="showMuted" v-on:click="showMuted = !showMuted" style="cursor: pointer;" class="badge badge-pill badge-dark m-r-10">only muted</div>
            <a href="{{ route('Config.index') }}#settings-hfc" class="m-r-15"><i class="fa fa-lg fa-cog" title="settings"></i></a>
        </div>
    </div>

    <div class="height-lg" style="overflow-y:scroll;overflow-x:hidden;">
        <table class="table m-b-0" style="width:100%;">
            <thead>
                <tr>
                    <th class="d-table-cell position-sticky fixed-top"></th>
                    <th class="d-none d-lg-table-cell position-sticky fixed-top" width="100px">Severity</th>
                    <th class="d-table-cell position-sticky fixed-top">Type</th>
                    <th class="d-table-cell position-sticky fixed-top">Size</th>
                    <th class="d-table-cell position-sticky fixed-top">Host</th>
                    <th class="d-none d-wide-table-cell position-sticky fixed-top">Detected</th>
                    <th class="d-table-cell position-sticky fixed-top">Status</th>
                    <th class="d-none d-sm-table-cell position-sticky fixed-top text-center">Actions</th>
                </tr>
            </thead>
            <tbody v-show="init">
                <template v-for="netelement in impaired">
                    <tr :key="netelement.id"
                        v-show="(hostAcknowledged(netelement) && (netelement.severity >= filter && netelement.partiallyImpaired)) || (hostAcknowledged(netelement) && netelement.hasMutedServices)"
                        >
                        <td style="cursor: pointer" v-on:click="setCollapseState(netelement)"><i class="fa" :class="'fa-' + (collapsed.includes(netelement.id) ? 'minus' : 'plus')"></i></td>
                        <td :class="colors[netelement.severity]"
                            class='f-s-13 d-none d-lg-table-cell'
                            style="cursor: pointer;"
                            v-text="netelement.severity >= 3 ? 'CRITICAL' : (netelement.severity == 2 ? 'MAJOR' :(netelement.severity == 1 ? 'MINOR' : 'OK'))"
                            v-on:click="setCollapseState(netelement)"
                            >
                        </td>
                        <td style="cursor: pointer;"
                            v-on:click="setCollapseState(netelement)"
                            v-text="netelement.criticalModems > netelement.offlineModems ? 'Proactive' : ((netelement.offlineModems) > 0 ? 'Outage' : '')"
                            >
                        </td>
                        <td style="cursor: pointer;"
                            v-on:click="setCollapseState(netelement)"
                            >
                            <div v-if="(netelement.offlineModems + netelement.criticalModems) > 0">
                                @{{ netelement.offlineModems + netelement.criticalModems }}/@{{ netelement.allModems }}
                            </div>
                        </td>
                        <td class='f-s-13 breakAll'>
                            <a :href="netelement.controllingLink" target="_blank">
                                <i class="fa fa-wrench"></i>
                                <span v-text="netelement.name"></span>
                            </a>
                        </td>
                        <td class='f-s-13 d-none d-wide-table-cell'
                            style="cursor: pointer;"
                            v-on:click="setCollapseState(netelement)"
                            v-text="netelement.last_hard_state_change"
                            >
                        </td>
                        <td class='f-s-13 breakAll'
                            v-on:click="setCollapseState(netelement)"
                            v-text="netelement.isProvisioningSystem ? '' : (acknowledged[netelement.icinga_object.object_id] ? 'Muted' : (netelement.hasMutedServices ? 'partially Muted' : 'Open'))">
                        </td>
                        <td class="d-none d-sm-table-cell" max-width="180px" >
                            <div class="d-flex align-items-center justify-content-end">
                                <a :href="netelement.mapLink" target="_blank" class="btn btn-light p-5 m-l-10"><i class="fa fa-lg fa-map"></i></a>
                                <a :href="netelement.ticketLink" target="_blank" class="btn btn-light p-5 m-l-10"><i class="fa fa-lg fa-ticket"></i></a>
                                <form method="POST"
                                    style="position:relative;"
                                    v-on:submit.prevent="mute(netelement, $event)"
                                    v-if="!netelement.isProvisioningSystem"
                                    :object-id="netelement.icinga_object.object_id"
                                    :action="netelement.acknowledgeLink">
                                    <div v-if="!acknowledged[netelement.icinga_object.object_id]">
                                        <div class="btn btn-light p-5 m-l-10" style="cursor: pointer;" v-on:click="showMuteDialog(netelement.icinga_object.object_id)">
                                            <i class="fa fa-lg"
                                                :class="loading[netelement.icinga_object.object_id] ? 'fa-circle-o-notch fa-spin' : 'fa-bell-slash-o'">
                                            </i>
                                        </div>
                                        <div v-if="showMuteForm.includes(netelement.icinga_object.object_id)"
                                            class="d-flex card-2"
                                            style="position:absolute;right:20px;padding:10px;background-color:white;z-index:100;"
                                            >
                                            <input class="m-r-5" style="width:50px;" v-model="duration" type="number" max=999 v-if="!durationType.includes('inf')"></input>
                                            <select class="m-r-5" v-model="durationType">
                                                <option disabled value="">Please select Duration</option>
                                                <option value="minutes">Minutes</option>
                                                <option value="hours">Hours</option>
                                                <option value="days">Days</option>
                                                <option value="inf">Infinite</option>
                                                </select>
                                            <button class="m-r-5" type="submit" class="btn">Mute</button>
                                            <div class="m-l-5 d-flex align-items-center" style="cursor: pointer;" v-on:click="hideMuteDialog(netelement.icinga_object.object_id)">
                                                <i class="fa fa-times"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else>
                                        <button class="btn btn-light p-5 m-l-10" type="submit" class="btn">
                                            <i class="fa fa-lg"
                                                :class="loading[netelement.icinga_object.object_id] ? 'fa-circle-o-notch fa-spin' : 'fa-bell-o'">
                                            </i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <tr v-for="(service, index) in netelement.icingaServices"
                        :key="service.service_object_id"
                        v-show="serviceAcknowledged(service, netelement) && collapsed.includes(netelement.id) && service.last_hard_state >= serviceFilter"
                        >
                        <td class="d-none d-sm-table-cell" colspan="3">
                            <i class="fa fa-circle p-r-10" :class="'text-' + serviceColors[(service.last_hard_state)]"></i> @{{ service.icinga_object.name2 }}</td>
                        <td class="d-lg-none" colspan="2" class="p-20" v-text="service.check_command"></td>
                        <td class="d-none d-lg-table-cell  p-10"
                            :class="isWideScreen ? 'd-wide-table-cell' : 'd-wide-none'"
                            :colspan="isWideScreen ? 3 : 2"
                            >
                            <div v-if="service.additionalData.length">
                                <div v-for="detail in service.additionalData">
                                    <div v-if="detail.hasOwnProperty('per')"
                                        class=" d-flex-md align-items-center progress progress-striped m-b-0"
                                        style="position:relative;">
                                        <div class="progress-bar" :class="'progress-bar-' + serviceColors[(service.last_hard_state)]"
                                            style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" :style="'width:' + detail.per + '%;'">
                                            <div class='text-inverse' style="position:absolute;width:auto;left:40px;" v-text="detail.text">
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else>
                                        <span v-text="detail.text"></span>: <span v-text="detail.val"></span>
                                    </div>
                                </div>
                            </div>
                            <div v-else v-text="service.output">
                            </div>
                        </td>
                        <td class="d-none d-sm-table-cell">
                            <div v-if="service.icinga_object.name2.includes('clusters_online')">
                                @{{ netelement.modems_count - netelement.modems_online_count}}/@{{ netelement.modems_count }} offline
                            </div>
                            <div v-else-if="service.icinga_object.name2.includes('clusters_power')">
                                @{{ netelement.modems_critical_count}}/@{{ netelement.modems_count }} critical
                            </div>
                            <div v-else>
                                @{{ service.last_hard_state_change }}
                            </div>
                        </td>
                        <td class="d-none d-sm-table-cell" align="right">
                            <div class="d-flex align-items-center justify-content-end">
                                <!-- <a href="#" target="_blank" class="btn btn-light p-5 m-l-10"><i class="fa fa-lg fa-hdd-o"></i></a> -->
                                <a :href="service.ticketLink" target="_blank" class="btn btn-light p-5 m-l-10"><i class="fa fa-lg fa-ticket"></i></a>
                                <a :href="service.icingaLink" target="_blank" class="btn btn-light p-5 m-l-10">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" version="1.1" >
                                        <symbol id="icinga2symbol" viewBox="-5 -5 105 105" v-if="index == 1">
                                            <path d="m 40.704846,40.305609 0,0 12.22301,-25.1583 m -20.21584,28.9132 0,0 -20.59136,-16.8982 m 26.61011,23.7512 0,0 14.00908,23.4685 m -14.95037,-24.5016 0,0 50.21059,-12.3916 m -50.21059,12.3916 0,0 -24.25801,34.7343" style="fill:none;stroke:#000000;stroke-width:1.2216469;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1" id="path3906"/>
                                            <path d="m 26.601396,35.704509 0,0 c 7.05279,-5.7261 17.39572,-4.693 23.13121,2.3477 5.73549,7.0407 4.70145,17.3659 -2.35135,23.0933 -7.05154,5.6323 -17.39447,4.5991 -23.12996,-2.4416 -5.73549,-6.9468 -4.60744,-17.2721 2.3501,-22.9994 z m 23.13121,-33.2309002 0,0 c 3.6674,-2.91059997 9.02688,-2.34779997 12.035,1.2195 3.00938,3.661 2.44535,9.0119002 -1.22205,12.0163002 -3.6674,3.0044 -9.02688,2.4403 -12.035,-1.2208 -3.00938,-3.661 -2.44535,-9.0119002 1.22205,-12.0150002 z m 30.37077,34.6393002 0,0 c -0.28202,-3.6611 2.5381,-6.8531 6.2055,-7.1345 3.76141,-0.2815 6.95754,2.4403 7.23955,6.1026 0.28201,3.7548 -2.5381,6.9456 -6.2055,7.2283 -3.66741,0.2814 -6.95754,-2.4416 -7.23955,-6.1964 z m -72.4951504,-7.416 0,0 c -1.1283,-2.3464 -0.18801,-5.3508 2.25659,-6.4778 2.4447304,-1.2195 5.3596004,-0.1876 6.5816504,2.2539 1.22205,2.3465 0.18801,5.3509 -2.25609,6.4766 -2.44498,1.2208 -5.3598504,0.1876 -6.5821504,-2.2527 l 0,0 z m 41.7483704,44.9658 0,0 c 0.188,-1.8774 1.88007,-3.2858 3.76015,-3.0969 1.88133,0.2814 3.29139,1.9712 3.00938,3.8487 -0.18801,1.8774 -1.88008,3.192 -3.76141,3.0031 -1.88008,-0.1876 -3.29014,-1.8774 -3.00812,-3.7549 l 0,0 z m -48.7063704,11.6411 0,0 c -0.47013,-6.1026 4.04313,-11.3596 10.1548804,-11.8287 6.11138,-0.469 11.37685,4.1314 11.84687,10.1389 0.47127,6.1013 -4.04216,11.4527 -10.1543,11.922 -6.1117504,0.3755 -11.4713504,-4.1309 -11.8474504,-10.2322 z" style="fill:#000000;fill-opacity:1;fill-rule:nonzero;stroke:none" id="path4016"/>
                                        </symbol>
                                        <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#icinga2symbol"></use>
                                    </svg>
                                </a>
                                <form method="POST"
                                    v-on:submit.prevent="mute(service, $event)"
                                    :object-id="service.service_object_id"
                                    :action="service.acknowledgeLink">
                                    <button type="submit" class="btn btn-light p-5 m-l-10">
                                        <i class="fa fa-lg"
                                            :class="loading[service.service_object_id] ? 'fa-circle-o-notch fa-spin' : (acknowledged[service.service_object_id] ? 'fa-bell-o' : 'fa-bell-slash-o')">
                                        </i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
        <div class="d-flex justify-content-center m-t-20">
            <div id="loader" v-show="! init"></div>
        </div>
    </div>
</div>
