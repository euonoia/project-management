/*
  Firebase Web v10 Authentication Setup (ESM, CDN)
  Usage (HTML):
    <script>
      window.FIREBASE_CONFIG = {
        apiKey: "YOUR_API_KEY",
        authDomain: "YOUR_PROJECT.firebaseapp.com",
        projectId: "YOUR_PROJECT_ID",
        appId: "YOUR_APP_ID",
        // optional: measurementId, storageBucket, messagingSenderId, etc.
      };
    </script>
    <script type="module" src="./config/firebase.js"></script>
    <script type="module">
      import { emailSignIn, onAuth, getAuthToken, googleSignIn } from './config/firebase.js';
      onAuth(async (user) => {
        console.log('Auth state:', user?.uid || null);
        if (user) {
          const token = await getAuthToken();
          // Example: send token to your PHP backend for verification
          // await fetch('/api/session/login', { method:'POST', headers: { 'Content-Type':'application/json' }, body: JSON.stringify({ token }) });
        }
      });
    </script>
*/
import { initializeApp, getApps, getApp } from 'https://www.gstatic.com/firebasejs/10.12.1/firebase-app.js';
import {
  getAuth,
  setPersistence,
  browserLocalPersistence,
  indexedDBLocalPersistence,
  inMemoryPersistence,
  onAuthStateChanged,
  onIdTokenChanged,
  signInWithEmailAndPassword,
  createUserWithEmailAndPassword,
  signOut,
  GoogleAuthProvider,
  signInWithPopup,
} from 'https://www.gstatic.com/firebasejs/10.12.1/firebase-auth.js';

// Require config to be present (injected by firebase-config.php)
if (!(typeof window !== 'undefined' && window.FIREBASE_CONFIG)) {
  throw new Error('FIREBASE_CONFIG not found. Ensure you include config/firebase-config.php before firebase.js');
}
const firebaseConfig = window.FIREBASE_CONFIG;

// Initialize app singleton
export const app = getApps().length ? getApp() : initializeApp(firebaseConfig);
export const auth = getAuth(app);

// Configure persistence with a sensible fallback chain
(async () => {
  try {
    // Prefer IndexedDB (best for large apps), fall back to localStorage, then memory
    await setPersistence(auth, indexedDBLocalPersistence);
  } catch (_) {
    try { await setPersistence(auth, browserLocalPersistence); }
    catch { await setPersistence(auth, inMemoryPersistence); }
  }
})();

// Optional: configure language for auth flows
// auth.languageCode = 'en';

// Providers
const googleProvider = new GoogleAuthProvider();

// --------------- Helpers ---------------
export function onAuth(cb) {
  // cb receives (user|null)
  return onAuthStateChanged(auth, cb);
}

export function onTokenChange(cb) {
  // cb receives (user|null)
  return onIdTokenChanged(auth, cb);
}

export async function emailSignIn(email, password) {
  if (!email || !password) throw new Error('Email and password are required');
  const res = await signInWithEmailAndPassword(auth, email, password);
  return res.user;
}

export async function emailSignUp(email, password) {
  if (!email || !password) throw new Error('Email and password are required');
  const res = await createUserWithEmailAndPassword(auth, email, password);
  return res.user;
}

export async function googleSignIn() {
  const res = await signInWithPopup(auth, googleProvider);
  return res.user;
}

export async function signOutUser() {
  await signOut(auth);
}

export async function getAuthToken(forceRefresh = false) {
  const user = auth.currentUser;
  if (!user) return null;
  return await user.getIdToken(forceRefresh);
}

export function getCurrentUser() {
  return auth.currentUser;
}

// Attaches Authorization: Bearer <token> header to fetch
export async function fetchWithAuth(input, init = {}) {
  const token = await getAuthToken();
  const headers = new Headers(init.headers || {});
  if (token) headers.set('Authorization', `Bearer ${token}`);
  return fetch(input, { ...init, headers });
}

// For debugging (disable in production if needed)
if (typeof window !== 'undefined') {
  window.__firebaseAuth = { app, auth, onAuth, onTokenChange, emailSignIn, emailSignUp, googleSignIn, signOutUser, getAuthToken, getCurrentUser, fetchWithAuth };
}
