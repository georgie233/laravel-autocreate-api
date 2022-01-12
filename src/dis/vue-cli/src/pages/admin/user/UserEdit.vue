<template>
    <span>
        <a-modal :width="900" v-model="visible" title="修改" ok-text="提交" cancel-text="取消" @ok="okFun">
            <UserEditForm ref="form" :id='id' :data="data"  @editComplete="editComplete"></UserEditForm>
        </a-modal>
    </span>
</template>

<script>
import UserEditForm from "./Form/UserEditForm";
import {userShow} from "@/services/admin/user";
export default {
    name: "UserEdit",
    components: {UserEditForm},
    data() {
        return {
            visible: false,
            id:-1,
            data:null,
        };
    },
    methods: {
        editComplete(values){
            this.visible = false;
            this.data = null;
            this.$emit('editComplete',values);
        },
        okFun() {
            this.$refs.form.submit();
        },
        open(id){
            if (!id) return;
            if(this.id !== id || !this.data){
                this.id = id;
                const hide = this.$message.loading('加载中...', 0);
                userShow({'id':id}).then(res=>{
                    hide();
                    let data = res.status===200?res.data:[];
                    if(!data || data.code !== 200)return this.$message.error('初始化数据失败');
                    this.data = data.data;
                    this.visible = true;
                });
                setTimeout(hide, 10000);
            }else
             this.visible = true;
        }
    },
}
</script>

<style scoped>

</style>
