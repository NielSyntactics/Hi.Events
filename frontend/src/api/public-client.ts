import axios from "axios";
import {isSsr} from "../utilites/helpers";
import {getConfig} from "../utilites/config";

export const publicApi = axios.create({
    withCredentials: true,
    transformResponse: [(data) => {
        if (typeof data === 'string') {
            try {
                const jsonStart = data.indexOf('{');
                const jsonEnd = data.lastIndexOf('}');
                if (jsonStart !== -1 && jsonEnd !== -1) {
                    const jsonStr = data.substring(jsonStart, jsonEnd + 1);
                    return JSON.parse(jsonStr);
                }
            } catch (e) {
                // If extraction/parsing fails, try default JSON parsing
            }
        }
        try {
            return JSON.parse(data);
        } catch (e) {
            return data;
        }
    }]
});

publicApi.interceptors.request.use((config) => {
    const baseUrl = isSsr()
        ? getConfig('VITE_API_URL_SERVER')
        : getConfig('VITE_API_URL_CLIENT');

    const prefix = getConfig('VITE_API_PUBLIC_PREFIX') || '/public';
    config.baseURL = `${baseUrl}${prefix}`;
    return config;
}, (error) => {
    return Promise.reject(error);
});

axios.defaults.withCredentials = true;
