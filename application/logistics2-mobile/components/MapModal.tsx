import React, { useEffect, useState } from 'react';
import { Modal, View, Text, TouchableOpacity } from 'react-native';
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
            [pickupLng, pickupLat], // ORS requires [lon, lat]
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

        // ORS GeoJSON polyline
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
      let b,
        shift = 0,
        result = 0;
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
      <View style={{ flex: 1 }}>
        <TouchableOpacity onPress={onClose} style={{ alignSelf: 'flex-end', margin: 10 }}>
          <Text style={{ fontWeight: 'bold', fontSize: 16 }}>Close</Text>
        </TouchableOpacity>

        {error ? (
          <Text style={{ textAlign: 'center', marginTop: 20, color: 'red' }}>{error}</Text>
        ) : loading ? (
          <Text style={{ textAlign: 'center', marginTop: 20 }}>Loading route...</Text>
        ) : (
          <MapView
            style={{ flex: 1 }}
            initialRegion={{
              latitude: coords[0]?.latitude || 14.5995,
              longitude: coords[0]?.longitude || 120.9842,
              latitudeDelta: 0.05,
              longitudeDelta: 0.05,
            }}
          >
            {coords.length > 0 && (
              <>
                <Marker coordinate={coords[0]} title={`Pickup: ${pickup}`} />
                <Marker coordinate={coords[coords.length - 1]} title={`Dropoff: ${dropoff}`} />
                <Polyline coordinates={coords} strokeColor="#00f" strokeWidth={4} />
              </>
            )}
          </MapView>
        )}
      </View>
    </Modal>
  );
}
