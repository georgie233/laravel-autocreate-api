import {request, METHOD} from '@/utils/request'
import {ADMIN_USER_PATH,ADMIN_USER_RELATION_DATA} from '@/services/api';

export async function userList(data) {
return request(ADMIN_USER_PATH, METHOD.GET, data);
}

export async function userCreated(data) {
return request(ADMIN_USER_PATH, METHOD.POST, data);
}

export async function userShow(data) {
const url = ADMIN_USER_PATH + '/' + data['id'];
return request(url, METHOD.GET);
}

export async function userUpdate(data) {
const url = ADMIN_USER_PATH + '/' + data['id'];
delete data['id'];
return request(url, METHOD.PUT, data);
}

export async function userDestroy(data) {
const url = ADMIN_USER_PATH + `/${data}`;
return request(url, METHOD.DELETE);
}

export async function userRelationData(data){
if(!data)return;
const url = ADMIN_USER_RELATION_DATA + `?relation=${data}`
return request(url, METHOD.GET);
}
