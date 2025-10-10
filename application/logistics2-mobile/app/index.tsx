import React, { useState, useEffect } from "react";
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  Dimensions,
  PixelRatio,
  KeyboardAvoidingView,
  Platform,
} from "react-native";
import { useRouter } from "expo-router";
import AsyncStorage from "@react-native-async-storage/async-storage";
import Constants from "expo-constants";

const { width, height } = Dimensions.get("window");

// --- Responsive scaling helper
const scaleFont = (size: number) => {
  const scale = width / 380;
  return Math.round(PixelRatio.roundToNearestPixel(size * scale));
};

const API_URL = Constants.expoConfig?.extra?.API_URL;

export default function LoginScreen() {
  const router = useRouter();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");

  // Check existing session
  useEffect(() => {
    const checkSession = async () => {
      const user = await AsyncStorage.getItem("driver");
      if (user) {
        const { firstname, lastname } = JSON.parse(user);
        router.replace({
          pathname: "/(tabs)/home",
          params: { firstname, lastname },
        });
      }
    };
    checkSession();
  }, []);

  const handleLogin = async () => {
    console.log("Login button pressed");
    try {
      const res = await fetch(`${API_URL}/login`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, password }),
      });

      const data = await res.json();
      console.log("Server response:", data);

      if (data.success && data.role === "driver") {
        await AsyncStorage.setItem(
          "driver",
          JSON.stringify({
            user_id: data.user_id,
            firstname: data.firstname,
            lastname: data.lastname,
          })
        );
        router.replace("/(tabs)/home");
      } else if (data.success) {
        console.warn("Access Denied: Only drivers can log in here.");
      } else {
        console.warn("Login Failed:", data.message || "Invalid credentials");
      }
    } catch (err) {
      console.error("Error: Could not connect to server.", err);
    }
  };

  return (
    <KeyboardAvoidingView
      behavior={Platform.OS === "ios" ? "padding" : undefined}
      style={styles.container}
    >
      <View style={styles.card}>
        <Text style={styles.title}>Driver Login</Text>

        <TextInput
          style={styles.input}
          placeholder="Email"
          autoCapitalize="none"
          keyboardType="email-address"
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
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: "#fcfcfcff",
    justifyContent: "center",
    paddingHorizontal: width * 0.05,
  },
  card: {
    backgroundColor: "#fff",
    borderRadius: 16,
    padding: width * 0.06,
    shadowColor: "#000",
    shadowOpacity: 0.08,
    shadowRadius: 6,
    shadowOffset: { width: 0, height: 2 },
    elevation: 3,
    borderWidth: 1,
    borderColor: "#e5e7eb",
  },
  title: {
    fontSize: scaleFont(22),
    fontWeight: "700",
    color: "#1f2937",
    marginBottom: height * 0.03,
    textAlign: "center",
  },
  input: {
    borderWidth: 1,
    borderColor: "#e5e7eb",
    borderRadius: 10,
    paddingVertical: height * 0.015,
    paddingHorizontal: width * 0.04,
    marginBottom: height * 0.02,
    fontSize: scaleFont(14),
    color: "#1f2937",
  },
  button: {
    backgroundColor: "#9a3412",
    paddingVertical: height * 0.015,
    borderRadius: 10,
    alignItems: "center",
    borderWidth: 1,
    borderColor: "#7c2d12",
  },
  buttonText: {
    color: "#fff",
    fontWeight: "700",
    fontSize: scaleFont(16),
  },
});
