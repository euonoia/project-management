import React, { useEffect, useState } from "react";
import {
  View,
  Text,
  TouchableOpacity,
  ScrollView,
  StyleSheet,
  Dimensions,
} from "react-native";
import AsyncStorage from "@react-native-async-storage/async-storage";
import { Ionicons } from "@expo/vector-icons";
import { useRouter } from "expo-router";
import Constants from "expo-constants";

const { width } = Dimensions.get("window");
const API_URL = Constants.expoConfig?.extra?.API_URL;

if (!API_URL) {
  throw new Error(
    "API_URL not defined. Please set API_BASE_URL in your .env and rebuild the app."
  );
}
// Types
interface Driver {
  firstname?: string;
  lastname?: string;
  user_id?: number;
}

interface EarningsResponse {
  success: boolean;
  total_earnings?: string;
  message?: string;
}

interface AssignedRidesResponse {
  success: boolean;
  assigned_count?: number;
  message?: string;
}

export default function DriverHome() {
  const [driver, setDriver] = useState<Driver>({});
  const [earnings, setEarnings] = useState<number | null>(null);
  const [assignedRides, setAssignedRides] = useState<number | null>(null);
  const [loading, setLoading] = useState<boolean>(true);
  const router = useRouter();

  // Load driver from AsyncStorage
  useEffect(() => {
    const loadDriver = async () => {
      try {
        const storedDriver = await AsyncStorage.getItem("driver");
        if (storedDriver) {
          const parsed: Driver = JSON.parse(storedDriver);
          setDriver({
            firstname: parsed.firstname || "",
            lastname: parsed.lastname || "",
            user_id: parsed.user_id,
          });
        } else {
          router.replace("/");
        }
      } catch (error) {
        console.error("Error loading driver:", error);
      }
    };
    loadDriver();
  }, []);

  // Fetch earnings
  useEffect(() => {
    const fetchEarnings = async () => {
      if (!driver.user_id) return;

      try {
        setLoading(true);
        const response = await fetch(`${API_URL}/user/${driver.user_id}/total-earnings`);
        const data: EarningsResponse = await response.json();
        if (data.success && data.total_earnings) {
          setEarnings(parseFloat(data.total_earnings));
        } else {
          console.warn("Could not fetch earnings:", data.message);
        }
      } catch (error) {
        console.error("Error fetching earnings:", error);
      } finally {
        setLoading(false);
      }
    };
    fetchEarnings();
  }, [driver.user_id]);

  // Fetch assigned rides including current driver
  useEffect(() => {
    const fetchAssignedRides = async () => {
      if (!driver.user_id) return;

      try {
        const response = await fetch(`${API_URL}/user/${driver.user_id}/assigned-count`);
        const data: AssignedRidesResponse = await response.json();
        if (data.success && typeof data.assigned_count === "number") {
          setAssignedRides(data.assigned_count); // now includes logged-in driver
        } else {
          console.warn("Could not fetch assigned rides:", data.message);
        }
      } catch (error) {
        console.error("Error fetching assigned rides:", error);
      }
    };
    fetchAssignedRides();
  }, [driver.user_id]);

  // Logout
  const handleLogout = async () => {
    try {
      await AsyncStorage.clear();
      router.replace("/");
    } catch (error) {
      console.error("Logout Error:", error);
    }
  };

  return (
    <ScrollView contentContainerStyle={styles.container} showsVerticalScrollIndicator={false}>
      <Text style={styles.greeting}>
        {driver.firstname && driver.lastname
          ? `Welcome, Driver ${driver.firstname} ${driver.lastname}!`
          : "Welcome, Driver!"}
      </Text>
      <Text style={styles.subtext}>Here’s your current overview</Text>

      <View style={styles.grid}>
        {[
          {
            icon: "car-outline",
            title: "Assigned Rides",
            subtitle:
              assignedRides !== null
                ? `${assignedRides} ride${assignedRides !== 1 ? "s" : ""} assigned`
                : "Loading...",
          },
          {
            icon: "wallet-outline",
            title: "Earnings",
            subtitle: loading
              ? "Loading..."
              : earnings !== null
              ? `₱${earnings.toFixed(2)} total`
              : "No earnings yet",
          },
        ].map((item, index) => (
          <TouchableOpacity key={index} style={styles.card} activeOpacity={0.8}>
            <Ionicons name={item.icon as any} size={width * 0.08} color="#9a3412" />
            <Text style={styles.cardTitle}>{item.title}</Text>
            <Text style={styles.cardSubtitle}>{item.subtitle}</Text>
          </TouchableOpacity>
        ))}
      </View>

      <TouchableOpacity onPress={handleLogout} style={styles.logoutButton} activeOpacity={0.8}>
        <Text style={styles.logoutText}>Logout</Text>
      </TouchableOpacity>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    paddingVertical: 40,
    paddingHorizontal: 20,
    backgroundColor: "#fff",
    flexGrow: 1,
  },
  greeting: {
    fontSize: width * 0.055,
    fontWeight: "700",
    color: "#1f2937",
  },
  subtext: {
    fontSize: width * 0.035,
    color: "#374151",
    marginTop: 4,
    marginBottom: 20,
  },
  grid: {
    flexDirection: "row",
    flexWrap: "wrap",
    justifyContent: "space-between",
  },
  card: {
    backgroundColor: "#ffffff",
    width: width < 400 ? "48%" : "47%",
    borderRadius: 16,
    padding: 16,
    marginBottom: 16,
    shadowColor: "#000",
    shadowOpacity: 0.08,
    shadowOffset: { width: 0, height: 2 },
    shadowRadius: 4,
    elevation: 3,
    borderWidth: 1,
    borderColor: "#e5e7eb",
  },
  cardTitle: {
    fontSize: width * 0.04,
    fontWeight: "600",
    color: "#1f2937",
    marginTop: 8,
  },
  cardSubtitle: {
    fontSize: width * 0.032,
    color: "#4b5563",
    marginTop: 2,
  },
  logoutButton: {
    backgroundColor: "#9a3412",
    paddingVertical: 14,
    borderRadius: 10,
    marginTop: 10,
    borderWidth: 1,
    borderColor: "#7c2d12",
  },
  logoutText: {
    color: "#ffffff",
    textAlign: "center",
    fontWeight: "600",
    fontSize: width * 0.04,
  },
});
