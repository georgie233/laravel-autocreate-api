<a-form-item  label="{COLUMN['title']}">
    <a-select
            v-if="selectData['category']"
            v-decorator="['{COLUMN['name']}', {rules: [{ required: true, message: '请选择'}],initialValue:selectData['category'][0][Object.keys(selectData['category'][0])[0]]}]"
    >
        <a-select-option v-for="(item,index) in selectData['category']" :value="item[Object.keys(item)[0]]" :key="index">
            {{ item[Object.keys(item)[1]] }}
        </a-select-option>
    </a-select>
    <div v-if="!selectData['category']">
        <a-button type="danger" shape="round" loading v-if="selectData['category']===null" />
        <a-button type="danger" @click="init_category" v-if="selectData['category']===false">重新加载数据</a-button>
        <a-input  v-decorator="['{COLUMN['name']}', {rules: [{ required: true, message: '请选择'}]}]" hidden></a-input>
    </div>
</a-form-item>
