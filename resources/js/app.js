require('./bootstrap');

import Vue from 'vue';
import VueRouter from 'vue-router';
import AccommodationList from './components/AccommodationList.vue';
import AccommodationDetails from './components/AccommodationDetails.vue';
import BookingForm from './components/BookingForm.vue';

Vue.use(VueRouter);

const routes = [
  { path: '/', component: AccommodationList },
  { path: '/accommodations/:id', name: 'accommodation.details', component: AccommodationDetails },
  { path: '/accommodations/:id/book', name: 'booking.form', component: BookingForm },
];

const router = new VueRouter({
  mode: 'history',
  routes,
});

const app = new Vue({
  el: '#app',
  router,
});

