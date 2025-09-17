import React, { useEffect, useState } from 'react';
import { Modal, View, Text, TouchableOpacity } from 'react-native';
import MapView, { Marker, Polyline } from 'react-native-maps';

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

const geocode = async (address: string) => {
  try {
    const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`);
    if (!res.ok) return null;
    const text = await res.text();
    const data = JSON.parse(text);
    if (data && data[0]) return [data[0].lat, data[0].lon];
  } catch (error) {
    console.error(error);
  }
  return null;
};

export default function MapModal({
  visible,
  onClose,
  pickup,
  dropoff,
  pickupLat,
  pickupLng,
  dropoffLat,
  dropoffLng
}: MapModalProps) {
  const [coords, setCoords] = useState<{latitude: number, longitude: number}[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
  if (!pickup || !dropoff) return;
  const fetchDirections = async () => {
    setLoading(true);
    setError('');
    let pickupCoord, dropoffCoord;

    console.log('pickupLat:', pickupLat, 'pickupLng:', pickupLng);
    console.log('dropoffLat:', dropoffLat, 'dropoffLng:', dropoffLng);

    if (
      typeof pickupLat === 'number' &&
      typeof pickupLng === 'number' &&
      typeof dropoffLat === 'number' &&
      typeof dropoffLng === 'number'
    ) {
      pickupCoord = [pickupLat, pickupLng];
      dropoffCoord = [dropoffLat, dropoffLng];
      console.log('Using coordinates from props:', pickupCoord, dropoffCoord);
    } else {
      pickupCoord = await geocode(pickup);
      dropoffCoord = await geocode(dropoff);
      console.log('Geocoded coordinates:', pickupCoord, dropoffCoord);
    }

    if (pickupCoord && dropoffCoord) {
      setCoords([
        { latitude: parseFloat(pickupCoord[0]), longitude: parseFloat(pickupCoord[1]) },
        { latitude: parseFloat(dropoffCoord[0]), longitude: parseFloat(dropoffCoord[1]) }
      ]);
    } else {
      setError('Failed to fetch location. Please try again later.');
    }
    setLoading(false);
  };
  fetchDirections();
}, [pickup, dropoff, pickupLat, pickupLng, dropoffLat, dropoffLng]);

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
            {coords[0] && <Marker coordinate={coords[0]} title="Pickup" />}
            {coords[1] && <Marker coordinate={coords[1]} title="Dropoff" />}
            {coords.length === 2 && (
              <Polyline coordinates={coords} strokeColor="#00f" strokeWidth={4} />
            )}
          </MapView>
        )}
      </View>
    </Modal>
  );
}