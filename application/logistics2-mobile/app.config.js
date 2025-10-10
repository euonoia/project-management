import 'dotenv/config';

export default {
  expo: {
    name: 'logistics2-mobile',
    slug: 'logistics2-mobile',
    version: '1.0.0',
    entryPoint: './node_modules/expo-router/entry',
    orientation: 'portrait',
    icon: './assets/images/icon.png',
    scheme: 'logistics2mobile',
    userInterfaceStyle: 'automatic',
    newArchEnabled: true,
    ios: { supportsTablet: true },
    android: {
      adaptiveIcon: {
        foregroundImage: './assets/images/adaptive-icon.png',
        backgroundColor: '#ffffff',
      },
      edgeToEdgeEnabled: true,
    },
    web: {
      bundler: 'metro',
      output: 'static',
      favicon: './assets/images/favicon.png',
    },
    plugins: [
      'expo-router',
       "expo-web-browser",
      [
        'expo-splash-screen',
        {
          image: './assets/images/splash-icon.png',
          imageWidth: 200,
          resizeMode: 'contain',
          backgroundColor: '#ffffff',
        },
      ],
    ],
    experiments: { typedRoutes: true },
    extra: {
      orsApiKey: process.env.ORS_API_KEY, 
       API_URL: process.env.API_URL,
    },
  },
};
