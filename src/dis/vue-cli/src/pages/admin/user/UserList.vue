<template>
    <a-card>
        <a-row>
            <div v-for="(item,index) in search" :key="index">
                <a-col :lg="8" :md="12" :sm="24" v-if="shrink && item.shrink || !item.shrink">
                    <a-form-item
                        :label="item.title"
                        :labelCol="{span: 4}"
                        :wrapperCol="{span: 18, offset: 1}">
                        <a-input v-model="item.value" placeholder="请输入"/>
                    </a-form-item>
                </a-col>
            </div>
            <a-col :lg="8" :md="12" :sm="24">
                <a-form-item
                    label="时间"
                    :labelCol="{span: 4}"
                    :wrapperCol="{span: 18, offset: 1}">
                    <time-range-selection v-model="range_time"></time-range-selection>
                </a-form-item>
            </a-col>
        </a-row>
        <div style="display: flex;justify-content: center;">
            <a @click="shrink = !shrink">
                {{ shrink ? '收起' : '展开' }}
                <a-icon :type="shrink ? 'up' : 'down'"/>
            </a>
        </div>
        <a-row type="flex" justify="space-between">
            <a-col style="margin-top: 10px;">
                <a-button type="primary" style="margin-right: 5px;" @click="created" v-auth="authorize.created">新建</a-button>
                <UserAdd ref="UserAdd" @addComplete="searchClick"></UserAdd>
                <a-dropdown v-if="selectedRows.length > 0">
                    <a-menu slot="overlay">
                        <a-menu-item @click="destory" key="delete" v-auth="authorize.destory">删除</a-menu-item>
                    </a-menu>
                    <a-button>
                        批量操作
                        <a-icon type="down"/>
                    </a-button>
                </a-dropdown>
            </a-col>
            <a-col style="margin-top: 10px;">
                <a-button @click="searchClick" type="primary">查询</a-button>
                <a-button @click="resetClick" style="margin-left: 8px">重置</a-button>
            </a-col>
        </a-row>
        <standard-table
            style="margin-top: 10px;"
            :loading="loading"
            :columns="columns"
            :pagination="pagination"
            row-key="id"
            :selectedRows.sync="selectedRows"
            :dataSource="dataSource"
            @change="onChange"
        >
            <!--     slot-scope="{record}"        -->
            <div slot="description" slot-scope="{record}">
                {{ record.name }}
            </div>
            <div slot="action" slot-scope="{record}">
                <a style="margin-right: 8px" @click="edit(record.id)" v-auth="authorize.edit">
                    <a-icon type="edit"/>
                    编辑
                </a>
                <a @click="deleteClick(record)" v-auth="authorize.destory">
                    <a-icon type="delete"/>
                    删除
                </a>
            </div>
        </standard-table>


        <UserEdit ref="UserEdit" @editComplete="getList"></UserEdit>
    </a-card>
</template>

<script>
import {mapState} from 'vuex'
import TimeRangeSelection from "@/components/georgie/TimeRangeSelection";
import StandardTable from "@/components/table/StandardTable";
import {userDestroy, userList} from "@/services/admin/user";
import UserAdd from "./UserAdd";
import UserEdit from "./UserEdit";
import {search,columns,withArr,authorizeConfig} from './index.js'

export default {
    name: 'UserList',
    components: {UserAdd, UserEdit, StandardTable, TimeRangeSelection},
    // i18n: require('./i18n'),
    data() {
        return {
            range_time: '',
            loading: false,
            shrink:true,
            selectedRows: [],
            authorize: authorizeConfig,
            pagination: {
                showSizeChanger: true,
                pageSize: 10,
                current: 1,
                total: 0
            },
            search: search,
            columns: columns,
            withArr: withArr,
            dataSource: [],
        }
    },
    computed: {
        ...mapState('setting', ['pageMinHeight']),
        desc() {
            return this.$t('description')
        }
    },
    authorize: authorizeConfig,//权限校验配置
    methods: {
        created(){
            this.$refs.UserAdd.open();
        },
        edit(id){
            this.$refs.UserEdit.open(id);
        },
        searchClick() {
            this.pagination.current = 1;
            this.selectedRows = [];
            this.getList();
        },
        resetClick() {
            this.search.forEach((item, index) => {
                this.search[index].value = '';
            })
        },
        destory() {
            let that = this;
            this.$confirm({
                title: '确定要执行删除? ',
                okText: '删除',
                okType: 'danger',
                cancelText: '取消',
                onOk() {
                    const id = that.getSelectRowKeys().join(',');
                    const hide = that.$message.loading('正在删除...', 0);
                    setTimeout(hide, 5000);
                    userDestroy(id).then(res => {
                        hide();
                        const data = res.data;
                        if (data.code === 200) {
                            that.$message.success('删除成功');
                            that.getList();
                        } else that.$message.error('失败');
                    });
                },
            });
        },
        deleteClick(e) {
            this.selectedRows = [e];
            this.destory();
        },
        getSelectRowKeys() {
            let arr = [];
            this.selectedRows.forEach(item => {
                arr.push(item.id);
            });
            return arr;
        },
        onChange(pagination) {
            this.pagination = pagination;
            this.getList();
        },
        getList() {
            this.loading = true;
            let json_ = {
                'start': (this.pagination.current - 1) * this.pagination.pageSize,
                'len': this.pagination.pageSize
            };
            this.withArr.forEach(item=>{
                json_[item['key']] = item['get'];
            });
            this.search.forEach(item => {
                if (item.value !== '') {
                    json_[item.dataIndex] = item.condition + ',' + item.value;
                }
            });
            if (this.range_time !== '') {
                json_['search_created_at'] = '>=,' + this.range_time.split(',')[0] + '&' + '<=,' + this.range_time.split(',')[1];
            }
            userList(json_).then(this.afterGetList)
        },
        afterGetList(res) {
            this.loading = false;
            const data = res.data;
            if (data.code === 200) {
                const {list, count} = data.data;
                this.dataSource = list;
                this.pagination.total = count;
                console.log(list, count);
            } else this.$message.error('失败', 3);
        }
    },
    mounted() {
        this.getList();
    }
}
</script>

<style scoped lang="less">
//@import "index";
</style>
