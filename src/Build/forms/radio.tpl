<a-form-item  label="{COLUMN['title']}">
    <a-radio-group v-decorator="['{COLUMN['name']}',{rules: [{ required: true, message: '请选择', whitespace: true,type:'number'}],initialValue:data.{COLUMN['name']}}]" >
        {FORM_HTML}
    </a-radio-group>
</a-form-item>
