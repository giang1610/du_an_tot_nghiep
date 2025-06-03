import axios from "axios";

const baseURL=process.env.REACT_APP_URI;
const timeout=+process.env.REACT_APP_TIME_OUT||20000;

const axiosInstance =axios.create({
    baseURL,
    timeout,
    withCredentials: true,
});

axiosInstance.interceptors.request.use(

    function(config){
        config.headers["Content-Type"]= "application/json";
        return config
    },
    function(error){
        return Promise.reject(error)
    }
);
axiosInstance.interceptors.response.use(
    function (response){
        if(response.data){
            return response.data
        }
        return response
    },
    function(error){
        return Promise.reject(error)
    }
)