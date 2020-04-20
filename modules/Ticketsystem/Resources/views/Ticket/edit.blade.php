@extends ('Layout.split-nopanel')

@section('content_top')

    {!! $headline !!}

@stop
@php($edit_left_md_size = 6)

@section('content_left')
    @include ('Generic.logging')
    <?php
    $blade_type = 'relations';
    ?>

    @include('Generic.above_infos')
    {!! Form::model($view_var, ['route' => [$form_update, $view_var->id], 'method' => 'put', 'files' => true, 'id' => 'EditForm']) !!}

    @include($form_path, $view_var)

    {{ Form::close() }}

@stop
{{--@php(dd($model_name, $view_var, $view_header, $form_path, $form_fields, $headline, $tabs, $relations, $action, $additional_data))--}}

{{--@section('content_left')--}}
{{--@parent--}}
{{--@endsection--}}

@section('content_right')
    <div id="app" class="col-lg-6">
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
                        <textarea v-model="new_comment" class="form-control" placeholder="write a comment..." rows="4" style="font-size: 16px"></textarea>
                        <br>
                        <button v-on:click="save(view_var.id)" type="button" class="btn btn-primary m-r-5 m-t-15 pull-right" style="simple" name="_save" value="1" title="">
                            <i class="fa fa-save fa-lg m-r-10" aria-hidden="true"></i>
                            Save</button>
                        <div class="clearfix"></div>
                        <hr>
                        <ul class="media-list">
                            <li v-for="comment in comments" class="media">
                                <img src="{{asset('images/support-avatar.png')}}" width="50" alt="" class="mr-3 rounded-circle">
                                <div class="media-body">
                                <span class="text-muted pull-right">
                                    <small class="text-muted">@{{comment.created_at}}</small>
                                </span>
                                    <strong class="text-success">@{{ comment.user.first_name }} @{{ comment.user.last_name }}</strong>
                                    <p v-text="comment.comment">@{{ comment.comment }} <a href="#">#consecteturadipiscing </a>.
                                    </p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Alert --}}
    @if (Session::has('alert'))
        @foreach (Session::get('alert') as $notif => $message)
            @include('bootstrap.alert', array('message' => $message, 'color' => $notif))
            <?php Session::forget("alert.$notif"); ?>
        @endforeach
    @endif

@stop
@section ('javascript_extra')
    @if (Module::collections()->has('PropertyManagement'))
        @include('provbase::Contract.hideAddress')
    @endif

    <script type="text/javascript">
        window.__INITIAL_STATE__ = "{!! addslashes(json_encode($view_var)) !!}";

        new Promise(function (resolve, reject) {

            document.getElementById("app").style.display = "block";
                resolve();

            }).then(function () {
            new Vue({
                el: document.getElementById("app"),
                data() {
                    return {
                        view_var: {!! $view_var !!},
                        comments: null,
                        current_user: {!!  Auth::user()->toJson() !!},
                        new_comment: ''
                    }
                },
                mounted() {
                    let self = this;
                    axios({
                        method: 'get',
                        url: '/admin/api/v1/Comment',
                        contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                        params: {filter_groups: [{filters: [{key: 'ticket_id', value: self.view_var.id, operator: 'eq', not: false}],or: false}], includes: ['user'], sort: [{key: 'created_at', direction: 'desc'}]}
                    }).then(function (response) {
                            self.comments = response.data
                        })
                        .catch(function (error) {
                            alert(error);
                        });
                },
                methods: {
                    save(ticket_id) {
                        let self = this;
                        axios({
                            method: 'post',
                            url: '/admin/api/v1/Comment',
                            contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                            data: {ticket_id: ticket_id, comment: this.new_comment, user_id: self.current_user.id}
                        })
                            .then(function (response) {
                               created_comment = response.data;
                               created_comment.user = self.current_user;
                               self.comments.unshift(created_comment);
                                self.new_comment = '';
                            })
                            .catch(function (error) {
                                alert(error);
                            });

                    }
                }
            })
        });
    </script>
@stop
