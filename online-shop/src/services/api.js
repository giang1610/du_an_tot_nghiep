import axios from "axios";

const API = axios.create({
  baseURL: "http://localhost:8000/api", // URL backend Laravel
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
});
export default API;
