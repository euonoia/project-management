import React, { useEffect, useState } from 'react';
import { Modal, View, Text, TouchableOpacity, ActivityIndicator, StyleSheet, Dimensions } from 'react-native';
import MapView, { Marker, Polyline } from 'react-native-maps';
import Constants from 'expo-constants';

interface MapModalProps {
  visible: boolean;
  onClose: () => void;
  pickup: string;
  dropoff: string;
  pickupLat?: number | null;
  pickupLng?: number | null;
  dropoffLat?: number | null;
  dropoffLng?: number | null;
}

export default function MapModal({
  visible,
  onClose,
  pickup,
  dropoff,
  pickupLat,
  pickupLng,
  dropoffLat,
  dropoffLng,
}: MapModalProps) {
  const [coords, setCoords] = useState<{ latitude: number; longitude: number }[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const orsApiKey = Constants.expoConfig?.extra?.orsApiKey;

  useEffect(() => {
    if (!pickupLat || !pickupLng || !dropoffLat || !dropoffLng) return;

    const fetchRoute = async () => {
      try {
        setLoading(true);
        setError('');

        const url = 'https://api.openrouteservice.org/v2/directions/driving-car';
        const body = {
          coordinates: [
            [pickupLng, pickupLat],
            [dropoffLng, dropoffLat],
          ],
        };

        const res = await fetch(url, {
          method: 'POST',
          headers: {
            Authorization: orsApiKey || '',
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(body),
        });

        const data = await res.json();

        if (!data.routes || data.routes.length === 0) {
          setError('No route found.');
          setLoading(false);
          return;
        }

        const geometry = data.routes[0].geometry;
        const decoded = decodePolyline(geometry);

        if (decoded.length > 0) {
          setCoords(decoded);
        } else {
          setError('Failed to decode route.');
        }
      } catch (err: any) {
        setError('Error fetching route: ' + err.message);
      } finally {
        setLoading(false);
      }
    };

    fetchRoute();
  }, [pickupLat, pickupLng, dropoffLat, dropoffLng]);

  // Decode ORS polyline
  function decodePolyline(encoded: string, precision = 5) {
    let index = 0,
      lat = 0,
      lng = 0,
      coordinates: { latitude: number; longitude: number }[] = [];
    const factor = Math.pow(10, precision);

    while (index < encoded.length) {
      let b, shift = 0, result = 0;
      do {
        b = encoded.charCodeAt(index++) - 63;
        result |= (b & 0x1f) << shift;
        shift += 5;
      } while (b >= 0x20);
      const deltaLat = result & 1 ? ~(result >> 1) : result >> 1;
      lat += deltaLat;

      shift = 0;
      result = 0;
      do {
        b = encoded.charCodeAt(index++) - 63;
        result |= (b & 0x1f) << shift;
        shift += 5;
      } while (b >= 0x20);
      const deltaLng = result & 1 ? ~(result >> 1) : result >> 1;
      lng += deltaLng;

      coordinates.push({ latitude: lat / factor, longitude: lng / factor });
    }

    return coordinates;
  }

  return (
    <Modal visible={visible} animationType="slide" onRequestClose={onClose}>
      <View style={styles.container}>
        {/* Header */}
        <View style={styles.header}>
          <Text style={styles.title}>Trip Route</Text>
          <TouchableOpacity onPress={onClose} style={styles.closeBtn}>
            <Text style={styles.closeText}>✕</Text>
          </TouchableOpacity>
        </View>

        {/* Info Card */}
        <View style={styles.infoCard}>
          <Text style={styles.label}>Pickup:</Text>
          <Text style={styles.value}>{pickup}</Text>

          <Text style={[styles.label, { marginTop: 8 }]}>Dropoff:</Text>
          <Text style={styles.value}>{dropoff}</Text>
        </View>

        {/* Map or Loading/Error */}
        <View style={styles.mapContainer}>
          {error ? (
            <Text style={styles.errorText}>{error}</Text>
          ) : loading ? (
            <View style={styles.loadingBox}>
              <ActivityIndicator size="large" color="#4F46E5" />
              <Text style={styles.loadingText}>Fetching best route...</Text>
            </View>
          ) : (
            <MapView
              style={styles.map}
              initialRegion={{
                latitude: coords[0]?.latitude || 14.5995,
                longitude: coords[0]?.longitude || 120.9842,
                latitudeDelta: 0.05,
                longitudeDelta: 0.05,
              }}
            >
              {coords.length > 0 && (
                <>
                  <Marker coordinate={coords[0]} title={`Pickup: ${pickup}`} pinColor="#16A34A" />
                  <Marker coordinate={coords[coords.length - 1]} title={`Dropoff: ${dropoff}`} pinColor="#DC2626" />
                  <Polyline coordinates={coords} strokeColor="#4F46E5" strokeWidth={4} />
                </>
              )}
            </MapView>
          )}
        </View>
      </View>
    </Modal>
  );
}

const { height } = Dimensions.get('window');

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F9FAFB',
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingTop: 14,
    paddingBottom: 10,
    borderBottomWidth: 1,
    borderColor: '#E5E7EB',
    backgroundColor: '#FFFFFF',
    elevation: 3,
  },
  title: {
    fontSize: 18,
    fontWeight: '700',
    color: '#111827',
  },
  closeBtn: {
    backgroundColor: '#EEF2FF',
    borderRadius: 50,
    paddingHorizontal: 10,
    paddingVertical: 4,
  },
  closeText: {
    fontSize: 18,
    color: '#4F46E5',
    fontWeight: 'bold',
  },
  infoCard: {
    backgroundColor: '#FFFFFF',
    marginHorizontal: 16,
    marginTop: 12,
    borderRadius: 12,
    padding: 12,
    shadowColor: '#000',
    shadowOpacity: 0.05,
    shadowRadius: 4,
    elevation: 1,
  },
  label: {
    fontSize: 13,
    fontWeight: '600',
    color: '#6B7280',
  },
  value: {
    fontSize: 14,
    color: '#111827',
  },
  mapContainer: {
    flex: 1,
    marginTop: 12,
    marginHorizontal: 8,
    borderRadius: 12,
    overflow: 'hidden',
    height: height * 0.7,
  },
  map: {
    flex: 1,
  },
  loadingBox: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    marginTop: 10,
    fontSize: 14,
    color: '#4B5563',
  },
  errorText: {
    textAlign: 'center',
    marginTop: 20,
    color: '#DC2626',
    fontWeight: '600',
  },
});
