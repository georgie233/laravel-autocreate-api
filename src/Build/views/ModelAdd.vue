<template>
    <span>
        <a-modal :width="900" v-model="visible" title="添加" ok-text="提交" cancel-text="取消" @ok="okFun">
            <{MODEL}AddForm ref="form"></{MODEL}AddForm>
        </a-modal>
    </span>
</template>

<script>
import DemoAddForm from "./Form/{MODEL}AddForm";
export default {
    name: "{MODEL}Add",
    components: {{MODEL}AddForm},
    data() {
        return {
            visible: false,
        };
    },
    methods: {
        okFun() {
            this.$refs.form.submit();
        },
        open(){
            this.visible = true;
        }
    },
}
</script>

<style scoped>

</style>
