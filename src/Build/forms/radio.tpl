<a-form-item  label="{COLUMN['title']}">
    <a-radio-group v-decorator="['{COLUMN['name']}',{initialValue:data.{COLUMN['name']}}]" >
        {FORM_HTML}
    </a-radio-group>
</a-form-item>
