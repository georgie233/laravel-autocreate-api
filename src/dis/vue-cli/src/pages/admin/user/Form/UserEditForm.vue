<template>
    <div>
        <a-form :form="form" class="form" v-bind="{labelCol: { span: 4 },wrapperCol: { span: 20 }}">

<a-form-item  label="账户名">
    <a-textarea auto-size placeholder="请输入账户名"
    v-decorator="['name', {rules: [{ required: true, message: '请输入账户名', whitespace: true,type:'string'}],initialValue:data.name}]" />
</a-form-item>

<a-form-item  label="昵称">
    <a-textarea auto-size placeholder="请输入昵称"
    v-decorator="['nick_name', {rules: [{ required: true, message: '请输入昵称', whitespace: true,type:'string'}],initialValue:data.nick_name}]" />
</a-form-item>

<a-form-item  label="新密码">
    <a-textarea auto-size placeholder="新密码，不更改无需填写"
    v-decorator="['password', {initialValue:data.password}]" />
</a-form-item>

<a-form-item  label="邮箱">
    <a-textarea auto-size placeholder="请输入邮箱"
    v-decorator="['email', {rules: [{ required: true, message: '请输入邮箱', whitespace: true,type:'string'}],initialValue:data.email}]" />
</a-form-item>

<a-form-item  label="推荐">
    <a-radio-group v-decorator="['gender',{rules: [{ required: true, message: '请选择', whitespace: true,type:'number'}],initialValue:data.gender}]" >
        <a-radio :value="0">保密</a-radio><a-radio :value="1">女</a-radio><a-radio :value="2">男</a-radio>
    </a-radio-group>
</a-form-item>

<a-form-item  label="状态">
    <a-radio-group v-decorator="['status',{rules: [{ required: true, message: '请选择', whitespace: true,type:'number'}],initialValue:data.status}]" >
        <a-radio :value="0">无效</a-radio><a-radio :value="1">正常</a-radio>
    </a-radio-group>
</a-form-item>

        </a-form>
    </div>
</template>

<script>
import {userRelationData,userUpdate} from "@/services/admin/user";

export default {
    name: "UserAddForm",
    props:['id','data'],
    data() {
        return {
            form: this.$form.createForm(this),
            selectData: {},
        }
    },
    methods: {
        initData() {
            userRelationData();//请删除这段代码

        },

        submit() {
            this.form.validateFields((err, values) => {
                if (!err) {
                    const hide = this.$message.loading('正在修改...', 0);
                    setTimeout(hide, 5000);
                    values['id'] = this.id;
                    userUpdate(values).then(res=>{
                        hide();
                        let data = res.status===200?res.data:[];
                        if (!data)return this.$message.error('修改失败');
                        if (data.code !== 200)return this.$message.error(data.message);
                        this.$message.success(data.message??'修改成功');
                        this.$emit('editComplete',values);
                    });
                }
            })
        }
    },
    mounted() {
        this.initData();
    }
}
</script>

<style scoped>

</style>
