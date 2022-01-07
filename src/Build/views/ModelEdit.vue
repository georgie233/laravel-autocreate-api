<template>
    <span>
        <a-modal :width="900" v-model="visible" title="修改" ok-text="提交" cancel-text="取消" @ok="okFun">
            <{MODEL}EditForm ref="form" :id='id' :data="data"  @editComplete="editComplete"></{MODEL}EditForm>
        </a-modal>
    </span>
</template>

<script>
import {MODEL}EditForm from "./Form/{MODEL}EditForm";
import {{SMODEL}Show} from "@/services/book/{SMODEL}";
export default {
    name: "{MODEL}Edit",
    components: {{MODEL}EditForm},
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
                {SMODEL}Show({'id':id}).then(res=>{
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
