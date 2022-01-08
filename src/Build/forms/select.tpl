<a-form-item  label="{COLUMN['title']}">
    <a-select
            v-if="selectData['{OBJMODEL}'] && selectData['{OBJMODEL}'].length"
            v-decorator="['{COLUMN['name']}', {rules: [{ required: true, message: '请选择'}],
            initialValue:data.categories_id?data.categories_id:selectData['category'][0][Object.keys(selectData['category'][0])[0]]}]"
    >
        <a-select-option v-for="(item,index) in selectData['{OBJMODEL}']" :value="item[Object.keys(item)[0]]" :key="index">
            {{ item[Object.keys(item)[1]] }}
        </a-select-option>
    </a-select>
    <a-form-item>
        <div v-if="!(selectData['{OBJMODEL}'] && selectData['{OBJMODEL}'].length)">
            无数据
            <a-input  v-decorator="['{COLUMN['name']}', {rules: [{ required: true, message: '请选择'}]}]" hidden></a-input>
        </div>
    </a-form-item>
    <a-form-item>
        <div v-if="!selectData['{OBJMODEL}']">
            <a-button type="danger" shape="round" loading v-if="selectData['{OBJMODEL}']===null" />
            <a-button type="danger" @click="init_{OBJMODEL}" v-if="selectData['{OBJMODEL}']===false">重新加载数据</a-button>
            <a-input  v-decorator="['{COLUMN['name']}', {rules: [{ required: true, message: '请选择'}]}]" hidden></a-input>
        </div>
    </a-form-item>
</a-form-item>
