import express from "express";
import mysql from "mysql2";
import bodyParser from "body-parser";
import cors from "cors";
import md5 from "md5";

const app = express();
app.use(cors());
app.use(bodyParser.json());

// Connect MySQL (XAMPP)
const db = mysql.createConnection({
  host: "localhost",
  user: "root",   // default XAMPP user
  password: "",   // default empty password
  database: "tnvs",
});

db.connect(err => {
  if (err) throw err;
  console.log("✅ MySQL Connected...");
});

app.post("/login", (req, res) => {
  const { email, password } = req.body;
  const hashedPassword = md5(password); // hash the input password

  db.query(
    "SELECT * FROM users WHERE email = ? AND password = ? LIMIT 1",
    [email, hashedPassword],
    (err, results) => {
      if (err) return res.json({ success: false, message: "DB error" });
      if (results.length === 0) return res.json({ success: false, message: "Invalid credentials" });
      const user = results[0];
      res.json({
        success: true,
        user_id: user.user_id,
        firstname: user.firstname,
        lastname: user.lastname,
        role: user.role,
      });
    }
  );
});


app.get("/assigned-customers/:user_id", (req, res) => {
  const { user_id } = req.params;

  // STEP 1: Validate user_id and get driver info
  const driverQuery = `
    SELECT CONCAT(firstname, ' ', lastname) AS driver_name 
    FROM users 
    WHERE user_id = ? 
    LIMIT 1
  `;

  db.query(driverQuery, [user_id], (err, users) => {
    if (err) {
      console.error("DB Error (fetch driver):", err);
      return res.status(500).json({ success: false, message: "Database error" });
    }

    if (users.length === 0) {
      return res.status(404).json({ success: false, message: "Driver not found" });
    }

    const driverName = users[0].driver_name;

    // STEP 2: Get assigned customers + cost analysis + location info
    const sql = `
      SELECT 
        vr.reservation_ref,
        vr.requester_name AS customer_name,
        u.firstname AS customer_firstname,
        u.lastname AS customer_lastname,
        vr.pickup_location, 
        vr.dropoff_location,
        vr.status,
        v.vehicle_plate,
        v.car_brand,
        v.model,
        sp.latitude AS pickup_latitude,
        sp.longitude AS pickup_longitude,
        sd.latitude AS dropoff_latitude,
        sd.longitude AS dropoff_longitude,

        -- COST ANALYSIS
        ca.distance_km,
        ca.estimated_time,
        ca.driver_earnings,
        ca.passenger_fare,
        ca.incentives,
        ca.created_at AS cost_created_at

      FROM vehicle_reservations vr
      INNER JOIN vehicles v 
        ON vr.vehicle_registration_id = v.registration_id
      INNER JOIN users u 
        ON vr.user_id = u.user_id
      LEFT JOIN saved_locations sp 
        ON sp.reservation_ref = vr.reservation_ref 
        AND sp.vehicle_registration_id = vr.vehicle_registration_id
        AND sp.type = 'pickup'
      LEFT JOIN saved_locations sd 
        ON sd.reservation_ref = vr.reservation_ref 
        AND sd.vehicle_registration_id = vr.vehicle_registration_id
        AND sd.type = 'dropoff'

      -- join cost_analysis based on the correct relationships
      LEFT JOIN cost_analysis ca
        ON ca.reservation_ref = vr.reservation_ref
        AND ca.registration_id = v.registration_id
        AND ca.user_id = vr.user_id

      WHERE vr.assigned_driver = ?
      ORDER BY vr.pickup_datetime DESC
    `;

    db.query(sql, [driverName], (err2, results) => {
      if (err2) {
        console.error("DB Error (fetch reservations):", err2);
        return res.status(500).json({ success: false, message: "Database error" });
      }

      // STEP 3: Optional optimization — compute total fare cleanly
      const enriched = results.map((row) => ({
        ...row,
        total_cost:
          row.passenger_fare && row.incentives
            ? (parseFloat(row.passenger_fare) + parseFloat(row.incentives)).toFixed(2)
            : row.passenger_fare
            ? parseFloat(row.passenger_fare).toFixed(2)
            : null,
      }));

      res.json(enriched);
    });
  });
});



app.get("/travel-history/:user_id", (req, res) => {
  const { user_id } = req.params;
  // Get driver's name
  db.query(
    "SELECT firstname, lastname FROM users WHERE user_id = ? LIMIT 1",
    [user_id],
    (err, users) => {
      if (err || users.length === 0) return res.json([]);
      const driverName = users[0].firstname + ' ' + users[0].lastname;
      // Fetch completed reservations for this driver
      const sql = `
        SELECT 
          vr.reservation_ref,
          vr.trip_date,
          vr.pickup_location,
          vr.dropoff_location,
          vr.status,
          v.vehicle_plate,
          v.car_brand,
          v.model,
          ca.driver_earnings
        FROM vehicle_reservations vr
        JOIN vehicles v ON vr.vehicle_registration_id = v.registration_id
        LEFT JOIN cost_analysis ca ON ca.reservation_ref = vr.reservation_ref
        WHERE vr.assigned_driver = ?
          AND vr.status = 'Completed'
        ORDER BY vr.pickup_datetime DESC
      `;
      db.query(sql, [driverName], (err2, results) => {
        if (err2) return res.json({ success: false, message: "DB error" });
        res.json(results);
      });
    }
  );
});

const PORT = 5000;
const HOST = '0.0.0.0'; // Listen on all network interfaces

app.listen(PORT, HOST, () => {
  console.log(` Server running at http://${HOST}:${PORT}`);
});