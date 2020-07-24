<script src="{{asset('components/assets-admin/plugins/Abilities/es6-promise.auto.min.js')}}"></script>
<script src="{{asset('components/assets-admin/plugins/vue/dist/vue.min.js')}}"></script>
{{-- When in Development use this Version
    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
--}}
<script src="{{asset('components/assets-admin/plugins/Abilities/lodash.core.min.js')}}"></script>
<script src="{{asset('components/assets-admin/plugins/Abilities/axios.min.js')}}"></script>
<script src="{{asset('components/assets-admin/plugins/vue-snotify/snotify.min.js')}}"></script>

<script type="text/javascript">
function handlePanelPositionToPreventCrash() {
    return new Promise(function(resolve, reject) {
        let targetPage = window.location.href;
            targetPage = targetPage.split('?');
            targetPage = targetPage[0];
        let panelPositionData = localStorage.getItem(targetPage) ? targetPage : "{!! isset($view_header) ? $view_header : 'undefined'!!}";

        if (panelPositionData) {
            localStorage.removeItem(panelPositionData)
        }

        resolve();
    });
}

handlePanelPositionToPreventCrash().then(function() {
new Vue({
    el: '#troubleDash',
    data() {
        return {
            init: false,
            showMuted: false,
            showMuteForm: [],
            duration: '',
            durationType: '',
            colors: ['success', 'info', 'warning', 'danger'],
            serviceColors: ['success', 'warning', 'danger', 'danger'],
            collapsed: [],
            acknowledged: {},
            loading: {},
            impaired: {},
            window: window,
            filter: 1,
            duration: ''
        }
    },
    mounted() {
        let self = this;
        this
        axios({
            method: 'get',
            url: "{{ route('TroubleDashboard.data') }}",
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
        })
        .then(function (response) {
            self.impaired = response.data
            self.init = true
        })
        .catch(function (error) {
            self.$snotify.error(error.message)
            self.init = true
        })
    },
    computed: {
        isWideScreen: function() {
            return window.matchMedia('(min-width: 1700px)').matches
        },
        serviceFilter: function() {
            return 1
        }
    },
    methods: {
        mute: function(element, event) {
            let self = this
            let requestId = event.target.getAttribute('object-id')
            this.loading[requestId] = true
            this.loading = _.clone(this.loading)
            self.hideMuteDialog(requestId)

            axios({
                method: 'post',
                url: event.target.getAttribute('action'),
                headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                data: {}
            })
            .then(function (response) {
                if (requestId != response.data.id) {
                    throw "Invalid Id Error";
                }

                self.acknowledged[response.data.id] = !self.acknowledged[response.data.id]
                self.acknowledged = _.clone(self.acknowledged)

                self.loading[response.data.id] = false
                self.loading = _.clone(self.loading)

                self.$snotify.success(response.data.results[0].status, 'Success')
            })
            .catch(function (error) {
                console.log(error)
                self.loading = {}
                self.$snotify.error(
                    'Did you configure Icinga2 correctly? Are API username and password set correctly in NMS Prime? Statuscode: ' + error.status,
                    'There was an Error processing your request!'
                )
            })
        },
        hostAcknowledged: function(netelement) {
            if (this.acknowledged.hasOwnProperty(netelement.icinga_object.object_id)) {
                return this.acknowledged[netelement.icinga_object.object_id] ||
                (_.filter(netelement.icingaServices, (service) => { return this.acknowledged[service.service_object_id] }).length )
            }

            if (netelement.icinga_object.host_status.problem_has_been_acknowledged ||
                _.filter(netelement.icingaServices, (service) => { return service.problem_has_been_acknowledged }).length ) {
                this.acknowledged[netelement.icinga_object.object_id] = true
            }

            return (this.acknowledged[netelement.icinga_object.object_id] && this.showMuted) ||
                (!this.acknowledged[netelement.icinga_object.object_id] && !this.showMuted)
        },
        serviceAcknowledged: function(service, netelement) {
            if (this.acknowledged.hasOwnProperty(service.service_object_id)) {
                return this.acknowledged[service.service_object_id] || this.hostAcknowledged(netelement)
            }

            if (service.problem_has_been_acknowledged) {
                this.acknowledged[service.service_object_id] = true
            }

            return this.hostAcknowledged(netelement)
        },
        setCollapseState: function(netelement) {
            if (this.collapsed.includes(netelement.id)) {
                let index = this.collapsed.indexOf(netelement.id)
                this.collapsed.splice(index, 1)
                return
            }

            this.collapsed.push(netelement.id)
        },
        showMuteDialog: function(id) {
            if (this.acknowledged[id]) {
                return
            }

            this.showMuteForm.splice(0, 0, id)
        },
        hideMuteDialog: function(id) {
            let index = this.showMuteForm.indexOf(id)
            this.showMuteForm.splice(index, 1)
        }
    }
})
})

</script>
