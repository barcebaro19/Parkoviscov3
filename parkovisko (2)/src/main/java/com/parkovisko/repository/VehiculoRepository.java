package com.parkovisko.repository;

import com.parkovisko.model.EstadoVehiculo;
import com.parkovisko.model.Usuario;
import com.parkovisko.model.Vehiculo;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import org.springframework.stereotype.Repository;

import java.util.List;
import java.util.Optional;

@Repository
public interface VehiculoRepository extends JpaRepository<Vehiculo, Long>, JpaSpecificationExecutor<Vehiculo> {
    Optional<Vehiculo> findByPlaca(String placa);
    List<Vehiculo> findByPropietario(Usuario propietario);
    List<Vehiculo> findByEstado(EstadoVehiculo estado);
    boolean existsByPlaca(String placa);
} 