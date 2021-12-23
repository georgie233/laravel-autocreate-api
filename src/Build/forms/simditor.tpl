<a-form-item  label="{COLUMN['title']}">
    <a-textarea  auto-size autoSize placeholder="请输入{COLUMN['title']}" v-decorator="['{COLUMN['name']}', {rules: [{ required: true, message: '请输入{COLUMN['title']}', whitespace: true}]}]" />
</a-form-item>
