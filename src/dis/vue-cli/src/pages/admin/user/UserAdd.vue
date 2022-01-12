<template>
    <span>
        <a-modal :width="900" v-model="visible" title="添加" ok-text="提交" cancel-text="取消" @ok="okFun">
            <UserAddForm ref="form" :data="{}" @addComplete="addComplete"></UserAddForm>
        </a-modal>
    </span>
</template>

<script>
import UserAddForm from "./Form/UserAddForm";
export default {
    name: "UserAdd",
    components: {UserAddForm},
    data() {
        return {
            visible: false,
        };
    },
    methods: {
        addComplete(values){
            this.visible = false;
            this.$emit('addComplete',values);
        },
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
