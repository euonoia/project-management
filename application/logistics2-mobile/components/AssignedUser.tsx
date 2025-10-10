import React, { useEffect, useState } from "react";
import {
  View,
  Text,
  FlatList,
  TouchableOpacity,
  Modal,
  ActivityIndicator,
  SafeAreaView,
  StyleSheet,
  ScrollView,
  StyleProp,
  TextStyle,
} from "react-native";
import AsyncStorage from "@react-native-async-storage/async-storage";
import MapModal from "../components/MapModal";
import { MaterialIcons, Ionicons } from "@expo/vector-icons";

// ----------------------
// Interfaces
// ----------------------
interface Customer {
  reservation_ref: string;
  customer_name: string;
  customer_firstname: string;
  customer_lastname: string;
  pickup_location: string;
  dropoff_location: string;
  vehicle_plate: string;
  car_brand: string;
  model: string;
  status: string;
  pickup_latitude?: number | null;
  pickup_longitude?: number | null;
  dropoff_latitude?: number | null;
  dropoff_longitude?: number | null;

  // normalized cost_analysis fields (numbers or null)
  total_fare?: number | null;
  distance_km?: number | null;
  estimated_time?: string | null;
}

interface TravelHistoryItem {
  reservation_ref: string;
  trip_date: string;
  pickup_location: string;
  dropoff_location: string;
  vehicle_plate: string;
  car_brand: string;
  model: string;
  status: string;
  driver_earnings?: number;
}

// ----------------------
// Main Component
// ----------------------
export default function AssignedUser({ navigation }: any) {
  const [customers, setCustomers] = useState<Customer[]>([]);
  const [loading, setLoading] = useState(true);
  const [modalVisible, setModalVisible] = useState(false);
  const [history, setHistory] = useState<TravelHistoryItem[]>([]);
  const [historyLoading, setHistoryLoading] = useState(false);
  const [mapVisible, setMapVisible] = useState(false);

  const [selectedPickup, setSelectedPickup] = useState("");
  const [selectedDropoff, setSelectedDropoff] = useState("");
  const [selectedPickupLat, setSelectedPickupLat] = useState<number | null>(null);
  const [selectedPickupLng, setSelectedPickupLng] = useState<number | null>(null);
  const [selectedDropoffLat, setSelectedDropoffLat] = useState<number | null>(null);
  const [selectedDropoffLng, setSelectedDropoffLng] = useState<number | null>(null);

  // ----------------------
  // Fetch Assigned Customers + Normalize numeric fields
  // ----------------------
  useEffect(() => {
    const fetchAssignedCustomers = async () => {
      try {
        const user = await AsyncStorage.getItem("driver");
        if (!user) return;
        const { user_id } = JSON.parse(user);
        const res = await fetch(`http://192.168.1.12:5000/assigned-customers/${user_id}`);
        const data = await res.json();

        // Normalise the response so distance_km & total_fare are numbers or null
        const normalized: Customer[] = (Array.isArray(data) ? data : []).map((item: any) => {
          // API may use different names (total_cost / passenger_fare / total_fare) — prefer total_fare then total_cost then passenger_fare
          const rawTotal =
            item.total_fare ?? item.total_cost ?? item.passenger_fare ?? null;
          const total_fare =
            rawTotal !== null &&
            rawTotal !== undefined &&
            rawTotal !== "" &&
            !Number.isNaN(Number(rawTotal))
              ? Number(rawTotal)
              : null;

          const rawDistance = item.distance_km ?? item.distance ?? null;
          const distance_km =
            rawDistance !== null &&
            rawDistance !== undefined &&
            rawDistance !== "" &&
            !Number.isNaN(Number(rawDistance))
              ? Number(rawDistance)
              : null;

          const estimated_time =
            item.estimated_time ?? item.estimatedTime ?? item.est_time ?? null;

          return {
            ...item,
            total_fare,
            distance_km,
            estimated_time,
          } as Customer;
        });

        setCustomers(normalized);
      } catch (err) {
        console.warn("Failed to fetch assigned customers:", err);
      } finally {
        setLoading(false);
      }
    };

    fetchAssignedCustomers();
    const interval = setInterval(fetchAssignedCustomers, 5000);
    return () => clearInterval(interval);
  }, []);

  // ----------------------
  // Fetch Travel History
  // ----------------------
  const openTravelHistory = async () => {
    setModalVisible(true);
    setHistoryLoading(true);

    try {
      const user = await AsyncStorage.getItem("driver");
      if (!user) return;

      const { user_id } = JSON.parse(user);
      const res = await fetch(`http://192.168.1.12:5000/travel-history/${user_id}`);
      const data = await res.json();

      setHistory(data);
    } catch (err) {
      console.warn("Failed to fetch history:", err);
    } finally {
      setHistoryLoading(false);
    }
  };

  const activeCustomers = customers.filter(
    (item) => item.status.toLowerCase() !== "completed"
  );

  // ----------------------
  // Helper: Status Style
  // ----------------------
  const getStatusBadgeStyle = (status: string): StyleProp<TextStyle> => {
    const base: TextStyle = {
      color: "#fff",
      fontWeight: "700",
      paddingVertical: 4,
      paddingHorizontal: 10,
      borderRadius: 8,
      fontSize: 12,
      overflow: "hidden",
    };

    switch (status.toLowerCase()) {
      case "pending":
        return { ...base, backgroundColor: "#ECC94B" };
      case "ongoing":
        return { ...base, backgroundColor: "#4299E1" };
      case "completed":
        return { ...base, backgroundColor: "#48BB78" };
      default:
        return { ...base, backgroundColor: "#A0AEC0" };
    }
  };

  // ----------------------
  // Loading
  // ----------------------
  if (loading) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#5A67D8" />
        <Text style={styles.loadingText}>Loading your assigned customers...</Text>
      </View>
    );
  }

  // ----------------------
  // Render
  // ----------------------
  return (
    <SafeAreaView style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Assigned Customers</Text>
        <TouchableOpacity onPress={openTravelHistory} style={styles.historyButton}>
          <Ionicons name="time-outline" size={22} color="#fff" />
          <Text style={styles.historyButtonText}>History</Text>
        </TouchableOpacity>
      </View>

      {/* Customer List */}
      {activeCustomers.length === 0 ? (
        <View style={styles.emptyState}>
          <Ionicons name="car-outline" size={60} color="#A0AEC0" />
          <Text style={styles.emptyText}>No assigned customers found.</Text>
        </View>
      ) : (
        <FlatList
          data={activeCustomers}
          keyExtractor={(item) => item.reservation_ref}
          showsVerticalScrollIndicator={false}
          renderItem={({ item }) => (
            <View style={styles.card}>
              <View style={styles.cardHeader}>
                <Text style={styles.customerName}>
                  {item.customer_firstname} {item.customer_lastname}
                </Text>
                <Text style={getStatusBadgeStyle(item.status)}>
                  {item.status.toUpperCase()}
                </Text>
              </View>

              <View style={styles.cardRow}>
                <Ionicons name="location-outline" size={16} color="#5A67D8" />
                <Text style={styles.locationText}>Pickup: {item.pickup_location}</Text>
              </View>

              <View style={styles.cardRow}>
                <MaterialIcons name="flag" size={16} color="#48BB78" />
                <Text style={styles.locationText}>Dropoff: {item.dropoff_location}</Text>
              </View>

              <Text style={styles.vehicleText}>
                {item.car_brand} {item.model} • {item.vehicle_plate}
              </Text>

              {/* 🟩 Cost details (safely guarded) */}
              {(item.total_fare !== null || item.distance_km !== null || item.estimated_time) && (
                <View style={styles.fareContainer}>
                  {item.distance_km !== null && typeof item.distance_km === "number" && (
                    <Text style={styles.fareText}>
                      Distance: {item.distance_km.toFixed(2)} km
                    </Text>
                  )}
                  {item.estimated_time && (
                    <Text style={styles.fareText}>Est. Time: {item.estimated_time}</Text>
                  )}
                  {item.total_fare !== null && typeof item.total_fare === "number" && (
                    <Text style={styles.totalFareText}>
                      Total Fare: ₱ {item.total_fare.toFixed(2)}
                    </Text>
                  )}
                </View>
              )}

              <TouchableOpacity
                style={styles.mapButton}
                onPress={() => {
                  setSelectedPickup(item.pickup_location);
                  setSelectedDropoff(item.dropoff_location);
                  setSelectedPickupLat(item.pickup_latitude ?? null);
                  setSelectedPickupLng(item.pickup_longitude ?? null);
                  setSelectedDropoffLat(item.dropoff_latitude ?? null);
                  setSelectedDropoffLng(item.dropoff_longitude ?? null);
                  setMapVisible(true);
                }}
              >
                <Ionicons name="navigate-outline" size={18} color="#fff" />
                <Text style={styles.mapButtonText}>Show Direction</Text>
              </TouchableOpacity>
            </View>
          )}
        />
      )}

      {/* Map Modal */}
      <MapModal
        visible={mapVisible}
        onClose={() => setMapVisible(false)}
        pickup={selectedPickup}
        dropoff={selectedDropoff}
        pickupLat={selectedPickupLat}
        pickupLng={selectedPickupLng}
        dropoffLat={selectedDropoffLat}
        dropoffLng={selectedDropoffLng}
      />

      {/* Travel History Modal */}
      <Modal visible={modalVisible} animationType="slide">
        <SafeAreaView style={styles.modalContainer}>
          <View style={styles.modalHeader}>
            <TouchableOpacity onPress={() => setModalVisible(false)}>
              <Ionicons name="close" size={28} color="#4A5568" />
            </TouchableOpacity>
            <Text style={styles.modalTitle}>Travel History</Text>
          </View>

          {historyLoading ? (
            <ActivityIndicator size="large" color="#5A67D8" />
          ) : (
            <ScrollView style={{ marginTop: 10 }}>
              {history.length === 0 ? (
                <Text style={styles.emptyText}>No travel history available.</Text>
              ) : (
                history.map((item) => (
                  <View key={item.reservation_ref} style={styles.historyCard}>
                    <Text style={styles.historyText}>
                      <Text style={styles.historyLabel}>Trip Date: </Text>
                      {item.trip_date}
                    </Text>
                    <Text style={styles.historyText}>
                      <Text style={styles.historyLabel}>Pickup: </Text>
                      {item.pickup_location}
                    </Text>
                    <Text style={styles.historyText}>
                      <Text style={styles.historyLabel}>Dropoff: </Text>
                      {item.dropoff_location}
                    </Text>
                    <Text style={styles.historyText}>
                      <Text style={styles.historyLabel}>Vehicle: </Text>
                      {item.car_brand} {item.model} ({item.vehicle_plate})
                    </Text>
                    {item.driver_earnings !== undefined && (
                      <Text style={styles.earningsText}>₱ {item.driver_earnings}</Text>
                    )}
                  </View>
                ))
              )}
            </ScrollView>
          )}
        </SafeAreaView>
      </Modal>
    </SafeAreaView>
  );
}

// ----------------------
// Styles
// ----------------------
const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: "#F7FAFC", padding: 16 },
  header: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
    marginBottom: 12,
  },
  headerTitle: { fontSize: 22, fontWeight: "700", color: "#2D3748" },
  historyButton: {
    flexDirection: "row",
    backgroundColor: "#5A67D8",
    paddingVertical: 8,
    paddingHorizontal: 12,
    borderRadius: 10,
    alignItems: "center",
  },
  historyButtonText: { color: "#fff", marginLeft: 6, fontWeight: "600" },
  card: {
    backgroundColor: "#fff",
    padding: 16,
    borderRadius: 12,
    marginBottom: 14,
    shadowColor: "#000",
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 2,
  },
  cardHeader: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
    marginBottom: 8,
  },
  customerName: { fontSize: 18, fontWeight: "600", color: "#2D3748" },
  cardRow: { flexDirection: "row", alignItems: "center", marginBottom: 4 },
  locationText: { marginLeft: 6, color: "#4A5568", fontSize: 14 },
  vehicleText: { marginTop: 8, color: "#718096", fontStyle: "italic" },
  fareContainer: {
    marginTop: 6,
    backgroundColor: "#F0FFF4",
    borderRadius: 8,
    padding: 8,
  },
  fareText: {
    color: "#2F855A",
    fontSize: 14,
  },
  totalFareText: {
    marginTop: 4,
    fontWeight: "700",
    color: "#38A169",
    fontSize: 15,
  },
  mapButton: {
    flexDirection: "row",
    backgroundColor: "#5A67D8",
    justifyContent: "center",
    alignItems: "center",
    paddingVertical: 10,
    borderRadius: 8,
    marginTop: 10,
  },
  mapButtonText: { color: "#fff", marginLeft: 6, fontWeight: "600" },
  emptyState: {
    flex: 1,
    alignItems: "center",
    justifyContent: "center",
    marginTop: 60,
  },
  emptyText: { color: "#A0AEC0", marginTop: 10, fontSize: 16 },
  centerContainer: { flex: 1, justifyContent: "center", alignItems: "center" },
  loadingText: { marginTop: 10, color: "#4A5568" },
  modalContainer: { flex: 1, backgroundColor: "#fff", padding: 16 },
  modalHeader: { flexDirection: "row", alignItems: "center" },
  modalTitle: { fontSize: 20, fontWeight: "700", color: "#2D3748", marginLeft: 10 },
  historyCard: {
    backgroundColor: "#EDF2F7",
    borderRadius: 10,
    padding: 12,
    marginBottom: 10,
  },
  historyText: { color: "#4A5568", fontSize: 14, marginBottom: 2 },
  historyLabel: { fontWeight: "600" },
  earningsText: { color: "#38A169", fontWeight: "700", marginTop: 6 },
});
