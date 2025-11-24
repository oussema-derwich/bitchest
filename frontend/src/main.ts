import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import './index.css'
// realtime must be initialized early so components can subscribe
import './services/realtime'

const app = createApp(App)
app.use(router)
app.mount('#app')
