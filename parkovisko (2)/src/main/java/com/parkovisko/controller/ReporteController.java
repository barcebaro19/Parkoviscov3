package com.parkovisko.controller;

import com.parkovisko.model.EstadoVehiculo;
import com.parkovisko.service.ReporteService;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpHeaders;
import org.springframework.http.MediaType;
import org.springframework.http.ResponseEntity;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/reportes")
@RequiredArgsConstructor
public class ReporteController {

    private final ReporteService reporteService;

    @GetMapping("/vehiculos")
    @PreAuthorize("hasAnyRole('ADMIN', 'VIGILANTE')")
    public ResponseEntity<byte[]> generarReporteVehiculos(
            @RequestParam(required = false) String marca,
            @RequestParam(required = false) String modelo,
            @RequestParam(required = false) EstadoVehiculo estado) {

        byte[] reporte = reporteService.generarReporteVehiculos(marca, modelo, estado);

        HttpHeaders headers = new HttpHeaders();
        headers.setContentType(MediaType.APPLICATION_PDF);
        headers.setContentDispositionFormData("filename", "reporte-vehiculos.pdf");

        return ResponseEntity.ok()
                .headers(headers)
                .body(reporte);
    }
} 