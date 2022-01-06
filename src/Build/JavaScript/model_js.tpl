import {request, METHOD} from '@/utils/request'
import {{IMPORT}} from '@/services/api';

export async function {SMODEL}List(data) {
return request({API_PATH}, METHOD.GET, data);
}

export async function {SMODEL}Created(data) {
return request({API_PATH}, METHOD.POST, data);
}

export async function {SMODEL}Show(data) {
const url = {API_PATH} + '/' + data['id'];
return request(url, METHOD.GET);
}

export async function {SMODEL}Update(data) {
const url = {API_PATH} + '/' + data['id'];
delete data['id'];
return request(url, METHOD.PUT, data);
}

export async function {SMODEL}Destroy(data) {
const url = {API_PATH} + `/${data}`;
return request(url, METHOD.DELETE);
}

export async function {SMODEL}RelationData(data){
if(!data)return;
const url = {API_RELATION} + `?relation=${data}`
return request(url, METHOD.GET);
}
