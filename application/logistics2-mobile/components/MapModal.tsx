import React, { useEffect, useState } from 'react';
import { Modal, View, Text, TouchableOpacity } from 'react-native';
import MapView, { Marker, Polyline } from 'react-native-maps';
// Import the API key from environment variables
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
    if (!pickup || !dropoff) return;

    const fetchCoords = async () => {
      setLoading(true);
      setError('');

      const pickupLatNum = Number(pickupLat);
      const pickupLngNum = Number(pickupLng);
      const dropoffLatNum = Number(dropoffLat);
      const dropoffLngNum = Number(dropoffLng);

      if (
        !isNaN(pickupLatNum) &&
        !isNaN(pickupLngNum) &&
        !isNaN(dropoffLatNum) &&
        !isNaN(dropoffLngNum)
      ) {
        setCoords([
          { latitude: pickupLatNum, longitude: pickupLngNum },
          { latitude: dropoffLatNum, longitude: dropoffLngNum },
        ]);
      } else {
        setError('Missing or invalid coordinates for pickup/dropoff.');
      }

      setLoading(false);
    };

    fetchCoords();
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
            {coords[0] && <Marker coordinate={coords[0]} title={`Pickup: ${pickup}`} />}
            {coords[1] && <Marker coordinate={coords[1]} title={`Dropoff: ${dropoff}`} />}
            {coords.length === 2 && (
              <Polyline coordinates={coords} strokeColor="#00f" strokeWidth={4} />
            )}
          </MapView>
        )}
      </View>
    </Modal>
  );
}
