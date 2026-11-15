import React, { createContext, useContext, useState } from 'react';

const BookingContext = createContext(null);

export function BookingProvider({ children }) {
  const [bookingData, setBookingData] = useState({
    serviceId: null,
    doctorId: null,
    date: null, // format YYYY-MM-DD
    time: null, // format "10:00"
    patientName: '',
    patientPhone: '',
    patientNotes: '',
  });

  const [bookingResult, setBookingResult] = useState(null);

  const updateBooking = (fields) => {
    setBookingData((prev) => ({
      ...prev,
      ...fields
    }));
  };

  const resetBooking = () => {
    setBookingData({
      serviceId: null,
      doctorId: null,
      date: null,
      time: null,
      patientName: '',
      patientPhone: '',
      patientNotes: '',
    });
    setBookingResult(null);
  };

  return (
    <BookingContext.Provider
      value={{
        bookingData,
        updateBooking,
        bookingResult,
        setBookingResult,
        resetBooking,
      }}
    >
      {children}
    </BookingContext.Provider>
  );
}

export function useBooking() {
  const context = useContext(BookingContext);
  if (!context) {
    throw new Error('useBooking must be used within a BookingProvider');
  }
  return context;
}

