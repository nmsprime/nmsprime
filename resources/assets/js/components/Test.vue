<template>
    <div v-bind:class="'col-md-' + md">
        <div class="panel panel-inverse card-2">
            <div class="panel-body fader" v-bind:style="[{overflow: overflow_y}]">
                <div class="card card-inverse col-12 p-b-5 p-t-10">
                    <div class="card-header m-b-15">
                        <ul class="nav nav-tabs card-header-tabs d-flex" id='tabs'>
                            <li v-for = "tab in tabs"
                                v-bind:key="tab.name"
                                v-bind:class="['nav-item',{show:  currentTab === tab.name}, {'pull-right': tab.name === 'Logging'}]" role="tab" v-on:click="currentTab = tab.name">
                                <a v-bind:id="tab.name + 'tab.name'" v-bind:class="[{active: currentTab === tab.name}]" v-bind:href="'#'+tab" data-toggle="tab">{{tab.name}}</a>
                                <div v-if="relations.length" class="col-lg-4">
                                    <div class="tab-content">
                                        <div v-for="relation in relations[tab.name]" v-bind:class="['tab-pane', {active: currentTab === tab.name}]" v-bind:id="tab.name">

                                        </div>
                                    </div>
                                </div>
                            </li>
                            <!--<li class="nav-item order-12 ml-auto" role="tab" style="float: right">-->
                                <!--<a id="loggingtab" v-bind:class="[{active: currentTab === 'Logging'}]" href="#logging" data-toggle="tab">Logging</a>-->
                            <!--</li>-->
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "Test",
        created() {
        },
        mounted() {
        },
        data: () => {
            return {
            }
        },
        props: {
            currentTab: String,
            tabs: Array,
            height: String,
            overflow_y: String,
            relations: Array,
            display: String,
            md: Number
        },
        computed: {
            currentTabComponent: function () {
                return 'tab-' + this.currentTab.toLowerCase()
            }
        }
    }
</script>

<style scoped>

</style>
