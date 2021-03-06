<a-form-item  label="{COLUMN['title']}">
    <a-select
            v-if="selectData['{OBJMODEL}'] && selectData['{OBJMODEL}'].length"
            v-decorator="['{COLUMN['name']}', {rules: [{ required: {COLUMN['nonull']}, message: '请选择'}],
            initialValue:data.{COLUMN['name']}!=null?data.{COLUMN['name']}:selectData['{OBJMODEL}'][0][Object.keys(selectData['{OBJMODEL}'][0])[0]]}]"
    >
        <a-select-option value="{SELECT_DEFAULT_VALUE}">{SELECT_DEFAULT_TEXT}</a-select-option>
        <a-select-option v-for="(item,index) in selectData['{OBJMODEL}']" :value="item[Object.keys(item)[0]]" :key="index">
            {{ item[Object.keys(item)[1]] }}
        </a-select-option>
    </a-select>
    <a-select
        v-else-if="selectData['{OBJMODEL}']"
        v-decorator="['{COLUMN['name']}', {rules: [{ required: {COLUMN['nonull']}, message: '请选择'}],
            initialValue:data.{COLUMN['name']}!=null?data.{COLUMN['name']}:'{SELECT_DEFAULT_VALUE}'}]"
    >
        <a-select-option value="{SELECT_DEFAULT_VALUE}">{SELECT_DEFAULT_TEXT}</a-select-option>
    </a-select>
    <a-form-item v-else>
        <div>
            <a-button type="danger" shape="round" loading v-if="selectData['{OBJMODEL}']===null" />
            <a-button type="danger" @click="init_{OBJMODEL}" v-if="selectData['{OBJMODEL}']===false">重新加载数据</a-button>
            <a-input  v-decorator="['{COLUMN['name']}', {rules: [{ required: {COLUMN['nonull']}, message: '请选择'}]}]" hidden></a-input>
        </div>
    </a-form-item>
</a-form-item>
