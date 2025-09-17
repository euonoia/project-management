import React, { useEffect, useState } from 'react';
import { View, Text, FlatList, Button, Modal, TouchableOpacity } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';

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

  useEffect(() => {
    let interval: any;
    const fetchAssignedCustomers = async () => {
      // Get the current logged-in driver info from AsyncStorage
      const user = await AsyncStorage.getItem('driver');
      if (!user) return;
      const { user_id } = JSON.parse(user); // Make sure user_id is saved in AsyncStorage after login

      // Fetch assigned customers from your backend
      const res = await fetch(`http://192.168.1.3:5000/assigned-customers/${user_id}`);
      const data = await res.json();
      setCustomers(data);
      setLoading(false);
    };
    fetchAssignedCustomers();
    interval = setInterval(fetchAssignedCustomers, 5000); // Poll every 5 seconds
    return () => clearInterval(interval);
  }, []);

  const openTravelHistory = async () => {
    setModalVisible(true);
    setHistoryLoading(true);
    const user = await AsyncStorage.getItem('driver');
    if (!user) return;
    const { user_id } = JSON.parse(user);

    const res = await fetch(`http://192.168.1.3:5000/travel-history/${user_id}`);
    const data = await res.json();
    setHistory(data);
    setHistoryLoading(false);
  };

  if (loading) return <Text style={{ textAlign: 'center', marginTop: 20 }}>Loading...</Text>;

  // Filter out completed customers
  const activeCustomers = customers.filter(item => item.status.toLowerCase() !== 'completed');

  return (
    <View style={{ flex: 1, padding: 16 }}>
      <Text style={{ fontSize: 20, fontWeight: 'bold', marginBottom: 12 }}>Assigned Customers</Text>
      <FlatList
        data={activeCustomers}
        keyExtractor={item => item.reservation_ref}
        renderItem={({ item }) => (
          <View style={{ padding: 12, borderBottomWidth: 1, borderColor: '#eee' }}>
            <Text>Customer: {item.customer_name} ({item.customer_firstname} {item.customer_lastname})</Text>
            <Text>Pickup: {item.pickup_location}</Text>
            <Text>Dropoff: {item.dropoff_location}</Text>
            <Text>Vehicle: {item.vehicle_plate} ({item.car_brand} {item.model})</Text>
            <Text>Status: {item.status}</Text>
          </View>
        )}
        ListEmptyComponent={<Text>No assigned customers found.</Text>}
      />
      <Button
        title="View Travel History"
        onPress={openTravelHistory}
      />

      <Modal
        visible={modalVisible}
        animationType="slide"
        onRequestClose={() => setModalVisible(false)}
        transparent={false}
      >
        <View style={{ flex: 1, padding: 16, backgroundColor: '#fff' }}>
          <TouchableOpacity
            onPress={() => setModalVisible(false)}
            style={{ alignSelf: 'flex-end', marginBottom: 10, padding: 8 }}
          >
            <Text style={{ fontWeight: 'bold', fontSize: 16 }}>Close</Text>
          </TouchableOpacity>
          <Text style={{ fontSize: 20, fontWeight: 'bold', marginBottom: 12 }}>Travel History</Text>
          {historyLoading ? (
            <Text style={{ textAlign: 'center', marginTop: 20 }}>Loading...</Text>
          ) : (
            <FlatList
              data={history}
              keyExtractor={item => item.reservation_ref}
              renderItem={({ item }) => (
                <View style={{ padding: 12, borderBottomWidth: 1, borderColor: '#eee' }}>
                  <Text>Reservation Ref: {item.reservation_ref}</Text>
                  <Text>Date: {item.trip_date}</Text>
                  <Text>Pickup: {item.pickup_location}</Text>
                  <Text>Dropoff: {item.dropoff_location}</Text>
                  <Text>Vehicle: {item.vehicle_plate} ({item.car_brand} {item.model})</Text>
                  <Text>Status: {item.status}</Text>
                  {item.driver_earnings !== undefined && (
                    <Text>Earnings: ₱ {item.driver_earnings}</Text>
                  )}
                </View>
              )}
              ListEmptyComponent={<Text>No travel history found.</Text>}
            />
          )}
        </View>
      </Modal>
    </View>
  );
}