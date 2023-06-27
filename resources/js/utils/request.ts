import axios from 'axios'
import { getToken } from '@/utils/auth'
import showValidationErrors from './show-validation-errors'


// create an axios instance
const request = axios.create({
    baseURL: '', // url = base url + request url
    // withCredentials: true, // send cookies when cross-domain requests
    timeout: 10 * (1000 * 60) // request timeout
})


// request interceptor
request.interceptors.request.use(
    config => {
        // do something before request is sent

        document.querySelectorAll('.is-invalid').forEach(element => {
            element.classList.remove('is-invalid')
            const el = element.closest('.form-group')
            if (el) {
                el.classList.remove('has-error')
                el.querySelector('.invalid-feedback')?.remove()
            }
        })

        if (1 > 0 || store.getters.token) {
            // let each request carry token
            // ['X-Token'] is a custom headers key
            // please modify it according to the actual situation
            // config.headers['X-Token'] = getToken()
            config.headers['Authorization'] = 'Bearer ' + getToken()
        }
        return config
    },
    error => {
        // do something with request error
        return Promise.reject(error)
    }
)

// response interceptor
request.interceptors.response.use(
    /**
   * If you want to get http information such as headers or status
   * Please return  response => response
  */

    /**
   * Determine the request status by custom code
   * Here is just an example
   * You can also judge the status by HTTP Status Code
   */
    response => {

        const res = response.data

        if (!response?.status) return false

        // if the custom code is not 200, it is judged as an error.
        if ((response.status !== 200 && response.status !== 201)) {
            alert(
                res.message || 'Error'
            )

            // 50008: Illegal token; 50012: Other clients logged in; 50014: Token expired;
            if (response.status === 50008 || response.status === 50012 || response.status === 50014) {
                // to re-login
                // alert('You have been logged out, you can cancel to stay on this page, or log in again', 'Confirm logout', {
                //     confirmButtonText: 'Re-Login',
                //     cancelButtonText: 'Cancel',
                //     type: 'warning'
                // })
                // .then(() => {
                //     store.dispatch('user/resetToken').then(() => {
                //         location.reload()
                //     })
                // })
            }
            return Promise.reject(new Error(res.message || 'Error'))
        } else {
            return res
        }
    },
    error => {

        if (error.response) {
            const data = error.response.data
            const { errors } = data
            showValidationErrors(errors)
        }


        alert(error.message)

        return Promise.reject(error)
    }
)

export default request
