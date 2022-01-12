import {MODEL}List from './{MODEL}List.vue'
//可搜索的字段
const search = [
    {SEARCH_ARR}
];
//列表显示的字段
const columns = [
    {COLUMNS_ARR}
];
//要关联查询的数组
const withArr = [
    // {key:'with_category',get:'name'}
];
const authorizeConfig = {//权限校验配置
    created: 'Modules\\{MODULE}\\Http\\Controllers\\{MODEL}Controller@create',
    edit: 'Modules\\{MODULE}\\Http\\Controllers\\{MODEL}Controller@edit',
    destory: 'Modules\\{MODULE}\\Http\\Controllers\\{MODEL}Controller@destory',
};
export default {MODEL}List
export{
    search,
    columns,
    withArr,
    authorizeConfig
}
