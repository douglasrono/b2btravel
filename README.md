# Project Documentation

## Project Structure

### Laravel Backend

1. **Models**
   - `Accommodation`: Represents accommodation details with fields like `name`, `description`, and `standard_rack_rate`.
   - `TravelAgent`: Represents a travel agent.
   - `Contract`: Represents a contract with fields for rates, start date, end date, and relationships with `Accommodation` and `TravelAgent`.

2. **Controllers**
   - `AccommodationController`: Handles CRUD operations for accommodations.
   - `ContractController`: Manages CRUD operations and booking logic for contracts.

3. **API Endpoints**
   - Accommodations:
     - `GET /api/accommodations`: Retrieve all accommodations.
     - `GET /api/accommodations/{id}`: Retrieve a specific accommodation.
     - `POST /api/accommodations`: Create a new accommodation.
     - `PUT /api/accommodations/{id}`: Update an accommodation.
     - `DELETE /api/accommodations/{id}`: Delete an accommodation.
   - Contracts:
     - `GET /api/contracts`: Retrieve all contracts.
     - `GET /api/contracts/{id}`: Retrieve a specific contract.
     - `POST /api/contracts`: Create a new contract.
     - `PUT /api/contracts/{id}`: Update a contract.
     - `DELETE /api/contracts/{id}`: Delete a contract.
     - `POST /api/contracts/book`: Book a contract.

### Vue.js Frontend

1. **Components**
   - `AccommodationListComponent`: Displays a list of accommodations.
   - `AccommodationDetailsComponent`: Displays details of a selected accommodation.
   - `BookingFormComponent`: Allows travel agents to input booking details.
   - `ContractRatesComponent`: Displays contract rates dynamically based on the selected travel agent.

2. **Routing**
   - Utilizes Vue Router for navigation between different components.

## Key Design Decisions

1. **Separation of Concerns**
   - Laravel backend handles data storage, business logic, and API endpoints.
   - Vue.js frontend manages user interface components and interactions.

2. **RESTful API Design**
   - Adheres to RESTful principles for API design, ensuring a clear and consistent structure.

3. **Component-Based Architecture**
   - Vue.js components are organized based on their functionality, promoting reusability.

4. **Dynamic Contract Rates**
   - Implemented a feature to dynamically display contract rates based on the selected travel agent, providing a personalized experience.

## Additional Features

### Bonus Features Implemented

1. **Error Handling**
   - Comprehensive error handling to provide users with meaningful feedback in case of issues.

2. **User Roles and Permissions**
   - Introduced user roles and permissions to restrict certain actions based on user privileges.

# Instructions for Local Setup

## Prerequisites

- Ensure that you have PHP, Composer, Node.js, and NPM installed on your machine.

## Backend Setup

1. Clone the Git repository:

   ```bash
      git clone https://github.com/douglasrono/b2btravel

## Navigate to the Laravel project directory:

 ```bash
     cd b2btravel

## Install backend dependencies:

 ```bash
     cd composer install
## Copy the .env.example file and create a new .env file:

 ```bash
      cp .env.example .env

 ## Generate the application key:  
 ```bash

     php artisan key:generate
   
## Configure the database settings in the .env file.

## Run database migrations:
 ```bash
        php artisan migrate



##  Frontend Setup
 ```bash

        Install frontend dependencies:
## Build the frontend assets:

 ```bash

       npm run dev
## Start the Laravel development server:     
 ```bash
        php artisan serve
