import OAuth from '../oauth'
import AxiosInterceptor from '../interceptors/axios'
import RouterInterceptor from '../interceptors/v-router'
export default 
{
	install(Vue, options){
		Vue.prototype.$oauth = new OAuth();
	}
}