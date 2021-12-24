<a-form-item  label="{COLUMN['title']}">
    <a-select
            v-if="selectData['{COLUMN['name']}'] && selectData['{COLUMN['name']}'].length"
            v-decorator="['{COLUMN['name']}', {rules: [{ required: true, message: '请选择'}],initialValue:selectData['{COLUMN['name']}'][0][Object.keys(selectData['{COLUMN['name']}'][0])[0]]}]"
    >
        <a-select-option v-for="(item,index) in selectData['{COLUMN['name']}']" :value="item[Object.keys(item)[0]]" :key="index">
            {{ item[Object.keys(item)[1]] }}
        </a-select-option>
    </a-select>
    <div v-if="!selectData['{COLUMN['name']}']">
        <a-button type="danger" shape="round" loading v-if="selectData['{COLUMN['name']}']===null" />
        <a-button type="danger" @click="init_{COLUMN['name']}" v-if="selectData['{COLUMN['name']}']===false">重新加载数据</a-button>
        <a-input  v-decorator="['{COLUMN['name']}', {rules: [{ required: true, message: '请选择'}]}]" hidden></a-input>
    </div>
</a-form-item>
