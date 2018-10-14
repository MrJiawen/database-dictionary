@extends('database_dictionary::layouts')

@section('js')
    <script>
        $(function () {
            let body = new Vue({
                el: '#body',
                data: {
                    navigation: [],
                    module: [],
                    allTable: [],
                    form: {
                        modal: {
                            title: null,
                            order: null
                        }
                    },
                    plane: {
                        navigation_id: 0,
                        table: [
                            {
                                tableName: null,
                                tableComment: null,
                                tableIsClose: true,
                                moduleId: null,
                                database_dictionary: null,
                                fields: [],
                                helper: {
                                    where: null,
                                    isClose: true,
                                    selectType: null,
                                    response: null,
                                },
                            }
                        ],
                        selectModule: 0
                    }
                },
                methods: {
                    setNavigation: function (navigation) {
                        for (item in navigation) {
                            navigation[item] = {...navigation[item], is_edit: false}
                        }
                        this.navigation = navigation
                    },
                    ajaxToIndex: (data = null, callback = () => {
                    }) => {
                        $.ajax({
                            url: '{{ route('database.dictionary.index') }}',
                            type: 'get',
                            data: data,
                            dataType: 'json',
                            success: (response) => {
                                callback(response)
                            },
                            error: () => {
                                console.log('error in index request')
                            }
                        })
                    },
                    ajaxToStore: (data = null, callback = () => {
                    }) => {
                        $.ajax({
                            url: '{{ route('database.dictionary.store') }}',
                            type: 'post',
                            data: data,
                            dataType: 'json',
                            success: (response) => {
                                callback(response)
                            },
                            error: () => {
                                console.log('error in store request')
                            }
                        })
                    },
                    ajaxToUpdate: (data = null, callback = () => {
                    }) => {
                        $.ajax({
                            url: '{{ route('database.dictionary.update') }}',
                            type: 'post',
                            data: {...data, _method: 'PUT'},
                            dataType: 'json',
                            success: (response) => {
                                callback(response)
                            },
                            error: () => {
                                console.log('error in update request')
                            }
                        })
                    },
                    ajaxToDestroy: (data = null, callback = () => {
                    }) => {
                        $.ajax({
                            url: '{{ route('database.dictionary.destroy') }}',
                            type: 'post',
                            data: {...data, _method: 'DELETE'},
                            dataType: 'json',
                            success: (response) => {
                                callback(response)
                            },
                            error: () => {
                                console.log('error in destroy request')
                            }
                        })
                    },
                    ajaxToGetAllTable: function (callback = () => {
                    }) {
                        $.ajax({
                            url: '{{ route('database.dictionary.table.index') }}',
                            type: 'get',
                            data: [],
                            dataType: 'json',
                            success: (response) => {
                                callback(response)
                            },
                            error: () => {
                                console.log('error in table index request')
                            }
                        })
                    },
                    ajaxToGetTableConstruct: function (data, callback = () => {
                    }) {
                        $.ajax({
                            url: '{{ route('database.dictionary.table.construct') }}',
                            type: 'get',
                            data: data,
                            dataType: 'json',
                            success: (response) => {
                                callback(response)
                            },
                            error: () => {
                                console.log('error in table construct request')
                            }
                        })
                    },
                    ajaxToGetMarkdown: function () {
                        $.ajax({
                            url: '{{ route('database.dictionary.markdown') }}',
                            type: 'get',
                            data: [],
                            dataType: 'json',
                            success: (response) => {
                                alert('success');
                            },
                            error: () => {
                                console.log('error in table construct request')
                            }
                        })
                    },
                    ajaxToHelper: function (data, callback = () => {
                    }) {
                        $.ajax({
                            url: '{{ route('database.dictionary.helper') }}',
                            type: 'get',
                            data: data,
                            dataType: 'json',
                            success: (response) => {
                                callback(response)
                            },
                            error: () => {
                                console.log('error in table helper request')
                            }
                        })
                    },


                    createModule: function () {
                        let that = this;
                        this.ajaxToStore({...this.form.modal, type: 1}, function () {
                            that.ajaxToIndex({type: 1}, function (response) {
                                that.setNavigation(response);
                            });
                            that.form.modal.title = null;
                            that.form.modal.order = null;
                        })
                    },
                    deleteModule: function (id) {
                        let that = this;
                        this.ajaxToDestroy({id: id}, function () {
                            that.ajaxToIndex({type: 1}, function (response) {
                                that.setNavigation(response);
                            });
                        })
                    },
                    moduleChangeToEdit: function (key) {
                        this.navigation[key].is_edit = !this.navigation[key].is_edit;
                    },
                    updateModule: function (key) {
                        let that = this;
                        this.ajaxToUpdate(this.navigation[key], function () {
                            that.ajaxToIndex({type: 1}, function (response) {
                                that.setNavigation(response);
                            });
                        });
                    },
                    selectNavigation: function (navigation_id = 0) {
                        this.plane.navigation_id = navigation_id;
                        this.plane.table = [];
                        if (navigation_id == 0) {
                            for (item in this.allTable) {
                                this.$set(this.plane.table, item, {
                                    tableName: this.allTable[item].TABLE_NAME,
                                    tableComment: this.allTable[item].TABLE_COMMENT,
                                    tableIsClose: true,
                                    moduleId: null,
                                    database_dictionary: null,
                                    fields: [],
                                    helper: {
                                        where: null,
                                        isClose: true,
                                        selectType: null,
                                        response: null,
                                    },
                                })
                            }
                        } else {
                            let that = this;
                            this.ajaxToIndex({
                                father_id: navigation_id,
                                type: 2
                            }, function (response) {
                                for (item in response) {
                                    that.$set(that.plane.table, item, {
                                        tableName: response[item].table_data.TABLE_NAME,
                                        tableComment: response[item].table_data.TABLE_COMMENT,
                                        tableIsClose: true,
                                        moduleId: null,
                                        database_dictionary: response[item],
                                        fields: [],
                                        helper: {
                                            where: null,
                                            isClose: true,
                                            selectType: null,
                                            response: null,
                                        },
                                    })
                                }
                            });
                        }
                    },
                    tablePlaneIsCollapsed: function (key) {
                        /**
                         * 如果是第一次直接 打开了 hepler  但是没有 field 时候， 此刻是加载 字段，不进行 折叠操作。其余情境正常流程
                         */
                        if (!(!this.plane.table[key].tableIsClose && this.plane.table[key].fields.length == 0)) {
                            this.plane.table[key].tableIsClose = !this.plane.table[key].tableIsClose;
                        }
                        let that = this;
                        if (!this.plane.table[key].tableIsClose) {
                            this.ajaxToGetTableConstruct({tableName: this.plane.table[key].tableName}, function (response) {
                                that.$set(that.plane.table[key], 'fields', response);
                            })
                        } else {
                            this.plane.table[key].helper.isClose = true;
                        }
                    },
                    tableAddToModule: function (tableInfo) {
                        let that = this;
                        this.ajaxToStore({
                            title: tableInfo.tableName,
                            father_id: tableInfo.moduleId,
                            type: 2
                        }, function () {
                            that.ajaxToGetAllTable(function (response) {
                                that.allTable = response;
                                that.selectNavigation();
                            })
                        })
                    },
                    deleteTable: function (id) {
                        let that = this;
                        this.ajaxToDestroy({id: id}, function () {
                            that.selectNavigation(that.plane.navigation_id);
                        })
                    },
                    tableChangeOrder: function (databaseDictionary) {
                        let that = this;
                        this.ajaxToUpdate(databaseDictionary, function () {
                            that.selectNavigation(that.plane.navigation_id)
                        });
                    },
                    getHelper: function (item, type = null) {
                        if (type !== null) {
                            item.helper.selectType = type;
                            item.helper.isClose = false;
                            item.tableIsClose = false;
                        }
                        this.ajaxToHelper({tableName: item.tableName, ...item.helper}, function (response) {
                            if (response.ServerNo == 200) {
                                item.helper.response = response.ServerData
                            } else {
                                item.helper.response = response.ServerMsg
                            }
                        })
                    },
                },
                mounted: function () {
                    let that = this;
                    this.ajaxToIndex({type: 1}, function (response) {
                        that.setNavigation(response);
                    });

                    this.ajaxToGetAllTable(function (response) {
                        that.allTable = response;
                        that.selectNavigation();
                    })
                }
            });

//            $('.bs-example-modal-lg').modal('show')
        })
    </script>
@endsection