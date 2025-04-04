// Import the functions you need from the SDKs you needimport { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging.js";
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";

// TODO: Add SDKs for Firebase products that you want to use
// https://firebase.google.com/docs/web/setup#available-libraries

// Your web app's Firebase configuration
const firebaseConfig = {
  apiKey: "AIzaSyD4ouDXqdVgAFI_NB6YOT8P5zv_pA86wsg",
  authDomain: "pesca-88b61.firebaseapp.com",
  projectId: "pesca-88b61",
  storageBucket: "pesca-88b61.firebasestorage.app",
  messagingSenderId: "375365901142",
  appId: "1:375365901142:web:9ef9c52e7bc4823cc254f7"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);

const messaging = getMessaging(app)

export {messaging, getToken}