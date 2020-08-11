
<script src="{{asset('components/assets-admin/plugins/Abilities/es6-promise.auto.min.js')}}"></script>
<script src="{{asset('components/assets-admin/plugins/Abilities/lodash.core.min.js')}}"></script>
<script src="{{asset('components/assets-admin/plugins/vue/dist/vue.min.js')}}"></script>
{{-- When in Development use this Version
    <script src="{{asset('components/assets-admin/plugins/vue/dist/vue.js')}}"></script>
--}}
<script src="{{asset('components/assets-admin/plugins/Abilities/axios.min.js')}}"></script>
<script src="{{asset('components/assets-admin/plugins/vue-snotify/snotify.min.js')}}"></script>

<script type="text/javascript">
    let storage = Vue.observable({
        request: {}
    })

    new Vue({
        el: '#historytable',
        data() {
            return {
                init: false,
                history: {}
            }
        },
        mounted() {
            let self = this;

            axios({
                method: 'get',
                url: "{{ route('IcingaStateHistory.table', $withHistory ?? null) }}",
                headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
            })
            .then(function (response) {
                storage.request = response.data
                self.history = storage.request.table
                self.init = true
                self.$nextTick(function () {
                    self.initDatatables()
                })
            })
            .catch(function (error) {
                self.$snotify.error(error.message)
                self.init = true
            })
        },
        computed: {
            isWideScreen: function() {
                return window.matchMedia('(min-width: 1700px)').matches
            }
        },
        methods: {
            initDatatables: function() {
                $('table.datatable').DataTable({
                    @include('datatables.lang')
                    responsive: {
                        details: {
                            type: 'column', {{-- auto resize the Table to fit the viewing device --}}
                        }
                    },
                    autoWidth: false, {{-- Option to ajust Table to Width of container --}}
                    dom: 'ltp', {{-- sets order and what to show  --}}
                    stateSave: true, {{-- Save Search Filters and visible Columns --}}
                    stateDuration: 0, // 60 * 60 * 24, {{-- Time the State is used - set to 24h --}}
                    lengthMenu:  [ [10, 25, 100, -1], [10, 25, 100, "{{ trans('view.jQuery_All') }}" ] ], {{-- Filter to List # Datasets --}}
                    columnDefs: [
                        { responsivePriority: 1, targets: 1 },
                        { responsivePriority: 2, targets: -1 }
                    ],
                    aoColumnDefs: [ {
                            className: 'control',
                            orderable: false,
                            searchable: false,
                            targets:   [0]
                        },
                        {
                            defaultContent: "",
                            targets: "_all"
                        }
                    ]
                })
            }
        }
    })

    new Vue({
        el: '#historyslider',
        data() {
            return {
                date: 'Select a date'
            }
        },
        computed: {
            slider_power: function() {
                return storage.request.slider_power
            },
            slider_online: function() {
                return storage.request.slider_online
            }
        },
        methods: {
            enter: function (event, index) {
                event.target.style.cssText = 'width:2.5%;height:45px;border: 2px solid white;box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);'
                this.date = index
            }
        }
    })
</script>
