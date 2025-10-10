// app/(tabs)/assigneduser.tsx
import React from 'react';
import { SafeAreaView } from 'react-native-safe-area-context';
import { StyleSheet, Platform, StatusBar } from 'react-native';
import AssignedUser from '../../components/AssignedUser';

export default function AssignedUserScreen() {
  return (
    <SafeAreaView style={styles.safeArea} edges={['top', 'left', 'right']}>
      <AssignedUser />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#F7FAFC', // match your AssignedUser background
    paddingTop: Platform.OS === 'android' ? StatusBar.currentHeight : 0,
  },
});
