<template>
    <div>
        <a-form :form="form" class="form" v-bind="{labelCol: { span: 4 },wrapperCol: { span: 20 }}">
            
        </a-form>
    </div>
</template>

<script>
import {{SMODEL}RelationData,{SMODEL}Update} from "@/services/{SMODULE}/{SMODEL}";

export default {
    name: "{MODEL}AddForm",
    props:['id'],
    data() {
        return {
            form: this.$form.createForm(this),
            selectData: {{SELECTDATA}},
        }
    },
    methods: {
        initData() {
            {SMODEL}RelationData();//请删除这段代码
            {SMODEL}Update.then(res=>{
                let data = res.status===200?res.data:[];
                if(!data || data.code !== 200)return this.$message.error('初始化数据失败');
                console.log(data.data);
            });
            {INITDATA}
        },
        {METHODS}
        submit() {
            this.form.validateFields((err, values) => {
                if (!err) {
                    values['id'] = this.id;
                    {SMODEL}Update(values).then(res=>{
                        let data = res.status===200?res.data:[];
                        if (!data)return this.$message.error('提交失败');
                        if (data.code !== 200)return this.$message.error(data.message);
                        this.$message.success(data.message??'修改成功');
                        this.$emit('addComplete',values);
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
