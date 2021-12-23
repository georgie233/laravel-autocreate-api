<template>
    <div>
        <a-form :form="form" class="form" v-bind="{labelCol: { span: 4 },wrapperCol: { span: 20 }}">
            {FORMROWS}
        </a-form>
    </div>
</template>

<script>
import {{SMODEL}RelationData} from "../../../services/{SMODULE}/{SMODEL}";

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
            {INITDATA}
        },
        {METHODS}
        submit() {
            this.form.validateFields((err, values) => {
                if (!err) {
                    console.log('Received values of form: ', values)
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
