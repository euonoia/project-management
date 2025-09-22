import React, { useEffect, useState } from 'react';
import { Modal, View, Text, TouchableOpacity } from 'react-native';
import MapView, { Marker, Polyline } from 'react-native-maps';
import { ORS_API_KEY } from '@env'; // ✅ Import from .env

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

  useEffect(() => {
    if (!pickupLat || !pickupLng || !dropoffLat || !dropoffLng) {
      setError('Missing coordinates for pickup/dropoff.');
      setLoading(false);
      return;
    }

    const fetchRoute = async () => {
      try {
        setLoading(true);

        const url = `https://api.openrouteservice.org/v2/directions/driving-car?api_key=${ORS_API_KEY}&start=${pickupLng},${pickupLat}&end=${dropoffLng},${dropoffLat}`;
        console.log('Fetching route:', url);

        const res = await fetch(url);
        const data = await res.json();

        if (!data.routes || data.routes.length === 0) {
          setError('No route found.');
          setLoading(false);
          return;
        }

        const coordsDecoded = data.routes[0].geometry.coordinates.map((c: [number, number]) => ({
          latitude: c[1],
          longitude: c[0],
        }));

        setCoords(coordsDecoded);
        setLoading(false);
      } catch (err) {
        console.error(err);
        setError('Failed to fetch route.');
        setLoading(false);
      }
    };

    fetchRoute();
  }, [pickupLat, pickupLng, dropoffLat, dropoffLng]);

  return (
    <Modal visible={visible} animationType="slide" onRequestClose={onClose}>
      <View style={{ flex: 1 }}>
        <TouchableOpacity onPress={onClose} style={{ alignSelf: 'flex-end', margin: 10 }}>
          <Text style={{ fontWeight: 'bold', fontSize: 16 }}>Close</Text>
        </TouchableOpacity>
        {error ? (
          <Text style={{ textAlign: 'center', marginTop: 20, color: 'red' }}>{error}</Text>
        ) : loading ? (
          <Text style={{ textAlign: 'center', marginTop: 20 }}>Loading map...</Text>
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
            <Marker coordinate={{ latitude: pickupLat!, longitude: pickupLng! }} title={`Pickup: ${pickup}`} />
            <Marker coordinate={{ latitude: dropoffLat!, longitude: dropoffLng! }} title={`Dropoff: ${dropoff}`} />
            {coords.length > 0 && (
              <Polyline coordinates={coords} strokeColor="#00f" strokeWidth={4} />
            )}
          </MapView>
        )}
      </View>
    </Modal>
  );
}
