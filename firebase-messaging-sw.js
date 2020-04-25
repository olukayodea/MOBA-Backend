importScripts('https://www.gstatic.com/firebasejs/7.14.1/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/7.14.1/firebase-messaging.js');

// Initialize the Firebase app in the service worker by passing in the
// messagingSenderId.
firebase.initializeApp({
   'messagingSenderId': '987446224980',
   'apiKey': 'AIzaSyAgGXpfWv4Be3Mwc7MwRQt9ULs73A77ryw',
   'projectId': 'moba-6a561', 
   'appId': '1:987446224980:web:78e9fdaf75d653f7eee5c1',
});

// Retrieve an instance of Firebase Messaging so that it can handle background
// messages.
const messaging = firebase.messaging();

messaging.setBackgroundMessageHandler(function(payload) {
  console.log('[firebase-messaging-sw.js] Received background message ', payload);
  // Customize notification here
  const notificationTitle = 'Background Message Title';
  const notificationOptions = {
    body: 'Background Message body.',
    icon: '/itwonders-web-logo.png'
  };

  return self.registration.showNotification(notificationTitle,
      notificationOptions);
});