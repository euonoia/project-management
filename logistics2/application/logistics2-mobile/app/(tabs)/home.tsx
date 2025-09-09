import React, { useEffect } from 'react';
import { View, Text, Button } from 'react-native';
import { useLocalSearchParams, useRouter } from 'expo-router';
import AsyncStorage from '@react-native-async-storage/async-storage';

export default function Home() {
  const { firstname, lastname } = useLocalSearchParams();
  const router = useRouter();

  useEffect(() => {
    // Optionally, check AsyncStorage for session here
    if (!firstname || !lastname) {
      router.replace('/');
    }
  }, [firstname, lastname]);

  const handleLogout = async () => {
    // Clear session from AsyncStorage
    await AsyncStorage.clear();
    router.replace('/');
  };

  return (
    <View>
      <Text>
        {firstname && lastname
          ? `Welcome, Driver ${firstname} ${lastname}!`
          : ''}
      </Text>
      <Button title="Logout" onPress={handleLogout} />
      {/* ...other home content... */}
    </View>
  );
}