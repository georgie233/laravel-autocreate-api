import UserList from './UserList.vue'
//可搜索的字段
const search = [
    {title: '编号', dataIndex: 'search_id', value: '', condition: '=',shrink:false},
	{title: '账户名', dataIndex: 'search_name', value: '', condition: 'like',shrink:true},
	{title: '昵称', dataIndex: 'search_nick_name', value: '', condition: 'like',shrink:true},
	{title: '密码', dataIndex: 'search_password', value: '', condition: 'like',shrink:true},
	{title: '邮箱', dataIndex: 'search_email', value: '', condition: 'like',shrink:true},
];
//列表显示的字段
const columns = [
    {title: '编号', dataIndex: 'id'},
	{title: '账户名', dataIndex: 'name'},
	{title: '昵称', dataIndex: 'nick_name'},
	{title: '邮箱', dataIndex: 'email'},
	{title: '操作', scopedSlots: {customRender: 'action'}, hideLabel: true}
];
//要关联查询的数组
const withArr = [
    // {key:'with_category',get:'name'}
];
const authorizeConfig = {//权限校验配置
    created: 'Modules\\Admin\\Http\\Controllers\\UserController@create',
    edit: 'Modules\\Admin\\Http\\Controllers\\UserController@edit',
    destory: 'Modules\\Admin\\Http\\Controllers\\UserController@destory',
};
export default UserList
export{
    search,
    columns,
    withArr,
    authorizeConfig
}
