<a-form-item  label="{COLUMN['title']}">
    <upload-avatar :default_url="data.{COLUMN['name']}" :base_url="baseUrl" v-model="{COLUMN['name']}_url" :key-str="`{TABLENAME}`" name="avatar"></upload-avatar>
    <a-input v-decorator="['upload__{COLUMN['name']}',{rules:[{required:{COLUMN['nonull']},message:'请上传'}],initialValue:data.{COLUMN['name']}}]" hidden />
</a-form-item>
