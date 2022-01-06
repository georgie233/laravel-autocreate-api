<template>
    <div>
        <a-form :form="form" class="form" v-bind="{labelCol: { span: 4 },wrapperCol: { span: 20 }}">
            {FORMROWS}
        </a-form>
    </div>
</template>

<script>
import {{SMODEL}RelationData,{SMODEL}Created} from "@/services/{SMODULE}/{SMODEL}";

export default {
    name: "{MODEL}AddForm",
    data() {
        return {
            form: this.$form.createForm(this),
            selectData: {{SELECTDATA}},
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
                    {SMODEL}Created(values).then(res=>{
                        let data = res.status===200?res.data:[];
                        if (!data)return this.$message.error('提交失败');
                        if (data.code !== 200)return this.$message.error(data.message);
                        this.$message.success(data.message??'添加成功');
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
