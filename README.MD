# Logistics 2 – TNVS Subsystem (Project Management)

**Logistics 2** in Project Management is a subsystem of a **TNVS (Transport Network Vehicle Service)** project developed for our Project Management course.  
This project integrates both **web and mobile applications**, developed in a **localhost environment**.  
It represents a significant milestone in my system development journey.

---

THE LANDING PAGE OF THE SYSTEM

![landing-page](<docs/images/Screenshot (188)-1.png>)
---
SIGN IN & REGISTRATION

![sign-in](docs/images/image.png)

**Registration Page**  
![Registration](docs/images/image-1.png)

---

## Reservation Page

This section contains the core functionality of the system.

![Reservation](docs/images/image-2.png)

### Geo-Location Map

A functional geo-location feature allows users to search for a place, which then automatically appears in the search bar.

**Pick-Up Location**  
![Pickup Location](docs/images/image-3.png)

**Error Handling (Duplicate Pick-Up)**  
![Error](docs/images/image-4.png)

**Drop-Off Location**  
![Drop-Off](docs/images/image-5.png)

---

## Vehicle Selection

Vehicle selection is automatically determined based on the availability and registered vehicle type (Sedan, SUV, Hatchback, MPV, Van).

**Selected Vehicle (Sedan)**  
![Vehicle Selection](docs/images/image-6.png)

**No Available SUV**  
![No Car](docs/images/image-7.png)

**No Available Sedan (Prevents Double Booking)**  
![No Sedan](docs/images/image-8.png)

---

## Schedule Selection

![Schedule](docs/images/image-9.png)

---

## Additional Information

This section automatically displays the name of the currently logged-in user and includes a **trip description** field to prevent unnecessary inputs.

![Additional Info](docs/images/image-10.png)

---

## Transport Cost Analysis

The transport cost is automatically calculated based on the **distance (in kilometers)** between the pick-up and drop-off locations.

![Transport Cost](docs/images/image-11.png)

---

## Reservation Cancellation

Users can cancel their reservation as long as it has not been dispatched yet.

![Cancel](docs/images/image-12.png)

---

## Completed Reservation

When the user reaches their destination, the system displays a **Completed Reservation** summary similar to e-commerce order confirmations.

![Completed Reservation](docs/images/image-13.png)

---

## Travel History

Displays the travel history of the currently logged-in user.

![Travel History](docs/images/image-14.png)

### Driver Rating

Users can rate their driver after completing a trip.

![Rate Driver](docs/images/image-15.png)

---

## Become a Driver

Users can register to become drivers through this section.

![Driver 1](docs/images/image-16.png)  
![Driver 2](docs/images/image-17.png)  
![Driver 3](docs/images/image-18.png)

---

## Admin Panel

The **Admin Side** provides system management and monitoring tools.

### Dashboard  
![Dashboard](docs/images/image-19.png)

### Reservation and Dispatch  
![Reservation](docs/images/image-20.png)

### Travel Records  
![Records](docs/images/image-21.png)

### Users  
![Users](docs/images/image-22.png)

### Drivers  
![Drivers](docs/images/image-23.png)

---

## Mobile Version (Driver Side)

This section showcases the **mobile interface** designed specifically for drivers.  
It includes key features such as the driver dashboard, assigned customer view, trip route with live geolocation, and trip history.

---

### Driver Dashboard
Displays actived assigned trips and total earned(based on trip the driver trip cost)

![Driver Dashboard](docs/images/image-24.png)

---

### Assigned Customer
Shows the passenger assigned to the driver, including pickup and destination details.

![Assigned Customer](docs/images/image-25.png)

---

### Trip Route
Provides a live route display powered by a built-in API using **latitude and longitude geolocation** for accurate tracking.  
This feature was one of the most technically challenging parts of the project.

![Trip Route](docs/images/image-26.png)

---

### Trip History
Displays all completed trips associated with the logged-in driver for record-keeping and review.

![Trip History](docs/images/image-27.png)

---
## Database

This system uses a **MySQL database** hosted on **XAMPP (localhost)** for managing all core data.  
It stores information such as user accounts, driver details, reservations, trip routes, and travel history.  
The database is structured for efficiency, ensuring reliable data handling between the **web** and **mobile** applications.

![Database](docs/images/image-28.png)

---
## Conclusion

The development of this system provided valuable hands-on experience in **full-stack web and mobile application development** using modern tools such as **node.js**, and **MySQL (XAMPP)**.  
Through this project, I learned how to design, integrate, and manage various components—from frontend interfaces to backend services and database connectivity—while maintaining functionality within a localhost environment.  

Building the Logistics 2 subsystem for the TNVS project strengthened my understanding of **system architecture, user interaction flow, and data management**.  
It also enhanced my problem-solving skills, particularly in implementing real-time features such as **geo-location routing** and **automated trip assignments**.  

Overall, this project marks a significant milestone in my learning journey as it reflects both the **technical knowledge** and **project management skills** essential for developing scalable, reliable systems in real-world scenarios.
---
**Developed by:** Ken Española 
**Year:** 2025
