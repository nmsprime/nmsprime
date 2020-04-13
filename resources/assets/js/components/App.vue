<template>
    <relation :ticket="ticket"></relation>
</template>
<script>
    import Relation from "./Relation.vue";
    import mixin from '../mixin.js'

    export default {
        name: "App",
        mixin: [mixin],

        components: {Relation},
        created() {
        },
        data: () => {
            return {ticket: null}
        },
        mounted(){
            console.log(window.__INITIAL_STATE__);
        },
        props: {
            initial_state: Object
        }
    }
</script>

<style scoped>

</style>
