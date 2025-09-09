import React, { useEffect, useState } from 'react';
import { View, Text, FlatList } from 'react-native';
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

export default function AssignedUser() {
  const [customers, setCustomers] = useState<Customer[]>([]);
  const [loading, setLoading] = useState(true);

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

  if (loading) return <Text style={{ textAlign: 'center', marginTop: 20 }}>Loading...</Text>;

  return (
    <View style={{ flex: 1, padding: 16 }}>
      <Text style={{ fontSize: 20, fontWeight: 'bold', marginBottom: 12 }}>Assigned Customers</Text>
      <FlatList
        data={customers}
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
    </View>
  )};