<a-form-item  label="{COLUMN['title']}">
    <upload-avatar v-model="{COLUMN['name']}_url" :key-str="`users`" name="avatar"></upload-avatar>
    <a-input v-decorator="['upload__{COLUMN['name']}',{rules:[{required:true,message:'请上传'}],initialValue:{COLUMN['name']}_url}]" hidden />
</a-form-item>
