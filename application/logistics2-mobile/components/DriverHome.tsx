import React, { useState, useEffect } from 'react';
import {
  ScrollView,
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  Dimensions,
} from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import Constants from 'expo-constants';
import { useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { Colors } from '@/constants/Colors';

const { width } = Dimensions.get('window');
const API_URL = Constants.expoConfig?.extra?.API_URL;

interface Driver {
  user_id?: number;
  firstname?: string;
  lastname?: string;
}

export default function DriverHome() {
  const router = useRouter();
  const [driver, setDriver] = useState<Driver>({});
  const [earnings, setEarnings] = useState<number | null>(null);
  const [assignedRides, setAssignedRides] = useState<number | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const loadDriver = async () => {
      const storedDriver = await AsyncStorage.getItem('driver');
      if (!storedDriver) return router.replace('/');
      setDriver(JSON.parse(storedDriver));
    };
    loadDriver();
  }, []);

  useEffect(() => {
    const fetchEarnings = async () => {
      if (!driver.user_id) return;
      setLoading(true);
      try {
        const res = await fetch(`${API_URL}/user/${driver.user_id}/total-earnings`);
        const data = await res.json();
        setEarnings(data.total_earnings ? parseFloat(data.total_earnings) : null);
      } catch {
        setEarnings(null);
      } finally {
        setLoading(false);
      }
    };
    fetchEarnings();
  }, [driver.user_id]);

  useEffect(() => {
    const fetchAssigned = async () => {
      if (!driver.user_id) return;
      try {
        const res = await fetch(`${API_URL}/user/${driver.user_id}/assigned-count`);
        const data = await res.json();
        setAssignedRides(data.assigned_count ?? 0);
      } catch {
        setAssignedRides(null);
      }
    };
    fetchAssigned();
  }, [driver.user_id]);

  const handleLogout = async () => {
    await AsyncStorage.clear();
    router.replace('/');
  };

  return (
    <ScrollView contentContainerStyle={[styles.container, { backgroundColor: Colors.light.background }]} showsVerticalScrollIndicator={false}>
      <Text style={[styles.greeting, { color: Colors.light.text }]}>
        Welcome, Driver {driver.firstname ?? ''} {driver.lastname ?? ''}!
      </Text>
      <Text style={[styles.subtext, { color: Colors.light.subtext }]}>
        Here’s your current overview
      </Text>

      <View style={styles.grid}>
        <View style={styles.card}>
          <Ionicons name="car-outline" size={width * 0.08} color={Colors.light.primary} />
          <Text style={styles.cardTitle}>Assigned Rides</Text>
          <Text style={styles.cardSubtitle}>
            {assignedRides !== null ? `${assignedRides} ride${assignedRides !== 1 ? 's' : ''}` : 'Loading...'}
          </Text>
        </View>

        <View style={styles.card}>
          <Ionicons name="wallet-outline" size={width * 0.08} color={Colors.light.primary} />
          <Text style={styles.cardTitle}>Earnings</Text>
          <Text style={styles.cardSubtitle}>
            {loading ? 'Loading...' : earnings !== null ? `₱${earnings.toFixed(2)}` : 'No earnings yet'}
          </Text>
        </View>
      </View>

      <TouchableOpacity style={styles.logoutButton} onPress={handleLogout}>
        <Text style={styles.logoutText}>Logout</Text>
      </TouchableOpacity>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: { paddingVertical: 40, paddingHorizontal: 20, flexGrow: 1 },
  greeting: { fontSize: width * 0.055, fontWeight: '700', marginBottom: 4 },
  subtext: { fontSize: width * 0.035, marginBottom: 20 },
  grid: { flexDirection: 'row', flexWrap: 'wrap', justifyContent: 'space-between' },
  card: {
    backgroundColor: '#fff',
    width: width < 400 ? '48%' : '47%',
    borderRadius: 16,
    padding: 16,
    marginBottom: 16,
    borderWidth: 1,
    borderColor: '#e5e7eb',
    shadowColor: '#000',
    shadowOpacity: 0.08,
    shadowOffset: { width: 0, height: 2 },
    shadowRadius: 4,
    elevation: 3,
    alignItems: 'center',
  },
  cardTitle: { fontSize: width * 0.04, fontWeight: '600', marginTop: 8 },
  cardSubtitle: { fontSize: width * 0.032, marginTop: 2 },
  logoutButton: {
    backgroundColor: Colors.light.primary,
    paddingVertical: 14,
    borderRadius: 10,
    borderWidth: 1,
    borderColor: Colors.light.secondary,
  },
  logoutText: { color: '#fff', textAlign: 'center', fontWeight: '600', fontSize: width * 0.04 },
});
