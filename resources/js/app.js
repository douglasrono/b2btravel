import './bootstrap'
import { createApp } from 'vue'
import ToastrPlugin from './toastr-plugin'

// Vuetify
import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

const vuetify = createVuetify({
  components,
  directives,
})

const app = createApp({})

app.use(ToastrPlugin)


import AccommodationComponent from './components/AccommodationComponent.vue';
app.component('accommodation-component', AccommodationComponent);

import ContractComponent from './components/ContractComponent.vue';
app.component('contract-component', ContractComponent);


app.use(vuetify).mount('#app')
