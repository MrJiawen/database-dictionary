<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">

    {{--如果是 全局公共厂库--}}

    <template v-for="(item,key) in plane.table">
        <div class="panel panel-success">
            <div class="panel-heading" style="padding: 7px 15px">
                <div class="row">
                    <div class="col-xs-6">
                        <h4 class="panel-title" style="line-height: 30px">
                            <a role="button" href="javascript:" @click="tablePlaneIsCollapsed(key)">
                                @{{ key + 1 }} . @{{ item.tableName }} --@{{ item.tableComment }}</a>
                        </h4>
                    </div>

                    <div class="col-xs-6">
                        <div class="row" v-if="plane.navigation_id === 0">
                            <div class="col-xs-offset-8 col-xs-4">
                                <select class="form-control pull-right input-sm" v-model="item.moduleId"
                                        @change="tableAddToModule(item)">
                                    <option v-for="option in navigation" :value="option.id">@{{ option.title }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="row text-center" v-else>
                            <div class="col-xs-offset-1 text-right col-xs-8">
                                <button type="button" class="btn btn-success  btn-sm"
                                        @click="getHelper(item,'swagger')">swagger3.0
                                </button>
                                <button type="button" class="btn btn-success  btn-sm" @click="getHelper(item,'json')">
                                    json
                                </button>
                                <button type="button" class="btn btn-success  btn-sm"
                                        @click="getHelper(item,'phpArray')">phpArray
                                </button>
                                <button type="button" class="btn btn-success  btn-sm"
                                        @click="item.helper.isClose=true;item.tableIsClose=true">关闭
                                </button>
                                <button type="button" class="btn btn-success  btn-sm"
                                        @click="deleteTable(item.database_dictionary.id)">移除
                                </button>
                            </div>
                            <div class="col-xs-3">
                                <input name="title" type="number" v-model="item.database_dictionary.order"
                                       @change="tableChangeOrder(item.database_dictionary)"
                                       class="form-control input-sm">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <transition name="plane-fade">
                <div class="panel-body" v-show="item.tableIsClose !== true">
                    <table class="table table-hover">
                        <tr v-show="item.fields.length !=0">
                            <th>Field</th>
                            <th>Type</th>
                            <th>Null</th>
                            <th>Key</th>
                            <th>Default</th>
                            <th>Extra</th>
                            <th>Comment</th>
                        </tr>
                        <template v-for="(value, key) in item.fields">
                            <tr>
                                <td>@{{ value.Field }}</td>
                                <td>@{{ value.Type }}</td>
                                <td>@{{ value.Null }}</td>
                                <td>@{{ value.Key }}</td>
                                <td>@{{ value.Default }}</td>
                                <td>@{{ value.Extra }}</td>
                                <td>@{{ value.Comment }}</td>
                            </tr>
                        </template>
                    </table>
                    <div class="jumbotron" v-show="!item.helper.isClose">
                        <div class="row">
                            <div class="col-xs-4">
                                <h3>辅助面板 - 获取 @{{ item.helper.selectType }} 内容</h3>
                            </div>
                            <div class="col-xs-4">
                                <h3>
                                    <small><a class="text-success" @click="getHelper(item,'swagger')">swagger3.0</a>
                                    </small>
                                    <small><a class="text-success" @click="getHelper(item,'json')">json</a></small>
                                    <small><a class="text-success" @click="getHelper(item,'phpArray')">phpArray</a>
                                    </small>
                                    <small><a class="text-success" @click="item.helper.isClose=true">关闭</a></small>
                                </h3>
                            </div>
                            <div class="col-xs-4">
                                <form class="form-horizontal" style="padding-top: 20px;margin-bottom:0;">
                                    <div class="form-group" style="margin-bottom:0;">
                                        <label class="col-sm-6 control-label">主键过滤:</label>
                                        <div class="col-sm-6">
                                            <input class="form-control" placeholder=""
                                                   v-model="item.helper.where"
                                                   @change="getHelper(item)"
                                            >
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <hr>
                        <pre v-show="item.helper.response">@{{ item.helper.response }}</pre>
                    </div>
                </div>
            </transition>
        </div>

    </template>
</div>