import React, { createContext, useContext, useState, useEffect, useCallback } from 'react';
import { api } from '../lib/api';
import { clinicData } from '../data/clinic';

const ClinicContext = createContext(null);

export function ClinicProvider({ children }) {
  const [clinic, setClinic] = useState(clinicData);
  const [services, setServices] = useState(clinicData.services);
  const [doctors, setDoctors] = useState(clinicData.doctors);
  const [loading, setLoading] = useState(true);

  const fetchClinicData = useCallback(async () => {
    try {
      const [clinicRes, servicesRes, doctorsRes] = await Promise.all([
        api.getClinicInfo(),
        api.getServices(),
        api.getDoctors(),
      ]);

      if (clinicRes) {
        setClinic(clinicRes);
      }
      if (servicesRes && Array.isArray(servicesRes) && servicesRes.length > 0) {
        setServices(servicesRes);
      }
      if (doctorsRes && Array.isArray(doctorsRes) && doctorsRes.length > 0) {
        setDoctors(doctorsRes);
      }
    } catch (err) {
      console.warn('Gagal memuat data dinamis klinik, menggunakan fallback:', err);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchClinicData();
  }, [fetchClinicData]);

  return (
    <ClinicContext.Provider
      value={{
        clinic,
        services,
        doctors,
        loading,
        refreshClinic: fetchClinicData,
      }}
    >
      {children}
    </ClinicContext.Provider>
  );
}

export function useClinic() {
  const context = useContext(ClinicContext);
  if (!context) {
    throw new Error('useClinic must be used within a ClinicProvider');
  }
  return context;
}

