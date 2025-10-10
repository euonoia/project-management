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
  Dimensions,
  PixelRatio,
  StyleProp,
  TextStyle,
} from "react-native";
import AsyncStorage from "@react-native-async-storage/async-storage";
import MapModal from "../components/MapModal";
import { MaterialIcons, Ionicons } from "@expo/vector-icons";
import Constants  from "expo-constants";
const { width, height } = Dimensions.get("window");
const API_URL = Constants.expoConfig?.extra?.API_URL;

// --- Responsive scaling helper
const scaleFont = (size: number) => {
  const scale = width / 380;
  return Math.round(PixelRatio.roundToNearestPixel(size * scale));
};

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

  useEffect(() => {
    const fetchAssignedCustomers = async () => {
      try {
        const user = await AsyncStorage.getItem("driver");
        if (!user) return;
        const { user_id } = JSON.parse(user);
        const res = await fetch(`${API_URL}/assigned-customers/${user_id}`);
        const data = await res.json();

        const normalized: Customer[] = (Array.isArray(data) ? data : []).map((item: any) => {
          const rawTotal = item.total_fare ?? item.total_cost ?? item.passenger_fare ?? null;
          const total_fare = Number.isFinite(Number(rawTotal)) ? Number(rawTotal) : null;

          const rawDistance = item.distance_km ?? item.distance ?? null;
          const distance_km = Number.isFinite(Number(rawDistance)) ? Number(rawDistance) : null;

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

  const openTravelHistory = async () => {
    setModalVisible(true);
    setHistoryLoading(true);
    try {
      const user = await AsyncStorage.getItem("driver");
      if (!user) return;
      const { user_id } = JSON.parse(user);
      const res = await fetch(`${API_URL}/travel-history/${user_id}`);
      const data = await res.json();
      setHistory(data);
    } catch (err) {
      console.warn("Failed to fetch history:", err);
    } finally {
      setHistoryLoading(false);
    }
  };

  const activeCustomers = customers.filter(
    (item) => item.status && item.status.toLowerCase() !== "completed"
  );

  const getStatusBadgeStyle = (status: string): StyleProp<TextStyle> => {
    const base: TextStyle = {
      color: "#fff",
      fontWeight: "700",
      paddingVertical: 4,
      paddingHorizontal: 10,
      borderRadius: 8,
      fontSize: scaleFont(12),
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

  if (loading) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#9a3412" />
        <Text style={styles.loadingText}>Loading your assigned customers...</Text>
      </View>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Assigned Customers</Text>
        <TouchableOpacity onPress={openTravelHistory} style={styles.historyButton}>
          <Ionicons name="time-outline" size={scaleFont(22)} color="#fff" />
          <Text style={styles.historyButtonText}>History</Text>
        </TouchableOpacity>
      </View>

      {activeCustomers.length === 0 ? (
        <View style={styles.emptyState}>
          <Ionicons name="car-outline" size={scaleFont(60)} color="#A0AEC0" />
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
                <Text style={getStatusBadgeStyle(item.status)}>{item.status.toUpperCase()}</Text>
              </View>

              <View style={styles.cardRow}>
                <Ionicons name="location-outline" size={scaleFont(16)} color="#9a3412" />
                <Text style={styles.locationText}>Pickup: {item.pickup_location}</Text>
              </View>

              <View style={styles.cardRow}>
                <MaterialIcons name="flag" size={scaleFont(16)} color="#7c2d12" />
                <Text style={styles.locationText}>Dropoff: {item.dropoff_location}</Text>
              </View>

              <Text style={styles.vehicleText}>
                {item.car_brand} {item.model} • {item.vehicle_plate}
              </Text>

              {(item.total_fare || item.distance_km || item.estimated_time) && (
                <View style={styles.fareContainer}>
                  {item.distance_km && (
                    <Text style={styles.fareText}>
                      Distance: {item.distance_km.toFixed(2)} km
                    </Text>
                  )}
                  {item.estimated_time && (
                    <Text style={styles.fareText}>Est. Time: {item.estimated_time}</Text>
                  )}
                  {item.total_fare && (
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
                <Ionicons name="navigate-outline" size={scaleFont(18)} color="#fff" />
                <Text style={styles.mapButtonText}>Show Direction</Text>
              </TouchableOpacity>
            </View>
          )}
        />
      )}

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

      <Modal visible={modalVisible} animationType="slide">
        <SafeAreaView style={styles.modalContainer}>
          <View style={styles.modalHeader}>
            <TouchableOpacity onPress={() => setModalVisible(false)}>
              <Ionicons name="close" size={scaleFont(28)} color="#4A5568" />
            </TouchableOpacity>
            <Text style={styles.modalTitle}>Travel History</Text>
          </View>

          {historyLoading ? (
            <ActivityIndicator size="large" color="#9a3412" />
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

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: "#fcfcfcff",
    padding: width * 0.04,
  },
  header: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
    marginBottom: height * 0.015,
  },
  headerTitle: {
    fontSize: scaleFont(22),
    fontWeight: "700",
    color: "#1f2937",
  },
  historyButton: {
    flexDirection: "row",
    backgroundColor: "#9a3412",
    paddingVertical: height * 0.01,
    paddingHorizontal: width * 0.03,
    borderRadius: 10,
    alignItems: "center",
  },
  historyButtonText: {
    color: "#fff",
    marginLeft: 6,
    fontWeight: "600",
    fontSize: scaleFont(14),
  },
  card: {
    backgroundColor: "#fff",
    padding: width * 0.04,
    borderRadius: 12,
    marginBottom: height * 0.02,
    shadowColor: "#000",
    shadowOpacity: 0.08,
    shadowRadius: 4,
    elevation: 2,
    borderWidth: 1,
    borderColor: "#e5e7eb",
  },
  cardHeader: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
    marginBottom: 8,
  },
  customerName: {
    fontSize: scaleFont(18),
    fontWeight: "600",
    color: "#1f2937",
  },
  cardRow: {
    flexDirection: "row",
    alignItems: "center",
    marginBottom: 4,
  },
  locationText: {
    marginLeft: 6,
    color: "#374151",
    fontSize: scaleFont(14),
  },
  vehicleText: {
    marginTop: 8,
    color: "#4b5563",
    fontStyle: "italic",
    fontSize: scaleFont(13),
  },
  fareContainer: {
    marginTop: 6,
    backgroundColor: "#fff",
    borderRadius: 8,
    padding: 8,
  },
  fareText: {
    color: "#9a3412",
    fontSize: scaleFont(14),
  },
  totalFareText: {
    marginTop: 4,
    fontWeight: "700",
    color: "#7c2d12",
    fontSize: scaleFont(15),
  },
  mapButton: {
    flexDirection: "row",
    backgroundColor: "#9a3412",
    justifyContent: "center",
    alignItems: "center",
    paddingVertical: height * 0.012,
    borderRadius: 8,
    marginTop: 10,
  },
  mapButtonText: {
    color: "#fff",
    marginLeft: 6,
    fontWeight: "600",
    fontSize: scaleFont(14),
  },
  emptyState: {
    flex: 1,
    alignItems: "center",
    justifyContent: "center",
    marginTop: 60,
  },
  emptyText: {
    color: "#6b7280",
    marginTop: 10,
    fontSize: scaleFont(16),
  },
  centerContainer: {
    flex: 1,
    justifyContent: "center",
    alignItems: "center",
  },
  loadingText: {
    marginTop: 10,
    color: "#374151",
  },
  modalContainer: {
    flex: 1,
    backgroundColor: "#fff",
    padding: width * 0.04,
  },
  modalHeader: {
    flexDirection: "row",
    alignItems: "center",
  },
  modalTitle: {
    fontSize: scaleFont(20),
    fontWeight: "700",
    color: "#1f2937",
    marginLeft: 10,
  },
  historyCard: {
    backgroundColor: "#f3f4f6",
    borderRadius: 10,
    padding: 12,
    marginBottom: 10,
  },
  historyText: {
    color: "#374151",
    fontSize: scaleFont(14),
    marginBottom: 2,
  },
  historyLabel: {
    fontWeight: "600",
  },
  earningsText: {
    color: "#9a3412",
    fontWeight: "700",
    marginTop: 6,
  },
});
