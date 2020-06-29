<div id="ticket-comments" style="display: none">
    <div class="panel-body fader" style="overflow-y:auto; height:100%; ">
        <textarea v-model="new_comment" class="form-control" placeholder="write a comment..." rows="4"
                  style="font-size: 16px"></textarea>
        <br>
        <button v-on:click="save(view_var.id)" type="button" class="btn btn-primary m-r-5 m-t-15 pull-right"
                name="_save" value="1" title="">
            <i class="fa fa-save fa-lg m-r-10" aria-hidden="true"></i>
            Save
        </button>
        <div class="clearfix"></div>
        <hr>
        <ul class="media-list">
            <li v-for="comment in comments" class="media">
                <img src="{{asset('images/support-avatar.png')}}" width="30" alt="" class="mr-3 rounded-circle">
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

@section('javascript_extra')
    <script src="{{asset('components/assets-admin/plugins/vue/dist/vue.min.js')}}"></script>


    {{-- When in Development use this Version
        <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
    --}}


    <script src="{{asset('components/assets-admin/plugins/Abilities/axios.min.js')}}"></script>
    <script type="text/javascript">
        new Promise(function (resolve, reject) {
            document.getElementById("ticket-comments").style.display = "block";
            resolve();
        }).then(function () {
            new Vue({
                el: document.getElementById("ticket-comments"),
                data() {
                    return {
                        view_var: {!! $view_var !!},
                        comments: null,
                        current_user: {!!  Auth::user()->toJson() !!},
                        new_comment: ''
                    }
                },
                mounted() {
                    this.getComments();
                },
                methods: {
                    save(ticket_id) {
                        let self = this;
                        if (self.new_comment === '') {
                            alert('No blank comments are allowed!');
                            return;
                        }
                        axios({
                            method: 'post',
                            url: '/admin/api/v1/Comment',
                            contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                            data: {ticket_id: ticket_id, comment: this.new_comment, user_id: self.current_user.id}
                        })
                            .then(function (response) {
                                self.getComments();
                                self.new_comment = '';
                            })
                            .catch(function (error) {
                                alert(error);
                            });
                    },
                    getComments() {
                        let self = this;
                        axios({
                            method: 'get',
                            url: '/admin/api/v1/Comment',
                            contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                            params: {
                                filter_groups: [{
                                    filters: [{
                                        key: 'ticket_id',
                                        value: self.view_var.id,
                                        operator: 'eq',
                                        not: false
                                    }], or: false
                                }], includes: ['user'], sort: [{key: 'created_at', direction: 'desc'}]
                            }
                        }).then(function (response) {
                            self.comments = response.data
                        }).catch(function (error) {
                            alert(error);
                        });
                    }
                }
            })
        });
    </script>
@stop
