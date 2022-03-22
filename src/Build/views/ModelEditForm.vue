<template>
    <div>
        <a-form :form="form" class="form" v-bind="{labelCol: { span: 4 },wrapperCol: { span: 20 }}">
            {FORMROWS}
        </a-form>
    </div>
</template>

<script>
import {{SMODEL}RelationData,{SMODEL}Update} from "@/services/{SMODULE}/{SMODEL}";
import {UPLOAD_FILE} from "@/services/api";
import {BASEURL} from "@/services/api";
{IMPORT}
export default {
    name: "{MODEL}AddForm",
    props:['id','data'],
    components: {{COMPONENT}},
    data() {
        return {
            baseUrl:BASEURL,
            form: this.$form.createForm(this),
            upload_path:UPLOAD_FILE,
            selectData: {{SELECTDATA}},
            {ADDFORMDATA}
        }
    },
    methods: {
        initData() {
            {SMODEL}RelationData();//请删除这段代码
            {INITDATA}
        },
        {METHODS}
        submit() {
            this.form.validateFields((err, values) => {
                if (!err) {
                    const hide = this.$message.loading('正在修改...', 0);
                    setTimeout(hide, 5000);
                    values['id'] = this.id;
                    {SMODEL}Update(values).then(res=>{
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
