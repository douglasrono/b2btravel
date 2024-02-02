<template>
    <v-container>
        <h1>Accommodations</h1>
        <v-data-table :items="accommodations" :headers="headers" :items-per-page="5" class="elevation-10">
            <template v-slot:item="{ item,index }">
                <tr>
                    <td>{{ index + 1 }}</td>
                    <td>{{ item.name }}</td>
                    <td>{{ item.description }}</td>
                    <td>{{ item.standard_rack_rate }}</td>
                </tr>
            </template>
        </v-data-table>
    </v-container>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            accommodations: [],
            headers: [
                { title: '#', value: 'index' },
                { title: 'Name', value: 'name' },
                { title: 'Description', value: 'description' },
                { title: 'Standard Rack Rate', value: 'standard_rack_rate' },
            ],
        };
    },
    created() {
        this.fetchAccommodations();
    },
    methods: {
        async fetchAccommodations() {
            try {
                const response = await axios.get('/api/accommodations');
                this.accommodations = response.data;
            } catch (error) {
                console.error('Error fetching accommodations:', error);
            }
        },
    },
};
</script>

