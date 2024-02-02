<template>
    <v-container>
      <h1>Contracts</h1>

      <!-- Add Contract Button -->
      <v-btn @click="openAddDialog" color="primary" class="mb-4">
        Add Contract
      </v-btn>

      <v-data-table :items="contracts" :headers="headers" :items-per-page="5" class="elevation-10">
        <template v-slot:item="{ item, index }">
          <tr>
            <td>{{ index + 1 }}</td>
            <td>{{ item.name }}</td>
            <td>{{ item.description }}</td>
            <td>{{ item.standard_rack_rate }}</td>
            <td>{{ item.rate }}</td>
            <td>{{ item.start_date }}</td>
            <td>{{ item.end_date }}</td>
            <td>{{ item.accommodation_id }}</td>
            <td>
              <v-icon @click="openViewDialog(item)" class="mr-2">mdi-eye</v-icon>
              <v-icon @click="openEditDialog(item)" class="mr-2">mdi-pencil</v-icon>
              <v-icon @click="openDeleteDialog(item)" class="mr-2">mdi-delete</v-icon>
            </td>
          </tr>
        </template>
      </v-data-table>

      <!-- Add Contract Dialog -->
      <v-dialog v-model="addDialog" max-width="600">
        <v-card>
          <v-card-title>Add Contract</v-card-title>
          <v-card-text>
            <v-form @submit.prevent="addContract">
              <v-text-field v-model="newContract.name" label="Name" required></v-text-field>
              <v-textarea v-model="newContract.description" label="Description" required></v-textarea>
              <v-text-field v-model="newContract.standard_rack_rate" label="Standard Rack Rate" type="number" required></v-text-field>
              <v-text-field v-model="newContract.rate" label="Rate" type="number" required></v-text-field>
              <v-input v-model="newContract.start_date" label="Start Date" type="date"></v-input>

              <v-input v-model="newContract.end_date" label="End Date" type="date" ></v-input>
              <v-text-field v-model="newContract.accommodation_id" label="Accommodation ID" type="number"></v-text-field>

              <!-- Add other form fields for additional contract properties -->

              <v-card-actions>
                <v-btn @click="closeDialog('addDialog')">Cancel</v-btn>
                <v-btn type="submit" color="primary">Save</v-btn>
              </v-card-actions>
            </v-form>
          </v-card-text>
        </v-card>
      </v-dialog>

      <!-- Edit Contract Dialog -->
      <!-- Similar structure as the Add Contract Dialog -->

      <!-- View Contract Dialog -->
      <!-- Similar structure as the Add Contract Dialog -->

      <!-- Delete Contract Dialog -->
      <!-- Similar structure as the Add Contract Dialog -->

    </v-container>
  </template>

  <script>
  import axios from 'axios';

  export default {
    data() {
      return {
        contracts: [],
        headers: [
          { title: '#', value: 'index' },
          { title: 'Name', value: 'name' },
          { title: 'Description', value: 'description' },
          { title: 'Standard Rack Rate', value: 'standard_rack_rate' },
          { title: 'Rate', value: 'rate' },
          { title: 'Start Date', value: 'start_date' },
          { title: 'End Date', value: 'end_date' },
          { title: 'Accommodation ID', value: 'accommodation_id' },
          { title: 'Actions', value: 'actions' },
        ],
        addDialog: false,
        editDialog: false,
        viewDialog: false,
        deleteDialog: false,
        selectedContract: null,
        newContract: {
          name: '',
          description: '',
          standard_rack_rate: null,
          rate: null,
          start_date: null,
          end_date: null,
          accommodation_id: null,
          // Add other properties as needed
        },
      };
    },
    created() {
      this.fetchContracts();
    },
    methods: {
      async fetchContracts() {
        try {
          const response = await axios.get('/api/contracts');
          this.contracts = response.data;
        } catch (error) {
          console.error('Error fetching contracts:', error);
        }
      },
      openAddDialog() {
        this.addDialog = true;
      },
      openEditDialog(contract) {
        this.selectedContract = contract;
        this.editDialog = true;
      },
      openViewDialog(contract) {
        this.selectedContract = contract;
        this.viewDialog = true;
      },
      openDeleteDialog(contract) {
        this.selectedContract = contract;
        this.deleteDialog = true;
      },
      closeDialog(dialogName) {
        this[dialogName] = false;
        this.selectedContract = null;
        // Reset the newContract object
        this.newContract = {
          name: '',
          description: '',
          standard_rack_rate: null,
          rate: null,
          start_date: null,
          end_date: null,
          accommodation_id: null,
          // Reset other properties as needed
        };
      },
      async addContract() {
        try {
          const response = await axios.post('/api/contracts', this.newContract);
          const newContract = response.data;

          // Update the local contracts array
          this.contracts.push(newContract);

          // Close the Add Contract dialog
          this.closeDialog('addDialog');
        } catch (error) {
          console.error('Error adding contract:', error);
        }
      },
      // Similar methods for editing, viewing, and deleting contracts
      // ...
    },
  };
  </script>
