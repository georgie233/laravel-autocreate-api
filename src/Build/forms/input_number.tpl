<a-form-item  label="{COLUMN['title']}">
    <a-input-number auto-size placeholder="请输入{COLUMN['title']}"
    v-decorator="['{COLUMN['name']}', {rules: [{ required: {COLUMN['nonull']}, message: '请输入{COLUMN['title']}', whitespace: true,type:'{COLUMN['formatType']}'}],initialValue:data.{COLUMN['name']}}]" />
</a-form-item>
