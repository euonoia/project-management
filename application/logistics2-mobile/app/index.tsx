import React, { useState, useEffect } from 'react';
import { View, Text, TextInput, Button, StyleSheet, Alert, TouchableOpacity } from 'react-native';
import { useRouter } from 'expo-router';
import AsyncStorage from '@react-native-async-storage/async-storage';

export default function LoginScreen() {
  const router = useRouter();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');

  // Check AsyncStorage for existing session on mount
  useEffect(() => {
    const checkSession = async () => {
      const user = await AsyncStorage.getItem('driver');
      if (user) {
        const { firstname, lastname } = JSON.parse(user);
        router.replace({
          pathname: '/(tabs)/home',
          params: { firstname, lastname },
        });
      }
    };
    checkSession();
  }, []);

  const handleLogin = async () => {
  console.log("Login button pressed");
  try {
    const res = await fetch("http://192.168.1.12:5000/login", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ email, password }),
    });

    const data = await res.json();
    console.log("Server response:", data);

    if (data.success && data.role === "driver") {
      // Save driver data
      await AsyncStorage.setItem(
        "driver",
        JSON.stringify({
          user_id: data.user_id,
          firstname: data.firstname,
          lastname: data.lastname,
        })
      );

      // Instantly navigate to driver home screen
      router.replace("/(tabs)/home");
    } else if (data.success) {
      console.log("Access Denied: Only drivers can log in here.");
    } else {
      console.log("Login Failed:", data.message || "Invalid credentials");
    }
  } catch (err) {
    console.log("Error: Could not connect to server.", err);
  }
};


  return (
    <View style={styles.container}>
      <Text style={styles.title}>Driver Login</Text>
      <TextInput
        style={styles.input}
        placeholder="Email"
        autoCapitalize="none"
        value={email}
        onChangeText={setEmail}
      />
      <TextInput
        style={styles.input}
        placeholder="Password"
        secureTextEntry
        value={password}
        onChangeText={setPassword}
      />
      <TouchableOpacity style={styles.button} onPress={handleLogin}>
        <Text style={styles.buttonText}>Login</Text>
      </TouchableOpacity>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, justifyContent: 'center', padding: 24, backgroundColor: '#fff' },
  title: { fontSize: 24, fontWeight: 'bold', marginBottom: 24, textAlign: 'center' },
  input: { borderWidth: 1, borderColor: '#ccc', padding: 12, marginBottom: 16, borderRadius: 8 },
  button: { backgroundColor: '#007bff', padding: 12, borderRadius: 8, alignItems: 'center' },
  buttonText: { color: '#fff', fontWeight: 'bold' },
});