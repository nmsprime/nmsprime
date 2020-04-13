<template>
    <div class="col-lg-4">
        <div class="tab-content">
            <div class="tab-pane active ui-sortable" id="Edit">
                <div class="panel panel-inverse card-2" data-sort-id="Edit-Comment">
                    <div class="panel-heading d-flex flex-row justify-content-between ui-sortable-handle">
                        <h4 class="panel-title d-flex">
                            Comments
                        </h4>
                        <div class="panel-heading-btn d-flex flex-row">
                            <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default d-flex"
                               data-click="panel-expand" style="justify-content: flex-end;align-items: center">
                                <i class="fa fa-expand d-flex"></i>
                            </a>
                            <!--a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-success" data-click="panel-reload"><i class="fa fa-repeat"></i></a-->
                            <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-warning d-flex"
                               data-click="panel-collapse" style="justify-content: flex-end;align-items: center">
                                <i class="fa fa-minus"></i>
                            </a>
                            <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-danger d-flex"
                               data-click="panel-remove" style="justify-content: flex-end;align-items: center">
                                <i class="fa fa-times"></i>
                            </a>
                        </div>
                    </div>
                    <div class="panel-body fader" style="overflow-y:auto; height:100%; ">
                        <textarea class="form-control" placeholder="write a comment..." rows="3"></textarea>
                        <br>
                        <button type="button" class="btn btn-info pull-right">Post</button>
                        <div class="clearfix"></div>
                        <hr>
                        <ul class="media-list">
                            <li class="media">
                                <a href="#" class="pull-left">
                                    <img src="https://bootdey.com/img/Content/user_1.jpg" alt="" class="img-circle">
                                </a>
                                <div class="media-body">
                                <span class="text-muted pull-right">
                                    <small class="text-muted">30 min ago</small>
                                </span>
                                    <strong class="text-success">@MartinoMont</strong>
                                    <p>
                                        Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                                        Lorem ipsum dolor sit amet, <a href="#">#consecteturadipiscing </a>.
                                    </p>
                                </div>
                            </li>
                            <li class="media">
                                <a href="#" class="pull-left">
                                    <img src="https://bootdey.com/img/Content/user_2.jpg" alt="" class="img-circle">
                                </a>
                                <div class="media-body">
                                <span class="text-muted pull-right">
                                    <small class="text-muted">30 min ago</small>
                                </span>
                                    <strong class="text-success">@LaurenceCorreil</strong>
                                    <p>
                                        Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                                        Lorem ipsum dolor <a href="#">#ipsumdolor </a>adipiscing elit.
                                    </p>
                                </div>
                            </li>
                            <li class="media">
                                <a href="#" class="pull-left">
                                    <img src="https://bootdey.com/img/Content/user_3.jpg" alt="" class="img-circle">
                                </a>
                                <div class="media-body">
                                <span class="text-muted pull-right">
                                    <small class="text-muted">30 min ago</small>
                                </span>
                                    <strong class="text-success">@JohnNida</strong>
                                    <p>
                                        Lorem ipsum dolor <a href="#">#sitamet</a> sit amet, consectetur adipiscing elit.
                                    </p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';
    export default {
        name: "Relation",

        created() {
        },

        data: () => {
            return {data: null}
        },
        mounted() {
            axios({
                method: 'get',
                url: '{!! route("customAbility.update") !!}',
                headers: {'X-CSRF-TOKEN': token},
                contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                data: {
                    id: id,
                    roleAbilities: this.roleAbilities,
                    roleForbiddenAbilities: this.roleForbiddenAbilities,
                    changed: this.changed,
                    roleId: '{!! $view_var->id !!}'
                }
            })
                .then(response => {
                    this.data = response.data
                })
                .catch(error => {
                    console.log(error)
                    this.errored = true
                })
                .finally(() => this.loading = false)
        }
    }
</script>

<style scoped>

</style>
